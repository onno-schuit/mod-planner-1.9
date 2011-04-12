<?php 

// IMPORTANT: in public_html/lib/grade/grade_item.php, the code on line 1598 must be changed from:
// $grade->timecreated = $datesubmitted;
// into:
// $grade->timecreated = ($datesubmitted) ? $datesubmitted : Time();

/*
Custom Fields:

companyname
companylocation
companyaddress
companyzipcode
companycity
companyphone
subscriptiondate
*/
// DOCUMENTATION: http://pear.php.net/manual/en/package.fileformats.spreadsheet-excel-writer.intro-format.php

    ini_set('include_path', $CFG->libdir.'/pear' . PATH_SEPARATOR . ini_get('include_path'));
    require_once 'Spreadsheet/Excel/Writer.php';
    require_once('class.planner_workbook.php');
    require_once('class.planner_base.php');
    
    $SPRING_GRADE_SCALE = array(
        '1/3' => get_string('spring_grade_1_of_3','planner'),
        '2/3' => get_string('spring_grade_2_of_3','planner'),
        '3/3' => get_string('spring_grade_3_of_3','planner'));

    
    class planner_export extends planner_base {

        function create_workbook($studentid) {
            global $CFG, $PLANNER_ERRORS; 
          
            if (! ($student = $this->get_student($studentid)) ) {
                $PLANNER_ERRORS[] = "could not find student with id = $studentid (in $this->create_sheet)";
                return false;          
            }
            $student_custom = $this->get_custom_fields($studentid);
            
            $coach = $this->get_coach($studentid);
            $coach_custom = $this->get_custom_fields($coach->id);
            
            $teachers = $this->get_teachers($studentid);
            
            
            $workbook = new PlannerWorkbook();
            

            $worksheet =& $workbook->addWorksheet('Planning');
            $worksheet->setColumn(0, 0, 30);
            $worksheet->setColumn(1, 4, 20);
            $worksheet->setColumn(5, 5, 10);

            $this->set_formats($workbook, $worksheet);

          
            
            $a = new object();
            $a->role_name = $this->get_role_name('student');
            $worksheet->write(0, 0, get_string('student_data', 'planner', $a), $worksheet->format_bold);
            
            $worksheet->rowOffSet++;
            $worksheet->write_string($worksheet->rowOffSet, 0, get_string('firstname'), $worksheet->format_bold );
            $worksheet->write_string($worksheet->rowOffSet, 1, get_string('lastname'), $worksheet->format_bold );
            $worksheet->write_string($worksheet->rowOffSet, 2, get_string('email'), $worksheet->format_bold );
            $worksheet->write_string($worksheet->rowOffSet, 3, get_string('subscription_date','planner'), $worksheet->format_bold );
            $worksheet->write_string($worksheet->rowOffSet, 4, get_string('lastlogin'), $worksheet->format_bold );
            $worksheet->write_string($worksheet->rowOffSet, 5, get_string('report_date','planner'), $worksheet->format_bold );
            

            $worksheet->rowOffSet++;        
            $worksheet->write_string($worksheet->rowOffSet, 0, $student->firstname );
            $worksheet->write_string($worksheet->rowOffSet, 1, $student->lastname );
            $worksheet->write_string($worksheet->rowOffSet, 2, $student->email );
            $worksheet->write_string($worksheet->rowOffSet, 3, $this->custom('subscriptiondate', $student_custom) );
            
            // apparently, lastlogin is not filled if user has logged in for the very first time. In that case, use lastaccess instead
            $lastlogin = ($l = $student->lastlogin) ? $l : $student->lastaccess; 
            $worksheet->write_string($worksheet->rowOffSet, 4, $this->timestamp2human($lastlogin) );
            $worksheet->write_string($worksheet->rowOffSet, 5, $this->timestamp2human(Time()) );

            $worksheet->rowOffSet += 2;        
            
            $this->export_sections($student, $worksheet);
            
            
            return $workbook;
        } // function create_workbook    
        
        
        function custom($key, $array) {
            if (! $array) return '';
            return (array_key_exists($key,$array)) ? $array[$key] : '';
        } // function custom
        
        
        function set_formats(&$workbook, &$worksheet) {
            $worksheet->format_bold =& $workbook->addFormat();
            $worksheet->format_bold->setBold();      
            // See http://www.mvps.org/dmcritchie/excel/colors.htm for colors,
            // but add 7 to the color indexes mentioned on that website.
            $colors = array(
                'red' => 'red',
                'white' => 'white',
                'green'=> 17,
                'orange' => 52,
                'yellow' => 'yellow',
                'blue' => 'blue',
                'purple' => 36);
            foreach($colors as $color => $value) {
                $worksheet->{"format_{$color}"} =& $workbook->addFormat();
                if ($color == 'white') continue;
                $worksheet->{"format_{$color}"}->setFgColor($value);
            }
            $worksheet->format_align_right =& $workbook->addFormat();
            $worksheet->format_align_right->setAlign('right');  
            $worksheet->format_mark_header =& $workbook->addFormat();
            $worksheet->format_mark_header->setAlign('right');  
            $worksheet->format_mark_header->setBold();      
        }// function set_formats    
        
        
        function export_sections($student, &$worksheet) {
            global $course;
            if (! ($course_sections = $this->get_course_sections($course->id)) ) {
                error(get_string('no_titled_sections','planner'));
                return false;
            }
            //exit(print_r($course_sections));
            foreach($course_sections as $course_section) {
                $worksheet->rowOffSet += 2;
                $worksheet->write_string($worksheet->rowOffSet, 0, $course_section->summary, $worksheet->format_bold );
                $worksheet->rowOffSet++;
                
                $worksheet->write_string($worksheet->rowOffSet, 0, get_string('activity'), $worksheet->format_bold );
                $worksheet->write_string($worksheet->rowOffSet, 1, get_string('plan_date','planner'), $worksheet->format_bold );
                $worksheet->write_string($worksheet->rowOffSet, 2, get_string('status'), $worksheet->format_bold );
                $worksheet->write_string($worksheet->rowOffSet, 3, get_string('date_delivered','planner'), $worksheet->format_bold );
                $worksheet->write_string($worksheet->rowOffSet, 4, get_string('date_marked','planner'), $worksheet->format_bold );
                $worksheet->write_string($worksheet->rowOffSet, 5, get_string('mark','planner'), $worksheet->format_mark_header );
                $worksheet->rowOffSet++;

                $this->export_section(
                    $student,
                    $worksheet,
                    $course_section,
                    $this->get_section($course_section->id) );
            }
        } // function export_sections
        
        
        function export_section($student, &$worksheet, $course_section, $section) {
            global $course, $EXCLUDED_MODS;
            $modinfo = get_fast_modinfo($course);
            $userid =  $student->id;
            $dates = $this->get_dates($userid, $course_section->id);
            $sectionmods = explode(",", $section->sequence);
            //$worksheet->setFormat('<l><vo>');
            foreach ($sectionmods as $modnumber) {
                // Please note: $modnumber = course_modules.id
                if (( in_array($modinfo->cms[$modnumber]->modname, $EXCLUDED_MODS) ) || ($modinfo->cms[$modnumber]->modname == '') ) {
                    continue;
                }            
                $instancename = format_string($modinfo->cms[$modnumber]->name, true,  $course->id);
                $worksheet->write_string($worksheet->rowOffSet, 0, $instancename );
                $obj_date = false;
                if ($obj_date = $this->end_date($dates, $modnumber)) {
                    $end_date = $this->timestamp2human($obj_date->end_date);
                    $worksheet->write_string($worksheet->rowOffSet, 1, $end_date );  
                }
                
                $grade = false;
                if ($grade = $this->get_grade($userid, $modinfo->cms[$modnumber]->modname, $modinfo->cms[$modnumber]->instance)) {
                    $worksheet->write_string($worksheet->rowOffSet, 3, $this->timestamp2human($grade->timecreated));
                    $worksheet->write_string($worksheet->rowOffSet, 4, $this->timestamp2human($grade->timemodified));
                    $worksheet->write_string($worksheet->rowOffSet, 5, $this->get_mark($grade), $worksheet->format_align_right);
                }
                if ($obj_date) { 
                    $color = $this->get_status($obj_date->end_date, $grade);
                    $worksheet->write_string($worksheet->rowOffSet, 2, "", $worksheet->{"format_{$color}"});
                }
                
                $worksheet->rowOffSet++;
                
                // <td>\$modnumber  = $modnumber -- \$instancename = $instancename -- \$modinfo->cms[$modnumber]->modname = {$modinfo->cms[$modnumber]->modname}<br/></td>
                //<td>" . $this->date_field($dates, $modnumber, $course_section_id) . "</td>
            }
        } // function export_section
        
        
        // HACK ahead: uses client's custom grade scale
        /*
        function get_mark($grade) {
            global $SPRING_GRADE_SCALE;
            if ((!$grade) || ($grade->rawgrade == 0)) {
                return "";   
            }
            $rounded = round($grade->rawgrade) . "/" . round($grade->rawgrademax);
            return (array_key_exists($rounded, $SPRING_GRADE_SCALE)) ? $SPRING_GRADE_SCALE[$rounded] : $rounded;
        } // function get_mark
        */
        


        // Please note that we are using the end date of the planning, instead of 
        // the due date of e.g. assignment activitiy. This is because there is no single table
        // to retrieve the actual due date from.
        /*
        function get_status($planned_date, $grade) {
            if ( !$grade ) {
                // student has not yet handed in or completed activity
                return (Time() < $planned_date) ? 'yellow' : 'red';
            }
            
            if ($grade->timecreated <= $planned_date) {
                // in time  
                if (!$grade->rawgrade) {
                    // no grade
                    return 'orange';
                } else {
                    // in time and graded
                    return 'green'; 
                }
            } else {
                // too late     
                if (!$grade->rawgrade) {
                    // no grade
                    return 'purple';
                } else {
                    // graded
                    return 'blue';
                } 
            }
                             
            return 'green';
        } // function get_status
         */
        
        
        function get_student($studentid) {
            global $PLANNER_ERRORS, $context;
            if (! has_capability('mod/planner:readforeignplanning', $context) ) {
                $PLANNER_ERRORS[] = "Operation not allowed ($this->get_student)";
                return false;
            }
            return get_record('user', 'id', $studentid);
        } // function get_student
        
        
        /*
        function get_grade($userid, $modname, $course_module_instance_id) {
            // course_modules.instance == grade_items.iteminstance
            global $CFG, $course;
          
            $grades = get_records_sql("SELECT DISTINCT grades.* 
                                       FROM {$CFG->prefix}grade_grades grades
                                       INNER JOIN {$CFG->prefix}grade_items i ON grades.itemid = i.id
                                       WHERE i.iteminstance = $course_module_instance_id
                                       AND i.courseid = $course->id
                                       AND i.itemmodule LIKE '{$modname}' 
                                       AND grades.userid = $userid");
            return ($grades) ? $grades[key($grades)] : false;
            
        } // function get_grade
        */
        
        
        function test() {
            $workbook = new Spreadsheet_Excel_Writer();
            $worksheet =& $workbook->addWorksheet('Planning');
            $worksheet->format_bold =& $workbook->addFormat();
            $worksheet->format_bold->setBold();
            
            $format_title =& $workbook->addFormat();
            $format_title->setBold();
            $format_title->setColor('white');
            $format_title->setPattern(1);
            $format_title->setFgColor('blue');
            // let's merge
            //$format_title->setAlign('merge');
            
            $worksheet =& $workbook->addWorksheet('Planning');
            $worksheet->write(0, 0, "Quarterly Profits for Dotcom.Com", $format_title);
            // Couple of empty cells to make it look better
            $worksheet->write(0, 1, "", $format_title);
            $worksheet->write(0, 2, "", $format_title);
            $worksheet->write(1, 0, "Quarter", $worksheet->format_bold);
            $worksheet->write(1, 1, "Profit", $worksheet->format_bold);
            $worksheet->write(2, 0, "Q1");
            $worksheet->write(2, 1, 0);
            $worksheet->write(3, 0, "Q2");
            $worksheet->write(3, 1, 0);   
            
            $worksheet->write(4, 0, "TEST", $worksheet->format_bold);
            $worksheet->write(5, 0, "And NOT bold");
            
              
            return $workbook;
        }
        

    } // class planner_export extends planner_base     
    
    
    
?>
