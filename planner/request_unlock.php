<?php  // $Id: request_unlock.php
/**
 * @author 
 * @version $Id: request_unlock.php
 * @package planner
 **/


    require_once("../../config.php");
    require_once("lib.php");
    require_once("class.planner_base.php");
   
    
    $id = required_param('id', PARAM_INT);                 // Course Module ID
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
    if (! $planner_base->request_unlock($planner) ) {     
        error(get_string('request_error', 'planner'));         
    } else {
        redirect("{$CFG->wwwroot}/mod/planner/view.php?id=$id", get_string('request_sent', 'planner') , $delay=2);
    }
    
    print_footer($course);
?>
