<?php
/* ezbbs - a simple bbs engine for the small web 
 *
 * Copyleft 2026 by the ezbbs contributors
 * 
 * new_reply.php - page for composing a new reply to a thread.
 * 
 */



require_once 'init.php';
if(!isset($_GET['id'])) {
    header('Location: /404/thread'); //specific 404 page for threads, so we can say "thread not found" instead of just "page not found"
    exit();
}
$thread_id = intval($_GET['id']);
$thread = do_getThreadById($thread_id);
if(!$thread) {
    header('Location: /404/thread'); //specific 404 page for threads, so we can say "thread not found" instead of just "page not found"
    exit();
}