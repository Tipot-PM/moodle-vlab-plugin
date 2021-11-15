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
 * Private vlab module utility functions
 *
 * @package    mod_vlab
 * @copyright  2020 Ethan Lin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/vlab/lib.php");

/**
 * This methods does weak vlab validation, we are looking for major problems only,
 * no strict RFE validation.
 *
 * @param $vlab
 * @return bool true is seems valid, false if definitely not valid vlab
 */
function vlab_appears_valid_vlab($vlab) {
    if (preg_match('/^(\/|https?:|ftp:)/i', $vlab)) {
        // note: this is not exact validation, we look for severely malformed vlabs only
        return (bool)preg_match('/^[a-z]+:\/\/([^:@\s]+:[^@\s]+@)?[a-z0-9_\.\-]+(:[0-9]+)?(\/[^#]*)?(#.*)?$/i', $vlab);
    } else {
        return (bool)preg_match('/^[a-z]+:\/\/...*$/i', $vlab);
    }
}

/**
 * Fix common vlab problems that we want teachers to see fixed
 * the next time they edit the resource.
 *
 * This function does not include any XSS protection.
 *
 * @param string $vlab
 * @return string
 */
function vlab_fix_submitted_vlab($vlab) {
    return $vlab;
}

/**
 * Return full vlab with all extra parameters
 *
 * This function does not include any XSS protection.
 *
 * @param string $vlab
 * @param object $cm
 * @param object $course
 * @param object $config
 * @return string vlab with & encoded as &amp;
 */
function vlab_get_full_url($vlab, $cm, $course, $config=null) {
	global $USER, $CFG;
    
	if (!$config) {
		$config = get_config('vlab');
	}
	
	$fullurl = $config->vlabserver . '/instance/instanceManagement/?';
	
	if ($course->idnumber != '')
	{
		$fullurl = $fullurl . '&courseId=' . $course->idnumber;
	}
	else{
		$fullurl = $fullurl . '&courseId=3000' . $course->id;
	}

	$fullurl = $fullurl . '&image_id=' . $vlab->image;
	$username = rawurlencode($USER->username);
	$fullurl = $fullurl . '&username=' . $username;
	$fullurl = $fullurl . '&webServer=' . $CFG->wwwroot;
	$rolename = get_user_role($USER->id, $course->id);
	$fullurl = $fullurl . '&role=' . $rolename;
	$fullurl = $fullurl . '&email=' . rawurlencode($USER->email);
	$fullurl = $fullurl . '&signature=' . get_vlab_signature();
    $fullurl = $fullurl . '&room_id=' . $vlab->rooms;
    // encode all & to &amp; entity
    $fullurl = str_replace('&', '&amp;', $fullurl);

	echo $fullurl;
    return $fullurl;
}

/**
 * Unicode encoding helper callback
 * @internal
 * @param array $matches
 * @return string
 */
function vlab_filter_callback($matches) {
    return rawurlencode($matches[0]);
}

/**
 * Print vlab header.
 * @param object $vlab
 * @param object $cm
 * @param object $course
 * @return void
 */
function vlab_print_header($vlab, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname.': '.$vlab->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($vlab);
    echo $OUTPUT->header();
}

/**
 * Print vlab heading.
 * @param object $vlab
 * @param object $cm
 * @param object $course
 * @param bool $notused This variable is no longer used.
 * @return void
 */
function vlab_print_heading($vlab, $cm, $course, $notused = false) {
    global $OUTPUT;
    echo $OUTPUT->heading(format_string($vlab->name), 2);
}

/**
 * Print vlab introduction.
 * @param object $vlab
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function vlab_print_intro($vlab, $cm, $course, $ignoresettings=false) {
    global $OUTPUT;

    $options = empty($vlab->displayoptions) ? array() : unserialize($vlab->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($vlab->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'vlabintro');
            echo format_module_intro('vlab', $vlab, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Display vlab frames.
 * @param object $vlab
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function vlab_display_frame($vlab, $cm, $course) {
    global $PAGE, $OUTPUT, $CFG;

    $frame = optional_param('frameset', 'main', PARAM_ALPHA);

    if ($frame === 'top') {
        $PAGE->set_pagelayout('frametop');
        vlab_print_header($vlab, $cm, $course);
        vlab_print_heading($vlab, $cm, $course);
        vlab_print_intro($vlab, $cm, $course);
        echo $OUTPUT->footer();
        die;

    } else {
        $config = get_config('vlab');
        $context = context_module::instance($cm->id);
        $exteurl = vlab_get_full_url($vlab, $cm, $course, $config);
        $navurl = "$CFG->wwwroot/mod/vlab/view.php?id=$cm->id&amp;frameset=top";
        $coursecontext = context_course::instance($course->id);
        $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));
        $title = strip_tags($courseshortname.': '.format_string($vlab->name));
        $framesize = $config->framesize;
        $modulename = s(get_string('modulename', 'vlab'));
        $contentframetitle = s(format_string($vlab->name));
        $dir = get_string('thisdirection', 'langconfig');

        $extframe = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html dir="$dir">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>$title</title>
  </head>
  <frameset rows="$framesize,*">
    <frame src="$navurl" title="$modulename"/>
    <frame src="$exteurl" title="$contentframetitle"/>
  </frameset>
</html>
EOF;

        @header('Content-Type: text/html; charset=utf-8');
        echo $extframe;
        die;
    }
}

/**
 * Print vlab info and link.
 * @param object $vlab
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function vlab_print_workaround($vlab, $cm, $course) {
    global $OUTPUT;

    vlab_print_header($vlab, $cm, $course);
    vlab_print_heading($vlab, $cm, $course, true);
    vlab_print_intro($vlab, $cm, $course, true);

    $fullurl = vlab_get_full_url($vlab, $cm, $course);

    $display = vlab_get_final_display_type($vlab);
    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $jsfullurl = addslashes_js($fullurl);
        $options = empty($vlab->displayoptions) ? array() : unserialize($vlab->displayoptions);
        $width  = empty($options['popupwidth']) ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $extra = "onclick=\"window.open('$jsfullurl', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $extra = "onclick=\"this.target='_blank';\"";

    } else {
        $extra = '';
    }

    echo '<div class="vlabworkaround">';
    print_string('clicktoopen', 'vlab', "<a href=\"$fullurl\" $extra>$fullurl</a>");
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}

/**
 * Display embedded vlab file.
 * @param object $vlab
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function vlab_display_embed($vlab, $cm, $course) {
    global $CFG, $PAGE, $OUTPUT;

    $fullurl  = vlab_get_full_url($vlab, $cm, $course);
	$mimetype = resourcelib_guess_url_mimetype($fullurl);
    $title    = $vlab->name;

    $link = html_writer::tag('a', $fullurl, array('href' => str_replace('&amp;', '&', $fullurl)));
    $clicktoopen = get_string('clicktoopen', 'vlab', $link);
    $moodlevlab = new moodle_url($fullurl);

    $extension = resourcelib_get_extension($fullurl);

    $mediamanager = core_media_manager::instance($PAGE);
    $embedoptions = array(
        core_media_manager::OPTION_TRUSTED => true,
        core_media_manager::OPTION_BLOCK => true
    );

    if (in_array($mimetype, array('image/gif' , 'image/jpeg' , 'image/png'))) {  // It's an image
        $code = resourcelib_embed_image($fullurl, $title);

    } else if ($mediamanager->can_embed_url($moodlevlab, $embedoptions)) {
        // Media (audio/video) file.
        $code = $mediamanager->embed_url($moodlevlab, $title, 0, 0, $embedoptions);

    } else {
        // anything else - just try object tag enlarged as much as possible
        $code = resourcelib_embed_general($fullurl, $title, $clicktoopen, $mimetype);
    }

    vlab_print_header($vlab, $cm, $course);
    vlab_print_heading($vlab, $cm, $course);

    echo $code;

    vlab_print_intro($vlab, $cm, $course);

    echo $OUTPUT->footer();
    die;
}

/**
 * Decide the best display format.
 * @param object $vlab
 * @return int display type constant
 */
function vlab_get_final_display_type($vlab) {
    global $CFG;

    if ($vlab->display != RESOURCELIB_DISPLAY_AUTO) {
        return $vlab->display;
    }

    // detect links to local moodle pages
    if (strpos($vlab->externalurl, $CFG->wwwroot) === 0) {
        if (strpos($vlab->externalurl, 'file.php') === false and strpos($vlab->externalurl, '.php') !== false ) {
            // most probably our moodle page with navigation
            return RESOURCELIB_DISPLAY_OPEN;
        }
    }

    static $download = array('application/zip', 'application/x-tar', 'application/g-zip',     // binary formats
                             'application/pdf', 'text/html');  // these are known to cause trouble for external links, sorry
    static $embed    = array('image/gif', 'image/jpeg', 'image/png', 'image/svg+xml',         // images
                             'application/x-shockwave-flash', 'video/x-flv', 'video/x-ms-wm', // video formats
                             'video/quicktime', 'video/mpeg', 'video/mp4',
                             'audio/mp3', 'audio/x-realaudio-plugin', 'x-realaudio-plugin',   // audio formats,
                            );

    $mimetype = resourcelib_guess_url_mimetype($vlab->externalurl);

    if (in_array($mimetype, $download)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    }
    if (in_array($mimetype, $embed)) {
        return RESOURCELIB_DISPLAY_EMBED;
    }

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}

/**
 * Get the parameters that may be appended to URL
 * @param object $config vlab module config options
 * @return array array describing opt groups
 */
function vlab_get_variable_options($config) {
    global $CFG;

    $options = array();
    $options[''] = array('' => get_string('chooseavariable', 'vlab'));

    $options[get_string('course')] = array(
        'courseid'        => 'id',
        'coursefullname'  => get_string('fullnamecourse'),
        'courseshortname' => get_string('shortnamecourse'),
        'courseidnumber'  => get_string('idnumbercourse'),
        'coursesummary'   => get_string('summary'),
        'courseformat'    => get_string('format'),
    );

    $options[get_string('modulename', 'vlab')] = array(
        'vlabinstance'     => 'id',
        'vlabcmid'         => 'cmid',
        'vlabname'         => get_string('name'),
        'vlabidnumber'     => get_string('idnumbermod'),
    );

    $options[get_string('miscellaneous')] = array(
        'sitename'        => get_string('fullsitename'),
        'serverurl'       => get_string('serverurl', 'vlab'),
        'currenttime'     => get_string('time'),
        'lang'            => get_string('language'),
    );
    
    $options[get_string('user')] = array(
        'userid'          => 'id',
        'userusername'    => get_string('username'),
        'useridnumber'    => get_string('idnumber'),
        'userfirstname'   => get_string('firstname'),
        'userlastname'    => get_string('lastname'),
        'userfullname'    => get_string('fullnameuser'),
        'useremail'       => get_string('email'),
        'usericq'         => get_string('icqnumber'),
        'userphone1'      => get_string('phone1'),
        'userphone2'      => get_string('phone2'),
        'userinstitution' => get_string('institution'),
        'userdepartment'  => get_string('department'),
        'useraddress'     => get_string('address'),
        'usercity'        => get_string('city'),
        'usertimezone'    => get_string('timezone'),
        'userurl'         => get_string('webpage'),
    );
    

    return $options;
}

/**
 * Get the parameter values that may be appended to URL
 * @param object $vlab module instance
 * @param object $cm
 * @param object $course
 * @param object $config module config options
 * @return array of parameter values
 */
function vlab_get_variable_values($vlab, $cm, $course, $config) {
    global $USER, $CFG;

    $site = get_site();

    $coursecontext = context_course::instance($course->id);

    $values = array (
        'courseid'        => $course->id,
        'coursefullname'  => format_string($course->fullname, true, array('context' => $coursecontext)),
        'courseshortname' => format_string($course->shortname, true, array('context' => $coursecontext)),
        'courseidnumber'  => $course->idnumber,
        'coursesummary'   => $course->summary,
        'courseformat'    => $course->format,
        'lang'            => current_language(),
        'sitename'        => format_string($site->fullname, true, array('context' => $coursecontext)),
        'serverurl'       => $CFG->wwwroot,
        'currenttime'     => time(),
        'vlabinstance'     => $vlab->id,
        'vlabcmid'         => $cm->id,
        'vlabname'         => format_string($vlab->name, true, array('context' => $coursecontext)),
        'vlabidnumber'     => $cm->idnumber,
    );

    if (isloggedin()) {
        $values['userid']          = $USER->id;
        $values['userusername']    = $USER->username;
        $values['useridnumber']    = $USER->idnumber;
        $values['userfirstname']   = $USER->firstname;
        $values['userlastname']    = $USER->lastname;
        $values['userfullname']    = fullname($USER);
        $values['useremail']       = $USER->email;
        $values['usericq']         = $USER->icq;
        $values['userphone1']      = $USER->phone1;
        $values['userphone2']      = $USER->phone2;
        $values['userinstitution'] = $USER->institution;
        $values['userdepartment']  = $USER->department;
        $values['useraddress']     = $USER->address;
        $values['usercity']        = $USER->city;
        $now = new DateTime('now', core_date::get_user_timezone_object());
        $values['usertimezone']    = $now->getOffset() / 3600.0; // Value in hours for BC.
        $values['userurl']         = $USER->url;
    }

   return $values;
}


/**
 * Optimised mimetype detection from general URL
 * @param $fullurl
 * @param int $size of the icon.
 * @return string|null mimetype or null when the filetype is not relevant.
 */
function vlab_guess_icon($fullurl, $size = null) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");

    $icon = file_extension_icon($fullurl, $size);
    $htmlicon = file_extension_icon('.htm', $size);
    $unknownicon = file_extension_icon('', $size);

    // We do not want to return those icon types, the module icon is more appropriate.
    if ($icon === $unknownicon || $icon === $htmlicon) {
        return null;
    }

    return $icon;
}

/**
 * Get list of images available from vlab server
 * @return array of imageid=>description pair.
 */
function vlab_get_image_options() {
    global $CFG;
	
	$results = getDataFromServer('/instance/getAllImages/');
    $options = array();
	$options[''] = get_string('chooseanimage', 'vlab');
	foreach($results as $image)
	{
		$options[$image['id']] = $image['description'];
	}	
	
	return $options;
}
function getTimeArray()
{

}

/**
 * Get list of available rooms from vlab server
 * @return array of imageid=>description pair.
 */
function vlab_get_room_options($activity_id = null) {
    global $CFG;
	$weekdayArray = array(0=>get_string('Monday', 'vlab'), 1=>get_string('Tuesday', 'vlab'), 2=>get_string('Wednesday', 'vlab'), 
                    3=>get_string('Thursday', 'vlab'), 4=>get_string('Friday', 'vlab'), 5=>get_string('Saturday', 'vlab'), 6=>get_string('Sunday', 'vlab'));
    $timeArray = array();
    for ($i=0; $i<=48; $i++)
    {
        if ($i % 2)
        {
            $timeArray[$i] = sprintf('%02d:30', $i/2);            
        }
        else
        {
            $timeArray[$i] = sprintf('%02d:00', $i/2);            
        }        
    }
    
	$result = getDataFromServer('/instance/rooms/');
	$options = array();
    $options[''] = get_string('choosearoom', 'vlab');
	foreach($result as $room)
	{
		if (($room['activityId'] == null) or ($room['activityId'] === $activity_id))
        {
            $description = $room['roomName'];
            $description .= ' ' . get_string('Capacity', 'vlab') . ': ' . $room['numOfInstances'];
            if ($room['roomType'] === 0)
            {
                $description .= ' ' . $weekdayArray[$room['day']];
                $description .= ' ' . $timeArray[$room['startTime']] . ' ' . get_string('to','vlab') . ' ' . $timeArray[$room['endTime']];
            }
            else
            {
                $description .= ' ' . get_string('always_open', 'vlab');
            }
            $options[$room['id']] = $description;
        }
	}	
	
    return $options;
}

/**
 * Get the current user role 
 * @param $userid
 * @param $courseid.
 * @return String Teacher or Student based on the role.
 */
function get_user_role($userid, $courseid)
{
	global $CFG, $USER, $DB;
	$sql = "SELECT ra.id as raid, ra.roleid, c.id as cid, c.path FROM {role_assignments} AS ra LEFT JOIN {context} AS c ON c.id = ra.contextid WHERE ra.userid = ? AND c.instanceid = ? order by roleid";
	$result = $DB->get_records_sql($sql, array($userid, $courseid ));
	$record = reset($result);
	$role = $record->{"roleid"};
	if (isset($USER->access))
	{
		if (count($USER->access['rsw']) != 0)
		{
			$assumedrole = $USER->access['rsw'][$record->{"path"}];
			$role = $assumedrole;
		}
	}

	if (strcmp($role, "5") < 0)
	{
		$role_name = 'Teacher';	
	}
	else{
		$role_name = 'Student';	
	}
	return $role_name;
}

function get_vlab_signature()
{
	$path = '/instance/restGetSignature/';
	$results = getDataFromServer($path);
    return $results;
}

/**
 * Save the room usage on vlab server
 * @param $activity_id: The activity which uses the rooms 
 * @param $image_id:
 * @param $room_id: 
 */
function saveRoomUsage($activity_id, $image_id, $room_id)
{
	$path = '/instance/saveRoomUsage/' . $room_id;
	$postdata = array(  'activityId' => $activity_id,
						'imageId'=>$image_id,
						);
    $results = postDataToServer($path, $postdata);
	
    return $results;
}

function deleteRoom($room_id)
{
	global $CFG;
	$config = get_config('vlab');
	$vlab_url = $config->vlabserver . '/instance/deleteRoom/' . $room_id;

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $vlab_url );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
   	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token ' . $config->license));
    $response = curl_exec( $ch );
	$results = json_decode($response, true);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close( $ch );
	
    if ($statusCode != 204)
    {
        throw new moodle_exception(get_string('serverError', 'vlab'));
    }

    if (array_key_exists('Error', $results) )
    {
        throw new moodle_exception($results['Error']);
    }
    
    return $response;
	
}

function getDataFromServer($path, $newLicense = null)
{
	global $CFG;
	$config = get_config('vlab');
	$vlab_url = $config->vlabserver . $path;
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_HTTPGET, true );
   	curl_setopt( $ch, CURLOPT_URL, $vlab_url );
 	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    if ($newLicense != null)
    {
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token ' . $newLicense));
    }
    else
    { 
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token ' . $config->license));
    }
	$response = curl_exec( $ch );
	$results = json_decode($response, true);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close( $ch );
    
    if ($statusCode != 200)
    {
        throw new moodle_exception(get_string('serverError', 'vlab'));
    }

    if (array_key_exists('Error', $results))
    {
        throw new moodle_exception($results['Error']);
    }
    
    return $results;
}
function postDataToServer($path, $postData)
{
	global $CFG;
	$config = get_config('vlab');
	$vlab_url = $config->vlabserver . $path;
	$requestBody = $postData;
	
	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_POST, true );
   	curl_setopt( $ch, CURLOPT_URL, $vlab_url );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $requestBody);
 	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token ' . $config->license));
    $response = curl_exec( $ch );
	$results = json_decode($response, true);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close( $ch );
	if ($statusCode != 200)
    {
        throw new moodle_exception(get_string('serverError', 'vlab'));
    }
    if (array_key_exists('Error', $results))
    {
        throw new moodle_exception($results['Error']);
    }

    return $results;
}

function get_room_data()
{
    /*$values = array('plan'=> array('num_of_instance' =>20 , 'num_of_snapshots' =>100),
                    'rooms'=> array( 'uuid1' => array('roomname'=>'Room 1', 'capacity'=>10, 'numOfSnapshot'=>5, 'day'=>1, 'startTime'=>0, 'endTime'=>24, 'activityId'=>'actUuid1'),
                                    'uuid2'=>array('roomname'=>'Room 2', 'capacity'=>20, 'numOfSnapshot'=>5, 'day'=>2, 'startTime'=>5, 'endTime'=>18),
                                    'uuid3'=> array('roomname'=>'Room 3', 'capacity'=>5, 'numOfSnapshot'=>5, 'day'=>3, 'startTime'=>2, 'endTime'=>14)));
	*/
    $rooms = getDataFromServer('/instance/rooms/');
    $roomArray = array();
    foreach($rooms as $room)
    {
        $roomArray[$room['id']] = $room;
    }

    $values = array('plan'=> getDataFromServer('/instance/getPlan/'),
                    'rooms'=> $roomArray);
	return $values;
}

function validateRoomUsage($rooms)
{
	return true;
}

function validate_license($license)
{
	global $CFG;
	$path = '/instance/validateLicense/';
	$results = getDataFromServer($path, $license);
    return $results['url'] === $CFG->wwwroot . '/';
}

function saveRoom($room)
{
	$path = '/instance/saveRoom/';
	$results = postDataToServer($path, $room);
	return $results;
}


