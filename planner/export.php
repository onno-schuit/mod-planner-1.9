<?php  // $Id: export.php



    require_once("../../config.php");
    require_once("lib.php");
    require_once('class.planner_export.php');
   
    
    $id = required_param('id', PARAM_INT);                 // Course Module ID
    $studentid = required_param('studentid', PARAM_INT);
    planner_set_variables('planner');
    require_course_login($course, false, $cm);

    if (!$planner = planner_get_planner($cm->instance)) {
        error("Course module is incorrect");
    }
    
    
    $strplanner = get_string('modulename', 'planner');
    $strplanners = get_string('modulenameplural', 'planner');
    
    $planner_export = new planner_export($cm, $id, $context);
    if (! ($workbook = $planner_export->create_workbook($studentid)) ) {   
        foreach($PLANNER_ERRORS as $error) {
            echo $error . "<br/>";  
        }
        error("Something went wrong in planner_create_sheet: could not create spreadsheet");
    }
    // This method call is writing the http headers, so do not move this function elsewhere
    /*
    $workbook->send('planning.xls');
    $workbook->close();
    */
    
    //$workbook =& planner_test(); 
    $workbook->send('planning.xls');
    $workbook->close();
    exit;  

    
?>
