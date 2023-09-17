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

use block_credits\output\user_credits_table;
use block_credits\output\user_credits_txs_table;

require('../../config.php');

$userid = required_param('id', PARAM_INT);
$contextid = optional_param('ctxid', SYSCONTEXTID, PARAM_INT);
$view = optional_param('view', 'credits', PARAM_ALPHANUMEXT);

$context = context::instance_by_id($contextid);

$baseurl = new moodle_url('/blocks/credits/manage_user.php', ['ctxid' => $contextid, 'id' => $userid]);
$url = new moodle_url($baseurl, ['view' => $view]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url($url);

require_login();
# TODO Require permissions.

$user = core_user::get_user($userid, '*', MUST_EXIST);
$PAGE->set_title(fullname($user));
$PAGE->set_heading(fullname($user));

echo $OUTPUT->header();

echo html_writer::start_div('d-flex flex-row justify-content-between');
echo html_writer::start_div();
echo $OUTPUT->tabtree([
    new tabobject('credits', new moodle_url($baseurl, ['view' => 'credits']), get_string('credits', 'block_credits')),
    new tabobject('tx', new moodle_url($baseurl, ['view' => 'tx']), get_string('creditshistory', 'block_credits')),
], $view);
echo html_writer::end_div();
echo html_writer::start_div();
echo html_writer::tag('button', get_string('addcredits', 'block_credits'), ['id' => 'addcreditsbtn',
    'class' => 'btn btn-primary', 'type' => 'button', 'data-userid' => $userid]);
echo html_writer::end_div();
echo html_writer::end_div();

$PAGE->requires->js_call_amd('block_credits/modals', 'registerAddCreditButton', ['#addcreditsbtn']);

if ($view === 'tx') {
    $table = new user_credits_txs_table($userid);
    $table->define_baseurl($url);
    $table->out(20, false);
} else {
    $table = new user_credits_table($userid);
    $table->define_baseurl($url);
    $table->out(20, false);
}

echo $OUTPUT->footer();

