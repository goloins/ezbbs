<?php
/* ezbbs - a simple bbs engine for the small web
 *
 * Copyleft 2026 by the ezbbs contributors
 *
 * edit_post.php - edit or append to an existing topic/reply post.
 */

require_once 'init.php';
do_requireLogin('/login');

$current_user_id = do_getCurrentUserId();
$kind = isset($_GET['kind']) ? trim($_GET['kind']) : '';
$post_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(($kind !== 'topic' && $kind !== 'reply') || $post_id <= 0) {
    header('Location: /');
    exit();
}

$error_message = '';
$post = null;
$thread_id = 0;
$post_author_id = 0;
$post_created_at = 0;
$post_content = '';
$redirect_anchor = '';

if($kind === 'topic') {
    $post = do_getThreadById($post_id);
    if(!$post) {
        header('Location: /404/thread');
        exit();
    }
    $thread_id = intval($post['id']);
    $post_author_id = intval($post['poster_id']);
    $post_created_at = intval($post['created_at']);
    $post_content = (string)$post['content'];
    $redirect_anchor = 'topic_' . $thread_id;
} else {
    $post = do_getReplyById($post_id);
    if(!$post) {
        header('Location: /404/thread');
        exit();
    }
    $thread_id = intval($post['thread_id']);
    $post_author_id = intval($post['poster_id']);
    $post_created_at = intval($post['created_at']);
    $post_content = (string)$post['content'];
    $redirect_anchor = 'reply_' . $post_id;
}

if(!do_canUserModifyPost($post_author_id, $current_user_id)) {
    header('Location: /thread/' . $thread_id);
    exit();
}

$within_window = do_isPostWithinEditWindow($post_created_at);
$can_full_edit = $within_window;

$selected_mode = $can_full_edit ? 'edit' : 'append';
$submitted_text = $can_full_edit ? $post_content : '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_mode = isset($_POST['mode']) && $_POST['mode'] === 'append' ? 'append' : 'edit';
    if(!$can_full_edit && $selected_mode !== 'append') {
        $selected_mode = 'append';
    }

    $submitted_text = isset($_POST['content']) ? trim($_POST['content']) : '';

    if($submitted_text === '') {
        $error_message = ($selected_mode === 'append') ? 'Append text is required.' : 'Post content is required.';
    } else {
        $ok = false;
        if($kind === 'topic') {
            $ok = do_updateTopicPostContent($post_id, $current_user_id, $submitted_text, $selected_mode);
        } else {
            $ok = do_updateReplyPostContent($post_id, $current_user_id, $submitted_text, $selected_mode);
        }

        if($ok) {
            header('Location: /thread/' . $thread_id . '#' . $redirect_anchor);
            exit();
        }

        $error_message = 'Could not save changes. If edit window expired, use append mode.';
    }
}

$page_title_prefix = ($kind === 'topic') ? 'Topic Post' : 'Reply';
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title><?php echo $page_title_prefix; ?> - Edit - <?php echo $site['site_name']; ?></title>
        <meta name="description" content="<?php echo $site['site_description']; ?>"/>
        <link rel="icon" type="image/gif" href="<?php echo $site['favicon_url']; ?>" />
        <link rel="stylesheet" type="text/css" media="screen" href="/assets/css/layout.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="/assets/css/<?php echo $user['theme']; ?>.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="/assets/css/vs.css" />
        <link rel="canonical" href="<?php echo $site['site_url']; ?>" />
    </head>
<body class="page-reply desktop-mode">

    <h1 class="top_text" id="logo">
        <a rel="index" href="/" class="help_cursor" title="<?php echo fun_getslogan(); ?>"><?php echo $site['site_name']; ?></a>
    </h1>
<?php echo do_renderLoginStatusChip(); ?>

<ul id="main_menu" class="menu">
<?php
foreach(do_getHomePageMenu() as $menu_item) {
    echo '<li><a href="' . $menu_item['url'] . '">' . $menu_item['name'] . '</a></li>';
}
?>
</ul>

<div id="body_wrapper">
    <h2 id="body_title"><?php echo $page_title_prefix; ?>: <?php echo ($can_full_edit ? 'Edit' : 'Append'); ?></h2>

<?php if($error_message !== '') { ?>
    <div id="notice" style="background-color: #ffcccc; color: #cc0000;">
        <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php } ?>

    <form method="post" action="/edit_post/<?php echo $kind; ?>/<?php echo $post_id; ?>" id="edit-post-form">
        <fieldset>
            <legend><?php echo ($selected_mode === 'append') ? 'Append to Post' : 'Edit Post'; ?></legend>

<?php if($can_full_edit) { ?>
            <label for="mode">Mode:</label>
            <select id="mode" name="mode">
                <option value="edit"<?php if($selected_mode === 'edit') { echo ' selected="selected"'; } ?>>Edit existing text</option>
                <option value="append"<?php if($selected_mode === 'append') { echo ' selected="selected"'; } ?>>Append new text</option>
            </select>
<?php } else { ?>
            <input type="hidden" name="mode" value="append" />
            <p class="unimportant">Edit window expired. Only append mode is available.</p>
<?php } ?>

<?php if($selected_mode === 'append') { ?>
            <label>Current post text:</label><br>
            <div id="existing_content" class="body border" style="max-height: 260px; overflow-y: auto;"><?php echo nl2br(htmlspecialchars($post_content, ENT_QUOTES, 'UTF-8')); ?></div><br>

            <label for="content">Your additions:</label>
            <textarea id="content" name="content" rows="8" style="width:100%;"><?php echo htmlspecialchars($submitted_text); ?></textarea>
<?php } else { ?>
            <label for="content">Post text:</label>
            <textarea id="content" name="content" rows="12" style="width:100%;"><?php echo htmlspecialchars($submitted_text); ?></textarea>
<?php } ?>

            <br/>
            <input type="submit" value="Save" />
            <a href="/thread/<?php echo $thread_id; ?>#<?php echo $redirect_anchor; ?>">[Cancel]</a>
        </fieldset>
    </form>
</div>

<div id="footer">
    <br/><div style="text-align:center" class="unimportant">
        <span><?php echo $site['disclaimer']; ?></span><br/><span>ezbbs, bby. <a href="https://github.com/goloins/ezbbs">Contribute on GitHub or get your own copy!</a></span></div><br/>
        <noscript><br /><span class="unimportant">Note: Your browser's JavaScript is disabled; some site features may not fully function, but don't worry, we're trying to get rid of all the js :^)</span></noscript>
</div>

</body>
</html>
