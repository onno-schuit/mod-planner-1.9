<?php  // $Id: teacher_view.php
/**
 * This page prints planner of a particular student (for benefit of teacher)
 * 
 * @author Onno Schuit (www.solin.eu)
 * @version $Id: 1.0
 * @package planner
 **/


    require_once("../../config.php");
    require_once("lib.php");
    require_once("class.planner_base.php");
   
    
    $id = required_param('id', PARAM_INT);
    $studentid = required_param('studentid', PARAM_INT);
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
    
    if (has_capability('mod/planner:readforeignplanning', $context)) {
        
        if (! ($student = get_record('user', 'id', $studentid)) ) {
            error(get_string('no_student_found', 'planner'));
        } 
        $planner_base = new planner_base($cm, $id, $context);
        $planner_base->back_to_list();
        $planner_base->print_owner($student);
        $planner_base->print_all($student);
        $planner_base->back_to_list();
    } 
    
    print_footer($course);
?>
