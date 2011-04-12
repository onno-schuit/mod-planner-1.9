<?php

$string['planner'] = 'Planner';

$string['modulenameplural'] = 'Planners';
$string['modulename'] = 'Planner';
$string['grace_days'] = 'Wijzigingen toegestaan gedurende';
$string['days'] = 'dag(en)';

$string['planner:receiveunlockrequest'] = 'Emailaanvraag vrijgave planning ontvangen';
$string['planner:unlockplanning'] = 'Planning vrijgeven';
$string['planner:readforeignplanning'] = 'Planning van andere personen lezen';



$string['request_subject'] = 'Aanvraag tot vrijgave planning van $a->firstname $a->lastname';
$string['request_body'] = '$a->firstname $a->lastname vraagt u om de planning vrij te geven.\n\n 
                           Klik op de onderstaande link om bij het formulier te komen waar
                           u de planning vrij kunt geven. Mocht uw emailprogramma dit niet toestaan,
                           selecteer dan de link (zonder de haakjes) en kopieer die in de adresbalk van uw internetprogramma (uw browser, 
                           bijvoorbeeld MS Internet Explorer).';
$string['request_link'] = '\n\n<br/><br/><a href=\'$a->link\'>Planning vrijgeven in Moodle</a> ($a->link)';


$string['unlock_not_allowed'] = 'Je hebt niet voldoende rechten om de planning vrij te geven.';

$string['no_titled_sections'] = "Geen onderwerpen met beschrijving aangetroffen. Vul a.j.b. een titel in bij de beschrijvingen van de onderwerpen.";
$string['end_date'] = "Eind-datum (dd-mm-jjjj)";
$string['activity'] = "Activiteit";
$string['save'] = "Bewaren";
$string['back'] = "Terug";
$string['error_sending_request'] = "Er is iets misgegaan bij het versturen van de aanvraag tot vrijgave. Probeer het later a.j.b. nog een keer.";
$string['request_unlock_link'] = 'Aanvraag voor vrijgeven planning <a href=\'$a->link\'>indienen</a>';
$string['unlock_error'] = "Er is iets misgegaan bij het vrijgeven van de planning.";
$string['message_unlocked'] = 'De planning is vrijgegeven.';

$string['unlocked_notification_body'] = "Je planning is vrijgegeven in Moodle.";
$string['unlocked_notification_subject'] = "Moodle planning vrijgegeven";

$string['request_sent'] = 'Er is een email verstuurd naar de persoon die verantwoordelijk is voor het vrijgeven van je planning. Zodra deze de planning heeft vrijgegeven, ontvang jij automatisch een email.';
$string['request_error'] = "Er is iets misgegaan bij het versturen van de email betreffende de vrijgave-aanvraag aan de docent.";

$string['empty_student_list'] = "Als u docent bent in een groep met studenten, zullen uw studenten hieronder worden weergegeven.";

$string['export_to_excel_text'] = 'Exporteer planning van $a->firstname $a->lastname naar Excel';
$string['export_to_excel_button'] = "Downloaden";
$string['excelsheet'] = "Excelsheet";
$string['no_student_found'] = "Geen student gevonden";

$string['planning_owner'] = 'Planning van $a->firstname $a->lastname';
$string['student_data'] = 'Gegevens van $a->role_name';
$string['subscription_date'] = "Inschrijfdatum";
$string['report_date'] = "Datum rapport";
$string['company_data'] = "Bedrijfsgegevens";
$string['company_name'] = "Naam";
$string['company_location'] = "Lokatie";
$string['company_address'] = "Adres";
$string['company_zipcode'] = "Postcode";
$string['company_city'] = "Plaats";
$string['company_phone'] = "Telefoon";

$string['coord_details'] = 'Studieloopbaan begeleider: $a->firstname $a->lastname';
$string['coach_details'] = 'Leermeester/praktijkopleider: $a->firstname $a->lastname';


$string['coordinator'] = "Studiebegeleider";
$string['teacher'] = "Docent";
$string['coach'] = "Leermeester/praktijkopleider";	
$string['plan_date'] = 'Plandatum';
//$string['status']= 
$string['date_delivered'] = 'Datum ingeleverd';
$string['date_marked'] = 'Datum beoordeeld';
$string['mark'] = 'Beoordeling';

$string['status_green'] = 'Volgens planning ingeleverd';
$string['status_orange'] = 'Volgens planning ingeleverd, opdracht nog niet beoordeeld';
$string['status_yellow'] = 'Opdracht nog niet ingeleverd, binnen de planning';
$string['status_red'] = 'Opdracht nog niet ingeleverd, planning niet gevolgd';
$string['status_purple'] = 'Niet volgens planning ingeleverd, opdracht nog niet beoordeeld';
$string['status_blue'] = 'Niet volgens planning ingeleverd, opdracht is beoordeeld';

$string['spring_grade_1_of_3'] = 'onvolledig';
$string['spring_grade_2_of_3'] = 'onvoldoende';
$string['spring_grade_3_of_3'] = 'voldoende';

$string['back_to_list'] = 'Terug';
$string['data_saved'] = 'De gegevens zijn bewaard';
$string['save_error'] = "Er is iets misgegaan bij het bewaren van de data.";
$string['calendar'] = "Kalender";
$string['date_format'] = '%%e-%%m-%%Y'; // Format for outputting date - escape percentage sign with itself for sprintf 
$string['date_normalize'] = '%%d-%%m-%%Y'; // Format for parsing date - escape percentage sign with itself for sprintf 
$string['calendar_language'] = "nl";



?>
