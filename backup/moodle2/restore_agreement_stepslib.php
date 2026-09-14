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
 * Restore structure for mod_agreement.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_agreement_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines restore paths.
     *
     * @return restore_path_element[]
     */
    protected function define_structure(): array {
        $paths = [new restore_path_element("agreement", "/activity/agreement")];
        $paths[] = new restore_path_element("agreement_version", "/activity/agreement/versions/version");
        if ($this->get_setting_value("userinfo")) {
            $paths[] = new restore_path_element(
                "agreement_response",
                "/activity/agreement/versions/version/responses/response"
            );
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restores the main activity record.
     *
     * @param array $data Backup data.
     * @return void
     */
    protected function process_agreement($data): void {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record("agreement", $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Restores a term version.
     *
     * @param array $data Backup data.
     * @return void
     */
    protected function process_agreement_version($data): void {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->agreementid = $this->get_new_parentid("agreement");
        $data->userid = empty($data->userid) ? null : $this->get_mappingid("user", $data->userid, null);
        $newitemid = $DB->insert_record("agreement_versions", $data);
        $this->set_mapping("agreement_version", $oldid, $newitemid);
    }

    /**
     * Restores a student response.
     *
     * @param array $data Backup data.
     * @return void
     */
    protected function process_agreement_response($data): void {
        global $DB;

        $data = (object)$data;
        $data->agreementid = $this->get_new_parentid("agreement");
        $data->versionid = $this->get_new_parentid("agreement_version");
        $data->userid = $this->get_mappingid("user", $data->userid, 0);
        if ($data->userid) {
            $DB->insert_record("agreement_responses", $data);
        }
    }

    /**
     * Restores intro embedded files.
     *
     * @return void
     */
    protected function after_execute(): void {
        $this->add_related_files("mod_agreement", "intro", null);
    }
}
