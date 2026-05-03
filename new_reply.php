<?php
/* ezbbs - a simple bbs engine for the small web 
 *
 * Copyleft 2026 by the ezbbs contributors
 * 
 * new_reply.php - page for composing a new reply to a thread.
 * 
 */

require_once 'init.php';
do_requireLogin('/login');

if(!isset($_GET['id'])) {
    header('Location: /404/thread');
    exit();
}

$thread_id = intval($_GET['id']);
$thread = do_getThreadById($thread_id);

if(!$thread) {
    header('Location: /404/thread');
    exit();
}

// Handle reply submission
$error_message = '';
$success_message = '';
$prefill_content = '';

if(isset($_GET['quote_topic']) && $_GET['quote_topic'] !== '') {
    $prefill_content = '>>' . $thread_id . "\n\n";
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply_content = isset($_POST['content']) ? trim($_POST['content']) : '';
    
    if(empty($reply_content)) {
        $error_message = 'Reply content cannot be empty.';
    } elseif(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
        $error_message = 'You must be logged in to post a reply.';
    } else {
        // Attempt to post the reply
        $new_reply_id = post_Reply($thread_id, $_SESSION['user_id'], $reply_content);
        if($new_reply_id !== false) {
            header('Location: /thread/' . $thread_id . '#reply_' . intval($new_reply_id));
            exit();
        } else {
            $error_message = 'Failed to post reply. Please try again.';
        }
    }
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<title>Reply to: <?php echo htmlspecialchars($thread['title']); ?> — <?php echo $site['site_name'];?></title>
        <meta name="description" content="<?php echo $site['site_description'];?>"/>
        <link rel="icon" type="image/gif" href="<?php echo $site['favicon_url'];?>" />
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/layout.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/<?php echo $user['theme'];?>.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/vs.css" />
		<link rel="canonical" href="<?php echo $site['site_url'];?>" />
	</head>
<body class="page-reply desktop-mode">

	<h1 class="top_text" id="logo">
		<a rel="index" href="/" class="help_cursor" title="<?php echo fun_getslogan();?>"><?php echo $site['site_name'];?></a>
	</h1>
<?php echo do_renderLoginStatusChip(); ?>

<div id="body_wrapper">
    <h2 id="body_title">
        <span class="pre_topic">Replying to:</span> <?php echo htmlspecialchars($thread['title']); ?>
    </h2>

    <?php if($error_message): ?>
        <div id="notice" style="background-color: #ffcccc; color: #cc0000;">
            <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if($success_message): ?>
        <div id="notice" style="background-color: #ccffcc; color: #00cc00;">
            <strong>Success:</strong> <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="reply-form">
        <fieldset>
            <legend>Compose Reply</legend>
            
            <label for="content">Your reply:</label>
            <textarea id="content" name="content" rows="10" cols="60" placeholder="Write your reply here... You can use markdown formatting."><?php echo htmlspecialchars(isset($reply_content) ? $reply_content : $prefill_content); ?></textarea>
            
            <br/><br/>
            
            <input type="submit" value="Post Reply" />
            <a href="/thread/<?php echo $thread_id; ?>">[Cancel]</a>
        </fieldset>
    </form>

</div><!-- body_wrapper -->

<div id="footer">
    <br/><div style="text-align:center" class="unimportant">
        <span><?php echo $site['disclaimer']; ?></span><br/><span>ezbbs, bby. <a href="https://github.com/goloins/ezbbs">Contribute on GitHub or get your own copy!</a></span></div><br/>
        <noscript><br /><span class="unimportant">Note: Your browser's JavaScript is disabled; some site features may not fully function, but don't worry, we're trying to get rid of all the js :^)</span></noscript>
</div>

</body>
</html>