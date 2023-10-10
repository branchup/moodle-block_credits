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

use block_credits\manager;

/**
 * Language file.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_credits extends block_base {

    /**
     * init.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_credits');
    }

    /**
     * Hide the header.
     */
    public function hide_header() {
        return true;
    }

    /**
     * Get content.
     *
     * @return object The content.
     */
    public function get_content() {
        global $OUTPUT, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $manager = manager::instance();
        $manager->check_for_expired_credits($USER->id);

        $context = $this->page->context->get_course_context(false) ?: context_system::instance();

        $expiringbuckets = $manager->get_buckets_expiring_before($USER->id, new DateTimeImmutable('+45 days'));
        $mycreditsurl = new moodle_url('/blocks/credits/my.php', ['ctxid' => $context->id]);
        $manageurl = new moodle_url('/blocks/credits/manage_users.php', ['ctxid' => $context->id]);
        $canmanage = has_capability('block/credits:manage', $context);
        $haseverhadcredits = $manager->has_ever_had_any_credits($USER->id);

        $this->content = new stdClass();
        $this->content->footer = '';
        $this->content->text = $OUTPUT->render_from_template('block_credits/block', [
            'credits' => $manager->get_available_credits_at_time($USER->id, new DateTimeImmutable()),
            'hasexpiringcredits' => !empty($expiringbuckets),
            'expiringcredits' => array_values(array_map(function($bucket) {
                return [
                    'credits' => $bucket->remaining,
                    'validuntilhtml' => userdate_htmltime($bucket->validuntil,
                        get_string('strftimedatemonthabbr', 'core_langconfig')),
                ];
            }, $expiringbuckets)),
            'showmycreditslink' => $haseverhadcredits,
            'mycreditsurl' => $mycreditsurl->out(false),
            'canmanage' => has_capability('block/credits:manage', $this->page->context),
            'manageurl' => $manageurl->out(false),
            'showactions' => $haseverhadcredits || $canmanage,
        ]);

        return $this->content;
    }

    /**
     * Applicable formats.
     *
     * @return array of the pages where the block can be added.
     */
    public function applicable_formats() {
        return [
            'all' => true,
        ];
    }

}
