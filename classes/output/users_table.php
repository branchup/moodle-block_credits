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
class users_table extends \table_sql {

    /** @var int The page context ID. */
    protected $pagectxid;

    public function __construct($options = []) {
        global $DB;

        $pagectxid = $options['pagectxid'] ?? SYSCONTEXTID;
        parent::__construct('block_credits_users_ctx' . $pagectxid);
        $this->pagectxid = $pagectxid;

        $filterparams = [];
        $filters = ['1=1'];

        if (!empty($options['query'])) {
            $filterparams = [
                $DB->sql_like($DB->sql_concat('u.firstname', "' '", 'u.lastname'), ':namefilterfl', false, false),
                $DB->sql_like($DB->sql_concat('u.lastname', "' '", 'u.firstname'), ':namefilterlf', false, false),
            ];
            $namefilterparams = [
                'namefilterfl' => '%' . $DB->sql_like_escape($options['query']) . '%',
                'namefilterlf' => '%' . $DB->sql_like_escape($options['query']) . '%',
            ];
            $filters[] = '(' . implode(' OR ', $filterparams) . ')';
            $filterparams += $namefilterparams;
        }

        $namefields = fields::for_name()->get_sql('u', false, '', '', false)->selects;
        $this->set_sql(
            implode(', ', [
                "u.id",
                $namefields,
                "SUM(c.total) AS total",
                "SUM(c.remaining) AS remaining",
                "SUM(c.used) AS used",
                "SUM(c.expired) AS expired",
                "(SELECT MIN(c2.validuntil) FROM {block_credits} c2
                                           WHERE c2.userid = u.id AND c2.validuntil > :now) AS validity"
            ]),
            "{block_credits} c JOIN {user} u ON u.id = c.userid",
            implode(' AND ', $filters) . " GROUP BY u.id, $namefields",
            ['now' => time()] + $filterparams,
        );
        $this->set_count_sql('
            SELECT COUNT(DISTINCT c.userid)
              FROM {block_credits} c
              JOIN {user} u ON u.id = c.userid
             WHERE ' . implode(' AND ', $filters),
            $filterparams);

        $columns = [
            'fullname' => get_string('fullname', 'core'),
            'remaining' => get_string('available', 'block_credits'),
            'total' => get_string('total', 'block_credits'),
            'used' => get_string('used', 'block_credits'),
            'expired' => get_string('expired', 'block_credits'),
            'validity' => get_string('validuntil', 'block_credits'),
        ];

        $this->define_columns(array_keys($columns));
        $this->define_headers(array_values($columns));

        $this->sortable(true, 'firstname', SORT_ASC);
        $this->collapsible(false);
    }

    public function col_fullname($row) {
        $name = fullname($row, has_capability('moodle/site:viewfullnames', $this->get_context()));
        if ($this->download) {
            return $name;
        }
        $link = new \moodle_url('/blocks/credits/manage_user.php', ['id' => $row->id, 'ctxid' => $this->pagectxid]);
        return html_writer::link($link, $name);
    }

    public function col_validity($row) {
        if (!$row->validity) {
            return '-';
        }
        $dt = (new DateTimeImmutable('@' . $row->validity))->setTimezone(core_date::get_user_timezone_object());
        return $dt->format('Y-m-d H:i');
    }

}
