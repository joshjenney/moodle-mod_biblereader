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
 * Activity creation/editing form for the mod_biblereader plugin.
 *
 * @package   mod_biblereader
 * @copyright 2024, Josh Jenney <josh@n2nministries.org>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/course/moodleform_mod.php');

use core_grades\component_gradeitems;

class mod_biblereader_mod_form extends moodleform_mod {

    function definition() {
        global $CFG, $DB, $OUTPUT;

        $mform =& $this->_form;

        // Section header title according to language file.
        $mform->addElement('header', 'general', get_string('general', 'biblereader'));

        // Add a text input for the name of the biblereader instance.
        $mform->addElement('text', 'name', get_string('name', 'biblereader'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Section description
        $this->standard_intro_elements(get_string('intro_description', 'biblereader'));

        // Add a program select menu for the 'use code' setting.
        $program_selector = [
            '1' => get_string('foundations', 'biblereader'),
        ];
        $mform->addElement('select', 'program', get_string('program_label', 'biblereader'), $program_selector);

        $mform->setDefault('program', 1);
        $mform->addHelpButton('program', 'program', 'biblereader');

        // Add a semester select menu for the 'use code' setting.
        $semester_selector = [
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            11 => 11,
            12 => 12,
        ];
        $mform->addElement('select', 'semester', get_string('semester_label', 'biblereader'), $semester_selector);

        $mform->setDefault('semester', 1);
        $mform->addHelpButton('semester', 'semester', 'biblereader');

        // Add standard grading elements.
        $this->standard_grading_coursemodule_elements();

        // Standard Moodle course module elements (course, category, etc.).
        $this->standard_coursemodule_elements();

        // Standard Moodle form buttons.
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = array();

        // Validate the 'name' field.
        if (empty($data['name'])) {
            $errors['name'] = get_string('errornoname', 'biblereader');
        }

        return $errors;
    }

    function data_preprocessing(&$default_values) {

        // check for existing data (update vs add)
        $isupdate = !empty($this->_cm);

        // Set default values for the form fields.
        # $default_values['name'] = get_string('defaultname', 'biblereader');
        $default_values['name'] = $isupdate ? $this->_cm->name : get_string('name', 'biblereader');
        #$default_values['semester'] = $isupdate ? $this->_cm->semester : 1;

        // debug
        # error_log(print_r($this->_cm,true));
        # error_log(print_r($default_values,true));

        # echo '<pre>'; print_r($this->_cm,true); echo '</pre>';

        $default_values['minimumpercentageenabled'] = !empty($default_values['minimumpercentage']) ? 1 : 0;
        if(empty($default_values['minimumpercentage'])) {
           $default_values['minimumpercentage'] = 30;
        }

    }

    function definition_after_data() {
        $mform = $this->_form;
        $data = $this->get_data();

        # error_log(print_r($data,true));
        /*                                                                                                                                                     │
        │(
        |    [name] => Bible Reading Lab I                                                                                                                     │
        │    [semester] => 10                                                                                                                                  │
        │    [visible] => 1                                                                                                                                    │
        │    [visibleoncoursepage] => 1                                                                                                                        │
        │    [cmidnumber] =>                                                                                                                                   │
        │    [lang] =>                                                                                                                                         │
        │    [availabilityconditionsjson] => {"op":"&","c":[],"showc":[]}                                                                                      │
        │    [completionunlocked] => 0                                                                                                                         │
        │    [completion] => 0                                                                                                                                 │
        │    [completionexpected] => 0                                                                                                                         │
        │    [course] => 113                                                                                                                                   │
        │    [coursemodule] => 5996                                                                                                                            │
        │    [section] => 1                                                                                                                                    │
        │    [module] => 36                                                                                                                                    │
        │    [modulename] => biblereader                                                                                                                       │
        │    [instance] => 1                                                                                                                                   │
        │    [add] => 0                                                                                                                                        │
        │    [update] => 5996                                                                                                                                  │
        │    [return] => 0                                                                                                                                     │
        │    [sr] => 0                                                                                                                                         │
        │    [competencies] => Array                                                                                                                           │
        │        (                                                                                                                                             │
        │        )                                                                                                                                             │
        │                                                                                                                                                      │
        │    [competency_rule] => 0                                                                                                                            │
        │    [override_grade] => 0                                                                                                                             │
        │    [submitbutton] => Save and display                                                                                                                │
        │    [groupingid] => 0                                                                                                                                 │
        │    [completionview] => 0                                                                                                                             │
        │    [completionpassgrade] => 0                                                                                                                        │
        │    [completiongradeitemnumber] =>                                                                                                                    │
        │    [conditiongradegroup] => Array                                                                                                                    │
        │        (                                                                                                                                             │
        │        )                                                                                                                                             │
        │                                                                                                                                                      │
        │    [conditionfieldgroup] => Array                                                                                                                    │
        │        (                                                                                                                                             │
        │        )                                                                                                                                             │
        │                                                                                                                                                      │
        │    [downloadcontent] => 1                                                                                                                            │
        │    [groupmode] => 0                                                                                                                                  │
        │    [intro] => <p dir="ltr" style="text-align: left;">Example text here to load.<br></p>                                                              │
        │    [introformat] => 1                                                                                                                                │
        │    [id] => 1                                                                                                                                         │
        │)
        */

        // debug
        # error_log(print_r($this->_cm,true));
        # echo '<pre>'; print_r($this->_cm,true); echo '</pre>';

        // Disable the 'name' field if 'semester' is set to 1.
        #if ($data && !empty($data->semester)) {
        #     $mform->disabledIf('name', 'semester', 'eq', 1);
        #}

    }

    function preprocess_data($data) {
        // Modify the 'name' data before saving.
        # $data->name = strtoupper($data->name);

        return $data;
    }

  /**
   * Add elements for setting the custom completion rules.
   *
   * @category completion
   * @return array List of added element names, or names of wrapping group elements.
   */
    public function add_completion_rules() {

        $mform = $this->_form;

        $group = [
            $mform->createElement('checkbox', 'minimumpercentageenabled', ' '),
            $mform->createElement('text', 'minimumpercentage', ' ', ['size' => 3]),
        ];
        $mform->setType('minimumpercentage', PARAM_INT);
        $mform->addGroup($group, 'minimumpercentagegroup', get_string('minimumpercentage', 'biblereader'), [' '], false);
        $mform->addHelpButton('minimumpercentagegroup', 'minimumpercentage', 'biblereader');
        $mform->disabledIf('minimumpercentage', 'minimumpercentageenabled', 'notchecked');

        return ['minimumpercentagegroup'];
    }

    /**
     * Called during validation to see whether some activity-specific completion rules are selected.
     *
     * @param array $data Input data not yet validated.
     * @return bool True if one or more rules is enabled, false if none are.
     */
    public function completion_rule_enabled($data) {
        return (!empty($data['minimumpercentageenabled']) && $data['minimumpercentage'] != 0);
    }


    function get_data() {
      $data = parent::get_data();
      if (!$data) {
          return $data;
      }
      if (!empty($data->completionunlocked)) {
          // Turn off completion settings if the checkboxes aren't ticked
          $autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
          if (empty($data->completionpostsenabled) || !$autocompletion) {
            $data->minimumpercentage = 0;
          }
      }
      return $data;
    }
}
