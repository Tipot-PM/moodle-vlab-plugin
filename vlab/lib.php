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
 * Mandatory public API of vlab module
 *
 * @package    mod_vlab
 * @copyright  2020 Ethan Lin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * List of features supported in vlab module
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function vlab_supports($feature) {
    switch($feature) {
        case FEATURE_GROUPS:
		    return false;
        case FEATURE_GROUPINGS:
		    return false;
        case FEATURE_MOD_INTRO:
		    return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
		    return true;
        case FEATURE_GRADE_HAS_GRADE:
		    return false;
        case FEATURE_GRADE_OUTCOMES:
		    return false;
        case FEATURE_BACKUP_MOODLE2:
		    return true;
        case FEATURE_SHOW_DESCRIPTION:
		    return true;
        default: 
		    return null;
    }
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function vlab_reset_userdata($data) {

    // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
    // See MDL-9367.

    return array();
}

/**
 * List the actions that correspond to a view of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = 'r' and edulevel = LEVEL_PARTICIPATING will
 *       be considered as view action.
 *
 * @return array
 */
function vlab_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List the actions that correspond to a post of this module.
 * This is used by the participation report.
 *
 * Note: This is not used by new logging system. Event with
 *       crud = ('c' || 'u' || 'd') and edulevel = LEVEL_PARTICIPATING
 *       will be considered as post action.
 *
 * @return array
 */
function vlab_get_post_actions() {
    return array('update', 'add');
}

/**
 * Add vlab instance.
 * @param object $data
 * @param object $mform
 * @return int new vlab instance id
 */
function vlab_add_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/vlab/locallib.php');

    $parameters = array();
    
    $data->parameters = serialize($parameters);

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);
	//$roomsArray = $data->rooms;
	//$data->rooms = serialize($roomsArray);

    $data->timemodified = time();
    $data->id = $DB->insert_record('vlab', $data);
	
	saveRoomUsage($data->id, $data->image, $data->rooms);
    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'vlab', $data->id, $completiontimeexpected);

    return $data->id;
}

/**
 * Update vlab instance.
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function vlab_update_instance($data, $mform) {
    global $CFG, $DB;

    require_once($CFG->dirroot.'/mod/vlab/locallib.php');

    $parameters = array();
    
    $data->parameters = serialize($parameters);

    $displayoptions = array();
    if ($data->display == RESOURCELIB_DISPLAY_POPUP) {
        $displayoptions['popupwidth']  = $data->popupwidth;
        $displayoptions['popupheight'] = $data->popupheight;
    }
    if (in_array($data->display, array(RESOURCELIB_DISPLAY_AUTO, RESOURCELIB_DISPLAY_EMBED, RESOURCELIB_DISPLAY_FRAME))) {
        $displayoptions['printintro']   = (int)!empty($data->printintro);
    }
    $data->displayoptions = serialize($displayoptions);
	
	//$roomsArray = $data->rooms;
	//$data->rooms = serialize($roomsArray);
	
    $data->timemodified = time();
    $data->id           = $data->instance;

    saveRoomUsage($data->id, $data->image, $data->rooms);
    $DB->update_record('vlab', $data);
	

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($data->coursemodule, 'vlab', $data->id, $completiontimeexpected);

    return true;
}

/**
 * Delete vlab instance.
 * @param int $id
 * @return bool true
 */
function vlab_delete_instance($id) {
    global $DB;

    
    if (!$vlab = $DB->get_record('vlab', array('id' => $id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('vlab', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'vlab', $id, null);

    // note: all context files are deleted automatically
	$room_id = $vlab->rooms;
    $DB->delete_records('vlab', array('id' => $vlab->id));

    deleteRoomUsageOnServer($room_id);
    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return cached_cm_info info
 */
function vlab_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/vlab/locallib.php");

    if (!$vlab = $DB->get_record('vlab', array('id' => $coursemodule->instance),
            'id, name, display, displayoptions, externalurl, image, parameters, intro, introformat, rooms')) {
        return null;
    }

    $info = new cached_cm_info();
    $info->name = $vlab->name;

	// note: there should be a way to differentiate links from normal resources
    $info->icon = vlab_guess_icon($vlab->externalurl, 24);

    $display = vlab_get_final_display_type($vlab);

    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $fullurl = "$CFG->wwwroot/mod/vlab/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($vlab->displayoptions) ? array() : unserialize($vlab->displayoptions);
        $width  = empty($options['popupwidth']) ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->onclick = "window.open('$fullurl', '', '$wh'); return false;";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $fullurl = "$CFG->wwwroot/mod/vlab/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->onclick = "window.open('$fullurl'); return false;";

    }

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $info->content = format_module_intro('vlab', $vlab, $coursemodule->id, false);
    }

    return $info;
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function vlab_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $modulepagetype = array('mod-vlab-*' => get_string('page-mod-vlab-x', 'vlab'));
    return $modulepagetype;
}

/**
 * Export vlab resource contents
 *
 * @return array of file content
 */
function vlab_export_contents($cm, $baseurl) {
    global $CFG, $DB;
    require_once("$CFG->dirroot/mod/vlab/locallib.php");
    $contents = array();
    $context = context_module::instance($cm->id);

    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $vlabrecord = $DB->get_record('vlab', array('id' => $cm->instance), '*', MUST_EXIST);

    $fullurl = str_replace('&amp;', '&', url_get_full_url($vlabrecord, $cm, $course));
    $isurl = clean_param($fullurl, PARAM_URL);
    if (empty($isurl)) {
        return null;
    }

    $vlab = array();
    $vlab['type'] = 'vlab';
    $vlab['filename']     = clean_param(format_string($vlabrecord->name), PARAM_FILE);
    $vlab['filepath']     = null;
    $vlab['filesize']     = 0;
    $vlab['fileurl']      = $fullurl;
    $vlab['timecreated']  = null;
    $vlab['timemodified'] = $vlabrecord->timemodified;
    $vlab['sortorder']    = null;
    $vlab['userid']       = null;
    $vlab['author']       = null;
    $vlab['license']      = null;
    $contents[] = $vlab;

    return $contents;
}

/**
 * Register the ability to handle drag and drop file uploads
 * @return array containing details of the files / types the mod can handle
 */
function vlab_dndupload_register() {
    return array();
}

/**
 * Handle a file that has been uploaded
 * @param object $uploadinfo details of the file / content that has been uploaded
 * @return int instance id of the newly created mod
 */
function vlab_dndupload_handle($uploadinfo) {
    // Gather all the required data.
    $data = new stdClass();
    $data->course = $uploadinfo->course->id;
    $data->name = $uploadinfo->displayname;
    $data->intro = '<p>'.$uploadinfo->displayname.'</p>';
    $data->introformat = FORMAT_HTML;
    
    $data->timemodified = time();

    // Set the display options to the site defaults.
    $config = get_config('vlab');
    $data->display = $config->display;
    $data->popupwidth = $config->popupwidth;
    $data->popupheight = $config->popupheight;
    $data->printintro = $config->printintro;
	$data->externalurl = $config->vlabserver;

    return vlab_add_instance($data, null);
}

/**
 * Mark the activity completed (if required) and trigger the course_module_viewed event.
 *
 * @param  stdClass $vlab        vlab object
 * @param  stdClass $course     course object
 * @param  stdClass $cm         course module object
 * @param  stdClass $context    context object
 * @since Moodle 3.0
 */
function vlab_view($vlab, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $vlab->id
    );

    $event = \mod_vlab\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('vlab', $vlab);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

/**
 * Check if the module has any update that affects the current user since a given time.
 *
 * @param  cm_info $cm course module data
 * @param  int $from the time to check updates from
 * @param  array $filter  if we need to check only specific updates
 * @return stdClass an object with the different type of areas indicating if they were updated or not
 * @since Moodle 3.2
 */
function vlab_check_updates_since(cm_info $cm, $from, $filter = array()) {
    $updates = course_check_module_updates_since($cm, $from, array('content'), $filter);
    return $updates;
}

/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @param int $userid ID override for calendar events
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_vlab_core_calendar_provide_event_action(calendar_event $event,
                                                       \core_calendar\action_factory $factory, $userid = 0) {

    global $USER;
    if (empty($userid)) {
        $userid = $USER->id;
    }

    $cm = get_fast_modinfo($event->courseid, $userid)->instances['vlab'][$event->instance];

    $completion = new \completion_info($cm->get_course());

    $completiondata = $completion->get_data($cm, false, $userid);

    if ($completiondata->completionstate != COMPLETION_INCOMPLETE) {
        return null;
    }

    return $factory->create_instance(
        get_string('view'),
        new \moodle_vlab('/mod/vlab/view.php', ['id' => $cm->id]),
        1,
        true
    );
}

function deleteRoomUsageOnServer($room_id)
{
	global $CFG;
	$config = get_config('vlab');
	$vlab_url = $config->vlabserver . '/instance/deleteRoomUsage/' . $room_id;
		
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $vlab_url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE");
   	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token ' . $config->license));
    $response = curl_exec( $ch );
	$results = json_decode($response, true);
    curl_close( $ch );
	
    return $results;
}
