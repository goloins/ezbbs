<?php
/* ezbbs - a simple bbs engine for the small web 
 *
 * Copyleft 2026 by the ezbbs contributors
 * 
 * thread.php - page for displaying a single thread and its replies. also has the form for posting a reply to the thread.
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
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<title><?php echo htmlspecialchars($thread['title']); ?> — <?php echo $site['site_name'];?></title>
                    <meta name="description" content="<?php echo $site['site_description'];?>"/>
                <link rel="icon" type="image/gif" href="<?php echo $site['favicon_url'];?>" />
		<link rel="stylesheet" type="text/css" media="screen" href="assets/css/layout.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="assets/css/<?php echo $user['theme'];?>.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="assets/css/vs.css" />
		        <link rel="canonical" href="<?php echo $site['site_url'];?>" />
		</head>
<body class="page-index desktop-mode">	<h1 class="top_text" id="logo">
		<a rel="index" href="/" class="help_cursor" title="<?php echo fun_getslogan();?>"><?php echo $site['site_name'];?></a></h1>
<ul id="main_menu" class="menu">
<?php 
foreach($homepagemenu as $menu_item) {
    echo '<li><a href="' . $menu_item['url'] . '">' . $menu_item['name'] . '</a></li>';
}
?>
</ul>
</div>
<div id="body_wrapper">
    <h2 id="body_title">
		<span class="pre_topic">Topic:</span> <?php echo htmlspecialchars($thread['title']); ?>	</h2>
<h3 class="c" id="topic_<?php echo $thread_id;?>">
    <span class="joined help" title="This poster started the topic.">+</span><?php echo do_getFullyFormattedUsername($thread['poster_id']); ?>  — <strong><span class="help" title="<?php echo date('Y-m-d H:i:s \U\T\C — l \t\h\e jS \o\f F Y, g:i A', strtotime($thread['created_at'])); ?>"><?php fun_secondsToHumanReadable($thread['created_at']); ?></span> <span class="reply_id unimportant"><a href="/cat/<?php echo $thread['category_id']; ?>"><?php echo $categories[$thread['category_id']]['name']; ?></a></span></strong></h3> 
    <div class="body"><?php echo do_RenderTopicContent($thread['body']); ?>
    <ul class="menu"><li>
<!-- fix these-->
 <?php

 /*
        <a href="/compose_message/topic/68383">PM</a></li>
        <li><a href="/forget_thread/68383" onclick="return submitDummyForm('/forget_thread/68383', 'id', 68383, 'Really forget this thread?');">Forget Thread</a></li>
        <li><a href="/watch_topic/68383" onclick="return submitDummyForm('/watch_topic/68383', 'id', 68383, 'Add this topic to the watchlist?');">Watch</a></li>
        <li><a href="/new_reply/68383/quote_topic" onclick="quickQuote('OP');return false;">Quote</a></li>
        <li><a href="/new_reply/68383/cite_topic" onclick="quickCite('OP');return false;">Cite OP</a></li>
        <li><a href="/trivia_for_topic/68383" class="help" title="18 replies">491 visits</a></li></ul></div><br />
*/