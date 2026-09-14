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
 * Activity settings form for mod_agreement.
 *
 * @package   mod_agreement
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->dirroot}/course/moodleform_mod.php");

/**
 * Agreement activity form.
 */
class mod_agreement_mod_form extends moodleform_mod {
    /**
     * Defines the form.
     *
     * @return void
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement("header", "general", get_string("general", "form"));
        $mform->addElement("text", "name", get_string("agreementname", "mod_agreement"), ["size" => 64]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", get_string("required"), "required", null, "client");

        $this->standard_intro_elements();

        $editoroptions = [
            "maxfiles" => 0,
            "maxbytes" => 0,
            "trusttext" => true,
        ];
        $mform->addElement(
            "editor",
            "termtext_editor",
            get_string("termtext", "mod_agreement"),
            ["rows" => 16],
            $editoroptions
        );
        $mform->addHelpButton("termtext_editor", "termtext", "mod_agreement");

        if (!empty($this->current) && !empty($this->current->id)) {
            $mform->addElement("static", "versioninfo", get_string("currentversion", "mod_agreement"),
                get_string("versionnumber", "mod_agreement", (int)$this->current->currentversion));
            $mform->addElement("static", "versionnotice", "", get_string("versionnotice", "mod_agreement"));
        }

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Prepares editor values while editing an existing instance.
     *
     * @param array $defaultvalues Form default values.
     * @return void
     */
    public function data_preprocessing(&$defaultvalues): void {
        parent::data_preprocessing($defaultvalues);

        $defaultvalues["termtext_editor"] = [
            "text" => $defaultvalues["termtext"] ?? "",
            "format" => $defaultvalues["termformat"] ?? FORMAT_HTML,
        ];
    }

    /**
     * Validates activity data.
     *
     * @param array $data Submitted values.
     * @param array $files Submitted files.
     * @return array Validation errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $text = $data["termtext_editor"]["text"] ?? "";

        if (trim(strip_tags($text)) === "") {
            $errors["termtext_editor"] = get_string("errortermrequired", "mod_agreement");
        }

        return $errors;
    }
}
