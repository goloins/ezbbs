<?php
include('../init.php');

if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
    http_response_code(403);
    die('ezbbs error: you must be logged in to watch a thread.');
}

$thread_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($thread_id <= 0 || !do_getThreadById($thread_id)) {
    http_response_code(404);
    die('ezbbs error: thread not found.');
}

if(!do_watchThread($_SESSION['user_id'], $thread_id)) {
    die('ezbbs error: failed to watch thread.');
}

do_logentry('Notice', 'User ' . $_SESSION['user_id'] . ' watched thread ' . $thread_id);
do_sendnotification($_SESSION['user_id'], 'feedback', array('message' => 'Thread added to your watchlist.'));

$return_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/thread/' . $thread_id;
header('Location: ' . $return_to);
