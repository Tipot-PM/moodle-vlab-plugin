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
 * vlab module admin settings and defaults
 *
 * @package    mod_vlab
 * @copyright  2020 Ethan Lin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");
	require_once($CFG->dirroot.'/mod/vlab/admin_setting_vlab_license_configtext.php');
	require_once($CFG->dirroot.'/mod/vlab/admin_setting_vlab_server_value_configtext.php');
    set_config('coursebinenable', 0, 'tool_recyclebin');

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_POPUP,
														   RESOURCELIB_DISPLAY_OPEN,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_POPUP,
								   RESOURCELIB_DISPLAY_OPEN,
								   );

    // general settings -----------------------------------------------------------------------------------
    //$settings->add(new admin_setting_configtext('vlab/framesize',
        //get_string('framesize', 'vlab'), get_string('configframesize', 'vlab'), 130, PARAM_INT));
	$settings->add(new admin_setting_vlab_server_value_configtext('vlab/vlabserver',
        get_string('vlabserver', 'vlab'), get_string('configvlabserver', 'vlab'), get_string('defaultvlabserver', 'vlab')));
    $register_msg = get_string('clicktoregister', 'vlab');
    $link_label = get_string('registerlink', 'vlab');
    $str = $register_msg . '<a id="register_site" href="#" onclick="linkClick()" >' . $link_label . '</a>';
    $scriptString = '<script> 
                            function linkClick() { 
                                var serverField = document.getElementById("id_s_vlab_vlabserver");
                                var serverurl = serverField.value;
                                if (serverurl === "")
                                {
                                    alert("Server field is empty");                                    
                                }
                                else
                                {
                                    window.open(serverurl + "/registerMySite/?url=' . $CFG->wwwroot . '", "_blank");
                                }                                                                                                                      
                              }</script>';  
    //$settings->add(new admin_setting_heading('vlab', '', $str));    
    $settings->add(new admin_setting_vlab_license_configtext('vlab/license', get_string('license', 'vlab'), get_string('configlicense', 'vlab') . $str . $scriptString, ''));        
    $settings->add(new admin_setting_configmultiselect('vlab/displayoptions',
        get_string('displayoptions', 'vlab'), get_string('configdisplayoptions', 'vlab'),
        $defaultdisplayoptions, $displayoptions));
	
	$url = "$CFG->wwwroot/mod/vlab/manage_rooms.php";
    $link_value = get_string('clicktomanagerooms', 'vlab');
    $str = "<a href=\"$url\">$link_value</a>";
    $settings->add(new admin_setting_heading('vlabmodmanageroom', get_string('managerooms', 'vlab'),  $str));

	// modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('vlabmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('vlab/printintro',
        get_string('printintro', 'vlab'), get_string('printintroexplain', 'vlab'), 1));
    $settings->add(new admin_setting_configselect('vlab/display',
        get_string('displayselect', 'vlab'), get_string('displayselectexplain', 'vlab'), RESOURCELIB_DISPLAY_POPUP, $displayoptions));
    $settings->add(new admin_setting_configtext('vlab/popupwidth',
        get_string('popupwidth', 'vlab'), get_string('popupwidthexplain', 'vlab'), 1024, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('vlab/popupheight',
        get_string('popupheight', 'vlab'), get_string('popupheightexplain', 'vlab'), 768, PARAM_INT, 7));
    
}
