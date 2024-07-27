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
 * Import.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_credits\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir . '/formslib.php');

use core_text;
use csv_import_reader;
use moodleform;

/**
 * Import.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class import_form extends moodleform {

    /**
     * Form definintion.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        // CSV file.
        $mform->addElement('filepicker', 'csvfile', get_string('csvfile', 'block_credits'));
        $mform->addRule('csvfile', null, 'required');
        $mform->addHelpButton('csvfile', 'csvfile', 'block_credits');

        // Delimiter.
        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimname', get_string('csvfieldseparator', 'block_credits'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimname', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimname', 'semicolon');
        } else {
            $mform->setDefault('delimname', 'comma');
        }

        // File encoding.
        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'core_grades'), $choices);
        $mform->setDefault('encoding', 'UTF-8');

        $this->add_action_buttons(false, get_string('import', 'block_credits'));
    }

    /**
     * Get the data.
     *
     * @return stdClass
     */
    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }
        return $data;
    }

    /**
     * Validation.
     *
     * @param array $data The data.
     * @param array $files The files.
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $data = (object) $data;
        return $errors;
    }
}
