<?php
// This file is part of the Zoom plugin for Moodle - http://moodle.org/
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
 * AWS webgl module version info
 *
 * @package mod_webgl
 * @copyright  2020 Brain station 23 ltd <>  {@link https://brainstation-23.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/classes/BlobStorage.php');
$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n = optional_param('n', 0, PARAM_INT);  // ... webgl instance ID - it should be named as the first character of the module.

if ($id) {
    $cm = get_coursemodule_from_id('webgl', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $webgl = $DB->get_record('webgl', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $webgl = $DB->get_record('webgl', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $webgl->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('webgl', $webgl->id, $course->id, false, MUST_EXIST);
} else {
    throw new Exception('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_webgl\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $webgl);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/webgl/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($webgl->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_cacheable(false);
$context = context_course::instance($course->id);

echo $OUTPUT->header();

if ($webgl->intro) {
    echo $OUTPUT->box(format_module_intro('webgl', $webgl, $cm->id), 'generalbox mod_introbox', 'webglintro');
}

?>
    <iframe allowfullscreen="true" width="100%" height="400px" src="<?php echo $webgl->index_file_url;?>" frameborder="0"></iframe>
<?php

echo $OUTPUT->footer();
