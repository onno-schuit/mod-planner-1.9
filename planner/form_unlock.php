<?php  // $Id: form_unlock.php
/**
 * @author 
 * @version $Id: form_unlock.php
 * @package planner
 **/


    require_once("../../config.php");
    require_once("lib.php");
    
    $id = required_param('id', PARAM_INT);                 // Course Module ID
    $owner_id = required_param('owner_id', PARAM_INT);     // userid of student who made the planning
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
    if (! has_capability('mod/planner:unlockplanning', $context) ) {
        error(get_string("unlock_not_allowed", "planner"));  
    }
    if (! ($owner = get_record('user', 'id', $owner_id)) ) {
        $PLANNER_ERRORS[] = "could not find planner's owner with id = $owner_id";
        return false;
    }
    echo "<form class='planner_unlock_form' action='$CFG->wwwroot/mod/planner/unlock_planning.php' method='post' name='unlock'>
            <input type='hidden' value='$id' name='id'/>
            <input type='hidden' value='$owner_id' name='owner_id'/>
            <div class='planner_unlock_button'>Planning van $owner->firstname {$owner->lastname} <input type='submit' name='submit' value='Vrijgeven'/></div> 
          </form>";
    
    print_footer($course);
?>
