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

use block_credits\local\note\static_note;
use block_credits\local\reason\credits_reason;
use block_credits\manager;
use core\output\notification;

require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');

admin_externalpage_setup('block_credits_import');

$manager = manager::instance();

$form = new block_credits\form\import_form();
if ($data = $form->get_data()) {

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('importresults', 'block_credits'));

    core_php_time_limit::raise();
    raise_memory_limit(MEMORY_EXTRA);

    $iid = csv_import_reader::get_new_iid('block_credits_import');
    $cir = new csv_import_reader($iid, 'block_credits_import');
    $cir->load_csv_content(
        $form->get_file_content('csvfile'),
        $data->encoding,
        $data->delimname,
    );

    $error = $cir->get_error();
    if ($error) {
        echo $OUTPUT->notification($error, notification::NOTIFY_ERROR, false);
        echo $OUTPUT->footer();
        die();
    }

    $cir->init();
    $columns = $cir->get_columns();

    // Loop over the CSV lines.
    $line = 1;
    while ($rawline = $cir->next()) {
        $row = (object) array_combine($columns, $rawline);
        $line++;

        $userid = $row->userid ?? 0;
        $amount = $row->amount ?? 0;
        $user = core_user::get_user($userid, '*');
        if (!$userid || !$user || !$amount || $amount < 0) {
            echo $OUTPUT->notification(
                get_string('importlinenskippedmissing', 'block_credits', $line),
                notification::NOTIFY_WARNING,
                false
            );
            continue;
        }

        $validuntil = DateTimeImmutable::createFromFormat("Y-m-d", $row->validuntil,
            core_date::get_server_timezone_object())->setTime(23, 59, 59, 0);
        $note = new static_note($row->publicnote ?? null, $row->privatenote ?? null);

        echo $OUTPUT->notification(
            s(get_string('importingxfory', 'block_credits', [
                'name' => fullname($user),
                'amount' => $amount,
            ])),
            notification::NOTIFY_INFO,
            false
        );
        $manager->credit_user($userid, $amount, $validuntil, new credits_reason('reasonimported'), $note);
    }

    echo $OUTPUT->footer();
    die();

} else if ($form->is_cancelled()) {
    redirect($PAGE->url);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('importcredits', 'block_credits'));
$form->display();
echo $OUTPUT->footer();
