<?php  // $Id: view.php
/**
 * This page prints a particular instance of planner
 * 
 * @author Onno Schuit (www.solin.eu)
 * @version $Id: 1.0
 * @package planner
 **/


    require_once("../../config.php");
    require_once("lib.php");
    require_once("class.planner_base.php");
   
    
    $id = required_param('id', PARAM_INT);
    $group_id = optional_param('group', false, PARAM_INT);
    planner_set_variables('planner');
    require_course_login($course, false, $cm);

    if (!$planner = planner_get_planner($cm->instance)) {
        error("Course module is incorrect");
    }
    
   
    
    $strplanner = get_string('modulename', 'planner');
    $strplanners = get_string('modulenameplural', 'planner');

    $navigation = build_navigation('', $cm);
    print_header_simple(format_string($planner->name), "", $navigation, "", "", true,
                  update_module_button($cm->id, $course->id, $strplanner), navmenu($course, $cm));
    
    
                 
?>
  <style>
    <?php require_once("css/forms.css"); ?>
  </style>
  <script type="text/javascript">
  </script>

<?php

    groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/planner/view.php?id=' . $id); // temp 
    echo "<br/><br/>"; // temp
    $planner_base = new planner_base($cm, $id, $context);
    $planner_base->include_javascript();
    if (has_capability('mod/planner:readforeignplanning', $context)) {
        $planner_base ->print_students_list();  
    } else {
        $obj = new Object;
        $obj->link = "{$CFG->wwwroot}/mod/planner/request_unlock.php?id=$id";
        echo "<div class='planner_request_unlock'>" . get_string('request_unlock_link', 'planner', $obj). "</div>";
        //$planner->print_all_activities($USER->id);
        $planner_base->print_all($USER);

    }
    
  
  
    
    print_footer($course);
?>
