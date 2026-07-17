<?php
// https://moodledev.io/docs/apis/commonfiles/version.php
// This file is part of Moodle - http://moodle.org/
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
 * Version metadata for the mod_biblereader plugin.
 *
 * @package   mod_biblereader
 * @copyright 2024, Josh Jenney <josh@n2nministries.org>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2024030800; // xx branch
$plugin->requires = 2022041900;  // Moodle 4.0 required (note: does not like .00 added)
$plugin->supported = [400, 401];   // Moodle 4.0 series
// $plugin->incompatible = 311;   // Not available for Moodle 3.11.0 or earlier. (note: 401 does not like using [])
$plugin->component = 'mod_biblereader';
$plugin->maturity = MATURITY_ALPHA;
$plugin->release = '1.0.0';
/*
$plugin->dependencies = [
    'mod_forum' => 2022042100,  // mod_forum? why?
    'mod_data' => 2022042100    // mod_data? what?!
];
*/
