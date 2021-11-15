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
 * vlab module main user interface
 *
 * @package    mod_vlab
 * @copyright  2020 Ethan Lin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/mod/vlab/lib.php");
require_once("$CFG->dirroot/mod/vlab/locallib.php");
require_once($CFG->libdir . '/completionlib.php');

$id       = optional_param('id', 0, PARAM_INT);        // Course module ID
$u        = optional_param('u', 0, PARAM_INT);         // vlab instance id
$redirect = optional_param('redirect', 0, PARAM_BOOL);
$forceview = optional_param('forceview', 0, PARAM_BOOL);

if ($u) {  // Two ways to specify the module
    $vlab = $DB->get_record('vlab', array('id' => $u), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('vlab', $vlab->id, $vlab->course, false, MUST_EXIST);

} else {
    $cm = get_coursemodule_from_id('vlab', $id, 0, false, MUST_EXIST);
    $vlab = $DB->get_record('vlab', array('id' => $cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/vlab:view', $context);

// Completion and trigger events.
vlab_view($vlab, $course, $cm, $context);

$PAGE->set_url('/mod/vlab/view.php', array('id' => $cm->id));


$displaytype = vlab_get_final_display_type($vlab);
if ($displaytype == RESOURCELIB_DISPLAY_OPEN) {
    $redirect = true;
}

if ($redirect && !$forceview) {
    // coming from course page or vlab index page,
    // the redirection is needed for completion tracking and logging
    $fullurl = str_replace('&amp;', '&', vlab_get_full_url($vlab, $cm, $course));

    if (!course_get_format($course)->has_view_page()) {
        // If course format does not have a view page, add redirection delay with a link to the edit page.
        // Otherwise teacher is redirected to the external URL without any possibility to edit activity or course settings.
        $editvlab = null;
        if (has_capability('moodle/course:manageactivities', $context)) {
            $editvlab = new moodle_vlab('/course/modedit.php', array('update' => $cm->id));
            $edittext = get_string('editthisactivity');
        } else if (has_capability('moodle/course:update', $context->get_course_context())) {
            $editvlab = new moodle_vlab('/course/edit.php', array('id' => $course->id));
            $edittext = get_string('editcoursesettings');
        }
        if ($editvlab) {
            redirect($fullurl, html_writer::link($editvlab, $edittext)."<br/>".
                    get_string('pageshouldredirect'), 10);
        }
    }
    redirect($fullurl);
}

switch ($displaytype) {
    case RESOURCELIB_DISPLAY_EMBED:
        vlab_display_embed($vlab, $cm, $course);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        vlab_display_frame($vlab, $cm, $course);
        break;
    default:
        vlab_print_workaround($vlab, $cm, $course);
        break;
}
