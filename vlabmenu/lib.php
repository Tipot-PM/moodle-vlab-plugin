<?php
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
 * Extend navigation to add new vlab menus.
 *
 * @package    local_vlabmenu
 * @author     2020 Ethan Lin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  2017 Carlos Escobedo <http://www.twitter.com/carlosagile>)
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extend Navigation block and add options
 *
 * @param global_navigation $navigation {@link global_navigation}
 * @return void
 */
function local_vlabmenu_extend_navigation(global_navigation $navigation) {
    global $PAGE, $CFG;
	if (has_capability('mod/vlab:config', context_system::instance()))
	{
		$vlablinknode = $PAGE->navigation->add(get_string('manageroom', 'local_vlabmenu'), new moodle_url("$CFG->wwwroot/mod/vlab/manage_rooms.php"), navigation_node::TYPE_CONTAINER, null, 'manageroom', new pix_icon('i/settings', ''));
		$vlablinknode->showinflatnavigation = true;		
	}
	return true;	
}

