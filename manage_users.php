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

use block_credits\output\users_table;

require('../../config.php');

$contextid = optional_param('ctxid', SYSCONTEXTID, PARAM_INT);
$query = optional_param('q', null, PARAM_RAW);

$context = context::instance_by_id($contextid);

$url = new moodle_url('/blocks/credits/manage_users.php', ['ctxid' => $contextid, 'query' => $query]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('userscredits', 'block_credits'));
$PAGE->set_heading(get_string('userscredits', 'block_credits'));
$PAGE->set_url($url);

require_login();

# TODO Require permissions.

echo $OUTPUT->header();

echo html_writer::start_div('d-flex flex-row justify-content-between');
echo html_writer::start_div();
echo $OUTPUT->render_from_template('core/search_input', [
    'action' => $url,
    'inputname' => 'q',
    'searchstring' => get_string('search', 'core'),
    'query' => $query,
    'extraclasses' => 'mb-3'
]);
echo html_writer::end_div();
echo html_writer::start_div();
echo html_writer::tag('button', get_string('addcredits', 'block_credits'), ['id' => 'addcreditsbtn',
    'class' => 'btn btn-primary', 'type' => 'button']);
echo html_writer::end_div();
echo html_writer::end_div();

$PAGE->requires->js_call_amd('block_credits/modals', 'registerAddCreditButton', ['#addcreditsbtn']);

$table = new users_table(['query' => $query, 'pagectxid' => $contextid]);
$table->define_baseurl($url);
$table->out(20, false);

echo $OUTPUT->footer();
