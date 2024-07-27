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
use block_credits\output\my_credits_txs_table;

require('../../config.php');

$contextid = optional_param('ctxid', SYSCONTEXTID, PARAM_INT);
$view = optional_param('view', 'credits', PARAM_ALPHANUMEXT);

$context = \core\context::instance_by_id($contextid);

$baseurl = new moodle_url('/blocks/credits/my.php', ['ctxid' => $contextid]);
$url = new moodle_url($baseurl, ['view' => $view]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url($url);
$PAGE->add_body_class('limitedwidth');

$coursecontext = $context->get_course_context(false);
require_login($coursecontext ? $coursecontext->instanceid : null);
require_capability('block/credits:view', $context);

$PAGE->set_pagelayout('incourse');
$PAGE->set_title(get_string('mycredits', 'block_credits'));
$PAGE->set_heading(format_string($COURSE->fullname));

$manager = manager::instance();
$manager->check_for_expired_credits($USER->id);

echo $OUTPUT->header();

if ($PAGE->has_secondary_navigation() && !$PAGE->secondarynav->has_children()) {
    $backurl = new moodle_url('/course/view.php', ['id' => $COURSE->id]);
    $backtext = get_string('backtocourse', 'block_credits');
    if ($COURSE->id == SITEID) {
        $backurl = new moodle_url('/my/');
        $backtext = get_string('backtodashboard', 'block_credits');
    }
    echo html_writer::div(html_writer::link($backurl, '&lt; ' . $backtext), 'small mb-2');
}

if ($view === 'tx') {
    $backurl = new moodle_url($baseurl, ['view' => 'credits']);
    echo $OUTPUT->render_from_template('block_credits/page_header', [
        'backurl' => $backurl->out(false),
        'title' => get_string('creditshistory', 'block_credits'),
    ]);

    $table = new my_credits_txs_table($USER->id, $contextid);
    $table->define_baseurl($url);
    $table->out(25, false);

    echo $OUTPUT->footer();
    die();
}

$availablebuckets = $manager->get_buckets_available($USER->id);
$unavailablebuckets = $manager->get_buckets_unavailable($USER->id);
$txurl = new moodle_url($baseurl, ['view' => 'tx']);

echo html_writer::start_div('d-flex flex-row justify-content-between');
echo html_writer::start_div();
echo $OUTPUT->heading(get_string('myongoingcredits', 'block_credits'), 3);
echo html_writer::end_div();
echo html_writer::start_div();
if ($manager->has_ever_had_any_credits($USER->id)) {
    echo html_writer::link($txurl, get_string('history', 'block_credits'), ['class' => 'btn btn-secondary btn-sm']);
}
echo html_writer::end_div();
echo html_writer::end_div();

if (!empty($availablebuckets)) {
    $table = new html_table();
    $table->head = [
        get_string("creditedon", 'block_credits'),
        get_string('total', 'block_credits'),
        get_string('used', 'block_credits'),
        get_string('available', 'block_credits'),
        get_string('validuntil', 'block_credits')
    ];
    foreach ($availablebuckets as $bucket) {
        $table->data[] = [
            userdate($bucket->creditedon, get_string('strftimedate', 'core_langconfig')),
            $bucket->total,
            $bucket->used,
            $bucket->remaining,
            userdate($bucket->validuntil, get_string('strftimedate', 'core_langconfig')),
        ];
    }
    echo html_writer::table($table);

} else {
    echo html_writer::tag('p', get_string('nocreditsavailableatm', 'block_credits'));
}

if (!empty($unavailablebuckets)) {
    echo $OUTPUT->heading(get_string('pastcredits', 'block_credits'), 3, ['class' => 'mt-3']);
    $table = new html_table();
    $table->head = [
        get_string("creditedon", 'block_credits'),
        get_string('total', 'block_credits'),
        get_string('used', 'block_credits'),
        get_string('expired', 'block_credits'),
        get_string('validuntil', 'block_credits')
    ];
    foreach ($unavailablebuckets as $bucket) {
        $table->data[] = [
            userdate($bucket->creditedon, get_string('strftimedate', 'core_langconfig')),
            $bucket->total,
            $bucket->used,
            $bucket->expired,
            userdate($bucket->validuntil, get_string('strftimedate', 'core_langconfig')),
        ];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();

