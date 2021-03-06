<?php  // $Id: helper_methods.php
/**
 * Library of functions and constants for module planner
 *
 * @author 
 * @version $Id: helper_methods.php
 * @package planner
 **/

require("$CFG->dirroot/lib/fpdf/fpdf.php");
 
/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will create a new instance and return the id number 
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted planner record
 **/
function planner_add_instance($planner) {
    
    $planner->timemodified = time();
    return insert_record("planner", $planner);
}

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 **/
function planner_update_instance($planner) {

    $planner->timemodified = time();
    $planner->id = $planner->instance;

    return update_record("planner", $planner);
}


/**
 * Given an ID of an instance of this module, 
 * this function will permanently delete the instance 
 * and any data that depends on it. 
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function planner_delete_instance($id) {

    if (! $planner = get_record("planner", "id", "$id")) {
        return false;
    }

    $result = true;

    if (! delete_records("planner", "id", "$planner->id")) $result = false;
    $tables = array(
        "planner_dates"
    );
    foreach($tables as $table) {
        if (! delete_records($table, "planner_id", "$planner->id")) $result = false;
    }
    return $result;
}


/**
 * Return a small object with summary information about what a 
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 **/
function planner_user_outline($course, $user, $mod, $planner) {
    return $return;
}

/**
 * Print a detailed representation of what a user has done with 
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function planner_user_complete($course, $user, $mod, $planner) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in planner activities and print it out. 
 * Return true if there was output, or false is there was none. 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function planner_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such 
 * as sending out mail, toggling flags etc ... 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function planner_cron () {
    global $CFG;

    return true;
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user.  It also returns a maximum allowed grade.
 * 
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $plannerid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function planner_grades($plannerid) {
   return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of planner. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $plannerid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function planner_get_participants($plannerid) {
    return false;
}

/**
 * This function returns if a scale is being used by one planner
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $plannerid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function planner_scale_used ($plannerid,$scaleid) {
    return false;
}

function planner_get_navigation() {
    global $course;
    if ($course->category) {
        return "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        return '';
    }
} // planner_get_navigation


function planner_get_module_instance() {
    global $course, $cm, $id;
    
    $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    $a  = optional_param('a', 0, PARAM_INT);  // planner ID

    if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
    
        if (! $planner = get_record("planner", "id", $cm->instance)) {
            error("Course module is incorrect");
        }

    } else {
        if (! $planner = get_record("planner", "id", $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $planner->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("planner", $planner->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }
    return $planner;
} // function planner_get_module_instance



function planner_get_planner($plannerid) {
// Gets a full planner record

    if ($planner = get_record("planner", "id", $plannerid)) {
        return $planner;
    }
    return false;
} // function planner_get_planner


function planner_set_variables($module_name) {
    global $cm, $id, $course, $context;
    if (! $cm = get_coursemodule_from_id($module_name, $id)) {
        error("Course Module ID was incorrect");
    }

    if (! $course = get_record("course", "id", $cm->course)) {
        error("Course is misconfigured");
    } 
    
    if (!$context = get_context_instance(CONTEXT_MODULE, $cm->id)) {
        print_error('badcontext');
    }     
} // function planner_set_variables




 
 
?>
