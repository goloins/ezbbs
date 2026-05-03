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

if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
	http_response_code(403);
	die('ezbbs error: you must be logged in.');
}

$notifId = intval($_GET['id']);

if($notifId <= 0) {
	http_response_code(400);
	die('ezbbs error: invalid notification id.');
}

$stmt = $go_sql->prepare("SELECT user_id FROM notifications WHERE id = ?");
$stmt->bind_param("i", $notifId);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows === 0) {
	http_response_code(404);
	die('ezbbs error: notification not found.');
}
$row = $result->fetch_assoc();
if(intval($row['user_id']) !== intval($_SESSION['user_id']) && !chk_IsUserModeratorOrAdmin($_SESSION['user_id'])) {
	http_response_code(403);
	die('ezbbs error: not allowed to dismiss this notification.');
}

do_setnotifread($notifId) or die('ezbbs error: failed to mark notification as read. tell admin to check the logs.');
do_logentry("Notice", "Notification ".$notifId." set as read by user ".$_SESSION['user_id']);

// a good place to add logging for moderation purposes is here in a 'do' file since
// most user simple actions are going to be handled by one of these style files.

$return_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';
header('Location: ' . $return_to);

