<?php
/* ezbbs - a simple bbs engine for the small web 
 *
 * Copyleft 2026 by the ezbbs contributors
 * 
 * /do/example.php - 
 * a do-er located in the do folder. this handles actions without resorting to js/ajax.
 * 
 * About any user or moderator action goes through the do folder and its contents. the flow is as follows:
 *  1) We pull in init to handle login functionality. 
 *  2) we get our get variables. more complicated do actions may require modification
 *      of the htaccess file to ensure all parameters are passed. this is fine, do what 
 *      makes sense.
 *  3) since we should be in a sane environment with $_SESSION and $go_sql, we can do any kind 
 *      of modification to the DB or to the user session data. You've got the keys, don't fuck it up
 *      by trying to do custom logic here if you can help it. Try to write out sane functions in init
 *      to handle specific situations. 
 * 
 *  4) make sure to add a error handler, either via a die() (bad for user exp)
 *      or via a log entry (coming soon tm). Speaking of logs, if you want to 
 *      track user data for some reason, here's the place to stuff you callbacks.
 * 
 *  5) Send the user back where they came from! 
 * 
 * 
 *      This example file is (codewise) identical to the dismissnotif.php file. 
 *          Things here are very straightforward but powerful. You could make all
 *          sorts of things with this framework such as /do/sendpm/123 (user_id)
 *          or /do/ban/1234/3600 (user_id, time_ms) or /do/postthread (with a $_POST)
 * 
 *      The magic is the quick redirect back to the previous page quickly (unless we die()'d)
 * 
 *      That's basically the /do/ framework in a nutshell. go nuts 
 */


include('../init.php');

$notifId = intval($_GET['id']);

do_setnotifread($notifId) or die('ezbbs error: failed to mark notification as read. tell admin to check the logs.');

//you can also add $modlog=true if its some mod work such as bans/unbans.
do_logentry("Notice", "Notification ".$notifId." set as read by user ".$_SESSION['user_id']);

do_sendnotification($_SESSION['user_id'], "feedback", array("message" => "You found the example do file! neat!"));

// a good place to add logging for moderation purposes is here in a 'do' file since
// most user simple actions are going to be handled by one of these style files.

header('Location: ' . $_SERVER['HTTP_REFERER']);

