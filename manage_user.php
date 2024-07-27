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

use block_credits\manager;
use block_credits\output\user_credits_table;
use block_credits\output\user_credits_txs_table;

require('../../config.php');

$userid = required_param('id', PARAM_INT);
$contextid = optional_param('ctxid', SYSCONTEXTID, PARAM_INT);
$creditid = optional_param('creditid', null, PARAM_INT);
$view = optional_param('view', 'credits', PARAM_ALPHANUMEXT);

$context = \core\context::instance_by_id($contextid);

$baseparams = ['ctxid' => $contextid, 'id' => $userid];
$baseurl = new moodle_url('/blocks/credits/manage_user.php', $baseparams);

$pageparams = ['view' => $view];
if ($view === 'tx' && $creditid) {
    $pageparams += ['creditid' => $creditid];
}
$url = new moodle_url($baseurl, $pageparams);

$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url($url);

$manager = manager::instance();
$coursecontext = $context->get_course_context(false);
require_login($coursecontext ? $coursecontext->instanceid : null);
$manager->require_audit($context);

$user = core_user::get_user($userid, '*', MUST_EXIST);
$manager->require_audit_user($userid, $context);
$manager->check_for_expired_credits($userid);

$PAGE->set_title(fullname($user));
$PAGE->set_heading(format_string($COURSE->fullname));

echo $OUTPUT->header();

$backurl = new moodle_url('/blocks/credits/manage_users.php', ['ctxid' => $contextid]);
echo $OUTPUT->render_from_template('block_credits/page_header', [
    'backurl' => $backurl->out(false),
    'title' => fullname($user),
]);

echo html_writer::start_div('d-flex flex-row justify-content-between');
echo html_writer::start_div();
echo $OUTPUT->tabtree([
    new tabobject('credits', new moodle_url($baseurl, ['view' => 'credits']), get_string('credits', 'block_credits')),
    new tabobject('tx', new moodle_url($baseurl, ['view' => 'tx']), get_string('creditshistory', 'block_credits')),
], $view);
echo html_writer::end_div();
echo html_writer::start_div();
if ($manager->can_manage($context)) {
    echo html_writer::tag('button', get_string('addcredits', 'block_credits'), ['id' => 'addcreditsbtn',
        'class' => 'btn btn-primary', 'type' => 'button', 'data-userid' => $userid, 'data-pagectxid' => $contextid]);
    $PAGE->requires->js_call_amd('block_credits/modals', 'registerAddCreditButton', ['#addcreditsbtn']);
}
echo html_writer::end_div();
echo html_writer::end_div();


if ($view === 'tx') {

    if ($creditid) {
        $removefilterurl = new moodle_url($url);
        $removefilterurl->remove_params('creditid');
        echo html_writer::tag('p', get_string('transactionsfilteredforid', 'block_credits', $creditid)
            . ' ' . html_writer::link($removefilterurl, get_string('removefilter', 'block_credits')));
    }

    $table = new user_credits_txs_table($userid, $contextid, $creditid);
    $table->define_baseurl($url);
    $table->out(20, false);
} else {
    echo html_writer::start_div('block_credits-cancel-overflow'); // Else dropdown menu is cropped on some versions.
    $table = new user_credits_table($userid, $contextid, $manager->can_manage($context));
    $table->define_baseurl($url);
    $table->out(20, false);
    echo html_writer::end_div();

    $PAGE->requires->js_call_amd('block_credits/modals', 'delegateAdjustTotalButton', ['body', '[data-action=adjustotal]']);
    $PAGE->requires->js_call_amd('block_credits/modals', 'delegateChangeValidityButton', ['body', '[data-action=changevalidity]']);
    $PAGE->requires->js_call_amd('block_credits/modals', 'delegateExpireNowButton', ['body', '[data-action=expirenow]']);
}

echo $OUTPUT->footer();

