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

use block_credits\local\reason\credits_reason;
use block_credits\local\reason\orphan_reason;
use block_credits\local\reason\reason_with_location;
use core_user\fields;
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
class user_credits_txs_table extends \table_sql {

    /** @var int The page context ID. */
    protected $pagectxid;

    public function __construct($userid, $pagectxid, $creditid = null) {
        parent::__construct('block_credits_user_txs_' . $userid);
        $this->pagectxid = $pagectxid;

        $filters = ['t.userid = ?'];
        $filtersparams = [$userid];
        if ($creditid) {
            $filters[] = 't.creditid = ?';
            $filtersparams[] = $creditid;
        }

        $namefields = fields::for_name()->get_sql('u', false, '', '', false)->selects;
        $this->set_sql(
            "t.*, $namefields",
            '{block_credits_tx} t LEFT JOIN {user} u ON u.id = t.actinguserid',
            implode(' AND ', $filters),
            $filtersparams,
        );

        $columns = [
            'recordedon' => get_string('date', 'core'),
            'amount' => get_string('amount', 'block_credits'),
            'reason' => get_string('label', 'block_credits'),
            'ref' => '',
            'privatenote' => get_string('privatenote', 'block_credits'),
            'actinguserid' => get_string('actinguserid', 'block_credits'),
            'creditid' => 'CID',
        ];

        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        $nosortingcols = array_keys(array_diff_key($columns, ['recordedon' => true]));
        $this->sortable(true, 'recordedon', SORT_DESC);
        foreach ($nosortingcols as $nosortingcol) {
            $this->no_sorting($nosortingcol);
        }

        $this->collapsible(false);
    }

    public function col_actinguserid($row) {
        $name = null;
        if (!$row->actinguserid) {
            $name = get_string('systemuser', 'block_credits');
        } else if ($row->actinguserid != $row->userid) {
            $name = fullname($row);
        }

        if ($this->download || !$name) {
            return $name ?? '-';
        }

        return html_writer::link(new \moodle_url('/user/view.php', ['id' => $row->actinguserid]), $name);
    }

    public function col_amount($row) {
        if (empty($row->amount)) {
            return '0';
        }
        return $row->amount > 0 ? '+' . $row->amount : $row->amount;
    }

    public function col_reason($row) {
        global $OUTPUT;
        $reason = $this->get_reason_from_row($row);
        $desc = $reason->get_description()->out();
        return $OUTPUT->render_from_template('block_credits/tx_reason', [
            'desc' => $desc,
            'haspublicnote' => (bool) $row->publicnote,
            'publicnote' => $row->publicnote
        ]);
    }

    public function col_ref($row) {
        $location = null;
        $url = null;

        $reason = $this->get_reason_from_row($row);
        if ($reason instanceof reason_with_location) {
            $location = $reason->get_location_name();
            $url = $reason->get_url();
        }

        if (!$location) {
            return '';
        }
        if ($url) {
            return html_writer::link($url, s($location));
        }
        return s($location);
    }

    public function col_privatenote($row) {
        global $OUTPUT;
        if (!$row->privatenote) {
            return '-';
        }
        return html_writer::tag('button', $OUTPUT->render_from_template('block_credits/icon-note', []), [
            'class' => 'btn btn-link p-0 text-left text-body',
            'data-container' => 'body',
            'data-toggle' => 'popover',
            'data-content' => nl2br(s($row->privatenote)),
            'data-placement' => 'left',
            'data-html' => 'true'
        ]);
    }

    public function col_recordedon($row) {
        return userdate($row->recordedon, get_string('strftimedatetimeshortaccurate', 'core_langconfig'));
    }

    public function get_sort_columns() {
        $sortcols = parent::get_sort_columns();
        $dir = $sortcols['recordedon'] ?? SORT_DESC;
        return ['recordedon' => $dir, 'id' => $dir];
    }

    protected function get_reason_from_row($row) {
        if (!isset($row->_reason)) {
            $reason = null;
            $reasonargs = json_decode($row->reasonargs, true) ?: [];
            if ($row->component !== 'block_credits') {
                $reason = component_callback($row->component, 'block_credits_restore_reason', [$row->reasoncode, $reasonargs]);
                $reason = $reason ?? new orphan_reason($row->component, $row->reasoncode, $reasonargs, $row->reasondesc);
            } else {
                $reason = new credits_reason($row->reasoncode, $reasonargs);
            }
            $row->_reason = $reason;
        }
        return $row->_reason;
    }
}
