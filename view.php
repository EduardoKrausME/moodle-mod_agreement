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
 * Main student page for mod_agreement.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("agreement", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$agreement = $DB->get_record("agreement", ["id" => $cm->instance], "*", MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability("mod/agreement:view", $context);

$PAGE->set_url("/mod/agreement/view.php", ["id" => $cm->id]);
$PAGE->set_title(format_string($agreement->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$action = optional_param("action", "", PARAM_ALPHA);
if ($action === "respond" && data_submitted()) {
    require_capability("mod/agreement:respond", $context);
    require_sesskey();
    $response = required_param("response", PARAM_INT);
    if (!in_array($response, [0, 1], true)) {
        throw new moodle_exception("invalidresponse", "mod_agreement");
    }

    \mod_agreement\agreement_manager::record_response($agreement, $cm, $context, $USER->id, $response);
    redirect($PAGE->url, get_string("responsesaved", "mod_agreement"), null, \core\output\notification::NOTIFY_SUCCESS);
}

$event = \mod_agreement\event\course_module_viewed::create([
    "objectid" => $agreement->id,
    "context" => $context,
]);
$event->add_record_snapshot("course", $course);
$event->add_record_snapshot("course_modules", $cm);
$event->add_record_snapshot("agreement", $agreement);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$data = \mod_agreement\view_model::build($agreement, $cm, $context, $USER->id);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($agreement->name));
echo $OUTPUT->render_from_template("mod_agreement/view", $data);
echo $OUTPUT->footer();
