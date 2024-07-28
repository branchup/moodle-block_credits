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

namespace block_credits\output;

use action_menu;

/**
 * Renderer.
 *
 * @package    block_credits
 * @copyright  2024 Institut français du Japon
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {

    /**
     * Make a dropdown.
     *
     * @return action_menu
     */
    public function make_dropdown_menu() {
        $menu = new action_menu();
        $menu->prioritise = true;
        $icon = $this->pix_icon('i/menu', get_string('edit', 'core'));
        $menu->set_menu_trigger($icon, 'btn btn-icon d-flex align-items-center justify-content-center block_credits-no-caret');
        return $menu;
    }

}
