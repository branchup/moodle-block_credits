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
 * External function.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_credits\external;

use block_credits\local\note\static_note;
use block_credits\local\reason\credits_reason;
use block_credits\manager;
use context_system;
use core_date;
use DateTimeImmutable;
use external_api;
use external_function_parameters;
use external_value;

/**
 * External function.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class credit_user_for_purchase extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'userid' => new external_value(PARAM_INT),
            'quantity' => new external_value(PARAM_INT),
            'validuntil' => new external_value(PARAM_INT, 'Unix timestamp'),
            'reference' => new external_value(PARAM_RAW, '', VALUE_DEFAULT, ''),
        ]);
    }

    public static function execute($userid, $quantity, $validuntil, $reference) {
        $params = static::validate_parameters(static::execute_parameters(), [
            'userid' => $userid,
            'quantity' => $quantity,
            'validuntil' => $validuntil,
            'reference' => $reference,
        ]);
        $userid = $params['userid'];
        $quantity = $params['quantity'];
        $validuntil = $params['validuntil'];
        $reference = $params['reference'];

        $context = context_system::instance();
        static::validate_context($context);

        $manager = manager::instance();
        $manager->require_manage_user($userid, $context);

        $validuntildt = new DateTimeImmutable('@' . $validuntil);
        $reason = new credits_reason('reasonpurchase', [
            'validuntil' => $validuntildt->setTimezone(core_date::get_server_timezone_object())->format('Y-m-d')
        ]);
        $manager->credit_user($userid, $quantity, $validuntildt, $reason, new static_note($reference ?: null));

        return true;
    }

    public static function execute_returns() {
        return new external_value(PARAM_BOOL, '');
    }

}
