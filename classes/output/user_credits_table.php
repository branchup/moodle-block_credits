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
 * File.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_credits\output;

use core_date;
use core_user\fields;
use DateTimeImmutable;
use html_writer;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');

/**
 * Table.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_credits_table extends \table_sql {

    /** @var int The page context ID. */
    protected $pagectxid;

    public function __construct($userid) {
        global $DB;

        parent::__construct('block_credits_user_' . $userid);

        $this->set_sql(
            '*',
            '{block_credits}',
            'userid = ?',
            [$userid],
        );

        $columns = [
            'creditedon' => get_string('creditedon', 'block_credits'),
            'total' => get_string('total', 'block_credits'),
            'used' => get_string('used', 'block_credits'),
            'expired' => get_string('expired', 'block_credits'),
            'remaining' => get_string('available', 'block_credits'),
            'validuntil' => get_string('validuntil', 'block_credits'),
            'actions' => ''
        ];

        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        $this->sortable(true, 'creditedon', SORT_DESC);
        $this->collapsible(false);
    }

    public function col_creditedon($row) {
        $dt = (new DateTimeImmutable('@' . $row->creditedon))->setTimezone(core_date::get_user_timezone_object());
        return $dt->format('Y-m-d H:i');
    }

    public function col_validuntil($row) {
        $dt = (new DateTimeImmutable('@' . $row->validuntil))->setTimezone(core_date::get_user_timezone_object());
        return $dt->format('Y-m-d H:i');
    }

    public function col_actions($row) {
        global $OUTPUT;
        $actions = [];
        return implode(' ', $actions);
    }

}
