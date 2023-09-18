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
use context;
use core_date;
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

    public function require_manage_user($userid, context $context) {
        require_capability('block/credits:manage', $context);
        $coursecontext = $context->get_course_context(false);
        if ($coursecontext && $coursecontext->instanceid != SITEID && !is_enrolled($coursecontext, $userid)) {
            throw new \moodle_exception('cannotmanageuser', 'block_credits');
        }
    }

    public function check_for_expired_credits($userid = null) {
        global $DB;
        $filters = ['remaining > 0', 'validuntil < :now'];
        $params = ['now' => time()];
        if ($userid) {
            $filters[] = 'userid = :userid';
            $params['userid'] = $userid;
        }

        $bucketids = $DB->get_fieldset_select('block_credits', 'id', implode(' AND ', $filters), $params, 'validuntil ASC');
        foreach ($bucketids as $bucketid) {
            $transaction = $DB->start_delegated_transaction();
            $record = $DB->get_record('block_credits', ['id' => $bucketid]);
            $this->expire_credit_bucket($record, new credits_reason('reasonexpired'));
            $DB->commit_delegated_transaction($transaction);
        }
    }

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

    public function expire_credit_bucket($bucket, reason $reason, note $note = null) {
        global $DB, $USER;
        if ($bucket->remaining <= 0) {
            throw new \coding_exception('No credits to expire');
        }

        $transaction = $DB->start_delegated_transaction();

        $expiringcredits = (int) $bucket->remaining;
        $bucket->expired += $expiringcredits;
        $bucket->remaining = 0;
        $bucket->validuntil = min($bucket->validuntil, time()); // If we expire early, use current time.
        $DB->update_record('block_credits', $bucket);

        $reasondesc = $reason->get_description();
        if ($reasondesc instanceof \lang_string) {
            $reasondesc = $reasondesc->out('en');
        }
        $tx = (object) [
            'creditid' => $bucket->id,
            'userid' => $bucket->userid,
            'actinguserid' => $USER->id,
            'amount' => -$expiringcredits,
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

    public function extend_credit_bucket_validity($bucket, DateTimeImmutable $validuntil, note $note = null) {
        global $DB, $USER;
        if ($bucket->validuntil < time()) {
            throw new \coding_exception('Credit bucket already expired');
        } else if ($validuntil->getTimestamp() < $bucket->validuntil) {
            throw new \coding_exception('Cannot reduce validity');
        } else if ($validuntil->getTimestamp() < time()) {
            throw new \coding_exception('Cannot set validity in the past');
        }

        $transaction = $DB->start_delegated_transaction();

        $prevvaliduntil = new DateTimeImmutable('@' . $bucket->validuntil);
        $bucket->validuntil = $validuntil->getTimestamp();
        $DB->update_record('block_credits', $bucket);

        $reason = new credits_reason('reasonextended', [
            'from' => $prevvaliduntil->setTimezone(core_date::get_server_timezone_object())->format('Y-m-d'),
            'to' => $validuntil->setTimezone(core_date::get_server_timezone_object())->format('Y-m-d'),
        ]);
        $reasondesc = $reason->get_description();
        if ($reasondesc instanceof \lang_string) {
            $reasondesc = $reasondesc->out('en');
        }
        $tx = (object) [
            'creditid' => $bucket->id,
            'userid' => $bucket->userid,
            'actinguserid' => $USER->id,
            'amount' => 0,
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

    public function get_buckets_expiring_before($userid, \DateTimeImmutable $dt) {
        global $DB;
        return $DB->get_records_select('block_credits', 'remaining > 0 AND userid = ? AND validuntil <= ?',
            [$userid, $dt->getTimestamp()], 'validuntil ASC');
    }

    public function get_buckets_available($userid) {
        global $DB;
        return $DB->get_records_select('block_credits', 'remaining > 0 AND userid = ? AND validuntil >= ?',
            [$userid, time()], 'validuntil ASC');
    }

    public function get_buckets_unavailable($userid) {
        global $DB;
        return $DB->get_records_select('block_credits', 'userid = ? AND (remaining <= 0 OR validuntil < ?)',
            [$userid, time()], 'validuntil DESC');
    }

    public function has_ever_had_any_credits($userid) {
        global $DB;
        return $DB->record_exists_select('block_credits', 'userid = ?', [$userid]);
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
