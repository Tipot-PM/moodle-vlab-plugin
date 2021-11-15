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
 * Special admin setting for vlab license that validates with vlabserver.
 *
 * @package    vlab
 * @copyright  2020 Ethan Lin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/vlab/locallib.php');
 
class admin_setting_vlab_license_configtext extends admin_setting_configtext {

    /**
     * We need to validate license.
     *
     * @param string $data Form data.
     * @return string Empty when no errors.
     */
    public function validate($data) {

        $license = trim($data);
		if (empty($license))
		{
			return get_string('license_empty', 'vlab');
		}
		else
		{
			if (!validate_license($license))
			{
				return get_string('invalidlicense', 'vlab');
			}
		}
        return parent::validate($data);
    }
}
