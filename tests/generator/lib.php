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
 * Data generator for mod_agreement tests.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_agreement_generator extends testing_module_generator {
    /**
     * Creates a plugin instance.
     *
     * @param array|stdClass|null $record Record data.
     * @param array|null $options Options.
     * @return stdClass
     */
    public function create_instance($record = null, ?array $options = null): stdClass {
        $record = (object)($record ?? []);
        if (!isset($record->name)) {
            $record->name = "Agreement test";
        }
        if (!isset($record->termtext_editor)) {
            $record->termtext_editor = ["text" => "Test term", "format" => FORMAT_HTML];
        }
        return parent::create_instance($record, $options);
    }
}
