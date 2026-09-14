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

namespace mod_agreement\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for mod_agreement.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    /**
     * Describes stored personal data.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table("agreement_versions", [
            "userid" => "privacy:metadata:agreement_versions:userid",
            "timecreated" => "privacy:metadata:agreement_versions:timecreated",
        ], "privacy:metadata:agreement_versions");

        $collection->add_database_table("agreement_responses", [
            "userid" => "privacy:metadata:agreement_responses:userid",
            "response" => "privacy:metadata:agreement_responses:response",
            "timecreated" => "privacy:metadata:agreement_responses:timecreated",
        ], "privacy:metadata:agreement_responses");

        return $collection;
    }

    /**
     * Returns contexts containing data for a user.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {agreement} a ON a.id = cm.instance
             LEFT JOIN {agreement_responses} r ON r.agreementid = a.id AND r.userid = :responseuserid
             LEFT JOIN {agreement_versions} v ON v.agreementid = a.id AND v.userid = :versionuserid
                 WHERE ctx.contextlevel = :contextlevel
                   AND (r.userid IS NOT NULL OR v.userid IS NOT NULL)";
        $contextlist->add_from_sql($sql, [
            "modname" => "agreement",
            "contextlevel" => CONTEXT_MODULE,
            "responseuserid" => $userid,
            "versionuserid" => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Exports user data.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id("agreement", $context->instanceid);
            if (!$cm) {
                continue;
            }

            $sql = "SELECT r.id, r.response, r.timecreated, v.versionnumber
                      FROM {agreement_responses} r
                      JOIN {agreement_versions} v ON v.id = r.versionid
                     WHERE r.agreementid = :agreementid
                       AND r.userid = :userid
                  ORDER BY v.versionnumber ASC";
            $records = $DB->get_records_sql($sql, [
                "agreementid" => $cm->instance,
                "userid" => $contextlist->get_user()->id,
            ]);
            $responses = [];
            foreach ($records as $record) {
                $responses[] = (object)[
                    "version" => (int)$record->versionnumber,
                    "response" => (int)$record->response === 1
                        ? get_string("agree", "mod_agreement")
                        : get_string("disagree", "mod_agreement"),
                    "timecreated" => transform::datetime($record->timecreated),
                ];
            }

            $authoredversions = [];
            $versions = $DB->get_records("agreement_versions", [
                "agreementid" => $cm->instance,
                "userid" => $contextlist->get_user()->id,
            ], "versionnumber ASC");
            foreach ($versions as $version) {
                $authoredversions[] = (object)[
                    "version" => (int)$version->versionnumber,
                    "timecreated" => transform::datetime($version->timecreated),
                ];
            }

            writer::with_context($context)->export_data([], (object)[
                "responses" => $responses,
                "authoredversions" => $authoredversions,
            ]);
        }
    }

    /**
     * Deletes all user data in a context.
     *
     * @param \context $context Context.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id("agreement", $context->instanceid);
        if ($cm) {
            $DB->delete_records("agreement_responses", ["agreementid" => $cm->instance]);
            $DB->set_field("agreement_versions", "userid", null, ["agreementid" => $cm->instance]);
        }
    }

    /**
     * Deletes data for one user.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id("agreement", $context->instanceid);
            if ($cm) {
                $userid = $contextlist->get_user()->id;
                $DB->delete_records("agreement_responses", [
                    "agreementid" => $cm->instance,
                    "userid" => $userid,
                ]);
                $DB->set_field("agreement_versions", "userid", null, [
                    "agreementid" => $cm->instance,
                    "userid" => $userid,
                ]);
            }
        }
    }

    /**
     * Adds users with data in a context to the userlist.
     *
     * @param userlist $userlist User list.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id("agreement", $context->instanceid);
        if (!$cm) {
            return;
        }

        $userlist->add_from_sql("userid",
            "SELECT r.userid FROM {agreement_responses} r WHERE r.agreementid = :agreementid",
            ["agreementid" => $cm->instance]
        );
        $userlist->add_from_sql("userid",
            "SELECT v.userid FROM {agreement_versions} v WHERE v.agreementid = :agreementid AND v.userid IS NOT NULL",
            ["agreementid" => $cm->instance]
        );
    }

    /**
     * Deletes data for an approved set of users in one context.
     *
     * @param approved_userlist $userlist Approved user list.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if (!$context instanceof \context_module) {
            return;
        }
        $cm = get_coursemodule_from_id("agreement", $context->instanceid);
        if (!$cm) {
            return;
        }

        $userids = $userlist->get_userids();
        if (!$userids) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params["agreementid"] = $cm->instance;
        $DB->delete_records_select("agreement_responses", "agreementid = :agreementid AND userid {$insql}", $params);
        $DB->set_field_select("agreement_versions", "userid", null,
            "agreementid = :agreementid AND userid {$insql}", $params);
    }
}
