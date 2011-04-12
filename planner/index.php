<?php // $Id: index.php
/**
 * This page lists all the instances of planner in a particular course
 *
 * @author 
 * @version $Id: index.php
 * @package planner
 **/

/// Replace planner with the name of your module

    require_once("../../config.php");
    require_once("lib.php");

    $id = required_param('id', PARAM_INT);   // course

    if (! $course = get_record("course", "id", $id)) {
        error("Course ID is incorrect");
    }

    require_login($course->id);

    add_to_log($course->id, "planner", "view all", "index.php?id=$course->id", "");


/// Get all required stringsplanner

    $strplanners = get_string("modulenameplural", "planner");
    $strplanner  = get_string("modulename", "planner");


/// Print the header
    $navlinks = array();
    $navlinks[] = array('name' => $strplanners, 'link' => '', 'type' => 'activity');
    $navigation = build_navigation($navlinks);

    print_header_simple($strplanners, "", $navigation, "", "", true, "", navmenu($course));

/// Get all the appropriate data

    if (! $planners = get_all_instances_in_course("planner", $course)) {
        notice("There are no planners", "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname);
        $table->align = array ("center", "left");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname);
        $table->align = array ("center", "left", "left", "left");
    } else {
        $table->head  = array ($strname);
        $table->align = array ("left", "left", "left");
    }

    foreach ($planners as $planner) {
        if (!$planner->visible) {
            //Show dimmed if the mod is hidden
            $link = "<a class=\"dimmed\" href=\"view.php?id=$planner->coursemodule\">$planner->name</a>";
        } else {
            //Show normal if the mod is visible
            $link = "<a href=\"view.php?id=$planner->coursemodule\">$planner->name</a>";
        }

        if ($course->format == "weeks" or $course->format == "topics") {
            $table->data[] = array ($planner->section, $link);
        } else {
            $table->data[] = array ($link);
        }
    }

    echo "<br />";
    //echo "And here's the table:<br/>";
    print_table($table);

/// Finish the page

    print_footer($course);

?>
