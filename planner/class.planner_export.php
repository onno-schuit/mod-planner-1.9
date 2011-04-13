<?php 

// IMPORTANT: in public_html/lib/grade/grade_item.php, the code on line 1598 must be changed from:
// $grade->timecreated = $datesubmitted;
// into:
// $grade->timecreated = ($datesubmitted) ? $datesubmitted : Time();


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
                
            }
        } // function export_section
        
        
        function get_student($studentid) {
            global $PLANNER_ERRORS, $context;
            if (! has_capability('mod/planner:readforeignplanning', $context) ) {
                $PLANNER_ERRORS[] = "Operation not allowed ($this->get_student)";
                return false;
            }
            return get_record('user', 'id', $studentid);
        } // function get_student
        
        
    } // class planner_export extends planner_base     
    
    
    
?>
