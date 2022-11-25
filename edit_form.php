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
 * Configuration attestoodle block.
 *
 * @package    block_attestoodle
 * @copyright  2019 Marc Leconte <Marc.Leconte@univ-lemans.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Expand setting block for attestoodle block.
 *
 * @package    block_attestoodle
 * @copyright  2019 Marc Leconte <Marc.Leconte@univ-lemans.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_attestoodle_edit_form extends block_edit_form {
    /**
     * Addition of block-specific settings to the form.
     *
     * @param MoodleForm $mform where we add elements.
     */
    protected function specific_definition($mform) {

        $mform->addElement('header', 'configheader', get_string('configtitle', 'block_attestoodle'));

        $mform->addElement('selectyesno', 'config_displayratiomilestones',
            get_string('displayratiomilestones', 'block_attestoodle'));
        $mform->setDefault('config_displayratiomilestones', 1);

        $mform->addElement('selectyesno', 'config_displaynextcertificate',
            get_string('displaynextcertificate', 'block_attestoodle'));
        $mform->setDefault('config_displaynextcertificate', 1);

        $mform->addElement('selectyesno', 'config_displayprogress',
            get_string('displayprogress', 'block_attestoodle'));
        $mform->setDefault('config_displayprogress', 1);

        $mform->addElement('selectyesno', 'config_displaycertificatelist',
            get_string('displaycertificatelist', 'block_attestoodle'));
        $mform->setDefault('config_displaycertificatelist', 1);
    }
}
