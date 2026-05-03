<?php
include('../init.php');
do_requireLogin('/login');

$thread_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($thread_id <= 0) {
    http_response_code(400);
    die('ezbbs error: invalid thread id.');
}

if(!do_forgetThread(do_getCurrentUserId(), $thread_id)) {
    die('ezbbs error: failed to forget thread.');
}

do_logentry('Notice', 'User ' . do_getCurrentUserId() . ' removed thread ' . $thread_id . ' from watchlist');
do_sendnotification(do_getCurrentUserId(), 'feedback', array('message' => 'Thread removed from your watchlist.'));

$return_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/thread/' . $thread_id;
header('Location: ' . $return_to);
