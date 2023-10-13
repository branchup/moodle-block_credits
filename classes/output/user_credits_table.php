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

use action_menu;
use action_menu_link;
use action_menu_link_secondary;
use core_date;
use core_user\fields;
use DateTimeImmutable;
use html_writer;
use moodle_url;

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

    /** @var bool Whether we can manage the user. */
    protected $canmanage;
    /** @var int The page context ID. */
    protected $pagectxid;

    public function __construct($userid, $pagectxid, $canmanage = true) {
        parent::__construct('block_credits_user_' . $userid);
        $this->pagectxid = $pagectxid;
        $this->canmanage = $canmanage;

        $this->set_sql(
            '*',
            '{block_credits}',
            'userid = ?',
            [$userid],
        );

        $columns = [
            'id' => 'CID',
            'creditedon' => get_string('creditedon', 'block_credits'),
            'status' => '',
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

        $this->column_class('actions', 'p-1');
    }

    public function col_creditedon($row) {
        return userdate($row->creditedon, get_string('strftimedatetimeshort', 'core_langconfig'));
    }

    public function col_validuntil($row) {
        return userdate($row->validuntil, get_string('strftimedatetimeshort', 'core_langconfig'));
    }

    public function col_status($row) {
        if ($row->validuntil <= time()) {
            return '<span class="badge badge-dark">' . get_string('expired', 'block_credits') . '</span>';
        } else if (!$row->remaining) {
            return '<span class="badge badge-secondary">' . get_string('used', 'block_credits') . '</span>';
        }
        return '<span class="badge badge-success">' . get_string('available', 'block_credits') . '</span>';
    }

    public function col_actions($row) {
        global $OUTPUT;

        $menu = new action_menu();
        $menu->prioritise = true;
        $icon = $OUTPUT->pix_icon('i/menu', get_string('edit'));
        $menu->set_menu_trigger($icon, 'btn btn-icon d-flex align-items-center justify-content-center block_credits-no-caret');

        $menu->add(new action_menu_link_secondary(new moodle_url('/blocks/credits/manage_user.php', [
            'id' => $row->userid,
            'creditid' => $row->id,
            'ctxid' => $this->pagectxid,
            'view' => 'tx'
        ]), null, get_string('transactions', 'block_credits')));

        if ($this->canmanage && $row->validuntil > time()) {
            $menu->add(new action_menu_link_secondary(new moodle_url('#'), null, get_string('extendvalidity', 'block_credits'), [
                'data-creditid' => $row->id,
                'data-pagectxid' => $this->pagectxid,
                'data-action' => 'extendvalidity',
            ]));
        }

        if ($this->canmanage && $row->remaining > 0) {
            $menu->add(new action_menu_link_secondary(new moodle_url('#'), null, get_string('expirenow', 'block_credits'), [
                'data-creditid' => $row->id,
                'data-pagectxid' => $this->pagectxid,
                'data-action' => 'expirenow',
            ]));
        }

        return $OUTPUT->render($menu);
    }

}
