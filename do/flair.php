<?php
/* ezbbs - a simple bbs engine for the small web
 *
 * Copyleft 2026 by the ezbbs contributors
 *
 * flair.php - cast a flair vote on a thread.
 */

include('../init.php');
do_requireLogin('/login');

$thread_id = isset($_GET['thread_id']) ? intval($_GET['thread_id']) : 0;
$flair_id = isset($_GET['flair_id']) ? intval($_GET['flair_id']) : 0;
$user_id = do_getCurrentUserId();

if($thread_id <= 0 || $flair_id <= 0 || !$user_id) {
    header('Location: /');
    exit();
}

$thread = do_getThreadById($thread_id);
if(!$thread) {
    header('Location: /404/thread');
    exit();
}

if(do_voteFlairForThread($thread_id, $flair_id, intval($user_id))) {
    do_logentry('Notice', 'User ' . intval($user_id) . ' voted flair ' . intval($flair_id) . ' on thread ' . intval($thread_id));
} else {
    do_logentry('Warning', 'Flair vote failed for user ' . intval($user_id) . ' on thread ' . intval($thread_id) . ' with flair ' . intval($flair_id));
}

header('Location: /thread/' . $thread_id);
exit();
