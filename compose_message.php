<?php
require_once 'init.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
    header('Location: /');
    exit();
}

$thread_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
$thread = null;
$recipient_id = 0;
if($thread_id > 0) {
    $thread = do_getThreadById($thread_id);
    if($thread) {
        $recipient_id = intval($thread['poster_id']);
    }
}

if($recipient_id <= 0) {
    header('Location: /404/thread');
    exit();
}

$error_message = '';
$success_message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    if($subject === '' || $content === '') {
        $error_message = 'Subject and message are required.';
    } elseif(!do_sendPrivateMessage($_SESSION['user_id'], $recipient_id, $subject, $content, $thread_id)) {
        $error_message = 'Could not send message.';
    } else {
        do_logentry('Notice', 'User ' . $_SESSION['user_id'] . ' sent PM to user ' . $recipient_id . ' about thread ' . $thread_id);
        do_sendnotification($recipient_id, 'mention', array('link' => '/inbox/' . $_SESSION['user_id']));
        header('Location: /inbox/' . $recipient_id);
        exit();
    }
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>Compose Message - <?php echo $site['site_name']; ?></title>
        <meta name="description" content="<?php echo $site['site_description']; ?>"/>
        <link rel="icon" type="image/gif" href="<?php echo $site['favicon_url']; ?>" />
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/layout.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/<?php echo $user['theme']; ?>.css" />
    </head>
<body class="page-reply desktop-mode">
    <h1 class="top_text" id="logo">
        <a rel="index" href="/" class="help_cursor" title="<?php echo fun_getslogan(); ?>"><?php echo $site['site_name']; ?></a>
    </h1>

<div id="body_wrapper">
    <h2 id="body_title">Compose Message</h2>

<?php if($error_message !== '') { ?>
    <div id="notice" style="background-color: #ffcccc; color: #cc0000;"><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></div>
<?php } ?>
<?php if($success_message !== '') { ?>
    <div id="notice" style="background-color: #ccffcc; color: #00cc00;"><strong>Success:</strong> <?php echo htmlspecialchars($success_message); ?></div>
<?php } ?>

    <form method="POST">
        <fieldset>
            <legend>PM to <?php echo do_getFullyFormattedUsername($recipient_id); ?></legend>
            <label for="subject">Subject:</label><br/>
            <input type="text" id="subject" name="subject" maxlength="255" style="width:100%;" /><br/><br/>
            <label for="content">Message:</label><br/>
            <textarea id="content" name="content" rows="10" style="width:100%;"></textarea><br/><br/>
            <input type="submit" value="Send Message" />
            <a href="/thread/<?php echo $thread_id; ?>">[Cancel]</a>
        </fieldset>
    </form>
</div>
</body>
</html>
