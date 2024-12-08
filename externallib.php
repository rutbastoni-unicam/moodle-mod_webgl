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

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;

/**
 * External webgl API
 *
 * @package    mod_webgl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_webgl_external extends external_api
{
    /**
     * Mark this game loaded and seen by the current user
     *
     * @param int $webglid
     * @return bool
     */
    public static function signal_game_loaded(int $webglid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/webgl/lib.php");

        $params = self::validate_parameters(self::signal_game_loaded_parameters(), [
            'webglid' => $webglid
        ]);

        // Request and permission validation.
        $webgl = $DB->get_record('webgl', array('id' => $params['webglid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($webgl, 'webgl');

        //TODO check if validate_contex is enough to ensure the user is enrolled in this course and if not, implement the check
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        //SET THE GAME AS LOADED
        $completion = new completion_info($course);
        $completion->set_module_viewed($cm);

        // Trigger course_module_viewed event.
        webgl_view($course, $cm, $context, $webgl);

//        // Get achievements record
//        $webgl_achievement = $DB->get_record('webgl_achievements', ['webgl' => $params['webglid'], 'userid' => $USER->id]);

        return true;
    }

    /**
     * Defines the parameters for the signal_game_loaded method
     *
     * @return external_function_parameters
     */
    public static function signal_game_loaded_parameters() {
        return new external_function_parameters(
            [
                'webglid' => new external_value(PARAM_INT, 'The game to mark as loaded by the user')
            ]
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_value
     */
    public static function signal_game_loaded_returns() {
        return new external_value(PARAM_BOOL, 'True if the game was successfully marked as loaded by the use');
    }

    public static function signal_game_progress($webglid, $score, $completedlevels, $puzzlesolved) {

    }
}