<?php
/* ezbbs - a simple bbs engine for the small web 
 *
 * Copyleft 2026 by the ezbbs contributors
 * 
 * /do/unban.php - 
 * a do-er for bringing back up the banhammer. sets user to unbanned and fires off a notifcation to the user about it. only accessible by mods and admins.
 */


include('../init.php');


$userid = intval($_GET['id']);
$banlength = intval($_GET['length']);
$banreason = trim($_GET['reason']);

$moderatorId = $_SESSION['user_id'];

//do the thing or die trying.
do_setuserbanned($userid, $banlength, $banreason) or die('ezbbs error: failed to ban user. tell admin to check the logs.');

//write a modlog about it
do_logentry("Notice", "User ".$userid." was unbanned by moderator ".$moderatorId, $modlog=true);

//send feedback notification so we know it worked.
do_sendnotification($moderatorId, "feedback", array("message" => "You have successfully unbanned user ID ".$userid."!"));

// a good place to add logging for moderation purposes is here in a 'do' file since
// most user simple actions are going to be handled by one of these style files.

header('Location: ' . $_SERVER['HTTP_REFERER']);

