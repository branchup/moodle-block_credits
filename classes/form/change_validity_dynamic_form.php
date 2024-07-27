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
 * Form.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_credits\form;

use block_credits\local\note\static_note;
use block_credits\manager;
use context;
use core_date;
use core_form\dynamic_form;
use DateTimeImmutable;
use moodle_url;

/**
 * Form.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class change_validity_dynamic_form extends dynamic_form {

    /** @var stdClass|null Bucket, use get_bucket to read. */
    protected $bucket;

    /**
     * Definition.
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'creditid');
        $mform->setType('creditid', PARAM_INT);

        $mform->addElement('hidden', 'pagectxid');
        $mform->setType('pagectxid', PARAM_INT);

        $mform->addElement('date_selector', 'validuntil', get_string('validuntil', 'block_credits'));
        $mform->setDefault('validuntil', (new DateTimeImmutable('@' . $this->get_bucket()->validuntil))->getTimestamp());

        $mform->addElement('textarea', 'publicnote', get_string('publicnote', 'block_credits'), ['maxlength' => 255]);
        $mform->setType('publicnote', PARAM_RAW);

        $mform->addElement('textarea', 'privatenote', get_string('privatenote', 'block_credits'), ['maxlength' => 255]);
        $mform->setType('privatenote', PARAM_RAW);
    }

    /**
     * Get context.
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        return context::instance_by_id($this->get_page_context_id());
    }

    /**
     * Check permissions.
     *
     * @return void
     */
    protected function check_access_for_dynamic_submission(): void {
        $manager = manager::instance();
        $manager->require_manage_user($this->get_user_id(), $this->get_context_for_dynamic_submission());
    }

    /**
     * Process the form submission.
     *
     * @return mixed
     */
    public function process_dynamic_submission() {
        $data = $this->get_data();

        $validuntil = (new DateTimeImmutable('@' . $data->validuntil))
            ->setTimezone(core_date::get_server_timezone_object())
            ->setTime(23, 59, 59);

        $note = new static_note($data->publicnote, $data->privatenote);

        $manager = manager::instance();
        $manager->change_bucket_validity($this->get_bucket()->id, $validuntil, $note);

        return [
            'redirecturl' => (new moodle_url('/blocks/credits/manage_user.php',
                ['id' => $this->get_user_id(), 'ctxid' => $data->pagectxid]))->out(false),
        ];
    }

    /**
     * Load in existing data as form defaults.
     */
    public function set_data_for_dynamic_submission(): void {
        $creditid = $this->optional_param('creditid', 0, PARAM_INT);
        $data = [
            'pagectxid' => $this->get_page_context_id(),
            'creditid' => $creditid,
        ];
        $this->set_data($data);
    }

    /**
     * Returns url to set in $PAGE->set_url().
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        return new moodle_url('/blocks/credits/manage_user.php', ['id' => $this->get_user_id(),
            'ctxid' => $this->get_page_context_id()]);
    }

    /**
     * Get the bucket.
     */
    protected function get_bucket() {
        global $DB;
        if (!$this->bucket) {
            $creditid = $this->optional_param('creditid', 0, PARAM_INT);
            $this->bucket = $DB->get_record('block_credits', ['id' => $creditid], '*', MUST_EXIST);
        }
        return $this->bucket;
    }

    /**
     * Get the user ID.
     */
    protected function get_page_context_id() {
        return $this->optional_param('pagectxid', SYSCONTEXTID, PARAM_INT);
    }

    /**
     * Get the user ID.
     */
    protected function get_user_id() {
        return $this->get_bucket()->userid;
    }

    /**
     * Validation.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data)) {
            return $errors;
        }

        return $errors;
    }

}
