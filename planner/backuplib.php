<?php //$Id: backuplib.php
/**
 * Planner's backup routines
 *
 * @version $Id: backuplib.php
 * @author Onno Schuit (www.solin.eu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package planner
 **/

// See http://kb.ucla.edu/articles/moodle-module-backup-and-restore for an explanation of Moodle 1.9
// backup and restore

    // This function executes all the backup procedures for this mod
    function planner_backup_mods($bf, $preferences) {
        global $CFG;
        if (! $planners = get_records("planner", "course", $preferences->backup_course, "id") ) {
            return true;
        }
        $status = true;
        foreach ($planners as $planner) {
            if (backup_mod_selected($preferences, 'planner', $planner->id)) {
                $status = $status && planner_backup_one_mod($bf, $preferences, $planner);
            }
        }
        return $status;  
    } // function planner_backup_mods


    // Write the backup data to an xml file
    function planner_backup_one_mod($bf, $preferences, $planner) {
        global $CFG;
        if (is_numeric($planner)) {
            $planner = get_record('planner', 'id', $planner);
        }
        fwrite($bf, start_tag("MOD", 3, true) );
        fwrite($bf, full_tag("ID", 4, false, $planner->id) );
        fwrite($bf, full_tag("MODTYPE", 4, false, "planner") );
        fwrite($bf, full_tag("NAME", 4, false, $planner->name) );
        fwrite($bf, full_tag("GRACE_DAYS", 4, false, $planner->grace_days) );

        // Backup associated table planner_dates
        if (backup_userdata_selected($preferences, 'planner', $planner->id)) {
            if (!backup_planner_dates($bf, $preferences, $planner->id)) {
                return false;
            }
        }
        return fwrite($bf, end_tag("MOD", 3, true));
    } // function planner_backup_one_mod


    function backup_planner_dates($bf, $preferences, $planner_id) {
        global $CFG;
        if (! $planner_dates = get_records("planner_dates", "planner_id", $planner_id, "id") ) {
            return true;
        }
        $status = fwrite($bf, start_tag("PLANNER_DATES", 4, true) );
        foreach ($planner_dates as $date) {
            $status = fwrite($bf, start_tag("PLANNER_DATE", 5, true) );
            fwrite($bf, full_tag("ID", 6, false,$date->id) );
            fwrite($bf, full_tag("PLANNER_ID", 6, false,$date->planner_id) );
            fwrite($bf, full_tag("USER_ID", 6, false,$date->user_id) );
            fwrite($bf, full_tag("COURSE_MODULE_ID", 6, false,$date->course_module_id) );
            fwrite($bf, full_tag("COURSE_SECTION_ID", 6, false,$date->course_section_id) );
            fwrite($bf, full_tag("END_DATE", 6, false,$date->end_date) );
            fwrite($bf, full_tag("GRACE_START", 6, false,$date->grace_start) );

            $status = $status && fwrite($bf, end_tag("PLANNER_DATE", 5, true) );
        }
        return $status && fwrite($bf, end_tag("PLANNER_DATES", 4, true) );
    } // function backup_planner_dates

    
    // Returns an array of info (name, value)
    function planner_check_backup_mods($course, $user_data=false, $backup_unique_code, $instances=null) {
        if (!empty($instances) && is_array($instances) && count($instances)) {
            $info = array();
            foreach ($instances as $id => $instance) {
                $info += planner_check_backup_mods_instances($instance,$backup_unique_code);
            }
            return $info;
        }
        //First the course data
        $info[0][0] = get_string("modulenameplural","planner");
        if ($ids = planner_ids($course)) {
            $info[0][1] = count($ids);
        } else {
            $info[0][1] = 0;
        }

        //Now, if requested, the user_data
        if ($user_data) {
            $info[1][0] = get_string("dates","planner");
            if ($ids = planner_dates_ids_by_course ($course)) { 
                $info[1][1] = count($ids);
            } else {
                $info[1][1] = 0;
            }
        }
        return $info;
    } // function planner_check_backup_mods


    // Returns an array of info (name, value)
    function planner_check_backup_mods_instances($instance,$backup_unique_code) {
        //First the course data
        $info[$instance->id.'0'][0] = '<b>'.$instance->name.'</b>';
        $info[$instance->id.'0'][1] = '';

        //Now, if requested, the user_data
        if (!empty($instance->userdata)) {
            $info[$instance->id.'1'][0] = get_string("dates","planner");
            if ($ids = planner_dates_ids_by_instance ($instance->id)) { 
                $info[$instance->id.'1'][1] = count($ids);
            } else {
                $info[$instance->id.'1'][1] = 0;
            }
        }
        return $info;
    } // function planner_check_backup_mods_instances


    // Returns content with links encoded.
    // In this case, we don't have any links to other activities (in text / content),
    // so we just return the content.
    function planner_encode_content_links ($content, $preferences) {
        return $content;
    } // function planner_encode_content_links 


    // Returns an array of planner id 
    function planner_ids ($course) {
        global $CFG;
        return get_records_sql("SELECT id, course
                                FROM {$CFG->prefix}planner
                                WHERE course = '$course'");
    } // function planner_ids 

    
    // Returns an array of planner_submissions id
    function planner_dates_ids_by_course($course_id) {
        global $CFG;
        return get_records_sql("SELECT d.id, d.planner_id
                                FROM {$CFG->prefix}planner_dates d,
                                     {$CFG->prefix}planner p
                                WHERE p.course = $course_id AND
                                      a.planner_id = p.id");
    } // function planner_dates_ids_by_course 


    // Returns an array of planner_submissions id
    function planner_dates_ids_by_instance ($instance_id) {
        global $CFG;
        return get_records_sql("SELECT id, planner_id
                                FROM {$CFG->prefix}planner_dates 
                                WHERE planner_id = $instance_id");
    } // function planner_dates_ids_by_instance 
?>
