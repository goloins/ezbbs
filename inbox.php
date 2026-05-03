<?php
require_once 'init.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
    header('Location: /');
    exit();
}

$current_user_id = intval($_SESSION['user_id']);
$peer_id = isset($_GET['peer_id']) ? intval($_GET['peer_id']) : 0;
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$exact_match = isset($_GET['exact']) && intval($_GET['exact']) === 1;
$thread_page = isset($_GET['thread_page']) ? intval($_GET['thread_page']) : 1;
$thread_per_page = 30;
$thread_total_messages = 0;
$thread_total_pages = 1;
$error_message = '';
$success_message = '';
$compose_to_username = '';
$compose_subject = '';
$compose_content = '';
$reply_subject = '';
$reply_content = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : 'reply';

    if($action === 'compose_new') {
        $to_username = isset($_POST['to_username']) ? trim($_POST['to_username']) : '';
        $subject = isset($_POST['new_subject']) ? trim($_POST['new_subject']) : '';
        $content = isset($_POST['new_content']) ? trim($_POST['new_content']) : '';
        $compose_to_username = $to_username;
        $compose_subject = $subject;
        $compose_content = $content;

        if($to_username === '') {
            $error_message = 'Recipient username is required.';
        } else {
            $to_user = do_getUserByUsername($to_username);
            if(!$to_user) {
                $error_message = 'That user does not exist.';
            } else {
                $to_user_id = intval($to_user['id']);
                if($to_user_id === $current_user_id) {
                    $error_message = 'You cannot message yourself.';
                } elseif($subject === '' || $content === '') {
                    $error_message = 'Subject and message are required.';
                } elseif(!do_sendPrivateMessage($current_user_id, $to_user_id, $subject, $content, 0)) {
                    $error_message = 'Could not send your message.';
                } else {
                    do_logentry('Notice', 'User ' . $current_user_id . ' started inbox conversation with user ' . $to_user_id);
                    do_sendnotification($to_user_id, 'mention', array('link' => '/inbox/' . $current_user_id));
                    header('Location: /inbox/' . $to_user_id);
                    exit();
                }
            }
        }
    } else {
        $peer_id = isset($_POST['peer_id']) ? intval($_POST['peer_id']) : $peer_id;
        $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $reply_subject = $subject;
        $reply_content = $content;

        if($peer_id <= 0 || $peer_id === $current_user_id) {
            $error_message = 'Select a valid user conversation.';
        } elseif(!do_getUserById($peer_id)) {
            $error_message = 'That user does not exist.';
        } elseif($subject === '' || $content === '') {
            $error_message = 'Subject and message are required.';
        } elseif(!do_sendPrivateMessage($current_user_id, $peer_id, $subject, $content, 0)) {
            $error_message = 'Could not send your message.';
        } else {
            do_logentry('Notice', 'User ' . $current_user_id . ' sent inbox message to user ' . $peer_id);
            do_sendnotification($peer_id, 'mention', array('link' => '/inbox/' . $current_user_id));
            header('Location: /inbox/' . $peer_id);
            exit();
        }
    }
}

$conversations = do_getPrivateMessageConversations($current_user_id);

if($search_query !== '') {
    $conversations = array_values(array_filter($conversations, function($conv) use ($search_query, $exact_match) {
        if($exact_match) {
            return strcasecmp($conv['peer_username'], $search_query) === 0;
        }
        return stripos($conv['peer_username'], $search_query) !== false;
    }));
}

$peer_user = null;
$thread_messages = null;

if($peer_id > 0) {
    $peer_user = do_getUserById($peer_id);
    if($peer_user) {
        do_markPrivateMessagesRead($current_user_id, $peer_id);
        $thread_total_messages = do_getPrivateMessageThreadCount($current_user_id, $peer_id);
        $thread_total_pages = max(1, intval(ceil($thread_total_messages / $thread_per_page)));
        if($thread_page < 1) {
            $thread_page = 1;
        }
        if($thread_page > $thread_total_pages) {
            $thread_page = $thread_total_pages;
        }
        $thread_messages = do_getPrivateMessageThreadPage($current_user_id, $peer_id, $thread_page, $thread_per_page);
    } else {
        $peer_id = 0;
        $error_message = 'That conversation user no longer exists.';
    }
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>Inbox - <?php echo $site['site_name']; ?></title>
        <meta name="description" content="<?php echo $site['site_description']; ?>"/>
        <link rel="icon" type="image/gif" href="<?php echo $site['favicon_url']; ?>" />
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/layout.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/<?php echo $user['theme']; ?>.css" />
    </head>
<body class="page-index desktop-mode">
    <h1 class="top_text" id="logo">
        <a rel="index" href="/" class="help_cursor" title="<?php echo fun_getslogan(); ?>"><?php echo $site['site_name']; ?></a>
    </h1>
<ul id="main_menu" class="menu">
<?php
foreach(do_getHomePageMenu() as $menu_item) {
    echo '<li><a href="' . $menu_item['url'] . '">' . $menu_item['name'] . '</a></li>';
}
?>
</ul>

<div id="body_wrapper">
    <h2 id="body_title">Inbox</h2>

<?php if($error_message !== '') { ?>
    <div id="notice" style="background-color: #ffcccc; color: #cc0000;"><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></div>
<?php } ?>
<?php if($success_message !== '') { ?>
    <div id="notice" style="background-color: #ccffcc; color: #00cc00;"><strong>Success:</strong> <?php echo htmlspecialchars($success_message); ?></div>
<?php } ?>

    <table>
        <thead>
            <tr>
                <th style="width:35%;">Conversations</th>
                <th>Thread</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="vertical-align:top;">
                    <form method="get" action="/inbox" style="margin-bottom:0.75em;">
                        <label for="q">Filter users:</label>
                        <input type="text" id="q" name="q" value="<?php echo htmlspecialchars($search_query); ?>" />
                        <label for="exact" class="inline">Exact</label>
                        <input type="checkbox" id="exact" name="exact" value="1"<?php if($exact_match) { echo ' checked="checked"'; } ?> />
                        <input type="submit" value="Go" />
<?php if($search_query !== '' || $exact_match) { ?>
                        <a href="<?php echo ($peer_id > 0 ? '/inbox/' . intval($peer_id) : '/inbox'); ?>" class="unimportant">Clear</a>
<?php } ?>
                    </form>
                    <form method="post" style="margin-bottom:0.75em;">
                        <fieldset>
                            <legend>New Message</legend>
                            <input type="hidden" name="action" value="compose_new" />
                            <label for="to_username">To (username):</label>
                            <input type="text" id="to_username" name="to_username" maxlength="32" style="width:100%;" value="<?php echo htmlspecialchars($compose_to_username); ?>" />
                            <label for="new_subject">Subject:</label>
                            <input type="text" id="new_subject" name="new_subject" maxlength="255" style="width:100%;" value="<?php echo htmlspecialchars($compose_subject); ?>" />
                            <label for="new_content">Message:</label>
                            <textarea id="new_content" name="new_content" rows="4" style="width:100%;"><?php echo htmlspecialchars($compose_content); ?></textarea>
                            <input type="submit" value="Send New Message" />
                        </fieldset>
                    </form>
                    <ul>
<?php
if(count($conversations) > 0) {
    foreach($conversations as $conv) {
        $snippet = substr(trim($conv['last_content']), 0, 60);
        $conv_link = '/inbox/' . intval($conv['peer_id']);
        $conv_query = array();
        if($search_query !== '') {
            $conv_query[] = 'q=' . rawurlencode($search_query);
        }
        if($exact_match) {
            $conv_query[] = 'exact=1';
        }
        if(count($conv_query) > 0) {
            $conv_link .= '?' . implode('&', $conv_query);
        }
        $conv_class = intval($conv['unread_count']) > 0 ? ' class="inbox-conversation unread"' : ' class="inbox-conversation"';
        echo '<li' . $conv_class . '>';
        echo '<a href="' . $conv_link . '">' . htmlspecialchars($conv['peer_username']) . '</a>';
        if(intval($conv['unread_count']) > 0) {
            echo ' <strong>(' . intval($conv['unread_count']) . ' new)</strong>';
        }
        echo '<br/><span class="unimportant">' . htmlspecialchars($conv['last_subject']) . ' - ' . htmlspecialchars($snippet) . '</span>';
        echo '</li>';
    }
} else {
    echo '<li class="unimportant">No conversations yet.</li>';
}
?>
                    </ul>
                </td>
                <td style="vertical-align:top;">
<?php if($peer_id > 0 && $peer_user) { ?>
                    <h3>Conversation with <?php echo htmlspecialchars($peer_user['username']); ?></h3>
                    <div>
<?php
if(is_array($thread_messages) && count($thread_messages) > 0) {
    foreach($thread_messages as $msg) {
        $is_mine = intval($msg['from_user_id']) === $current_user_id;
        echo '<div class="reply" style="margin-bottom:0.75em;">';
        echo '<strong>' . ($is_mine ? 'You' : htmlspecialchars($peer_user['username'])) . '</strong>';
        echo ' <span class="unimportant">(' . htmlspecialchars(fun_timeAgo(intval($msg['created_at']))) . ')</span>';
        echo '<br/><span class="unimportant">' . htmlspecialchars($msg['subject']) . '</span>';
        echo '<div class="body">' . nl2br(htmlspecialchars($msg['content'])) . '</div>';
        echo '</div>';
    }
} else {
    echo '<p class="unimportant">No messages yet in this thread.</p>';
}

if($thread_total_pages > 1) {
    echo '<div class="unimportant" style="margin:0.75em 0;">Page ' . intval($thread_page) . ' of ' . intval($thread_total_pages) . ' ';
    if($thread_page > 1) {
        $latest_link = '/inbox/' . intval($peer_id);
        $latest_query = array();
        if($search_query !== '') {
            $latest_query[] = 'q=' . rawurlencode($search_query);
        }
        if($exact_match) {
            $latest_query[] = 'exact=1';
        }
        if(count($latest_query) > 0) {
            $latest_link .= '?' . implode('&', $latest_query);
        }
        echo '<a href="' . $latest_link . '">[Jump to latest]</a> ';
    }
    if($thread_page > 1) {
        $prev_link = '/inbox/' . intval($peer_id) . '?thread_page=' . intval($thread_page - 1);
        if($search_query !== '') {
            $prev_link .= '&q=' . rawurlencode($search_query);
        }
        if($exact_match) {
            $prev_link .= '&exact=1';
        }
        echo '<a href="' . $prev_link . '">[Newer]</a> ';
    }
    if($thread_page < $thread_total_pages) {
        $next_link = '/inbox/' . intval($peer_id) . '?thread_page=' . intval($thread_page + 1);
        if($search_query !== '') {
            $next_link .= '&q=' . rawurlencode($search_query);
        }
        if($exact_match) {
            $next_link .= '&exact=1';
        }
        echo '<a href="' . $next_link . '">[Older]</a>';
    }
    echo '</div>';
}
?>
                    </div>

                    <form method="post">
                        <fieldset>
                            <legend>Reply</legend>
                            <input type="hidden" name="action" value="reply" />
                            <input type="hidden" name="peer_id" value="<?php echo intval($peer_id); ?>" />
                            <label for="reply_subject">Subject:</label><br/>
                            <input type="text" id="reply_subject" name="subject" maxlength="255" style="width:100%;" value="<?php echo htmlspecialchars($reply_subject); ?>" /><br/><br/>
                            <label for="reply_content">Message:</label><br/>
                            <textarea id="reply_content" name="content" rows="8" style="width:100%;"><?php echo htmlspecialchars($reply_content); ?></textarea><br/><br/>
                            <input type="submit" value="Send" />
                        </fieldset>
                    </form>
<?php } else { ?>
                    <p class="unimportant">Select a conversation from the left to view the message thread.</p>
<?php } ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div id="footer">
    <br/><div style="text-align:center" class="unimportant">
        <span><?php echo $site['disclaimer']; ?></span><br/><span>ezbbs, bby. <a href="https://github.com/goloins/ezbbs">Contribute on GitHub or get your own copy!</a></span></div><br/>
</div>
</body>
</html>
