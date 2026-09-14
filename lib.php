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
 * Core callbacks for mod_agreement.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Declares supported Moodle features.
 *
 * @param string $feature Feature name.
 * @return mixed
 */
function agreement_supports(string $feature) {
    return match ($feature) {
        FEATURE_MOD_INTRO => true,
        FEATURE_SHOW_DESCRIPTION => true,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_COMPLETION_TRACKS_VIEWS => true,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_OTHER,
        default => null,
    };
}

/**
 * Creates an agreement activity.
 *
 * @param stdClass $data Instance data.
 * @param mod_agreement_mod_form|null $mform Form instance.
 * @return int
 */
function agreement_add_instance(stdClass $data, ?mod_agreement_mod_form $mform = null): int {
    return \mod_agreement\agreement_manager::add_instance($data);
}

/**
 * Updates an agreement activity.
 *
 * @param stdClass $data Instance data.
 * @param mod_agreement_mod_form|null $mform Form instance.
 * @return bool
 */
function agreement_update_instance(stdClass $data, ?mod_agreement_mod_form $mform = null): bool {
    return \mod_agreement\agreement_manager::update_instance($data);
}

/**
 * Deletes an agreement activity.
 *
 * @param int $id Agreement id.
 * @return bool
 */
function agreement_delete_instance(int $id): bool {
    return \mod_agreement\agreement_manager::delete_instance($id);
}
