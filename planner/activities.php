<?php  // $Id: activities.php



    require_once("../../config.php");
    require_once("lib.php");
    require_once("class.planner_base.php");
   
    
    $id = required_param('id', PARAM_INT);                 // Course Module ID
    $coursesectionid = required_param('coursesectionid', PARAM_INT);                 // CourseSection ID
    $studentid = optional_param('studentid', false, PARAM_INT);  // Authenticated in relevant functions of lib.php
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

    $planner_base = new planner_base($cm, $id, $context);
    $planner_base->include_javascript();
    $planner_base->print_navigation();
    if ( $studentid && ($USER->id != $studentid) ){
        if ($student = get_record('user', 'id', $studentid))  {
            $planner_base->print_owner($student);
        } 
    } else {
        $studentid = $USER->id; 
    }

    $planner_base->print_activities($coursesectionid, $studentid);
    
    print_footer($course);
?>
