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
 * Tests versioning and immutable responses.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class agreement_manager_test extends \advanced_testcase {
    /**
     * A changed term creates a new version while keeping the old one.
     *
     * @covers \mod_agreement\agreement_manager
     * @return void
     */
    public function test_term_change_creates_new_version(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module("agreement", [
            "course" => $course->id,
            "termtext_editor" => ["text" => "First term", "format" => FORMAT_HTML],
        ]);

        $data = clone $activity;
        $data->instance = $activity->id;
        $data->termtext_editor = ["text" => "Second term", "format" => FORMAT_HTML];
        \mod_agreement\agreement_manager::update_instance($data);

        $updated = $DB->get_record("agreement", ["id" => $activity->id]);
        $this->assertSame(2, (int)$updated->currentversion);
        $this->assertEquals(2, $DB->count_records("agreement_versions", ["agreementid" => $activity->id]));
    }
}
