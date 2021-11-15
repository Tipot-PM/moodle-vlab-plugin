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
 * Special admin setting for vlab url that validates against empty string.
 *
 * @package    vlab
 * @copyright  2020 Ethan Lin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class admin_setting_vlab_server_value_configtext extends admin_setting_configtext {

    /**
     * We need to validate server url.
     *
     * @param string $data Form data.
     * @return string Empty when no errors.
     */
    public function validate($data) {

        $value = trim($data);
		if (empty($value))
		{
			return get_string('invalidurl', 'vlab');
		}
       return parent::validate($data);
    }
}
