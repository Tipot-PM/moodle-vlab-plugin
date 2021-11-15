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
 * vlab external API
 *
 * @package    mod_vlab
 * @category   external
 * @copyright  2020 Ethan Lin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * vlab external functions
 *
 * @package    mod_vlab
 * @category   external
 * @copyright  2020 Ethan Lin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_vlab_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_vlab_parameters() {
        return new external_function_parameters(
            array(
                'vlabid' => new external_value(PARAM_INT, 'vlab instance id')
            )
        );
    }

    /**
     * Trigger the course module viewed event and update the module completion status.
     *
     * @param int $vlabid the vlab instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_vlab($vlabid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/vlab/lib.php");

        $params = self::validate_parameters(self::view_vlab_parameters(),
                                            array(
                                                'vlabid' => $vlabid
                                            ));
        $warnings = array();

        // Request and permission validation.
        $vlab = $DB->get_record('vlab', array('id' => $params['vlabid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($vlab, 'vlab');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/vlab:view', $context);

        // Call the vlab/lib API.
        vlab_view($vlab, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function view_vlab_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

    /**
     * Describes the parameters for get_vlabs_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_vlabs_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of vlabs in a provided list of courses.
     * If no list is provided all vlabs that the user can view will be returned.
     *
     * @param array $courseids course ids
     * @return array of warnings and vlabs
     * @since Moodle 3.3
     */
    public static function get_vlabs_by_courses($courseids = array()) {

        $warnings = array();
        $returnedvlabs = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_vlabs_by_courses_parameters(), $params);

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            // Get the vlabs in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $vlabs = get_all_instances_in_courses("vlab", $courses);
            foreach ($vlabs as $vlab) {
                $context = context_module::instance($vlab->coursemodule);
                // Entry to return.
                $vlab->name = external_format_string($vlab->name, $context->id);

                $options = array('noclean' => true);
                list($vlab->intro, $vlab->introformat) =
                    external_format_text($vlab->intro, $vlab->introformat, $context->id, 'mod_vlab', 'intro', null, $options);
                $vlab->introfiles = external_util::get_area_files($context->id, 'mod_vlab', 'intro', false, false);

                $returnedvlabs[] = $vlab;
            }
        }

        $result = array(
            'vlabs' => $returnedvlabs,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_vlabs_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_vlabs_by_courses_returns() {
        return new external_single_structure(
            array(
                'vlabs' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Module id'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                            'course' => new external_value(PARAM_INT, 'Course id'),
                            'name' => new external_value(PARAM_RAW, 'VLAB name'),
                            'intro' => new external_value(PARAM_RAW, 'Summary'),
                            'introformat' => new external_format_value('intro', 'Summary format'),
                            'introfiles' => new external_files('Files in the introduction text'),
                            'externalurl' => new external_value(PARAM_RAW_TRIMMED, 'External URL'),
							'image' => new external_value(PARAM_RAW_TRIMMED, 'Image'),
                            'rooms' => new external_value(PARAM_RAW_TRIMMED, 'Room')
                            'display' => new external_value(PARAM_INT, 'How to display the vlab'),
                            'displayoptions' => new external_value(PARAM_RAW, 'Display options (width, height)'),
                            'parameters' => new external_value(PARAM_RAW, 'Parameters to append to the URL'),
                            'timemodified' => new external_value(PARAM_INT, 'Last time the vlab was modified'),
                            'section' => new external_value(PARAM_INT, 'Course section id'),
                            'visible' => new external_value(PARAM_INT, 'Module visibility'),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode'),
                            'groupingid' => new external_value(PARAM_INT, 'Grouping id'),
                        )
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }
}
