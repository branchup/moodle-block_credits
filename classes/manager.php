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
use core\message\message;
use core\uuid;
use core_collator;
use core_date;
use core_user;
use DateTimeImmutable;
use moodle_url;

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

    /**
     * Whether the current user can audit someone.
     *
     * We either have access to everyone, or manage permissions.
     *
     * @param \context|\core\context $context The context.
     */
    public function can_audit($context) {
        return has_any_capability(['block/credits:manage', 'block/credits:viewall'], $context);
    }

    /**
     * Whether the current user has manage permissions.
     *
     * @param \context|\core\context $context The context.
     */
    public function can_manage($context) {
        return has_capability('block/credits:manage', $context);
    }

    /**
     * Get the renderer.
     *
     * @return \block_credits\output\renderer
     */
    public function get_renderer() {
        global $PAGE;
        return $PAGE->get_renderer('block_credits');
    }

    /**
     * Requires current user to be allowed to audit someone.
     *
     * We either have access to everyone, or manage permissions.
     *
     * @param \context|\core\context $context The context.
     */
    public function require_audit($context) {
        if (!$this->can_audit($context)) {
            require_capability('block/credits:viewall', $context);
        }
    }

    /**
     * Requires current user to have audit permission over user.
     *
     * @param int $userid The user ID.
     * @param \context|\core\context $context The context.
     */
    public function require_audit_user($userid, $context) {
        $this->require_audit($context);
        $coursecontext = $context->get_course_context(false);
        if ($coursecontext && $coursecontext->instanceid != SITEID && !is_enrolled($coursecontext, $userid)) {
            throw new \moodle_exception('cannotaudituser', 'block_credits');
        }
    }

    /**
     * Requires current user to have manage permission.
     *
     * @param \context|\core\context $context The context.
     */
    public function require_manage($context) {
        require_capability('block/credits:manage', $context);
    }

    /**
     * Requires current user to have manage permission over user.
     *
     * @param int $userid The user ID.
     * @param \context|\core\context $context The context.
     */
    public function require_manage_user($userid, $context) {
        $this->require_manage($context);
        $coursecontext = $context->get_course_context(false);
        if ($coursecontext && $coursecontext->instanceid != SITEID && !is_enrolled($coursecontext, $userid)) {
            throw new \moodle_exception('cannotmanageuser', 'block_credits');
        }
    }

    /**
     * Adjust bucket total.
     *
     * @param int $bucketid The bucket ID.
     * @param int $newtotal The new total.
     * @param note|null $note The note.
     */
    public function adjust_bucket_total($bucketid, $newtotal, note $note = null) {
        global $DB, $USER;

        $bucket = $DB->get_record('block_credits', ['id' => $bucketid], '*', MUST_EXIST);
        if ($newtotal == $bucket->total) {
            return;
        }

        if ($bucket->validuntil < time()) {
            throw new \coding_exception('Bucket has expired');
        }
        if ($newtotal < $bucket->used + $bucket->expired) {
            throw new \coding_exception('Cannot reduce total below used/expired credits');
        }

        $transaction = $DB->start_delegated_transaction();
        $prevtotal = $bucket->total;
        $change = $newtotal - $bucket->total;
        $bucket->total = $newtotal;
        $bucket->remaining = max(0, $bucket->remaining + $change);
        $DB->update_record('block_credits', $bucket);

        $reason = new credits_reason('reasontotalchanged', [
            'from' => $prevtotal,
            'to' => $bucket->total,
        ]);
        $tx = (object) [
            'creditid' => $bucketid,
            'userid' => $bucket->userid,
            'actinguserid' => $USER->id,
            'amount' => $change,
            'component' => $reason->get_component(),
            'reasoncode' => $reason->get_code(),
            'reasonargs' => json_encode($reason->get_args()),
            'reasondesc' => static::get_static_reason_desc($reason),
            'publicnote' => ($note ? $note->get_public_note() : null) ?? '',
            'privatenote' => ($note ? $note->get_private_note() : null) ?? '',
            'recordedon' => time(),
        ];
        $DB->insert_record('block_credits_tx', $tx);
        $DB->commit_delegated_transaction($transaction);
    }

    /**
     * Change bucket validity.
     *
     * @param int $bucketid The credit bucket.
     * @param DateTimeImmutable $validuntil The date.
     * @param note|null $note The note.
     */
    public function change_bucket_validity($bucketid, DateTimeImmutable $validuntil, note $note = null) {
        global $DB, $USER;

        $bucket = $DB->get_record('block_credits', ['id' => $bucketid], '*', MUST_EXIST);
        if ($validuntil->getTimestamp() == $bucket->validuntil) {
            return;
        }

        $transaction = $DB->start_delegated_transaction();

        $prevvaliduntil = new DateTimeImmutable('@' . $bucket->validuntil);
        $bucket->validuntil = $validuntil->getTimestamp();
        $bucket->expirynoticestage = null; // Reset the notices sent.
        $DB->update_record('block_credits', $bucket);

        $reason = new credits_reason('reasonextended', [
            'from' => $prevvaliduntil->setTimezone(core_date::get_server_timezone_object())->format('Y-m-d'),
            'to' => $validuntil->setTimezone(core_date::get_server_timezone_object())->format('Y-m-d'),
        ]);
        $tx = (object) [
            'creditid' => $bucket->id,
            'userid' => $bucket->userid,
            'actinguserid' => $USER->id,
            'amount' => 0,
            'component' => $reason->get_component(),
            'reasoncode' => $reason->get_code(),
            'reasonargs' => json_encode($reason->get_args()),
            'reasondesc' => static::get_static_reason_desc($reason),
            'publicnote' => ($note ? $note->get_public_note() : null) ?? '',
            'privatenote' => ($note ? $note->get_private_note() : null) ?? '',
            'recordedon' => time(),
        ];
        $DB->insert_record('block_credits_tx', $tx);

        // If we set the value in the future, and credits had expired, restore them.
        if ($bucket->expired > 0 && $validuntil->getTimestamp() > time()) {
            $amount = $bucket->expired;
            $bucket->remaining += $amount;
            $bucket->expired = 0;
            $DB->update_record('block_credits', $bucket);

            // Transaction for the revival.
            $reason = new credits_reason('reasonrevived');
            $tx = (object) [
                'creditid' => $bucket->id,
                'userid' => $bucket->userid,
                'actinguserid' => $USER->id,
                'amount' => $amount,
                'component' => $reason->get_component(),
                'reasoncode' => $reason->get_code(),
                'reasonargs' => json_encode($reason->get_args()),
                'reasondesc' => static::get_static_reason_desc($reason),
                'publicnote' => ($note ? $note->get_public_note() : null) ?? '',
                'privatenote' => ($note ? $note->get_private_note() : null) ?? '',
                'recordedon' => time(),
            ];
            $DB->insert_record('block_credits_tx', $tx);
        }

        // If we set the value in the past, always ensure credits are expired.
        if ($validuntil->getTimestamp() <= time() && $bucket->remaining > 0) {
            $this->expire_credit_bucket($bucket, new credits_reason('reasonexpired'));
        }

        $DB->commit_delegated_transaction($transaction);
    }

    /**
     * Check for expired credits.
     *
     * This will expire credits that are past their expiry date if needed.
     *
     * @param int|int $userid The user ID
     */
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

    /**
     * Credit the user.
     *
     * @param int $userid The user ID.
     * @param int $amount The number of credits.
     * @param DateTimeImmutable $validuntil Valid until.
     * @param reason $reason The reason.
     * @param note|null $note The note.
     */
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

        $tx = (object) [
            'creditid' => $creditid,
            'userid' => $userid,
            'actinguserid' => $USER->id,
            'amount' => $amount,
            'component' => $reason->get_component(),
            'reasoncode' => $reason->get_code(),
            'reasonargs' => json_encode($reason->get_args()),
            'reasondesc' => static::get_static_reason_desc($reason),
            'publicnote' => ($note ? $note->get_public_note() : null) ?? '',
            'privatenote' => ($note ? $note->get_private_note() : null) ?? '',
            'recordedon' => time(),
        ];
        $DB->insert_record('block_credits_tx', $tx);
        $DB->commit_delegated_transaction($transaction);
    }

    /**
     * Expire a bucket of credits.
     *
     * @param object $bucket The credit bucket.
     * @param reason $reason The reason.
     * @param note|null $note The note.
     */
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

        $tx = (object) [
            'creditid' => $bucket->id,
            'userid' => $bucket->userid,
            'actinguserid' => $USER->id,
            'amount' => -$expiringcredits,
            'component' => $reason->get_component(),
            'reasoncode' => $reason->get_code(),
            'reasonargs' => json_encode($reason->get_args()),
            'reasondesc' => static::get_static_reason_desc($reason),
            'publicnote' => ($note ? $note->get_public_note() : null) ?? '',
            'privatenote' => ($note ? $note->get_private_note() : null) ?? '',
            'recordedon' => time(),
        ];
        $DB->insert_record('block_credits_tx', $tx);

        $DB->commit_delegated_transaction($transaction);

        $this->send_expired_notice_for_bucket($bucket);
    }

    /**
     * Get the number of available credits at time.
     *
     * @param int $userid The user ID.
     * @param \DateTimeImmutable $dt The time.
     * @return int
     */
    public function get_available_credits_at_time($userid, \DateTimeImmutable $dt) {
        global $DB;
        return (int) $DB->get_field_select('block_credits', 'COALESCE(SUM(remaining), 0)', 'userid = ? AND validuntil >= ?',
            [$userid, $dt->getTimestamp()]);
    }

    /**
     * Get the credits buckets expiring before a date.
     *
     * @param int $userid The user ID.
     * @param \DateTimeImmutable $dt The time.
     * @return object[]
     */
    public function get_buckets_expiring_before($userid, \DateTimeImmutable $dt) {
        global $DB;
        return $DB->get_records_select('block_credits', 'remaining > 0 AND userid = ? AND validuntil <= ?',
            [$userid, $dt->getTimestamp()], 'validuntil ASC');
    }

    /**
     * Get the credit buckets available.
     *
     * This returns the buckets sorted by closest validity to farthest.
     *
     * @param int $userid The user ID.
     * @param \DateTimeImmutable $dt The time at which the buckets must available.
     * @return object[]
     */
    public function get_buckets_available($userid, \DateTimeImmutable $dt = null) {
        global $DB;
        $validuntil = $dt ? $dt->getTimestamp() : time();
        return $DB->get_records_select('block_credits', 'remaining > 0 AND userid = ? AND validuntil >= ?',
            [$userid, $validuntil], 'validuntil ASC');
    }

    /**
     * Get the credit buckets unavailable.
     *
     * @param int $userid The user ID.
     * @return object[]
     */
    public function get_buckets_unavailable($userid) {
        global $DB;
        return $DB->get_records_select('block_credits', 'userid = ? AND (remaining <= 0 OR validuntil < ?)',
            [$userid, time()], 'validuntil DESC');
    }

    /**
     * Get the farthest date in the future for which credits are available.
     *
     * @param int $userid The user ID.
     * @return DateTimeImmutable|null $dt The date, or null.
     */
    public function get_farthest_available_credit_validity($userid) {
        global $DB;
        $ts = (int) $DB->get_field_select('block_credits', 'COALESCE(MAX(validuntil), 0)',
            'userid = ? AND validuntil >= ? AND remaining > 0', [$userid, time()]);
        return $ts ? new DateTimeImmutable('@' . $ts) : null;
    }

    /**
     * Whether the user ever had credits.
     *
     * @param int $userid The user ID.
     * @return bool
     */
    public function has_ever_had_any_credits($userid) {
        global $DB;
        return $DB->record_exists_select('block_credits', 'userid = ?', [$userid]);
    }

    /**
     * Refund from an operation ID.
     *
     * @param int $userid The user ID.
     * @param string $operationid The operation ID.
     * @param reason $reason The reason for the refund.
     */
    public function refund_from_operation_id($userid, $operationid, reason $reason) {
        global $DB, $USER;

        $operations = $DB->get_records('block_credits_tx', ['userid' => $userid, 'operationid' => $operationid],
            'recordedon ASC, id ASC', '*');

        if (empty($operations)) {
            throw new \coding_exception('No transactions found for operation ID');
        }

        $refundedexpired = 0;
        $refundedexpiringsoon = 0;

        $transaction = $DB->start_delegated_transaction();
        foreach ($operations as $op) {
            if ($op->amount >= 0) { // We don't revert credits that were added.
                continue;
            }

            $amount = abs($op->amount);
            $bucket = $DB->get_record('block_credits', ['id' => $op->creditid], '*', MUST_EXIST);
            $isexpired = $bucket->validuntil < time();
            $isexpiringsoon = $bucket->validuntil < (time() + WEEKSECS);

            // Cap to min and max in case the same operation is refunded more than once by accident.
            $bucket->used = max(0, $bucket->used - $amount);
            if ($isexpired) {
                $bucket->expired = min($bucket->total, $bucket->expired + $amount);
            } else {
                $bucket->remaining = min($bucket->total, $bucket->remaining + $amount);
            }

            $txs = [];
            $txs[] = (object) [
                'creditid' => $bucket->id,
                'userid' => $userid,
                'actinguserid' => $USER->id,
                'amount' => $amount,
                'component' => $reason->get_component(),
                'reasoncode' => $reason->get_code(),
                'reasonargs' => json_encode($reason->get_args()),
                'reasondesc' => static::get_static_reason_desc($reason),
                'recordedon' => time(),
                'privatenote' => "[System] Reverse TX {$op->id}.",
            ];

            if ($isexpired) {
                $expreason = new credits_reason('reasonrefundafterexpiry');
                $txs[] = (object) [
                    'creditid' => $bucket->id,
                    'userid' => $userid,
                    'actinguserid' => $USER->id,
                    'amount' => -$amount,
                    'component' => $expreason->get_component(),
                    'reasoncode' => $expreason->get_code(),
                    'reasonargs' => json_encode($expreason->get_args()),
                    'reasondesc' => static::get_static_reason_desc($expreason),
                    'recordedon' => time(),
                    'privatenote' => "[System] Reverse TX {$op->id}.",
                ];
            }

            // Capture the number of credits that we could not refund.
            if ($isexpired) {
                $refundedexpired += $amount;
            } else if ($isexpiringsoon) {
                $refundedexpiringsoon += $amount;
            }

            $DB->update_record('block_credits', $bucket);
            foreach ($txs as $tx) {
                $DB->insert_record('block_credits_tx', $tx);
            }
        }

        $DB->commit_delegated_transaction($transaction);

        if ($refundedexpired || $refundedexpiringsoon) {
            $url = new \moodle_url('/blocks/credits/manage_user.php', ['id' => $userid]);
            $user = core_user::get_user($userid, '*', IGNORE_MISSING);
            $content = markdown_to_html(get_string('messageexpiredrefund', 'block_credits', [
                'fullname' => $user ? fullname($user) : '?',
                'opid' => $operationid,
                'reason' => static::get_static_reason_desc($reason),
                'expired' => $refundedexpired,
                'expiringsoon' => $refundedexpiringsoon,
                'url' => $url->out(false),
            ]));
            $contentplain = html_to_text($content);

            $managers = get_users_by_capability(\core\context\system::instance(), 'block/credits:receivemanagernotifications');
            foreach ($managers as $manager) {
                $message = new message();
                $message->component = 'block_credits';
                $message->name = 'expiredrefund';
                $message->userfrom = core_user::get_noreply_user();
                $message->userto = $manager;
                $message->subject = get_string('messageexpiredrefundsubject', 'block_credits');
                $message->fullmessage = $contentplain;
                $message->fullmessageformat = FORMAT_PLAIN;
                $message->fullmessagehtml = $content;
                $message->notification = 1;
                message_send($message);
            }
        }
    }

    /**
     * Refund credits.
     *
     * @param int $userid The user ID.
     * @param int $quantity The quantity.
     * @param reason $reason The reason.
     * @param DateTimeImmutable|null $validasat The date at which credits were available.
     * @deprecated Since 1.2.0, use refund_from_operation_id instead.
     */
    public function refund_user_credits($userid, $quantity, reason $reason, DateTimeImmutable $validasat = null) {
        global $DB, $USER;

        if ($quantity <= 0) {
            throw new \coding_exception('Invalid number of credits to refund.');
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
                'reasondesc' => static::get_static_reason_desc($reason),
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
                'reasondesc' => static::get_static_reason_desc($reason),
                'recordedon' => time(),
            ];
            $DB->insert_record('block_credits_tx', $tx);

            $reason = new credits_reason('reasonrefundafterexpiry');
            $tx = (object) [
                'creditid' => $bucket->id,
                'userid' => $userid,
                'actinguserid' => $USER->id,
                'amount' => -$remainingtorefund,
                'component' => $reason->get_component(),
                'reasoncode' => $reason->get_code(),
                'reasonargs' => json_encode($reason->get_args()),
                'reasondesc' => static::get_static_reason_desc($reason),
                'recordedon' => time(),
            ];
            $DB->insert_record('block_credits_tx', $tx);
        }

        $DB->commit_delegated_transaction($transaction);
    }

    /**
     * Send the expiry notices.
     *
     * @return void
     */
    public function send_expiry_notices() {
        global $DB, $USER;

        $now = time();
        $stages = [
            (object) [
                'stage' => 7,
                'threshold' => $now + 7 * DAYSECS,
            ],
            (object) [
                'stage' => 30,
                'threshold' => $now + 30 * DAYSECS,
            ],
            (object) [
                'stage' => 90,
                'threshold' => $now + 90 * DAYSECS,
            ],
        ];

        // Fetch the buckets that are expiring before the threshold time, but have not expired yet.
        // Of course, those buckets must containing remaining credits, and we also must not have sent
        // a notice for this stage yet.
        core_collator::asort_objects_by_property($stages, 'threshold', core_collator::SORT_NUMERIC);
        foreach ($stages as $stage) {
            $sql = 'validuntil < :threshold
                AND validuntil > :now
                AND remaining > 0
                AND (expirynoticestage > :stage OR expirynoticestage IS NULL)';
            $records = $DB->get_records_select('block_credits', $sql, [
                'now' => time(),
                'stage' => $stage->stage,
                'threshold' => $stage->threshold,
            ]);

            foreach ($records as $bucket) {
                try {
                    $this->send_expiry_notice_for_bucket($bucket);
                } catch (\moodle_exception $e) {
                    debugging("An error occurred while sending the expiry for bucket {$bucket->id}.");
                    continue;
                }

                // Update the bucket to save the stage.
                $bucket->expirynoticestage = $stage->stage;
                $DB->update_record('block_credits', $bucket);

                // Record notice sent.
                $validuntil = new DateTimeImmutable('@' . $bucket->validuntil);
                $reason = new credits_reason('reasonexpirynoticesent', [
                    'credits' => $bucket->remaining,
                    'expiry' => $validuntil->setTimezone(core_date::get_server_timezone_object())->format('Y-m-d'),
                ]);
                $tx = (object) [
                    'creditid' => $bucket->id,
                    'userid' => $bucket->userid,
                    'actinguserid' => $USER->id,
                    'amount' => 0,
                    'component' => $reason->get_component(),
                    'reasoncode' => $reason->get_code(),
                    'reasonargs' => json_encode($reason->get_args()),
                    'reasondesc' => static::get_static_reason_desc($reason),
                    'recordedon' => time(),
                ];
                $DB->insert_record('block_credits_tx', $tx);
            }
        }
    }

    /**
     * Send an expiry notice for a bucket.
     *
     * @param object $bucket The bucket as from the database.
     */
    protected function send_expiry_notice_for_bucket($bucket) {
        $user = core_user::get_user($bucket->userid, '*', MUST_EXIST);

        $days = max(1, ceil(($bucket->validuntil - time()) / DAYSECS));
        $args = [
            'credits' => $bucket->remaining,
            'expirydate' => userdate($bucket->validuntil, get_string('strftimedate', 'core_langconfig'),
                core_date::get_user_timezone($user)),
            'wwwroot' => (new moodle_url('/'))->out(false),
            'days' => $days,
        ];
        $content = markdown_to_html(get_string('messageexpirynotice', 'block_credits', $args));
        $contentplain = html_to_text($content);

        $message = new message();
        $message->component = 'block_credits';
        $message->name = 'expirynotice';
        $message->userfrom = core_user::get_support_user();
        $message->userto = $user;
        $message->subject = get_string('messageexpirynoticesubject', 'block_credits', $args);
        $message->fullmessage = $contentplain;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = $content;
        $message->notification = 1;
        message_send($message);
    }

    /**
     * Send expired notice for a bucket.
     *
     * @param object $bucket The bucket as from the database.
     */
    protected function send_expired_notice_for_bucket($bucket) {
        $user = core_user::get_user($bucket->userid, '*', MUST_EXIST);

        $args = [
            'credits' => $bucket->expired,
            'wwwroot' => (new moodle_url('/'))->out(false),
        ];
        $content = markdown_to_html(get_string('messageexpirednotice', 'block_credits', $args));
        $contentplain = html_to_text($content);

        $message = new message();
        $message->component = 'block_credits';
        $message->name = 'expirynotice';
        $message->userfrom = core_user::get_support_user();
        $message->userto = $user;
        $message->subject = get_string('messageexpirednoticesubject', 'block_credits', $args);
        $message->fullmessage = $contentplain;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = $content;
        $message->notification = 1;
        message_send($message);
    }

    /**
     * Spend user credits.
     *
     * @param int $userid The user ID.
     * @param int $quantity The quantity.
     * @param reason $reason The reason.
     * @param DateTimeImmutable|null $validasat The date at which credits must be available.
     * @return string The operation ID.
     */
    public function spend_user_credits($userid, $quantity, reason $reason, DateTimeImmutable $validasat = null) {
        global $DB, $USER;

        if ($quantity <= 0) {
            throw new \coding_exception('Invalid number of credits to spend.');
        }

        $opid = uuid::generate();

        // Actual spending.
        $transaction = $DB->start_delegated_transaction();
        $buckets = $this->get_buckets_available($userid, $validasat);
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
                'reasondesc' => static::get_static_reason_desc($reason),
                'recordedon' => time(),
                'operationid' => $opid
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

        return $opid;
    }

    /**
     * Get a static version of the reason.
     *
     * This is a string that can be archived.
     *
     * @return string
     */
    public static function get_static_reason_desc(reason $reason) {
        $reasondesc = $reason->get_description();
        if ($reasondesc instanceof \lang_string) {
            $reasondesc = $reasondesc->out('en');
        }
        return $reasondesc;
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
