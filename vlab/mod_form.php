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
 * vlab configuration form
 *
 * @package    mod_vlab
 * @copyright  2020 Ethan Lin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/vlab/locallib.php');

class mod_vlab_mod_form extends moodleform_mod {
    
    function definition() {
        global $CFG, $DB;
        $mform = $this->_form;

        $config = get_config('vlab');

        // -------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size' => '48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();
        $element = $mform->getElement('introeditor');
        $attributes = $element->getAttributes();
        $attributes['rows'] = 5;
        $element->setAttributes($attributes);
		
		$mform->addElement('hidden', 'externalurl');
		$mform->setType('externalurl', PARAM_RAW);
		$mform->setDefault('externalurl', $config->vlabserver);

        $scriptString = '<script> 
                            function showAlert() { alert("'.get_string('imageselectwarning', 'vlab').'");}</script>';
        $mform->addElement('html', $scriptString);
		$options = vlab_get_image_options();
        $attribute = array('onchange' => 'javascript:showAlert()');
        if ($this->current->instance) {
            $mform->addElement('select', 'image', get_string('imageselect', 'vlab'), $options, $attribute);
        } else {
            $mform->addElement('select', 'image', get_string('imageselect', 'vlab'), $options);
        }
		$mform->addRule('image', null, 'required', null, 'client');
        $mform->addHelpButton('image', 'imageselect', 'vlab');
		
		$roomOptions = vlab_get_room_options($this->current->id);
		$roomSelector = $mform->addElement('select', 'rooms', get_string('roomselect', 'vlab'), $roomOptions);
		$mform->addRule('rooms', null, 'required', null, 'client');
        $mform->addHelpButton('rooms', 'roomselect', 'vlab');
				
		$mform->addElement('hidden', 'license');
		$mform->setType('license', PARAM_RAW);
		$mform->setDefault('license', $config->license);
		
        // -------------------------------------------------------
        $mform->addElement('header', 'optionssection', get_string('appearance'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'vlab'), $options);
            $mform->setDefault('display', $config->display);
            $mform->addHelpButton('display', 'displayselect', 'vlab');
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'vlab'), array('size' => 3));
            if (count($options) > 1) {
                $mform->hideIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'vlab'), array('size' => 3));
            if (count($options) > 1) {
                $mform->hideIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_AUTO, $options) or
          array_key_exists(RESOURCELIB_DISPLAY_EMBED, $options) or
          array_key_exists(RESOURCELIB_DISPLAY_FRAME, $options)) {
            $mform->addElement('checkbox', 'printintro', get_string('printintro', 'vlab'));
            $mform->hideIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_POPUP);
            $mform->hideIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_OPEN);
            $mform->hideIf('printintro', 'display', 'eq', RESOURCELIB_DISPLAY_NEW);
            $mform->setDefault('printintro', $config->printintro);
        }

        // -------------------------------------------------------
        $this->standard_coursemodule_elements();

        // -------------------------------------------------------
        $this->add_action_buttons();
    }

    function data_preprocessing(&$defaultValues) {
        if (!empty($defaultValues['displayoptions'])) {
            $displayoptions = unserialize($defaultValues['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $defaultValues['printintro'] = $displayoptions['printintro'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $defaultValues['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $defaultValues['popupheight'] = $displayoptions['popupheight'];
            }
        }
		if(!empty($defaultValues['rooms'])){
			$rooms = $defaultValues['rooms'];
			$defaultValues['rooms'] = $rooms;
		}
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);

		$config = get_config('vlab');
		$vlabServer = $config->vlabserver;
			
        // Validating Entered vlab, we are looking for obvious problems only,
        // teachers are responsible for testing if it actually works.

        // This is not a security validation!! Teachers are allowed to enter "javascript:alert(666)" for example.

        // NOTE: do not try to explain the difference between vlab and URI, people would be only confused...
        $rooms = $data['rooms'];
		if (!validateRoomUsage($rooms)){
			$errors['rooms'] = get_string('roomOccupied', 'vlab');
		}			
        return $errors;
    }

	function get_upload_image_url() {
		global $USER, $CFG;
    
		$config = get_config('vlab');
			
		$fullurl = $config->vlabserver . '/instance/uploadImage/?';
	
		$username = rawurlencode($USER->username);
		$fullurl = $fullurl . 'username=' . $username;
				
		return $fullurl;
	}
}
