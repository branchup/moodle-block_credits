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
 * Upgrade.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade function.
 *
 * @param int $oldversion Old version.
 * @return true
 */
function xmldb_block_credits_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023102000) {

        // Define field operationid to be added to block_credits_tx.
        $table = new xmldb_table('block_credits_tx');
        $field = new xmldb_field('operationid', XMLDB_TYPE_CHAR, '64', null, null, null, null, 'recordedon');

        // Conditionally launch add field operationid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Credits savepoint reached.
        upgrade_block_savepoint(true, 2023102000, 'credits');
    }

    if ($oldversion < 2023102001) {

        // Define index operationid (not unique) to be added to block_credits_tx.
        $table = new xmldb_table('block_credits_tx');
        $index = new xmldb_index('operationid', XMLDB_INDEX_NOTUNIQUE, ['operationid']);

        // Conditionally launch add index operationid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Credits savepoint reached.
        upgrade_block_savepoint(true, 2023102001, 'credits');
    }

    if ($oldversion < 2024072703) {

        // Define field expirynoticestage to be added to block_credits.
        $table = new xmldb_table('block_credits');
        $field = new xmldb_field('expirynoticestage', XMLDB_TYPE_INTEGER, '2', null, null, null, null, 'validuntil');

        // Conditionally launch add field expirynoticestage.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Credits savepoint reached.
        upgrade_block_savepoint(true, 2024072703, 'credits');
    }

    if ($oldversion < 2024072704) {

        // Define index remainvalid (not unique) to be added to block_credits.
        $table = new xmldb_table('block_credits');
        $index = new xmldb_index('remainvalid', XMLDB_INDEX_NOTUNIQUE, ['remaining', 'validuntil']);

        // Conditionally launch add index remainvalid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Credits savepoint reached.
        upgrade_block_savepoint(true, 2024072704, 'credits');
    }

    return true;
}
