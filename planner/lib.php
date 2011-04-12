<?php  // $Id: lib.php
/**
 * Library of functions and constants for module planner
 *
 * @author 
 * @version $Id: lib.php
 * @package planner
 **/


require_once("helper_methods.php");
//require_once("lib_export.php");

/*
$PLANNER_ERRORS = array();
$EXCLUDED_MODS = array('planner','data','chat','forum');



function planner_print_coach($student) {
    if (! $coach = planner_get_coach($student->id)) {
        return "";
    }
    
    if ($fields = planner_get_custom_fields($coach->id)) {
        // to do 
    }
    echo "<div class='planning_coach_details'>" . get_string('coach_details','planner',$coach) ."</div>";
} // function planner_print_coach


function planner_print_coord($student) {
    if (! $coord = planner_get_coordinator($student->id)) {
        return "";
    }
    
    if ($fields = planner_get_custom_fields($coord->id)) {
        // to do 
    }
    echo "<div class='planning_coord_details'>" . get_string('coord_details','planner',$coord) ."</div>";
} // function planner_print_coord


function planner_print_legend() {
    $colors = array(
        'green',
        'orange',
        'yellow',
        'red',
        'purple',
        'blue');  
  
    echo "<table class='planner_legend'>";
    foreach ($colors as $color) {
        echo "<tr>
                <td><div style='width:75px;height:20px;margin-bottom:3px;background-color:$color;'></div></td>
                <td>" . get_string("status_$color",'planner') ."</td>
              </tr>";
    }
    echo "</table>";
} // planner_print_legend


function planner_print_all($student) {
    global $CFG, $id, $course;
    if (! ($course_sections = planner_get_course_sections($course->id)) ) {
        error(get_string('no_titled_sections','planner'));
        return false;
    }
    planner_print_legend();
    echo "<table class='planner_all' cellpadding='4'>";
    foreach($course_sections as $course_section) {
        echo "<tr><td colspan='6'><hr/></td></tr>";
        echo "<tr><td class='planner_section_title' colspan='6'><b>{$course_section->summary}</b></td></tr>";
   
        echo "<tr class='planner_activity_headers'>
                  <th><b>" . get_string('activity'). "</b></th>
                  <th><b>" . get_string('plan_date','planner'). "</b></th>
                  <th><b>" . get_string('status'). "</b></th>
                  <th><b>" . get_string('date_delivered','planner'). "</b></th>
                  <th><b>" . get_string('date_marked','planner'). "</b></th>
                  <th><b>" . get_string('mark','planner'). "</b></th>
              </tr>";
        planner_all_activities($course_section->id, $student->id);
    }
  
    echo "</table>";    
} // function planner_print_all


function planner_print_status($obj_date, $grade) {
    if ( !$obj_date ) {
        return "";  
    }
    $color = planner_get_status($obj_date->end_date, $grade);
    return "<div class='planner_status_colorbox' style='width:75px;height:20px;background-color:$color;'></div>";
} // function planner_print_status


function planner_all_activities($coursesectionid, $studentid) {
    global $course, $CFG, $USER, $EXCLUDED_MODS, $id, $context;
    $section = planner_get_section($coursesectionid);
    $modinfo = get_fast_modinfo($course);
    $dates = planner_get_dates($studentid, $coursesectionid);
    $sectionmods = explode(",", $section->sequence);
    foreach ($sectionmods as $modnumber) {
        // Please note: $modnumber = course_modules.id
        if (( in_array($modinfo->cms[$modnumber]->modname, $EXCLUDED_MODS) ) || ($modinfo->cms[$modnumber]->modname == '') ) {
            continue;
        }
        $instancename = format_string($modinfo->cms[$modnumber]->name, true,  $course->id); 
        $link = "<a href='{$CFG->wwwroot}/mod/{$modinfo->cms[$modnumber]->modname}/view.php?id={$modinfo->cms[$modnumber]->id}'>{$instancename}</a>";       
        
        $obj_date = ($d = planner_end_date($dates, $modnumber)) ? $d : false;
        $grade = ($g = planner_get_grade($studentid, $modinfo->cms[$modnumber]->modname, $modinfo->cms[$modnumber]->instance)) ? $g : false;
        
        $readonly = ($studentid == $USER->id) ? false : true;
        $time_created = ($grade) ? $grade->timecreated : "";
        $time_modified = ($grade) ? $grade->timemodified : "";
        
        echo "<tr>
                <td>$link</td>                
                <td>" . planner_date_field($dates, $modnumber, $coursesectionid, $readonly) . "</td>
                <td>" . planner_print_status($obj_date, $grade) . "</td>
                <td>" . planner_timestamp2human($time_created) . "</td>
                <td>" . planner_timestamp2human($time_modified) . "</td>
                <td>". planner_get_mark($grade) ."</td>
              </tr>";
    }
    
} // function planner_all_activities


function planner_print_sections($courseid, $studentid = false) {
    global $CFG, $id;
    if (! ($course_sections = planner_get_course_sections($courseid)) ) {
        error(get_string('no_titled_sections','planner'));
        return false;
    }    
    echo "<table class='planner_sections' cellpadding='3'>";
    $str_student = ($studentid) ? "&studentid=$studentid" : "";
    foreach($course_sections as $course_section) {
      echo "<tr><td><a href='{$CFG->wwwroot}/mod/planner/activities.php?id=$id&coursesectionid={$course_section->id}$str_student'>{$course_section->summary}</a></td></tr>";  
    }
  
    echo "</table>";
}// function planner_print_sections


function planner_get_course_sections($courseid) {
    global $CFG;
    return get_records_sql("SELECT * 
                            FROM {$CFG->prefix}course_sections 
                            WHERE summary <> '' 
                            AND course = $courseid
                            ORDER BY section");
    //AND section > 0     
} // function planner_get_course_sections()


function planner_get_section($coursesectionid) {
    global $CFG;
    if (! ($sections = get_records_sql("SELECT * 
                            FROM {$CFG->prefix}course_sections 
                            WHERE id = $coursesectionid")) ) {
        error("Could not find section with id $coursesectionid");
        return false;
    }
    return $sections[key($sections)];      
} // function planner_get_section


function planner_print_activities($coursesectionid, $studentid) {
    global $course;
    planner_print_section(planner_get_section($coursesectionid),
                          $coursesectionid,
                          $studentid);
    //get_list_of_plugins('mod')
} // function planner_print_activities


function planner_print_section($section, $course_section_id, $studentid=false) {
    global $CFG, $USER, $EXCLUDED_MODS, $id, $context;

    //exit(print_r($modinfo->cms));
    planner_print_legend();
    echo "<form action='$CFG->wwwroot/mod/planner/save.php' method='post' name='planner'>
            <input type='hidden' name='id' value='$id' />
            <input type='hidden' name='coursesectionid' value='$course_section_id' />
            <table class='planner_dates' cellpadding='3'>
              <tr>
                  <th><b>" . get_string('activity'). "</b></th>
                  <th><b>" . get_string('end_date','planner'). "</b></th>
                  <th><b>" . get_string('status'). "</b></th>
                  <th><b>" . get_string('date_delivered','planner'). "</b></th>
                  <th><b>" . get_string('date_marked','planner'). "</b></th>
                  <th><b>" . get_string('mark','planner'). "</b></th>
                
              </tr>";
    planner_all_activities($course_section_id, $studentid);
    echo "  </table>";
    // Teachers cannot save students' plannings
    if ($studentid == $USER->id ) {           
        echo "<input type='submit' name='submit' value='". get_string('save', 'planner') ."'/>";
    }
    echo "</form>";
} // function planner_print_section


function planner_date_field($dates, $course_module_id, $course_section_id, $readonly=false) {
    global $CFG;
    $date = planner_end_date($dates, $course_module_id);
    if ($readonly) {
        return planner_readonly_field($date);    
    }
    
    if ($date) {
        return planner_update_field($date, $course_module_id, $course_section_id);  
    } else {
        return planner_editable_field(false, $course_module_id, $course_section_id);
    }
} // function planner_date_field


function planner_update_field($date, $course_module_id, $course_section_id) {
    global $CFG;
    if (planner_inside_grace($date->grace_start)) {
        return planner_editable_field($date, $course_module_id, $course_section_id);      
    }
    return planner_readonly_field($date);
    
} // function planner_update_field


function planner_editable_field($date, $course_module_id, $course_section_id) {
    global $CFG;
    $end_date = ($date) ? planner_timestamp2human($date->end_date) : "";
    $date_id = ($date) ? $date->id : "";    
    return "<input type='hidden' value='$date_id' name='planner[$course_module_id][date_id]' />
            <input type='hidden' value='$course_section_id' name='planner[$course_module_id][course_section_id]' />
            <input style='width:79px' value='$end_date' name='planner[$course_module_id][end_date]' id='planner_{$course_module_id}' class='planner_date_field' type='text' />
            <img id='planner_{$course_module_id}_trigger' class='calendarTrigger' title='Kalender' alt='Kalender' src='{$CFG->wwwroot}/mod/planner/images/calendar.png'/>
            <script type='text/javascript'>
              Calendar.setup({
                inputField : 'planner_{$course_module_id}',
                ifFormat : '%e-%m-%Y',
                button : 'planner_{$course_module_id}_trigger',
                align : 'Tl', // alignment (defaults to 'Bl')
                singleClick : true
              }); 
            </script>";  
} // function planner_create_field


function planner_readonly_field($date) {
    if ( (!$date) || (!property_exists($date, 'end_date')) ) return "";
    $end_date = planner_timestamp2human($date->end_date);
    return "<input  style='width:79px' readonly='readonly' value='$end_date' name='dummy_{$date->id}' id='dummy_{$date->id}' class='planner_date_field planner_readonly' type='text' />";    
} // function planner_readonly_field


function planner_end_date($dates, $course_module_id) {
    if (! is_array($dates) ) return false;
    foreach($dates as $date) {
        if ($course_module_id == $date->course_module_id) {
            return $date;
        }
    }
    return false;
}


function planner_timestamp2human($timestamp) {
    return ($timestamp > 0 ) ? date("d-m-Y", $timestamp) : "";  
} // function planner_timestamp2human


function planner_print_navigation() {
    global $CFG, $id;
    echo "<div class='planner_navigation'><a href='{$CFG->wwwroot}/mod/planner/view.php?id=$id'>". get_string('back', 'planner') ."</a></div>";
} // function planner_print_navigation


function planner_get_dates($user_id, $course_section_id = false) {
    global $CFG, $planner;
    $condition = ($course_section_id) ? " AND course_section_id = $course_section_id " : "";
    return get_records_sql("SELECT * 
                            FROM {$CFG->prefix}planner_dates 
                            WHERE user_id = $user_id 
                            AND planner_id = $planner->id
                            $condition");  
} // function planner_get_dates


function planner_save($dates) {
    foreach($dates as $course_module_id => $date) {
        if ( !( planner_save_date($course_module_id, $date)) ) {
            return false;    
        }
    }
    return true;
} // function planner_save


function planner_save_date($course_module_id, $date) {
    global $USER, $planner, $PLANNER_ERRORS;
    
    if ($date['end_date'] == '') return true; // no end date given
    
    $obj = new stdClass();
    $obj->user_id = $USER->id;
    $obj->planner_id = $planner->id;
    $obj->course_section_id = $date['course_section_id'];
    $obj->end_date = planner_normalize_date($date['end_date']);
    //exit("\$obj->end_date = ".$obj->end_date);
    $obj->course_module_id = $course_module_id;    

    if ($date['date_id'] == '') {
        $obj->grace_start = Time();
        if (! $output->id = insert_record('planner_dates', $obj)) {
            $PLANNER_ERRORS[] = "call of insert_record returned false (in planner_save_date), \$date = " . print_r($obj);
            return false;
        }
        return $output->id;       
    } else {
        // check user's credentials        
        if (! ($planning = planner_check_credentials($date['date_id'])) ) return false;
        if (!planner_inside_grace($planning->grace_start)) {
            $PLANNER_ERRORS[] = "Operation not allowed: grace period has expired!";
            error("Operation not allowed: grace period has expired!");
            return false;
        }
        $obj->id = $date['date_id'];
        if (update_record("planner_dates", $obj)) return true;
        $PLANNER_ERRORS[] = "call of update_record returned false (in planner_save_date), \$date = " . print_r($obj);
        return false;        
    }
} // function planner_save


function planner_inside_grace($timestamp) {
    global $planner, $PLANNER_ERRORS;
    return ( Time() <= ($timestamp + ($planner->grace_days * 24 * 60 * 60)) );     
} // function planner_inside_grace


function planner_normalize_date($input_date) {
    $aResult = strptime($input_date,"%d-%m-%Y");
    //exit(print_r($aResult));
    
    // setting hour, minute, second to something other than 0 results in conversion troubles on some systems
    return mktime(0, 0, 0, $aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900);    
    
    //return strtotime($input_date);
} // function planner_normalize_date


function planner_check_credentials($date_id) {
    global $USER, $PLANNER_ERRORS;
    if (! ($planning = planner_get_date($date_id)) ) return false; 
    if ($planning->user_id != $USER->id) {
        $PLANNER_ERRORS[] = "Operation not allowed! User ids do not match! (in planner_check_credentials)";
        return false;          
    } 
    return $planning;
} // function planner_check_credentials


function planner_get_date($date_id) {
    global $PLANNER_ERRORS;
    if ( !($rec = get_record("planner_dates", "id", $date_id)) ) {
        $PLANNER_ERRORS[] = "could not find record with id = $date_id (in planner_get_date)";
        return false;
    }
    return $rec;
} // function planner_get_date


function planner_request_unlock() {
    global $CFG, $USER, $PLANNER_ERRORS, $course, $context, $id;
    if (! ($members = planner_get_members($USER->id, $course->id)) ) {
        $PLANNER_ERRORS[] = "could not find any group members for user with id = {$USER->id} (in planner_request_unlock)";
        return false;          
    }
    
    // Compose and send email...
    foreach ($members as $member) {
        if (has_capability('mod/planner:unlockplanning', $context, $member->userid)) {
            if (! (planner_mail_unlock_request($member->userid)) ) {
                error(get_string('error_sending_request','planner'), 
                      "{$CFG->wwwroot}/mod/planner/view.php?id=$id");
            }
        }            
    }
    return true;  
} // function planner_request_unlock


function planner_mail_unlock_request($recipient_id) {
    global $id, $USER, $CFG;
    if (! ($recipient = get_record('user', 'id', $recipient_id)) ) {
        $PLANNER_ERRORS[] = "could not find recipient with id = $recipient_id (in planner_mail_unlock_request)";
        return false;
    }
    $body = get_string('request_body', 'planner', $USER); // text explaining unlock request etc.
    $obj = new Object;
    $obj->link = "$CFG->wwwroot/mod/planner/form_unlock.php?id=$id&owner_id=$USER->id";
    $body .= get_string('request_link', 'planner', $obj);
    return email_to_user($recipient, $USER, get_string('request_subject', 'planner', $USER), $body, $body);    
} // function planner_mail_unlock_request


// Assumption (or prerequisite): each student user belongs to exactly one group, 
// which he shares with a teacher (and maybe a coordinator)  
function planner_get_members($userid,$courseid) {
    global $CFG;
    $where = " groupid IN (
                 SELECT groupid
                 FROM {$CFG->prefix}groups_members
                 WHERE userid = $userid
               ) 
               AND groupid IN (
                 SELECT id 
                 FROM {$CFG->prefix}groups
                 WHERE courseid = $courseid)";
    return get_records_select('groups_members', $where);
} // function planner_get_members


function planner_unlock($planner, $owner_id) {
    global $context, $USER;
    
    if (! has_capability('mod/planner:unlockplanning', $context, $USER->id) ) {
        error(get_string("unlock_not_allowed", "planner"));  
    }   
    
    if (! (planner_current_user_in_same_group_as($owner_id)) ) {
        error(get_string("unlock_not_allowed", "planner"));
    }  
    if (set_field_select('planner_dates', 'grace_start', Time(), "planner_id = {$planner->id} AND user_id = $owner_id")) {
        planner_mail_unlocked_notification($owner_id);      
    }
    return true;
} // function planner_unlock


function planner_mail_unlocked_notification($owner_id) {
    global $USER;
    
    if (! ($owner = get_record('user', 'id', $owner_id)) ) {
        $PLANNER_ERRORS[] = "could not find planner's owner with id = $owner_id (in planner_mail_unlocked_notification)";
        return false;
    }     
    
    $body = get_string('unlocked_notification_body', 'planner');
    $subject = get_string('unlocked_notification_subject', 'planner');
    
    return email_to_user($owner, $USER, $subject, $body, $body);  
} // function planner_mail_unlocked_notification


function planner_current_user_in_same_group_as($user_id) {
    global $USER, $course;
    $members = planner_get_members($user_id, $course->id);
    foreach ($members as $member) {
        if ($member->userid == $USER->id) return true;  
    }
    return false;
} // function planner_current_user_in_same_group_as


// Assumption: both study coordinator and teacher have 'mod/planner:readforeignplanning' capability
function planner_print_students_list() {
    global $context, $CFG, $USER, $course, $PLANNER_ERRORS, $id;
    
    if (! has_capability('mod/planner:readforeignplanning', $context, $USER->id) ) {
        error("You are not authorized to perform this operation");  
    }
    
    if (! ($students = planner_get_students($USER->id)) ) {
        echo get_string('empty_student_list','planner');
        return;      
    }
    
    echo "<table class='planner_student_list'>";            
    foreach ($students as $student) {
        // get_string('export_to_excel_text', 'planner', $student)
        echo "<tr>
                <td>
                  <a href='$CFG->wwwroot/mod/planner/teacher_view.php?id=$id&studentid=$student->id'>$student->firstname $student->lastname</a>
                </td>
                <td>
                  <form action='$CFG->wwwroot/mod/planner/export.php' method='post' name='export'>
                    <input type='hidden' name='studentid' value='$student->id' />
                    <input type='hidden' name='id' value='$id' />
                    <input type='submit' value='". get_string('export_to_excel_button', 'planner') ."' name='go'/>
                  </form>
                </td>
              </tr>"; 
        
    }
    echo "</table>";
          
    
} // function planner_print_students_list


function planner_print_owner($student) {
    global $id, $CFG;
    echo "<form action='$CFG->wwwroot/mod/planner/export.php' method='post' name='export'>
            <input type='hidden' name='studentid' value='$student->id' />
            <input type='hidden' name='id' value='$id' />    
            <div class='planning_owner'>" . get_string('planning_owner','planner',$student);
            
    echo "    <input type='submit' value='". get_string('export_to_excel_button', 'planner') ."' name='go'/>";
    echo "  </div>
          </form>";  
} // function planner_print_owner


function planner_get_coordinator($studentid) {
    global $course, $context;
    $coords = get_members_by_role($studentid, "r.shortname LIKE 'coordinator'");
    return $coords[key($coords)];
} // function planner_get_coordinator


function planner_get_coach($studentid) {
    global $course, $context;
    $coaches = get_members_by_role($studentid, "r.shortname LIKE 'coach'");
    return $coaches[key($coaches)];
} // function planner_get_coordinator


function planner_get_teachers($studentid) {
    return get_members_by_role($studentid, "(r.shortname LIKE 'teacher' OR r.shortname LIKE 'editingteacher')");
} // function planner_get_teachers


function planner_get_students($userid) {
    return get_members_by_role($userid, "r.shortname LIKE 'student'");
} // function planner_get_teachers





function get_members_by_role($userid, $sql_where) {
    global $CFG, $course;   
    return get_records_sql("SELECT DISTINCT u.*
                            FROM {$CFG->prefix}groups_members gm
                            INNER JOIN {$CFG->prefix}groups g ON g.id = gm.groupid
                            INNER JOIN {$CFG->prefix}user u ON u.id = gm.userid
                            INNER JOIN {$CFG->prefix}role_assignments ra ON ra.userid = u.id 
                            INNER JOIN {$CFG->prefix}role r ON r.id = ra.roleid
                            WHERE gm.groupid IN (
                              SELECT groupid 
                              FROM {$CFG->prefix}groups_members 
                              WHERE userid=$userid
                            )
                            AND $sql_where
                            AND g.courseid = $course->id ");     
} // function get_members_by_role


function planner_get_custom_fields($userid) {
    global $CFG;
    if (! ($values = get_records_sql("SELECT *
                               FROM {$CFG->prefix}user_info_data v
                               INNER JOIN {$CFG->prefix}user_info_field f ON v.fieldid = f.id
                               WHERE v.userid=$userid")) ) {
        return false;
    }
    $compiled = array();
    foreach($values as $value) {
        $compiled[$value->shortname] = $value->data;
    }
    return $compiled;
} // function get_custom_fields

*/

?>
