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

defined('MOODLE_INTERNAL') || die();

use block_credits\local\note\static_note;
use block_credits\local\reason\credits_reason;
use block_credits\manager;
use context;
use context_system;
use core_date;
use core_form\dynamic_form;
use core_user;
use DateTimeImmutable;
use html_writer;
use moodle_url;

/**
 * Form.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class credit_user_dynamic_form extends dynamic_form {

    /**
     * Definition.
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'pagectxid');
        $mform->setType('pagectxid', PARAM_INT);

        $mform->addElement('hidden', 'lockuser');
        $mform->setType('lockuser', PARAM_BOOL);
        $lockuser = $this->optional_param('lockuser', false, PARAM_BOOL);

        if ($lockuser) {
            $user = core_user::get_user($this->optional_param('userid', 0, PARAM_INT), '*', MUST_EXIST);
            $mform->addElement('hidden', 'userid');
            $mform->setType('userid', PARAM_INT);
            $mform->addElement('static', 'fullname', get_string('fullname', 'core'), html_writer::div(fullname($user), 'mt-2'));

        } else {
            $attributes = [
                'multiple' => false,
                'ajax' => 'core_user/form_user_selector',
                'valuehtmlcallback' => function($userid) {
                    global $OUTPUT;

                    $context = \context_system::instance();
                    $fields = \core_user\fields::for_name()->with_identity($context, false);
                    $record = \core_user::get_user($userid, 'id' . $fields->get_sql()->selects);

                    $user = (object)[
                        'id' => $record->id,
                        'fullname' => fullname($record, has_capability('moodle/site:viewfullnames', $context)),
                        'extrafields' => [],
                    ];

                    foreach ($fields->get_required_fields([\core_user\fields::PURPOSE_IDENTITY]) as $extrafield) {
                        $user->extrafields[] = (object)[
                            'name' => $extrafield,
                            'value' => s($record->$extrafield)
                        ];
                    }

                    return $OUTPUT->render_from_template('core_user/form_user_selector_suggestion', $user);
                },
            ];
            $mform->addElement('autocomplete', 'userid', get_string('user', 'core'), [], $attributes);
            $mform->addRule('userid', null, 'required', null, 'client');
        }

        $mform->addElement('text', 'amount', get_string('credits', 'block_credits'));
        $mform->setType('amount', PARAM_INT);
        $mform->setDefault('amount', 10);

        $mform->addElement('date_selector', 'validuntil', get_string('validuntil', 'block_credits'));
        $mform->setDefault('validuntil', (new DateTimeImmutable("+1 year"))->getTimestamp());

        $mform->addElement('select', 'reasoncode', get_string('reason', 'block_credits'), [
            'reasonpurchase' => get_string('purchase', 'block_credits'),
            'reasonother' => get_string('other', 'block_credits'),
        ]);

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
        return context_system::instance();
    }

    /**
     * Check permissions.
     *
     * @return void
     */
    protected function check_access_for_dynamic_submission(): void {
        # TODO Capability check.
        // require_capability('local/callista:setactivitiesoptions', $this->get_context_for_dynamic_submission());
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

        $reason = new credits_reason($data->reasoncode, ['validuntil' => $validuntil->format('Y-m-d')]);
        $note = new static_note($data->publicnote, $data->privatenote);

        $manager = manager::instance();
        $manager->credit_user($data->userid, $data->amount, $validuntil, $reason, $note);

        return [
            'redirecturl' => (new moodle_url('/blocks/credits/manage_user.php',
                ['id' => $data->userid, 'pagectxid' => $data->pagectxid]))->out(false),
        ];
    }

    /**
     * Load in existing data as form defaults.
     */
    public function set_data_for_dynamic_submission(): void {
        $userid = $this->optional_param('userid', null, PARAM_INT);
        $pagectxid = $this->optional_param('pagectxid', SYSCONTEXTID, PARAM_INT);
        $lockuser = $this->optional_param('lockuser', false, PARAM_BOOL);
        $data = [
            'pagectxid' => $pagectxid,
            'userid' => $userid ?: null,
            'lockuser' => $lockuser,
        ];
        $this->set_data($data);
    }

    /**
     * Returns url to set in $PAGE->set_url().
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $userid = $this->optional_param('userid', 0, PARAM_INT);
        $pagectxid = $this->optional_param('pagectxid', SYSCONTEXTID, PARAM_INT);
        return new moodle_url('/blocks/credits/manage_user.php', ['id' => $userid, 'pagectxid' => $pagectxid]);
    }

    /**
     * Validation.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data)) {
            return $errors;
        }

        if (!core_user::is_real_user($data['userid'], true)) {
            $errors['userid'] = get_string('invaliddata', 'core_error');
        }
        if ($data['amount'] < 1) {
            $errors['amount'] = get_string('invaliddata', 'core_error');
        }
        if ($data['validuntil'] < time()) {
            $errors['validuntil'] = get_string('invaliddata', 'core_error');
        }

        return $errors;
    }

}
