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
 * Reason.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_credits\local\reason;

/**
 * Orphan reason.
 *
 * Basic reason where the owning component is unknown.
 *
 * @package    block_credits
 * @copyright  2023 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class orphan_reason implements reason {

    /** @var string */
    protected $component;
    /** @var string */
    protected $code;
    /** @var array */
    protected $args;
    /** @var string */
    protected $description;

    public function __construct($component, $code, $args, $description) {
        $this->component = $component;
        $this->code = $code;
        $this->args = $args;
        $this->description = $description;
    }

    public function get_component() {
        return $this->component;
    }

    public function get_code() {
        return $this->code;
    }

    public function get_args() {
        return $this->args;
    }

    public function get_description() {
        return $this->description;
    }

}

