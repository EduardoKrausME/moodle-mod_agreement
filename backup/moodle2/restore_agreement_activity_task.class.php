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
 * Restore task for mod_agreement.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class restore_agreement_activity_task
 */
class restore_agreement_activity_task extends restore_activity_task {
    /**
     * define_my_settings
     *
     * @return void
     */
    protected function define_my_settings(): void {
    }

    /**
     * Defines restore steps.
     *
     * @return void
     */
    protected function define_my_steps(): void {
        $this->add_step(new restore_agreement_activity_structure_step("agreement_structure", "agreement.xml"));
    }

    /**
     * Defines content decoding rules.
     *
     * @return restore_decode_content[]
     */
    public static function define_decode_contents(): array {
        return [new restore_decode_content("agreement", ["intro", "termtext"], "agreement")];
    }

    /**
     * Defines link decoding rules.
     *
     * @return restore_decode_rule[]
     */
    public static function define_decode_rules(): array {
        return [
            new restore_decode_rule("AGREEMENTVIEWBYID", "/mod/agreement/view.php?id=$1", "course_module"),
            new restore_decode_rule("AGREEMENTINDEX", "/mod/agreement/index.php?id=$1", "course"),
        ];
    }
}
