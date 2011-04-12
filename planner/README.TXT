README.TXT for Moodle 1.9 Planner module

Author: Onno Schuit (o.schuit at or around solin.nl) - http://www.solin.eu

*** CREDITS ***
Concepts & Sponsoring: Spring - http://www.springinstituut.nl
The original version of this module was commissioned by Stoas - http://www.stoas.nl



*** What is the Planner Module? ***

The Planner module for Moodle 1.9 allows students to assign a planned end date 
to each activity.
Teachers have the option to check the planning and export it to an Excel sheet.
The Excel sheet shows the status of an activity, e.g. if a student hands in
a paper late (i.e. after the planned end date), the status is red. Please note
that the Planner modules does not use the activities' due dates (because these 
are not stored in a central database table).

To prevent students from "cheating", i.e. changing the planned end date at the
very last moment, the end date is locked after a grace period has expired 
(e.g. 1 day).
 

1. Installation

Copy the mod directory 'planner' into your moodle mod directory. Go to the
administrator's section of your Moodle installation.



2. Configuration

2.1 Grace Period

On the Settings tab page, in the "Grace Period" field, specifiy how long student 
users are able to change specific planning dates. By default, this is one day 
after which a new planning date will be locked.

2.2 Groups

Mod Planner ignores the "Group mode" setting because it always uses groups to
display the correct data. This means that you have to set up a group for each
student which contains:

- the student
- a teacher (who will automatically have the ability to read and unlock a 
  student's planning)
- optionally a "coordinator" (a duplicate of the teacher role, but without the
  ability to unlock a student's planning)

If you decide to include a user with the coordinator role, you will have to add
the role with exactly the name "coordinator" to your Moodle installation and 
specify these settings on the "Manage Roles" tab:

* Read other people's planning: allow
* Unlock planning: prohibit

2.3 Excluded activities

The planner lists all activities which are not "excluded". You can exclude 
activities which are meaningless in the context of the planner. Go to 
mod/planner/class.planner_base.php and add the activities to the array on line 16:

$EXCLUDED_MODS = array('planner','data','chat','forum');

(Yes, the planner itself must be mentioned there).



3. PREREQUISITES / ASSUMPTIONS

3.1. Patch public_html/lib/grade/grade_item.php

IMPORTANT: in public_html/lib/grade/grade_item.php, the code on line 1598 must 
be changed from:
$grade->timecreated = $datesubmitted;
into:
$grade->timecreated = ($datesubmitted) ? $datesubmitted : Time();



4. Room for Improvement

We don't have any plans to add features to the current version (i.e. the Moodle 1.9 version).
For a future Moodle 2 version, however, we are considering adding the following features.

* The Planner mod does not use the activities' end dates to compute the status.
So for example, if you hand in an assignment after it is due (according to the
setting of this particular activity), but before your planned end date, the
status will be okay as far as the Planner is concerned.

    - In a future version, all due dates should be retrieved from the activities
      themselves.

* Add pagination for lists of users, planning items
