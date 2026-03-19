<?php
/* ezbbs - a simple bbs engine for the small web 
 *
 * Copyleft 2026 by the ezbbs contributors
 * 
 * dismissnotif.php - a do-er located in the do folder. this handles actions without resorting to js/ajax
 * these files are simple. they include init.php, run a function or two (maybe some logic), and then redirect back to where the user came from.
 * exactly what it says on the tin. an example can be found in example.php. copy/paste that to add other do functions.
 * 
 */


include('../init.php');

$notifId = intval($_GET['id']);

do_setnotifread($notifId) or die('ezbbs error: failed to mark notification as read. tell admin to check the logs.');
do_logentry("Notice", "Notification ".$notifId." set as read by user ".$_SESSION['user_id']);

// a good place to add logging for moderation purposes is here in a 'do' file since
// most user simple actions are going to be handled by one of these style files.

header('Location: ' . $_SERVER['HTTP_REFERER']);

