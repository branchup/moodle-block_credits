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
 * Settings.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\dataformat;

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');

admin_externalpage_setup('block_credits_export_txs');

$userfields = ['email', 'firstname', 'lastname'];
$userfieldssql = implode(', ', array_map(function($field) {
    return 'u.' . $field . ' AS u_' . $field;
}, $userfields));
$actinguserfieldssql = implode(', ', array_map(function($field) {
    return 'au.' . $field . ' AS au_' . $field;
}, $userfields));

$sql = "SELECT tx.*,
               c.total AS c_total,
               c.creditedon AS c_creditedon,
               c.validuntil AS c_validuntil,
               $userfieldssql,
               $actinguserfieldssql
          FROM {block_credits_tx} tx
          JOIN {block_credits} c
            ON c.id = tx.creditid
     LEFT JOIN {user} u
            ON u.id = tx.userid
     LEFT JOIN {user} au
            ON au.id = tx.userid
      ORDER BY tx.id ASC";

$headers = [
    'date',
    'user_id',
    'firstname',
    'lastname',
    'email',
    'amount',
    'reason',
    'public_note',
    'private_note',
    'acting_user_id',
    'acting_user_firstname',
    'acting_user_lastname',
    'acting_user_email',
    'acting_plugin',
    'reason_code',
    'reason_args',
    'tx_id',
    'tx_operation_id',
    'bucket_id',
    'bucket_total',
    'bucket_credited_on',
    'bucket_valid_until'
];

$recordset = $DB->get_recordset_sql($sql, []);
dataformat::download_data('credits-txs-' . date('Y-m-d'), 'csv', $headers, $recordset, function($record) {
    return [
        date('Y-m-d', $record->recordedon), // date
        $record->userid, // user_id
        $record->u_firstname ?? '-', // firstname
        $record->u_lastname ?? '-', // lastname
        $record->u_email ?? '-', // email
        $record->amount, // amount
        $record->reasondesc, // reason
        $record->publicnote, // public_note
        $record->privatenote, // private_note
        $record->actinguserid, // acting_user_id
        $record->au_firstname ?? '-', // acting_user_firstname
        $record->au_lastname ?? '-', // acting_user_lastname
        $record->au_email ?? '-', // acting_user_email
        $record->component, // acting_plugin
        $record->reasoncode, // reason_code
        $record->reasonargs, // reason_args
        $record->id,
        $record->operationid ?? '',
        $record->creditid,
        $record->c_total, // bucket_total
        date('Y-m-d', $record->c_creditedon), // bucket_credited_on
        date('Y-m-d', $record->c_validuntil), // bucket_valid_until
    ];
});
$recordset->close();
