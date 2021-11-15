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
// This file allows to manage the default behaviour of the display formats

require_once("../../config.php");
require_once($CFG->libdir.'/adminlib.php');
require_once("lib.php");
require_once($CFG->dirroot.'/mod/vlab/locallib.php');

$id   = optional_param('id', '', PARAM_ALPHANUMEXT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$confirm = optional_param('confirm', '', PARAM_ALPHANUMEXT);
$continueUrl = new moodle_url('/', ['redirect'=>0]);

$url = new moodle_url('/mod/vlab/manage_rooms.php');

$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
//admin_externalpage_setup('managemodules'); 

$form = data_submitted();

$room_data = get_room_data();
$plan_data = $room_data['plan'];
$rooms = $room_data['rooms'];
$formInvalid = false;
if ($form)
{
    if (!isset($form->confirm))
    {
        $validateResult = check_valid_data($room_data, $form);
        if ($validateResult === true)
        {
            $postData = array();
			if (isset($form->id))
            {
                $postData['id'] = $form->id;
			}
			$postData['roomName'] = $form->roomName;
			$postData['numOfInstances'] = $form->numOfInstances;
            if (isset($form->alwaysOpen))
            {
                $postData['roomType'] = $form->alwaysOpen;
            }
            else
            {
                $postData['roomType'] = '0';
            }
			$postData['day'] = $form->day;
			$postData['startTime'] = $form->startTime;
			$postData['endTime'] = $form->endTime;
				
            saveRoom($postData);
			redirect($url);
        }
        else
        {
            $formInvalid = true;
        }
    }    
}
$editurl = new moodle_url('/mod/vlab/manage_rooms.php', array('action'=>'edit'));
$deleteurl = new moodle_url('/mod/vlab/manage_rooms.php', array('action'=>'delete'));
$createurl = new moodle_url('/mod/vlab/manage_rooms.php', array('action'=>'edit'));;

$strmodulename = get_string("modulename", "vlab");
$weekdayArray = array(0=>get_string('Monday', 'vlab'), 1=>get_string('Tuesday', 'vlab'), 2=>get_string('Wednesday', 'vlab'), 
                    3=>get_string('Thursday', 'vlab'), 4=>get_string('Friday', 'vlab'), 5=>get_string('Saturday', 'vlab'), 6=>get_string('Sunday', 'vlab'));

$startTimeArray = array(0=>'00:00');
$endTimeArray = array();

for ($i=1; $i<48; $i++)
{
    if ($i % 2)
    {
        $startTimeArray[$i] = sprintf('%02d:30', $i/2);        
    }
    else
    {
        $startTimeArray[$i] = sprintf('%02d:00', $i/2);        
    }
    $endTimeArray[$i] = $startTimeArray[$i];
}
$endTimeArray[48] = '24:00';
if ($action || $formInvalid)
{
    if (($action === 'edit') || $formInvalid)
    {
        echo $OUTPUT->header();
        if ($id)
        {
            echo $OUTPUT->heading($strmodulename . ': ' . get_string("editroom", "vlab"));
            $roomToEdit = $rooms[$id];                               
        }
        else
        {
            echo $OUTPUT->heading($strmodulename . ': ' . get_string("newroom", "vlab"));
            $keys = array('roomName','numOfInstances','roomType','day','startTime','endTime');
            $roomToEdit = array_fill_keys($keys, '');
            $roomToEdit['roomType'] = 0;
        }
        echo html_writer::empty_tag('br');
        echo html_writer::start_tag('form', array('action' => 'manage_rooms.php', 'method' => 'post', 'id' => 'editroomform'));
        if ($id)
        {
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $id));
        }
        if ($form)
        {
            $roomToEdit['roomName'] = $form->roomName;
            $roomToEdit['numOfInstances'] = $form->numOfInstances;
            if (isset($form->alwaysOpen))
            {
                $roomToEdit['roomType'] = 1;
            }
            else
            {
                $roomToEdit['roomType'] = 0;
            }
            $roomToEdit['day'] = $form->day;
            $roomToEdit['startTime'] = $form->startTime;
            $roomToEdit['endTime'] = $form->endTime;
            if (isset($form->id))
            {
                echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $form->id));
            }
            echo '<div style="width:80%; text-align:center; color:red;">'. $validateResult . '</div>';
        }
        echo '<table width="80%" align="center" class="generalbox"><tr><td align="right">';
        echo html_writer::label(get_string("roomName", "vlab") . ': ', 'roomName');
        echo '</td><td>';
        echo html_writer::empty_tag('input', array('type' => 'text',
                                                    'id' => 'roomName',
                                                    'name' => 'roomName',
                                                    'size' => '50',
                                                    'value' => $roomToEdit['roomName']));
        echo '</td></tr><tr><td align="right">';                           
        echo html_writer::label(get_string("Capacity","vlab") . ': ', 'numOfInstances'); 
        echo '</td><td>';
        echo html_writer::empty_tag('input', array('type' => 'text',
                                                    'id' => 'numOfInstances',
                                                    'name' => 'numOfInstances',
                                                    'size' => '5',
                                                    'value' => $roomToEdit['numOfInstances']));
        echo '</td></tr><tr><td/><td align="left">';
        echo '<input type="checkbox" onclick="handleClick()" id="alwaysOpen" name="alwaysOpen" value="1" ';
        if  ($roomToEdit['roomType'] === 1)
        {
            echo 'checked >';
        }
        echo '<label for="alwaysOpen">' . get_string("alwayOpen", "vlab") . '</label></td></tr>';
        $scriptString = '<script> 
                            function handleClick() { 
                                var checkbox = document.getElementById("alwaysOpen");
                                var dayRow = document.getElementById("dayRow");
                                var startTimeRow = document.getElementById("startTimeRow");
                                var endTimeRow = document.getElementById("endTimeRow");
                                if (checkbox.checked)
                                {
                                    dayRow.style.visibility = "hidden";
                                    startTimeRow.style.visibility = "hidden";
                                    endTimeRow.style.visibility = "hidden";
                                }
                                else
                                {
                                    dayRow.style.visibility = "visible";
                                    startTimeRow.style.visibility = "visible";
                                    endTimeRow.style.visibility = "visible";
                                }   
                              }</script>'; 
        echo $scriptString;
        $display = 'style="visibility:visible;"';
        if ($roomToEdit['roomType'] === 1)
        {
            $display = 'style="visibility:hidden;"';
        }
        
        echo '<tr id="dayRow" ' . $display .'><td align="right">';
        echo html_writer::label(get_string("openingday", "vlab") . ': ', 'day');
        echo '</td><td>';
        echo html_writer::select($weekdayArray, 'day', $roomToEdit['day'], array('' => get_string('chooseaday', 'vlab')));
        echo '</td></tr><tr id="startTimeRow" ' . $display . '><td align="right">';
        echo html_writer::label(get_string("starttime", "vlab") . ': ', 'startTime');
        echo '</td><td>';
        echo html_writer::select($startTimeArray, 'startTime', $roomToEdit['startTime'], array('' => get_string('chooseatime', 'vlab')));
        echo '</td></tr><tr id="endTimeRow" ' . $display . '><td align="right">';
        echo html_writer::label(get_string("endtime", "vlab") . ': ', 'endTime');
        echo '</td><td>';
        echo html_writer::select($endTimeArray, 'endTime', $roomToEdit['endTime'], array('' => get_string('chooseatime', 'vlab')));
        echo '</td></tr></table><br/><div align="center">';
        echo html_writer::empty_tag('input', array('type' => 'submit',
                                                   'value' => 'Save',
                                                   'class'=>'btn btn-primary' ));
        
        echo html_writer::link($url, get_string('Cancel', 'vlab'),['class' => 'btn btn-secondary']);
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('form');
        echo $OUTPUT->footer();
    }
    else if ($action === 'delete')
    {
        echo $OUTPUT->header();
        echo $OUTPUT->heading($strmodulename . ': ' . get_string('deleteroom', 'vlab'));

        $optionsyes = array('id'=>$id, 'confirm'=>md5($id));
        $confirmurl = new moodle_url($url, $optionsyes);
        $deletebutton = new single_button($confirmurl, 'Delete', 'post');

        echo $OUTPUT->confirm(get_string('deletethisroom', 'vlab'), $deletebutton, $url);
        echo $OUTPUT->footer();      
    }
}
else if($confirm)
{
    deleteRoom($id);
	redirect($url); 
}
else
{
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strmodulename . ': ' . get_string('managerooms', 'vlab'));

    echo html_writer::empty_tag('br');
    $table = new html_table();
    $table->head = array(
            get_string("roomName", "vlab"),
            get_string("Capacity","vlab"),
            get_string("openingday", "vlab"),
            get_string("starttime", "vlab"),
            get_string("endtime", "vlab"),
            get_string("roomstatus", "vlab"),
            get_string("action", "vlab")
        );
    $table->align = array('center', 'center', 'center', 'center');
    $table->wrap = array(false,false,false,false);

    foreach ($rooms as $roomId=>$room)
    {
        $buttons = array();
        $editurl->param('id', $roomId);
        $deleteurl->param('id', $roomId);
        if (roomEditable($room))
        {
            $buttons[] = html_writer::link($editurl, $OUTPUT->pix_icon('t/edit', 'Edit'));
            $buttons[] = html_writer::link($deleteurl, $OUTPUT->pix_icon('t/delete', 'Delete'));
        }
        if ($room['roomType'] === 0)
        {
            $table->data[] = array(
                    $room['roomName'],
                    $room['numOfInstances'],
                    $weekdayArray[$room['day']],
                    $startTimeArray[$room['startTime']],
                    $endTimeArray[$room['endTime']],
                    ($room['activityId'] == null ? get_string('room_available', 'vlab'):get_string('room_assigned', 'vlab')),
                    implode(' ', $buttons)
            ); 
        }
        else
        {
            $table->data[] = array(
                $room['roomName'],
                $room['numOfInstances'],
                get_string('always_open', 'vlab'),
                '',
                '',
                ($room['activityId'] == null ? get_string('room_available', 'vlab'):get_string('room_assigned', 'vlab')),
                implode(' ', $buttons)
            ); 
        }       
    }
    echo html_writer::table($table);
    echo html_writer::link($createurl, get_string('clicktoaddnewroom', 'vlab') ,['class' => 'btn btn-primary ml-1']);
    echo $OUTPUT->continue_button($continueUrl);
    echo $OUTPUT->footer();        
}

function check_valid_data($all_data, $form)
{
    $plan_data = $all_data['plan'];
    $instanceLimit = $plan_data['num_of_instance'];
    $rooms = $all_data['rooms'];
    if ($form)
    {
        if (trim($form->roomName) === '')
        {
            return get_string('Err_RoomNameEmpty', 'vlab');
        }
        if (!is_numeric($form->numOfInstances))
        {
            return get_string('Err_CapacityMustBeInteger', 'vlab');
        }
        if (!preg_match("/^[0-9]+$/",$form->numOfInstances))
        {
            return get_string('Err_CapacityMustBeInteger', 'vlab');
        }
        if ($form->numOfInstances > $instanceLimit)
        {
            return get_string('Err_MaximumInstanceLimitReached', 'vlab');
        }
        if (!isset($form->alwaysOpen))
        {
            if (($form->startTime === '') || ($form->endTime === '') || ($form->day === ''))
            {
                return get_string('Err_TimeRequired', 'vlab');
            }
            if ($form->startTime >= $form->endTime)
            {
                return get_string('Err_InvalidStartEndTime', 'vlab');
            }
        }
        $instanceArray = array();
        for ($i = 0; $i < 7; $i++)
        {
            if (isset($form->alwaysOpen) && $form->alwaysOpen)
            {
                $instanceArray[$i] = array_fill(0, 48, intval($form->numOfInstances));
            }
            else
            {
                $instanceArray[$i] = array_fill(0, 48, 0);
            }
        }
        
        if (!isset($form->alwaysOpen))
        {
            for ($i = $form->startTime; $i< $form->endTime; $i++)
            {
                $instanceArray[$form->day][$i] += intval($form->numOfInstances);
            }
        }
        foreach($rooms as $room)
        {
            if ((!isset($form->id)) || ($form->id !== $room['id']))
            {
                if ($room['roomType'] === 1)
                {
                    for ($i = 0; $i < 7; $i++)
                    {
                        for ($j = 0; $j < 48; $j++)
                        {
                            $instanceArray[$i][$j] += $room['numOfInstances'];
                        }
                    }
                }
                else
                {
                    for ($i = $room['startTime']; $i< $room['endTime']; $i++)
                    {
                        $instanceArray[$room['day']][$i] += $room['numOfInstances'];
                    }
                }
            }
        }
        for ($i = 0; $i < 7; $i++)
        {
            for ($j = 0; $j <48; $j++)
            {
                if ($instanceArray[$i][$j] > $instanceLimit)
                {
                    return get_string('Err_MaximumInstanceLimitReached', 'vlab');
                }
            }
        }
    }
    return true;   
}
function roomEditable($room)
{
    if ($room['activityId'] == null)
    {
        return true;
    }
    if ($room['roomType'] === 1)
    {
        return false;
    }
    $now = new DateTime("now", core_date::get_server_timezone_object());
    $dayofweek = $now->format('N');
    if (intval($dayofweek) != ($room['day'] + 1))
    {
        return true;
    }
    $hour = $now->format('G');
    $minute = $now->format('i');
    $timeSlot = intval($hour) * 2;
    $timeSlot += intdiv(intval($minute), 30);
    
    return $timeSlot < $room['startTime'] || $timeSlot > $room['endTime'];

}