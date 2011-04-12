<?php  // $Id: save.php
/**
 * This page prints a particular instance of planner
 * 
 * @author 
 * @version $Id: save.php
 * @package planner
 **/


    require_once("../../config.php");
    require_once("lib.php"); 
    require_once("class.planner_base.php");
                             
    
    $id = required_param('id', PARAM_INT);                 // Course Module ID
    //$coursesectionid = required_param('coursesectionid', PARAM_INT);                 
    $planner_dates = required_param('planner', PARAM_RAW);                 
    
    
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
    if (! $planner_base->save($planner_dates) ) {     
        error( get_string('save_error', 'planner') );         
    } else {
        redirect("{$CFG->wwwroot}/mod/planner/view.php?id=$id", $message = get_string('data_saved', 'planner'), $delay = 1);
    }
    
    print_footer($course);
?>
