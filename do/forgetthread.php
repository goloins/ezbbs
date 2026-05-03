<?php
include('../init.php');

if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
    http_response_code(403);
    die('ezbbs error: you must be logged in to forget a thread.');
}

$thread_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($thread_id <= 0) {
    http_response_code(400);
    die('ezbbs error: invalid thread id.');
}

if(!do_forgetThread($_SESSION['user_id'], $thread_id)) {
    die('ezbbs error: failed to forget thread.');
}

do_logentry('Notice', 'User ' . $_SESSION['user_id'] . ' removed thread ' . $thread_id . ' from watchlist');
do_sendnotification($_SESSION['user_id'], 'feedback', array('message' => 'Thread removed from your watchlist.'));

$return_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/thread/' . $thread_id;
header('Location: ' . $return_to);
