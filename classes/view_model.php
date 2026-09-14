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

namespace mod_agreement;

/**
 * Builds data for the student Mustache template.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class view_model {
    /**
     * Builds template data.
     *
     * @param \stdClass $agreement Agreement record.
     * @param \stdClass $cm Course module record.
     * @param \context_module $context Module context.
     * @param int $userid Current user id.
     * @return array
     */
    public static function build(\stdClass $agreement, \stdClass $cm, \context_module $context, int $userid): array {
        global $DB;

        $version = agreement_manager::get_current_version($agreement);
        $canrespond = has_capability("mod/agreement:respond", $context);
        $response = false;
        if ($canrespond) {
            $response = $DB->get_record("agreement_responses", [
                "versionid" => $version->id,
                "userid" => $userid,
            ]);
        }

        $data = [
            "introhtml" => format_module_intro("agreement", $agreement, $cm->id, false),
            "hasintro" => trim((string)$agreement->intro) !== "",
            "termhtml" => format_text($version->termtext, $version->termformat, ["context" => $context]),
            "versionlabel" => get_string("versionnumber", "mod_agreement", (int)$version->versionnumber),
            "versiondate" => userdate($version->timecreated),
            "sesskey" => sesskey(),
            "formaction" => (new \moodle_url("/mod/agreement/view.php", ["id" => $cm->id]))->out(false),
            "hasresponse" => (bool)$response,
            "canrespond" => $canrespond,
            "canviewreport" => has_capability("mod/agreement:viewresponses", $context),
            "reporturl" => (new \moodle_url("/mod/agreement/report.php", ["id" => $cm->id]))->out(false),
        ];

        if ($response) {
            $agreed = (int)$response->response === 1;
            $data["response"] = [
                "agreed" => $agreed,
                "label" => get_string($agreed ? "agree" : "disagree", "mod_agreement"),
                "date" => userdate($response->timecreated),
            ];
        }

        return $data;
    }
}
