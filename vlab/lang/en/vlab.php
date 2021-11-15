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
 * Strings for component 'vlab', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    mod_vlab
 * @copyright  2020 Ethan Lin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['clicktoopen'] = 'Click {$a} link to open resource.';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['configframesize'] = 'When a web page or an uploaded file is displayed within a frame, this value is the height (in pixels) of the top frame (which contains the navigation).';
$string['configrolesinparams'] = 'Enable if you want to include localized role names in list of available parameter variables.';
$string['configvlabserver'] = 'Provide the URL for vLab virtual lab platform.';
$string['configsecretphrase'] = 'This secret phrase is used to produce encrypted code value that can be sent to some servers as a parameter.  The encrypted code is produced by an md5 value of the current user IP address concatenated with your secret phrase. ie code = md5(IP.secretphrase). Please note that this is not reliable because IP address may change and is often shared by different computers.';
$string['contentheader'] = 'Content';
$string['createvlab'] = 'Create a Virtual Lab';
$string['displayoptions'] = 'Available display options';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting, together with the URL file type and whether the browser allows embedding, determines how the URL is displayed. Options may include:

* Automatic - The best display option for the URL is selected automatically
* Embed - The URL is displayed within the page below the navigation bar together with the URL description and any blocks
* Open - Only the URL is displayed in the browser window
* In pop-up - The URL is displayed in a new browser window without menus or an address bar
* In frame - The URL is displayed within a frame below the navigation bar and URL description
* New window - The URL is displayed in a new browser window with menus and an address bar';
$string['displayselectexplain'] = 'Choose display type, unfortunately not all types are suitable for all URLs.';
$string['imageselect'] = 'Virtual machine image: ';
$string['imageselectwarning'] = 'Switching to a different image will delete all snapshots saved with the current image.';
$string['imageselect_help'] = 'Select an image from the drop-down list to use for virtual machines';
$string['chooseanimage'] = 'Choose a virtual machine image:';
$string['externalurl'] = 'External URL';
$string['framesize'] = 'Frame height';
$string['invalidstoredurl'] = 'Cannot display this resource, URL is invalid.';
$string['chooseavariable'] = 'Choose a variable...';
$string['indicator:cognitivedepth'] = 'URL cognitive';
$string['indicator:cognitivedepth_help'] = 'This indicator is based on the cognitive depth reached by the student in a URL resource.';
$string['indicator:cognitivedepthdef'] = 'URL cognitive';
$string['indicator:cognitivedepthdef_help'] = 'The participant has reached this percentage of the cognitive engagement offered by the URL resources during this analysis interval (Levels = No view, View)';
$string['indicator:cognitivedepthdef_link'] = 'Learning_analytics_indicators#Cognitive_depth';
$string['indicator:socialbreadth'] = 'URL social';
$string['indicator:socialbreadth_help'] = 'This indicator is based on the social breadth reached by the student in a URL resource.';
$string['indicator:socialbreadthdef'] = 'URL social';
$string['indicator:socialbreadthdef_help'] = 'The participant has reached this percentage of the social engagement offered by the URL resources during this analysis interval (Levels = No participation, Participant alone)';
$string['indicator:socialbreadthdef_link'] = 'Learning_analytics_indicators#Social_breadth';
$string['invalidurl'] = 'Entered URL is invalid';
$string['modulename'] = 'vLab';
$string['modulename_help'] = 'The vLab module enables a teacher to create a virtual lab room that provides on-demand virtual machines loaded with a course related image for enrolled students to conduct lab activities. An image determines the functionality of the virtual machines and can be selected from an approved image list.  

There are two display options for the vLab, opening in the current window or opening in a new window.';
$string['modulenameplural'] = 'vLabs';
$string['page-mod-vlab-x'] = 'Any vLab module page';
$string['parameterinfo'] = '&amp;parameter=variable';
$string['parametersheader'] = 'URL variables';
$string['parametersheader_help'] = 'Some internal Moodle variables may be automatically appended to the URL. Type your name for the parameter into each text box(es) and then select the required matching variable.';
$string['pluginadministration'] = 'vLab module administration';
$string['pluginname'] = 'vLab';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['printintro'] = 'Display vLab description';
$string['printintroexplain'] = 'Display virtual lab description below content? Some display types may not display description even if enabled.';
$string['privacy:metadata'] = 'The vLAB activity plugin does not store any personal data.';
$string['rolesinparams'] = 'Include role names in parameters';
$string['vlabserver'] = 'Virtual lab platform URL';
$string['search:activity'] = 'vLab';
$string['serverurl'] = 'vLab Platform URL';
$string['vlab:addinstance'] = 'Add a new vLab activity';
$string['vlab:view'] = 'View vLab';
$string['license'] = 'Product key';
$string['configlicense'] = 'Provide product key for this plugin<br>';
$string['license_empty'] = 'Product key is empty';
$string['invalidlicense'] = 'Product key is not valid or expired.';
$string['vlabserverdefaults'] = 'vLab server configurations';
$string['vlabserverdefaultshelp'] = 'Configure system settings on vLab server';
$string['numofinstance'] = 'Number of instance can be created';
$string['numofinstancehelp'] = 'Maximum number of instances can be created.' ;
$string['invalidnumofinstance'] = 'Number of instance setting is not valid.';
$string['keepalive'] = 'Number of days an instance can be kept on server.';
$string['keepalivehelp'] = 'Maximum number of days an instance can be kept without being deleted.';
$string['invalidkeepalive'] = 'Keep instance alive days setting is not valid.';
$string['keepsnapshot'] = 'Number of days a snapshot can be kept on server';
$string['keepsnapshothelp'] = 'Maximum number of days a snapshot can be kept without being deleted.';
$string['invalidkeepsnapshot'] = 'Keep snapshot days setting is not valid.';
$string['choosearoom'] = 'Please select a vLab room';
$string['roomselect'] = 'vLab room to perform this activity';
$string['roomselect_help'] = 'Help for select vLab room';
$string['defaultvlabserver'] = 'https://vlab.tipot.org';
$string['editroom'] = 'Edit Room:';
$string['newroom'] = 'New Room';
$string['roomName'] = 'Name';
$string['Capacity'] = 'Capacity';
$string['openingday'] =' Opening Day';
$string['starttime'] = 'Start Time';
$string['endtime'] = 'End Time';
$string['roomstatus'] = 'Status';
$string['room_assigned'] = 'Assigned';
$string['room_available'] = 'Available';
$string['action'] = 'Action';
$string['Cancel'] = 'Cancel';
$string['deleteroom'] = 'Delete room';
$string['deletethisroom'] = 'If a room is assigned, deleting the room will cause the activity using that room not functional. Do you want to proceed?';
$string['managerooms'] = 'Manage Rooms';
$string['clicktoaddnewroom'] = 'Click to add new room';
$string['clicktomanagerooms'] = 'Click to manage rooms';
$string['Sunday'] = 'Sunday';
$string['Monday'] = 'Monday';
$string['Tuesday'] = 'Tuesday';
$string['Wednesday'] = 'Wednesday';
$string['Thursday'] = 'Thursday';
$string['Friday'] = 'Friday';
$string['Saturday'] = 'Saturday';
$string['chooseaday'] = 'Choose a day...';
$string['chooseatime'] = 'Choose a time...';
$string['vlab:config'] = 'Configure vLab settings';
$string['to'] = 'to';
$string['Err_RoomNameEmpty'] = 'Error: Room name cannot be empty.';
$string['Err_CapacityMustBeInteger'] = 'Error: Room capacity must be integer value.';
$string['Err_TimeRequired'] = 'Error: Time is required.';
$string['Err_MaximumInstanceLimitReached'] = 'Error: Maximum instance limit reached.';
$string['Err_InvalidStartEndTime'] = 'Error: Invalid start or end time.';
$string['always_open'] = 'Opens all days';
$string['alwayOpen'] = 'Is always open';
$string['clicktoregister'] = 'Don\'t have the product key. Click ';
$string['registerlink'] = 'Register my site';
$string['serverError'] = "Server returns error.";