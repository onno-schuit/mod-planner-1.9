<?php
class PlannerWorkbook extends Spreadsheet_Excel_Writer {
    function &addWorksheet($name = ''){
        global $CFG;
        
        $index      = count($this->_worksheets);
        $sheetname = $this->_sheetname;


        $worksheet =& new PlannerWorksheet($this->_BIFF_version,
                                          $name, $index,
                                          $this->_activesheet, $this->_firstsheet,
                                          $this->_str_total, $this->_str_unique,
                                          $this->_str_table, $this->_url_format,
                                          $this->_parser);

        $this->_worksheets[$index] = &$worksheet;     // Store ref for iterator
        $this->_sheetnames[$index] = $name;             // Store EXTERNSHEET names
        $this->_parser->setExtSheet($name, $index);  // Register worksheet name with parser

        if(!isset($CFG->latinexcelexport) || !$CFG->latinexcelexport) {
            $worksheet->setInputEncoding('UTF-16LE');
            // $worksheet->setInputEncoding('utf-8');
        }
        return $worksheet;
    }
} // class PlannerWorkbook extends Spreadsheet_Excel_Writer


class PlannerWorksheet extends Spreadsheet_Excel_Writer_Worksheet {
    var $rowOffSet = 0; 
  
    function write_string($row, $col, $str, $format=false) {
        if ($format) {
            parent::write($row, $col, $str, $format);
        } else {
            parent::write($row, $col, $str);  
        }
    } // function write_string 
  
} // class PlannerWorksheet extends Spreadsheet_Excel_Writer_Worksheet


function planner_feedback_convert_to_win($text) {
    global $CFG;
    static $textlib;
    static $newwincharset;
    static $oldcharset;
    
    if(!isset($textlib)) {
        $textlib = textlib_get_instance();
    }
    
    if(!isset($newwincharset)) {
        if(!isset($CFG->latinexcelexport) || !$CFG->latinexcelexport) {
            $newwincharset = 'UTF-16LE';
        }else {
            $newwincharset = get_string('localewincharset');
            if($newwincharset == '') {
                $newwincharset = 'windows-1252';
            }
        }
    }
    
    if(!isset($oldcharset)) {
        $oldcharset = get_string('thischarset');
    }
    
    //converting <br /> into newline
    $newtext = str_ireplace('<br />', "\n", $text);
    $newtext = str_ireplace('<br>', "\n", $newtext);
    
    return $textlib->convert($newtext, $oldcharset, $newwincharset);
} // planner_feedback_convert_to_win

?>
