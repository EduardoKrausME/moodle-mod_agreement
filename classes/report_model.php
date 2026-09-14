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
 * Builds audit report data for the Mustache template.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_model {
    /**
     * Builds report data.
     *
     * @param \stdClass $agreement Agreement record.
     * @param \stdClass $cm Course module record.
     * @param \context_module $context Module context.
     * @return array
     */
    public static function build(\stdClass $agreement, \stdClass $cm, \context_module $context): array {
        global $DB;

        $versions = $DB->get_records("agreement_versions", ["agreementid" => $agreement->id], "versionnumber DESC");
        $versionitems = [];
        foreach ($versions as $version) {
            $counts = $DB->get_records_sql_menu(
                "SELECT response, COUNT(1) AS total
                   FROM {agreement_responses}
                  WHERE versionid = :versionid
               GROUP BY response",
                ["versionid" => $version->id]
            );
            $agreed = (int)($counts[1] ?? 0);
            $disagreed = (int)($counts[0] ?? 0);
            $versionitems[] = [
                "number" => (int)$version->versionnumber,
                "label" => get_string("versionnumber", "mod_agreement", (int)$version->versionnumber),
                "date" => userdate($version->timecreated),
                "agreed" => $agreed,
                "disagreed" => $disagreed,
                "total" => $agreed + $disagreed,
                "current" => (int)$version->versionnumber === (int)$agreement->currentversion,
                "hash" => $version->contenthash,
                "termhtml" => format_text($version->termtext, $version->termformat, ["context" => $context]),
            ];
        }

        $sql = "SELECT r.id, r.response, r.timecreated, r.userid,
                       v.versionnumber, v.contenthash,
                       u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic,
                       u.middlename, u.alternatename
                  FROM {agreement_responses} r
                  JOIN {agreement_versions} v ON v.id = r.versionid
                  JOIN {user} u ON u.id = r.userid
                 WHERE r.agreementid = :agreementid
              ORDER BY v.versionnumber DESC, r.timecreated DESC, r.id DESC";
        $records = $DB->get_records_sql($sql, ["agreementid" => $agreement->id]);

        $responses = [];
        foreach ($records as $record) {
            $agreed = (int)$record->response === 1;
            $responses[] = [
                "fullname" => fullname($record),
                "userurl" => (new \moodle_url("/user/view.php", ["id" => $record->userid, "course" => $cm->course]))->out(false),
                "version" => (int)$record->versionnumber,
                "response" => get_string($agreed ? "agree" : "disagree", "mod_agreement"),
                "agreed" => $agreed,
                "date" => userdate($record->timecreated),
            ];
        }

        $currentversion = agreement_manager::get_current_version($agreement);
        $currentcounts = $DB->get_records_sql_menu(
            "SELECT response, COUNT(1) AS total
               FROM {agreement_responses}
              WHERE versionid = :versionid
           GROUP BY response",
            ["versionid" => $currentversion->id]
        );
        $enrolled = count_enrolled_users($context, "mod/agreement:respond");
        $currentagreed = (int)($currentcounts[1] ?? 0);
        $currentdisagreed = (int)($currentcounts[0] ?? 0);
        $currentanswered = $currentagreed + $currentdisagreed;

        return [
            "backurl" => (new \moodle_url("/mod/agreement/view.php", ["id" => $cm->id]))->out(false),
            "currentversion" => (int)$agreement->currentversion,
            "currentagreed" => $currentagreed,
            "currentdisagreed" => $currentdisagreed,
            "currentpending" => max(0, $enrolled - $currentanswered),
            "versions" => $versionitems,
            "hasresponses" => !empty($responses),
            "responses" => $responses,
        ];
    }
}
