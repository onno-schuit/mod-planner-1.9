<?php //$Id: restorelib.php
/**
 * Planner's restore routines
 *
 * @version $Id: restorelib.php
 * @author Onno Schuit (www.solin.eu)
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package planner
 **/

    //This function executes all the restore procedures for this mod
    function planner_restore_mods($mod, $restore) {
        global $CFG;

        // Get object $data with backup data, among which old id from table backup_ids.
        // Please note that the relevant record does not become available until the 
        // actual restore process takes place. Also, the entire backup_ids table is
        // emptied after the restore process has finished.
        if (! $data = backup_getid($restore->backup_unique_code, $mod->modtype, $mod->id) ) {
            return false;
        }

        // Now, build the planner record structure
        $planner->course = $restore->course_id;
        // $data property 'info' is an array which contains the parsed xml of the backup file.
        $planner->name = backup_todb($data->info['MOD']['#']['NAME']['0']['#']);
        $planner->practice = backup_todb($data->info['MOD']['#']['GRACE_DAYS']['0']['#']);

        $new_id = insert_record("planner", $planner);

        // Do some output
        if (!defined('RESTORE_SILENTLY')) {
            echo "<li>" . get_string("modulename", "planner") . " \"" . format_string(stripslashes($planner->name), true) . "\"</li>";
        }
        // output buffer to 'screen'
        backup_flush(300);

        if (! $new_id) {
            return false;
        }
        // We have the newid, update backup_ids
        backup_putid($restore->backup_unique_code, $mod->modtype, $mod->id, $new_id);

        return planner_dates_restore($new_id, $data->info, $restore);
    } // function planner_restore_mods


    // This function restores the planner dates
    function planner_dates_restore($planner_id, $info, $restore) {
        global $CFG;

        if (! isset($info['MOD']['#']['PLANNER_DATES']['0']['#']['PLANNER_DATE'])) {
            backup_flush(300);
            return true;
        }
        $status = true;
        $planner_dates = $info['MOD']['#']['PLANNER_DATES']['0']['#']['PLANNER_DATE'];
        for($i = 0; $i < sizeof($planner_dates); $i++) {
            $planner_date_info = $planner_dates[$i];
            $status = $status && planner_restore_one_date($planner_id, $planner_date_info, $restore);
            // Do some output
            if (($i+1) % 50 == 0) {
                if (!defined('RESTORE_SILENTLY')) {
                    echo ".";
                    if (($i+1) % 1000 == 0) {
                        echo "<br/>";
                    }
                }
                backup_flush(300);
            }
        }
        return $status;
    } // function planner_dates_restore


    function planner_restore_one_date($planner_id, $planner_date_info, $restore) {
        $planner_date = new object();
        $planner_date->planner_id = $planner_id;
        $planner_date->user_id = backup_todb($planner_date_info['#']['USER_ID']['0']['#']);
        $planner_date->course_module_id = backup_todb($planner_date_info['#']['COURSE_MODULE_ID']['0']['#']);
        $planner_date->course_section_id = backup_todb($planner_date_info['#']['COURSE_SECTION_ID']['0']['#']);
        $planner_date->end_date = backup_todb($planner_date_info['#']['END_DATE']['0']['#']);
        $planner_date->grace_start = backup_todb($planner_date_info['#']['GRACE_START']['0']['#']);

        $conversions = array(
            'user_id' => 'user',
            'course_module_id' => 'course_modules',
            'course_section_id' => 'course_sections'
        );
        foreach($conversions as $id_field_name => $table_name) {
            $planner_date = planner_convert_id($planner_date, $table_name, $id_field_name, $restore);
        }

        if (! $new_id = insert_record("planner_dates", $planner_date) ) {
            return false;
        }
        return backup_putid($restore->backup_unique_code, "planner_dates", $planner_date_info['#']['ID']['0']['#'], $new_id);
    } // function planner_restore_one_date


    // get new id for old id
    function planner_convert_id($object, $table_name, $id_field_name, $restore) {
        if ( $new_object = backup_getid($restore->backup_unique_code, $table_name, $object->$id_field_name) ) {
            $object->$id_field_name = $new_object->new_id;
        }
        return $object;
    } // function planner_convert_id


    // Return a content decoded to support interactivities linking. Every module
    // should have its own. They are called automatically from
    // planner_decode_content_links_caller() function in each module
    // in the restore process
    function planner_decode_content_links($content, $restore) {
        return $content;
    } // function planner_decode_content_links 


    // This function is called from restore_decode_content_links()
    // in the restore process.
    function planner_decode_content_links_caller($restore) {
        global $CFG;
        $status = true;
        return $status;
    } // function planner_decode_content_links_caller


    // This function returns a log record with all the necessay transformations
    // done. It's used by restore_log_module() to restore modules log.
    function planner_restore_logs($restore, $log) {

        $status = false;

        // Depending of the action, we recode different things
        switch ($log->action) {
        case "add":
            if ($log->cmid) {
                //Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "update":
            if ($log->cmid) {
                // Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "view all":
            $log->url = "index.php?id=".$log->course;
            $status = true;
            break;
        case "start":
            if ($log->cmid) {
                // Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code,$log->module,$log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "end":
            if ($log->cmid) {
                // Get the new_id of the module (to recode the info field)
                $mod = backup_getid($restore->backup_unique_code, $log->module, $log->info);
                if ($mod) {
                    $log->url = "view.php?id=".$log->cmid;
                    $log->info = $mod->new_id;
                    $status = true;
                }
            }
            break;
        case "view":
            if ($log->cmid) {
                // Get the new_id of the page (to recode the url field)
                $pag = backup_getid($restore->backup_unique_code,"planner_pages",$log->info);
                if ($pag) {
                    $log->url = "view.php?id=".$log->cmid."&action=navigation&pageid=".$pag->new_id;
                    $log->info = $pag->new_id;
                    $status = true;
                }
            }
            break;
        default:
            if (!defined('RESTORE_SILENTLY')) {
                echo "action (".$log->module."-".$log->action.") unknown. Not restored<br/>";                 //Debug
            }
            break;
        }

        if ($status) {
            $status = $log;
        }
        return $status;
    } // function planner_restore_logs
?>
