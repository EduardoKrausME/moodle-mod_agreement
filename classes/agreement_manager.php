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
 * Business rules for agreement instances and responses.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class agreement_manager {
    /**
     * Adds a new agreement instance and its first immutable version.
     *
     * @param \stdClass $data Form data.
     * @return int Instance id.
     */
    public static function add_instance(\stdClass $data): int {
        global $DB, $USER;

        [$termtext, $termformat] = self::extract_term($data);
        $now = time();
        $transaction = $DB->start_delegated_transaction();

        $record = (object)[
            "course" => $data->course,
            "name" => $data->name,
            "intro" => $data->intro ?? "",
            "introformat" => $data->introformat ?? FORMAT_HTML,
            "termtext" => $termtext,
            "termformat" => $termformat,
            "currentversion" => 1,
            "timecreated" => $now,
            "timemodified" => $now,
        ];
        $record->id = $DB->insert_record("agreement", $record);

        self::insert_version($record->id, 1, $termtext, $termformat, !empty($USER->id) ? (int)$USER->id : null, $now);
        $transaction->allow_commit();

        return (int)$record->id;
    }

    /**
     * Updates an agreement. Changing the term creates a new immutable version.
     *
     * @param \stdClass $data Form data.
     * @return bool
     */
    public static function update_instance(\stdClass $data): bool {
        global $DB, $USER;

        $id = (int)$data->instance;
        $current = $DB->get_record("agreement", ["id" => $id], "*", MUST_EXIST);
        [$termtext, $termformat] = self::extract_term($data);
        $now = time();
        $changed = self::content_hash($current->termtext, (int)$current->termformat)
            !== self::content_hash($termtext, $termformat);

        $transaction = $DB->start_delegated_transaction();

        $update = (object)[
            "id" => $id,
            "name" => $data->name,
            "intro" => $data->intro ?? "",
            "introformat" => $data->introformat ?? FORMAT_HTML,
            "termtext" => $termtext,
            "termformat" => $termformat,
            "currentversion" => (int)$current->currentversion,
            "timemodified" => $now,
        ];

        if ($changed) {
            $update->currentversion++;
            $userid = !empty($USER->id) ? $USER->id : null;
            self::insert_version($id, $update->currentversion, $termtext, $termformat, $userid, $now);
        }

        $DB->update_record("agreement", $update);
        $transaction->allow_commit();
        return true;
    }

    /**
     * Deletes an agreement and all audit records.
     *
     * @param int $id Instance id.
     * @return bool
     */
    public static function delete_instance(int $id): bool {
        global $DB;

        if (!$DB->record_exists("agreement", ["id" => $id])) {
            return false;
        }

        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records("agreement_responses", ["agreementid" => $id]);
        $DB->delete_records("agreement_versions", ["agreementid" => $id]);
        $DB->delete_records("agreement", ["id" => $id]);
        $transaction->allow_commit();
        return true;
    }

    /**
     * Records one immutable response for the current term version.
     *
     * @param \stdClass $agreement Agreement record.
     * @param \stdClass $cm Course module record.
     * @param \context_module $context Module context.
     * @param int $userid User id.
     * @param int $response 1 for agree, 0 for disagree.
     * @return int Response id.
     */
    public static function record_response(
        \stdClass $agreement,
        \stdClass $cm,
        \context_module $context,
        int $userid,
        int $response
    ): int {
        global $DB;

        if (!in_array($response, [0, 1], true)) {
            throw new \moodle_exception("invalidresponse", "mod_agreement");
        }

        $version = self::get_current_version($agreement);
        if ($DB->record_exists("agreement_responses", ["versionid" => $version->id, "userid" => $userid])) {
            throw new \moodle_exception("alreadyresponded", "mod_agreement");
        }

        $now = time();
        $record = (object)[
            "agreementid" => $agreement->id,
            "versionid" => $version->id,
            "userid" => $userid,
            "response" => $response,
            "timecreated" => $now,
            "timemodified" => $now,
        ];
        $record->id = $DB->insert_record("agreement_responses", $record);

        $event = \mod_agreement\event\response_recorded::create([
            "objectid" => $record->id,
            "context" => $context,
            "relateduserid" => $userid,
            "other" => [
                "agreementid" => (int)$agreement->id,
                "versionnumber" => (int)$version->versionnumber,
                "response" => $response,
            ],
        ]);
        $event->add_record_snapshot("agreement", $agreement);
        $event->add_record_snapshot("agreement_responses", $record);
        $event->trigger();

        return (int)$record->id;
    }

    /**
     * Returns the current immutable version record.
     *
     * @param \stdClass $agreement Agreement record.
     * @return \stdClass
     */
    public static function get_current_version(\stdClass $agreement): \stdClass {
        global $DB;

        return $DB->get_record("agreement_versions", [
            "agreementid" => $agreement->id,
            "versionnumber" => $agreement->currentversion,
        ], "*", MUST_EXIST);
    }

    /**
     * Extracts term editor data.
     *
     * @param \stdClass $data Form data.
     * @return array{0:string,1:int}
     */
    private static function extract_term(\stdClass $data): array {
        if (isset($data->termtext_editor) && is_array($data->termtext_editor)) {
            return [
                (string)($data->termtext_editor["text"] ?? ""),
                (int)($data->termtext_editor["format"] ?? FORMAT_HTML),
            ];
        }

        return [
            (string)($data->termtext ?? ""),
            (int)($data->termformat ?? FORMAT_HTML),
        ];
    }

    /**
     * Inserts one immutable version record.
     *
     * @param int $agreementid Agreement id.
     * @param int $versionnumber Version number.
     * @param string $termtext Term content.
     * @param int $termformat Text format.
     * @param int|null $userid Editor user id.
     * @param int $timecreated Creation timestamp.
     * @return int
     */
    private static function insert_version(
        int $agreementid,
        int $versionnumber,
        string $termtext,
        int $termformat,
        ?int $userid,
        int $timecreated
    ): int {
        global $DB;

        return (int)$DB->insert_record("agreement_versions", (object)[
            "agreementid" => $agreementid,
            "versionnumber" => $versionnumber,
            "termtext" => $termtext,
            "termformat" => $termformat,
            "contenthash" => self::content_hash($termtext, $termformat),
            "userid" => $userid,
            "timecreated" => $timecreated,
        ]);
    }

    /**
     * Produces a deterministic audit hash for term content.
     *
     * @param string $termtext Term content.
     * @param int $termformat Text format.
     * @return string SHA-256 hash.
     */
    private static function content_hash(string $termtext, int $termformat): string {
        return hash("sha256", $termformat . "\n" . $termtext);
    }
}
