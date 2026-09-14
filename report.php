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
 * Teacher report for mod_agreement.
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
require_capability("mod/agreement:viewresponses", $context);

$PAGE->set_url("/mod/agreement/report.php", ["id" => $cm->id]);
$PAGE->set_title(get_string("report", "mod_agreement"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$data = \mod_agreement\report_model::build($agreement, $cm, $context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("reportfor", "mod_agreement", format_string($agreement->name)));
echo $OUTPUT->render_from_template("mod_agreement/report", $data);
echo $OUTPUT->footer();
