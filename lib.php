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
 * Standard library of functions and constants for the mod_biblereader plugin.
 *
 * @package   mod_biblereader
 * @copyright 2024, Josh Jenney <josh@n2nministries.org>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_biblereader\completion\custom_completion;

// $course is full couurse object
# $completion_info = new completion_info($course);
// $cm is course module object
# $completion = $completion_info->update_state($cm, COMPLETION_COMPLETE));

 function biblereader_supports(string $feature) {
     switch ($feature) {
         case FEATURE_GRADE_HAS_GRADE:
              return false;

         case FEATURE_ADVANCED_GRADING:
              return false;

         case FEATURE_GRADE_OUTCOMES:
              return false;

         case FEATURE_COMPLETION_TRACKS_VIEWS:
              return false;

         case FEATURE_COMPLETION_HAS_RULES:
              return true;

         case MOD_PURPOSE_ASSESSMENT:
              return true;

         case FEATURE_MOD_PURPOSE:
              return true;
             #return MOD_PURPOSE_ASSESSMENT;

         case FEATURE_SHOW_DESCRIPTION:
              return true;

         case FEATURE_MOD_PURPOSE:
            # return MOD_PURPOSE_INTERACTIVECONTENT;
              return false;

         default:
             return null;
     }
 }

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @global object
 * @param object $biblereader Biblereader post data from the form
 * @return int
 **/
function biblereader_add_instance($data, $mform = null) {
  global $DB;

  $cmid = $data->coursemodule;
  #$draftitemid = $data->mediafile;
  $context = context_module::instance($cmid);

  # biblereader_process_pre_save($data);

  unset($data->mediafile);

  $data->timecreated = time();
  $data->timemodified = time();

  $biblereaderid = $DB->insert_record("biblereader", $data);

  /*
  // we need to use context now, so we need to make sure all needed info is already in db
  $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
  $context = context_module::instance($cmid);

  if ($mform and !empty($data->page['itemid'])) {
      $draftitemid = $data->page['itemid'];
      $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_page', 'content', 0, page_get_editor_options($context), $data->content);
      $DB->update_record('page', $data);
  }

  $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
  \core_completion\api::update_completion_date_event($cmid, 'page', $data->id, $completiontimeexpected);

  return $data->id;
  */

  $data->id = $biblereaderid;

  #biblereader_update_media_file($biblereaderid, $context, $draftitemid);

  #biblereader_process_post_save($data);

  // GRADING diabled
  #biblereader_grade_item_update($data);

  return $biblereaderid;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $biblereader Biblereader post data from the form
 * @return boolean
 **/
function biblereader_update_instance($data, $mform) {

    global $DB, $CFG;
    require_once($CFG->libdir.'/completionlib.php');

    $data->timemodified = time();
    $data->id = $data->instance;
    $cmid = $data->coursemodule;
    #$draftitemid = $data->mediafile;
    $context = context_module::instance($cmid);

    $data->introformat = FORMAT_HTML;
    #biblereader_process_pre_save($data);

    unset($data->mediafile);

    // if an object member is NOT declared in the install.xml,
    // it is silently dropped by Moodle
    #$DB->set_debug(true);
    $DB->update_record("biblereader", $data);
    #$DB->set_debug(false);

    /*
    $draftitemid = $data->page['itemid'];
    $context = context_module::instance($cmid);
    if ($draftitemid) {
        $data->content = file_save_draft_area_files($draftitemid, $context->id, 'mod_biblereader', 'content', 0, page_get_editor_options($context), $data->content);
        $DB->update_record('biblereader', $data);
    }

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'biblereader', $data->id, $completiontimeexpected);
    */

    #biblereader_update_media_file($data->id, $context, $draftitemid);

    #biblereader_process_post_save($data);

    // debug: test
    #$save_form_data = new stdClass();
    #$save_form_data->id = $data->id;
    #$save_form_data->semester = $data->semester;
    #$DB->update_record("biblereader", $save_form_data);

    // DISABLE GRADING: update grade item definition
    # biblereader_grade_item_update($data);

    // update grades - TODO: do it only when grading style changes
    # biblereader_update_grades($data, 0, false);

    // debug: log
    #error_log(print_r($data, true));
    #error_log(print_r($result, true));

    #$biblereader = new biblereader($context, null, null);
    #return $biblereader->update_instance($data);

    return true;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function biblereader_delete_instance($biblereader) {
    global $DB, $CFG;
    // require_once($CFG->dirroot . '/mod/biblereader/locallib.php');

    if(!$biblereader = $DB->get_record('biblereader', array('id' => $id))) {
        return false;
    }

    // Delete any dependent records here.
    $DB->delete_records('biblereader', array('id' => $biblereader->id));

    /*
    $cm = get_coursemodule_from_instance('page', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'page', $id, null);
    */

    // DISABLE GRADING: remove gradebook entry for instance
    # biblereader_grade_item_delete($biblereader);
    return true;

    // $biblereader = $DB->get_record("biblereader", array("id"=>$id), '*', MUST_EXIST);
    // $biblereader = new biblereader($biblereader);
    // return $biblereader->delete();
}

/* Gradebook API */

/**
 * Is a given scale used by the instance of biblereader?
 *
 * This function returns if a scale is being used by one biblereader
 * if it has support for grading and scales.
 *
 * @param int $biblereaderid ID of an instance of this module
 * @param int $scaleid ID of the scale
 * @return bool true if the scale is used by the given biblereader instance
 */
function biblereader_scale_used($biblereaderid, $scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('biblereader', array('id' => $biblereaderid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of biblereader.
 *
 * This is used to find out if scale used anywhere.
 *
 * @param int $scaleid ID of the scale
 * @return boolean true if the scale is used by any biblereader instance
 */
function biblereader_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid and $DB->record_exists('biblereader', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

function biblereader_grade_item_update($instance, $grades=NULL){
    global $CFG;

    // workaround for buggy PHP versions
    if (!function_exists('grade_update'))
        require_once($CFG->libdir.'/gradelib.php');

    $params = array();
    $params['itemname'] = clean_param($instance->name, PARAM_NOTAGS);
    $params['gradetype'] = GRADE_TYPE_VALUE;

    if ($instance->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = 100; // $instance->grade;
        $params['grademin']  = 0;
        # error_log('grade min:0 max:100 grade:' .print_r($instance->grade, true));
    } else if ($instance->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$instance->grade;

        // Make sure current grade fetched correctly from $grades
        $currentgrade = null;
        if (!empty($grades)) {
            if (is_array($grades)) {
                $currentgrade = reset($grades);
            } else {
                $currentgrade = $grades;
            }
        }

        // When converting a score to a scale, use scale's grade maximum to calculate it.
        if (!empty($currentgrade) && $currentgrade->rawgrade !== null) {
            $grade = grade_get_grades($instance->course, 'mod', 'biblereader', $instance->id, $currentgrade->userid);
            $params['grademax']   = reset($grade->items)->grademax;
        }

    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades  === 'reset') {
        $params['reset'] = true;
        $grades = null;

    } else if (!empty($grades)) {

        // Need to calculate raw grade (Note: $grades has many forms)
        if (is_object($grades)) {
            $grades = array($grades->userid => $grades);
            // error_log('grade is object');
        } else if (array_key_exists('userid', $grades)) {
            $grades = array($grades['userid'] => $grades);
            // error_log('grade is array');
        }

        foreach ($grades as $key => $grade) {
            if (!is_array($grade)) {
                $grades[$key] = $grade = (array) $grade;
                // error_log('add grade to array');
            }

            // check raw grade isnt null otherwise we erroneously insert a grade of 0
            if (isset($grade['rawgrade']) && $grade['rawgrade'] !== null) {
                $grades[$key]['rawgrade'] = ($grade['rawgrade'] * $params['grademax'] / 100);
                // error_log('rawgrade: '. print_r($grades[$key]['rawgrade'], true));

            } else {
                // setting rawgrade to null just in case user is deleting a grade
                $grades[$key]['rawgrade'] = null;
                // error_log('rawgrade is null (deleting?)');
            }
        }
    }

    return grade_update('mod/biblereader', $instance->course, 'mod', 'biblereader', $instance->id, 0, $grades, $params);
}


/**
 * Delete grade item for given biblereader instance
 *
 * @param stdClass $biblereader instance object
 * @return grade_item
 */
function biblereader_grade_item_delete($biblereader) {
    global $CFG;
    require_once($CFG->libdir.'/gradelib.php');

    return grade_update('mod/biblereader', $biblereader->course, 'mod', 'biblereader',
            $biblereader->id, 0, null, array('deleted' => 1));
}

/**
 * Its parameters are:
 * stdClass $instance the activity module settings.
 * int $userid A user ID or 0 for all users.
 * bool $nullifnone If a single user is specified, $nullifnone is true and the user has no grade then a grade item with a null rawgrade should be inserted
 */
 /*
function biblereader_update_grades($instance, $userid=0, $nullifnone=true) {
  // retrieving the grades for the user from the activity module's own tables
  // then calling biblereader_grade_item_update()
  global $CFG, $DB;

  // workaround for buggy PHP versions
  if (!function_exists('grade_update'))
      require_once($CFG->libdir.'/gradelib.php');

  // do not store a grade with a score of zero
  if ($instance->grade == 0) {
      biblereader_grade_item_update($instance);

  // store a user grade with a score higher than zero
  } elseif ($userid && $instance->grade > 0) {
      $grade = new stdClass();
      $grade->userid   = $userid;
      # $grade->rawgrade = (float) 1.000000;
      $grade->rawgrade = $instance->grade;
      biblereader_grade_item_update($instance, $grade);

  // reset grade for a specific student
  } elseif ($userid and $nullifnone) {
      $grade = new stdClass();
      $grade->userid   = $userid;
      $grade->rawgrade = null;
      biblereader_grade_item_update($instance, $grade);

  // default: do not store grade
  } else {
      biblereader_grade_item_update($instance);
  }
}
*/

/* Navigation API */

/**
 * Extends the settings navigation with the biblereader settings
 *
 * This function is called when the context for the page is a biblereader module. This is not called by AJAX
 * so it is safe to rely on the $PAGE.
 *
 * @param settings_navigation $settingsnav complete settings navigation tree
 * @param navigation_node $biblereadernode biblereader administration node
 */
/*
function biblereader_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $biblereadernode=null) {
    global $PAGE;
    //if (has_capability('mod/biblereader:editquestions', $PAGE->cm->context)) {
    if(true){
        $url = new moodle_url('/mod/biblereader/mod_form.php', array('id' => $PAGE->cm->id));
        $biblereadernode->add(get_string('settings', 'mod_biblereader'), $url,
                navigation_node::TYPE_SETTING);
    }
}
*/

/*
// DEBUG: moved to externallib.php
function biblereader_submit_grading_form($instance, $userid) {
    // debug
    # $this->biblereader->grade = 100;
    # biblereader_update_grades($instance, $this->request->userid);
    error_log('TODO: save data');
    return 'Yay!';
}
*/

/*
function biblereader_grading_areas_list() {
  return [
        'biblereader' => 'placeholder', // get_string('grade_biblereader_header', 'biblereader'),
    ];
}
*/

/**
 * Add a get_coursemodule_info function in case any biblereader type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
 function biblereader_get_coursemodule_info($coursemodule) {
     # error_log('breakpoint1');
     global $DB;

     $dbparams = ['id' => $coursemodule->instance];
     $fields = 'id, name, intro, introformat, minimumpercentage';
     if (!$biblereader = $DB->get_record('biblereader', $dbparams, $fields)) {
         return false;
     }

     $result = new cached_cm_info();
     $result->name = $biblereader->name;

     if ($coursemodule->showdescription) {
         // Convert intro to html. Do not filter cached version, filters run at display time.
         $result->content = format_module_intro('biblereader', $biblereader, $coursemodule->id, false);
     }

     // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
     if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
         $result->customdata['customcompletionrules']['minimumpercentage'] = $biblereader->minimumpercentage;
         # error_log('breakpoint2');
     }

     return $result;
 }

 /**
  * Obtains the automatic completion state for this biblereader based on any conditions
  * in biblereader settings.
  *
  * @param object $course Course
  * @param object $cm Course-module
  * @param int $userid User ID
  * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
  * @return bool True if completed, false if not, $type if conditions not set.
  */
 function biblereader_get_completion_state($course, $cm, $userid, $type) {
     global $CFG,$DB;     

     // Get biblereader details
     $biblereader = $DB->get_record('biblereader', ['id' => $cm->instance], '*', MUST_EXIST);

     // If completion option is enabled, evaluate it and return true/false
     if ($biblereader->minimumpercentage) {
          $total = $DB->get_field_sql("
 SELECT
     COUNT(id) AS `total`
 FROM
     {biblereader_plans}
 WHERE
     semester = ? AND
     program = ?
",      [
          $biblereader->semester,
          $biblereader->program
        ]);

        $read = $DB->get_field_sql("
SELECT
   COUNT(plans.id) AS `read`
FROM
   {biblereader_plans} plans
   LEFT OUTER JOIN ({biblereader_completions} completions)
     ON (plans.id = completions.pageid)
WHERE
     plans.semester = ? AND
     plans.program = ? AND
     completions.userid = ?
",      [
          $biblereader->semester,
          $biblereader->program,
          $userid
        ]);

        // DEBUG:
        # $biblereader->minimumpercentage = 4;

        // return true/false
        #error_log("breakpoint");
        #error_log(round(($read / $total) * 100));
        return (round(($read / $total) * 100) >= $biblereader->minimumpercentage ? true : false );

     } else {
         // Completion option is not enabled so just return $type
         return $type;
     }
 }



 /**
  * Callback which returns human-readable strings describing the active completion custom rules for the module instance.
  *
  * @param cm_info|stdClass $cm object with fields ->completion and ->customdata['customcompletionrules']
  * @return array $descriptions the array of descriptions for the custom rules.
  */
 function biblereader_get_completion_active_rule_descriptions($cm) {
     # error_log('breakpoint3');
     // Values will be present in cm_info, and we assume these are up to date.
     if (empty($cm->customdata['customcompletionrules']) || $cm->completion != COMPLETION_TRACKING_AUTOMATIC) {
         return [];
     }

     $descriptions = [];
     foreach ($cm->customdata['customcompletionrules'] as $key => $val) {
         switch ($key) {
             case 'minimum':
             case 'minimumpercentage':
                 if (!empty($val)) {
                     $descriptions[] = get_string('completionminimumpercentagedesc', 'biblereader', $val);
                 }
                 break;
             default:
                 break;
         }
     }
     return $descriptions;
 }


 /**
  * Fetch the list of custom completion rules that this module defines.
  *
  * @return array
  */
/*
 function get_defined_custom_rules(): array {
     return [
         'minimumpercentage'
     ];
 }
 */

 /**
 * Fetches the completion state for a given completion rule.
 *
 * @param string $rule The completion rule.
 * @return int The completion state.
 */
 /*
public function get_state(string $rule): int {
    global $DB;
    // Make sure to validate the custom completion rule first.
    $this->validate_rule($rule);

    // Fetch the completion status of the custom completion rule.
    $status = COMPLETION_INCOMPLETE;
    if ($rule === 'completionsubmit') {
        $hassubmission = $DB->record_exists('choice_answers', ['choiceid' => $this->cm->instance, 'userid' => $this->userid]);
        $status = $hassubmission ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    return $status;
}
*/

/**
 * Returns an associative array of the descriptions of custom completion rules.
 *
 * @return array
 */
/*
function get_custom_rule_descriptions(): array {
    return [
        'completionsubmit' => get_string('completiondetail:submit', 'choice')
    ];
}
*/

/**
 * Returns an array of all completion rules, in the order they should be displayed to users.
 *
 * @return array
 */
 /*
public function get_sort_order(): array {
    return [
        'completionview',
        'completionminattempts',
        'completionusegrade',
        'completionpassorattemptsexhausted',
    ];
}
*/

/**
 * Returns a list of important dates in mod_choice
 *
 * @return array
 */
 /*
protected function get_dates(): array {
    $timeopen = $this->cm->customdata['timeopen'] ?? null;
    $timeclose = $this->cm->customdata['timeclose'] ?? null;
    $now = time();
    $dates = [];

    if ($timeopen) {
        $openlabelid = $timeopen > $now ? 'activitydate:opens' : 'activitydate:opened';
        $dates[] = [
            'label' => get_string($openlabelid, 'course'),
            'timestamp' => (int) $timeopen,
        ];
    }

    if ($timeclose) {
        $closelabelid = $timeclose > $now ? 'activitydate:closes' : 'activitydate:closed';
        $dates[] = [
            'label' => get_string($closelabelid, 'course'),
            'timestamp' => (int) $timeclose,
        ];
    }

    return $dates;
}
*/
