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

$settings = new admin_category('block_credits_category', get_string('pluginname', 'block_credits'));

$settingspage = new admin_externalpage('block_credits_import', get_string('importcredits', 'block_credits'),
    new moodle_url('/blocks/credits/settings_import.php'));
$settings->add('block_credits_category', $settingspage);

$settingspage = new admin_externalpage('block_credits_export_txs', get_string('downloadtxs', 'block_credits'),
    new moodle_url('/blocks/credits/settings_export_txs.php'));
$settings->add('block_credits_category', $settingspage);

