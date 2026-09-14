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
 * Backup task for mod_agreement.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_agreement_activity_task extends backup_activity_task {
    /**
     * define_my_settings
     *
     * @return void
     */
    protected function define_my_settings(): void {
    }

    /**
     * Defines backup steps.
     *
     * @return void
     */
    protected function define_my_steps(): void {
        $this->add_step(new backup_agreement_activity_structure_step("agreement_structure", "agreement.xml"));
    }

    /**
     * Encodes links to this activity.
     *
     * @param string $content Content.
     * @return string
     */
    public static function encode_content_links($content): string {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");
        $content = preg_replace(
            "/({$base}\\/mod\\/agreement\\/index.php\\?id=)([0-9]+)/",
            "\$@AGREEMENTINDEX*\$2@\$",
            $content
        );
        return preg_replace(
            "/({$base}\\/mod\\/agreement\\/view.php\\?id=)([0-9]+)/",
            "\$@AGREEMENTVIEWBYID*\$2@\$",
            $content
        );
    }
}
