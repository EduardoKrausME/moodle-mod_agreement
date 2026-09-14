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
 * Backup structure for mod_agreement.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_agreement_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the backup tree.
     *
     * @return backup_nested_element
     */
    protected function define_structure(): backup_nested_element {
        $userinfo = $this->get_setting_value("userinfo");

        $agreement = new backup_nested_element("agreement", ["id"], [
            "name", "intro", "introformat", "termtext", "termformat", "currentversion",
            "timecreated", "timemodified",
        ]);
        $versions = new backup_nested_element("versions");
        $version = new backup_nested_element("version", ["id"], [
            "versionnumber", "termtext", "termformat", "contenthash", "userid", "timecreated",
        ]);
        $responses = new backup_nested_element("responses");
        $response = new backup_nested_element("response", ["id"], [
            "userid", "response", "timecreated", "timemodified",
        ]);

        $agreement->add_child($versions);
        $versions->add_child($version);
        $version->add_child($responses);
        $responses->add_child($response);

        $agreement->set_source_table("agreement", ["id" => backup::VAR_ACTIVITYID]);
        $version->set_source_table("agreement_versions", ["agreementid" => backup::VAR_PARENTID]);
        if ($userinfo) {
            $response->set_source_table("agreement_responses", ["versionid" => backup::VAR_PARENTID]);
        }

        $version->annotate_ids("user", "userid");
        $response->annotate_ids("user", "userid");
        $agreement->annotate_files("mod_agreement", "intro", null);

        return $this->prepare_activity_structure($agreement);
    }
}
