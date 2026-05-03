<?php
include('../init.php');
do_requireLogin('/login');

$thread_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($thread_id <= 0 || !do_getThreadById($thread_id)) {
    http_response_code(404);
    die('ezbbs error: thread not found.');
}

if(!do_watchThread(do_getCurrentUserId(), $thread_id)) {
    die('ezbbs error: failed to watch thread.');
}

do_logentry('Notice', 'User ' . do_getCurrentUserId() . ' watched thread ' . $thread_id);
do_sendnotification(do_getCurrentUserId(), 'feedback', array('message' => 'Thread added to your watchlist.'));

$return_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/thread/' . $thread_id;
header('Location: ' . $return_to);
