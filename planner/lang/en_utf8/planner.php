<?php

$string['planner'] = 'Planner';

$string['modulenameplural'] = 'Planners';
$string['modulename'] = 'Planner';
$string['grace_days'] = 'Grace period';
$string['days'] = 'days';

$string['planner:receiveunlockrequest'] = 'Receive email with unlock request';
$string['planner:unlockplanning'] = 'Unlock planning';
$string['planner:readforeignplanning'] = "Read other people's planning";

$string['request_subject'] = 'Request to unlock planning for $a->firstname $a->lastname';
$string['request_body'] = '$a->firstname $a->lastname is asking you to unlock the planning.\n\n 
                           Please click on the link below to access the form where you can unlock the planning. 
                           If your mail program does not allow this, select the link (without the brackets) en copy
                           it to the address bar of your browser (e.g. MS Internet Explorer).'; 
$string['request_link'] = '\n\n<br/><br/><a href=\'$a->link\'>Unlock planning in Moodle</a> ($a->link)';


$string['unlock_not_allowed'] = 'You are not authorized to unlock the planning.';

$string['no_titled_sections'] = "No properly titled sections found. Please edit the sections' summaries.";
$string['activity'] = "Activity";
$string['end_date'] = "End date (dd-mm-yyyy)";
$string['save'] = "Save";
$string['back'] = "Back";
$string['error_sending_request'] = "Something went wrong while emailing the request. Please try again later.";
$string['request_unlock_link'] = '<a href=\'$a->link\'>Submit</a> request for unlocking planning';
$string['unlock_error'] = "Something went wrong while trying to unlock the planning.";
$string['message_unlocked'] = 'The planning has been unlocked.';

$string['unlocked_notification_body'] = "Your planning has been unlocked in Moodle.";
$string['unlocked_notification_subject'] = "Moodle planning now unlocked";


$string['request_sent'] = 'We have sent an email to the person responsible for unlocking your planning. As soon as they have done so, you will automatically receive an email.';
$string['request_error'] = "Something went wrong while emailing the request to the person responsible for unlocking your planning.";

$string['empty_student_list'] = "If you are a teacher in a group with students, your student will be listed below.";
$string['planning_owner'] = 'Planning of $a->firstname $a->lastname';

$string['export_to_excel_text'] = 'Export planning of $a->firstname $a->lastname to Excel';
$string['export_to_excel_button'] = "Download";
$string['excelsheet'] = "Excelsheet";
$string['no_student_found'] = "No student found";

$string['student_data'] = '$a->role_name data';
$string['subscription_date'] = "Subscription date";
$string['report_date'] = "Report date";
$string['company_data'] = "Company data";

$string['company_name'] = "Name";
$string['company_location'] = "Location";
$string['company_address'] = "Address";
$string['company_zipcode'] = "Postal Code";
$string['company_city'] = "City";
$string['company_phone'] = "Phone";

$string['coordinator'] = "Coordinator";
$string['teacher'] = "Teacher";
$string['coach'] = "Coach";

$string['coord_details'] = 'Coordinator: $a->firstname $a->lastname';
$string['coach_details'] = 'Coach: $a->firstname $a->lastname';


$string['plan_date'] = 'Planned date';
//$string['status']= 
$string['date_delivered'] = 'Date handed in';
$string['date_marked'] = 'Date marked';
$string['mark'] = 'Mark';


$string['status_green'] = 'Handed in according to planning';
$string['status_orange'] = 'Handed in according to planning, activity has not been graded yet';
$string['status_yellow'] = 'Not handed in yet, still within planning';
$string['status_red'] = 'Not handed in yet, planning date exceeded';
$string['status_purple'] = 'Handed in late, activity has not been graded yet';
$string['status_blue'] = 'Handed in late, activity has been graded';
$string['back_to_list'] = 'Back';
$string['data_saved'] = 'The data has been saved';
$string['save_error'] = "Something went wrong while saving the data.";
$string['calendar'] = "Calendar";
$string['date_format'] = '%%m/%%e/%%Y'; // Format for outputting date - escape percentage sign with itself for sprintf
$string['date_normalize'] = '%%m/%%d/%%Y'; // Format for parsing date - escape percentage sign with itself for sprintf 
$string['calendar_language'] = "en";


?>
