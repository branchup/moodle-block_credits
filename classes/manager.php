<?php
// This file is part of a 3rd party created module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Manager.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_credits;

use block_credits\local\note\note;
use block_credits\local\reason\credits_reason;
use block_credits\local\reason\reason;
use DateTimeImmutable;

/**
 * Manager.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /** @var static The instance. */
    protected static $instance;

    public function credit_user($userid, $amount, DateTimeImmutable $validuntil, reason $reason, note $note = null) {
        global $DB, $USER;

        $transaction = $DB->start_delegated_transaction();
        $record = (object) [
            'userid' => $userid,
            'total' => $amount,
            'remaining' => $amount,
            'used' => 0,
            'expired' => 0,
            'creditedon' => time(),
            'validuntil' => $validuntil->getTimestamp()
        ];
        $creditid = $DB->insert_record('block_credits', $record);

        $reasondesc = $reason->get_description();
        if ($reasondesc instanceof \lang_string) {
            $reasondesc = $reasondesc->out('en');
        }
        $tx = (object) [
            'creditid' => $creditid,
            'userid' => $userid,
            'actinguserid' => $USER->id,
            'amount' => $amount,
            'component' => $reason->get_component(),
            'reasoncode' => $reason->get_code(),
            'reasonargs' => json_encode($reason->get_args()),
            'reasondesc' => $reasondesc,
            'publicnote' => ($note ? $note->get_public_note() : null) ?? '',
            'privatenote' => ($note ? $note->get_private_note() : null) ?? '',
            'recordedon' => time(),
        ];
        $DB->insert_record('block_credits_tx', $tx);
        $DB->commit_delegated_transaction($transaction);
    }

    public function get_available_credits_at_time($userid, \DateTimeImmutable $dt) {
        global $DB;
        return (int) $DB->get_field_select('block_credits', 'COALESCE(SUM(remaining), 0)', 'userid = ? AND validuntil >= ?',
            [$userid, $dt->getTimestamp()]);
    }

    public function refund_user_credits($userid, $quantity, reason $reason, DateTimeImmutable $validasat = null) {
        global $DB, $USER;

        if ($quantity <= 0) {
            throw new \coding_exception('Invalid number of credits to refund.');
        }

        // Generate the description.
        $reasondesc = $reason->get_description();
        if ($reasondesc instanceof \lang_string) {
            $reasondesc = $reasondesc->out('en');
        }

        // Prepare filters to find buckets where we can refund.
        $filters = [
            'userid = :userid',
            'used > 0',
            'validuntil > :now'
        ];
        $params = ['userid' => $userid, 'now' => time()];
        if ($validasat) {
            $filters[] = 'validuntil >= :validasat';
            $params['validasat'] = $validasat->getTimestamp();
        }

        // Do the actual refund.
        $transaction = $DB->start_delegated_transaction();
        $buckets = $DB->get_records_select('block_credits', implode(' AND ', $filters), $params, 'validuntil DESC');
        $remainingtorefund = $quantity;
        foreach ($buckets as $bucket) {
            $available = (int) $bucket->used;
            $localrefund = min($remainingtorefund, $available);

            $bucket->remaining += $localrefund;
            $bucket->used -= $localrefund;

            $tx = (object) [
                'creditid' => $bucket->id,
                'userid' => $userid,
                'actinguserid' => $USER->id,
                'amount' => $localrefund,
                'component' => $reason->get_component(),
                'reasoncode' => $reason->get_code(),
                'reasonargs' => json_encode($reason->get_args()),
                'reasondesc' => $reasondesc,
                'recordedon' => time(),
            ];

            $DB->update_record('block_credits', $bucket);
            $DB->insert_record('block_credits_tx', $tx);

            $remainingtorefund = max(0, $remainingtorefund - $localrefund);
            if ($remainingtorefund <= 0) {
                break;
            }
        }

        if ($remainingtorefund > 0) {
            $now = time();
            $bucket = (object) [
                'userid' => $userid,
                'total' => $remainingtorefund,
                'remaining' => 0,
                'used' => 0,
                'expired' => $remainingtorefund,
                'creditedon' => $now,
                'validuntil' => $now
            ];
            $bucket->id = $DB->insert_record('block_credits', $bucket);

            $tx = (object) [
                'creditid' => $bucket->id,
                'userid' => $userid,
                'actinguserid' => $USER->id,
                'amount' => $remainingtorefund,
                'component' => $reason->get_component(),
                'reasoncode' => $reason->get_code(),
                'reasonargs' => json_encode($reason->get_args()),
                'reasondesc' => $reasondesc,
                'recordedon' => time(),
            ];
            $DB->insert_record('block_credits_tx', $tx);

            $reason = new credits_reason('reasonrefundafterexpiry');
            $reasondesc = $reason->get_description();
            if ($reasondesc instanceof \lang_string) {
                $reasondesc = $reasondesc->out('en');
            }
            $tx = (object) [
                'creditid' => $bucket->id,
                'userid' => $userid,
                'actinguserid' => $USER->id,
                'amount' => -$remainingtorefund,
                'component' => $reason->get_component(),
                'reasoncode' => $reason->get_code(),
                'reasonargs' => json_encode($reason->get_args()),
                'reasondesc' => $reasondesc,
                'recordedon' => time(),
            ];
            $DB->insert_record('block_credits_tx', $tx);
        }

        $DB->commit_delegated_transaction($transaction);
    }

    public function spend_user_credits($userid, $quantity, reason $reason) {
        global $DB, $USER;

        if ($quantity <= 0) {
            throw new \coding_exception('Invalid number of credits to spend.');
        }

        // Generate the description.
        $reasondesc = $reason->get_description();
        if ($reasondesc instanceof \lang_string) {
            $reasondesc = $reasondesc->out('en');
        }

        // Actual spending.
        $transaction = $DB->start_delegated_transaction();
        $buckets = $DB->get_records_select('block_credits', 'userid = ? AND remaining > 0 AND validuntil > ?',
            [$userid, time()], 'validuntil ASC');
        $remainingtospend = $quantity;
        foreach ($buckets as $bucket) {
            $available = (int) $bucket->remaining;
            $localspend = min($remainingtospend, $available);

            // Note that this is non-atomic operation.
            $bucket->used += $localspend;
            $bucket->remaining -= $localspend;

            $tx = (object) [
                'creditid' => $bucket->id,
                'userid' => $userid,
                'actinguserid' => $USER->id,
                'amount' => -$localspend,
                'component' => $reason->get_component(),
                'reasoncode' => $reason->get_code(),
                'reasonargs' => json_encode($reason->get_args()),
                'reasondesc' => $reasondesc,
                'recordedon' => time(),
            ];

            $DB->update_record('block_credits', $bucket);
            $DB->insert_record('block_credits_tx', $tx);

            $remainingtospend = max(0, $remainingtospend - $localspend);
            if ($remainingtospend <= 0) {
                break;
            }
        }

        // Whoops, we didn't have enough!
        if ($remainingtospend > 0) {
            throw new \moodle_exception('notenoughtcredits', 'block_credits', null, [
                'required' => $quantity,
                'available' => $quantity - $remainingtospend
            ]);
        }

        $DB->commit_delegated_transaction($transaction);
    }

    /**
     * Get the manager.
     *
     * @return static
     */
    public static function instance() {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

}
