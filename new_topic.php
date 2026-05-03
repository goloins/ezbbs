<?php
/* ezbbs - a simple bbs engine for the small web
 *
 * Copyleft 2026 by the ezbbs contributors
 *
 * new_topic.php - page for composing and posting a new topic.
 */

require_once 'init.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
    header('Location: /');
    exit();
}

$error_message = '';
$success_message = '';

$title = '';
$content = '';
$category_id = 1;
$tags_input = '';
$media_input = '';
$links_input = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $tags_input = isset($_POST['tags']) ? trim($_POST['tags']) : '';
    $media_input = isset($_POST['media']) ? trim($_POST['media']) : '';
    $links_input = isset($_POST['links']) ? trim($_POST['links']) : '';

    $media = array();
    if($media_input !== '') {
        $raw_media = preg_split('/[\r\n,]+/', $media_input);
        foreach($raw_media as $item) {
            $item = trim($item);
            if($item !== '') {
                $media[] = $item;
            }
        }
        $media = array_values(array_unique($media));
    }

    $attached_links = array();
    if($links_input !== '') {
        $raw_links = preg_split('/[\r\n,]+/', $links_input);
        foreach($raw_links as $item) {
            $item = trim($item);
            if($item !== '') {
                $attached_links[] = $item;
            }
        }
        $attached_links = array_values(array_unique($attached_links));
    }

    $tags = array();
    if($tags_input !== '') {
        $raw_tags = preg_split('/[\r\n,]+/', $tags_input);
        foreach($raw_tags as $tag) {
            $tag = trim($tag);
            if($tag === '') {
                continue;
            }
            $tag = preg_replace('/\s+/', '_', $tag);
            $tag = preg_replace('/[^a-zA-Z0-9_\-]/', '', $tag);
            $tag = strtolower($tag);
            if($tag !== '') {
                $tags[] = $tag;
            }
        }
        $tags = array_values(array_unique($tags));
    }

    if($title === '' || $content === '') {
        $error_message = 'Title and content are required.';
    } elseif(!isset($categories[$category_id])) {
        $error_message = 'Please select a valid category.';
    } else {
        $link_gate_content = $content;
        if(count($attached_links) > 0) {
            $link_gate_content .= "\n" . implode("\n", $attached_links);
        }
        if(count($media) > 0) {
            $link_gate_content .= "\n" . implode("\n", $media);
        }

        if(!chk_UserCanPostOutboundLinks($_SESSION['user_id'], $link_gate_content)) {
            $error_message = 'You need more posts before including outbound links.';
        } else {
            if(post_Topic($title, $content, $_SESSION['user_id'], $category_id, $media, $attached_links, 0, $tags)) {
                $new_topic_id = intval($go_sql->insert_id);
                header('Location: /topic/' . $new_topic_id);
                exit();
            } else {
                $error_message = 'Could not create topic. Please try again.';
            }
        }
    }
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>New Topic - <?php echo $site['site_name']; ?></title>
        <meta name="description" content="<?php echo $site['site_description']; ?>"/>
        <link rel="icon" type="image/gif" href="<?php echo $site['favicon_url']; ?>" />
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/layout.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/<?php echo $user['theme']; ?>.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/vs.css" />
        <link rel="canonical" href="<?php echo $site['site_url']; ?>" />
    </head>
<body class="page-reply desktop-mode">

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
    <h2 id="body_title">Create a New Topic</h2>

<?php if($error_message !== '') { ?>
    <div id="notice" style="background-color: #ffcccc; color: #cc0000;">
        <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
    </div>
<?php } ?>

<?php if($success_message !== '') { ?>
    <div id="notice" style="background-color: #ccffcc; color: #00cc00;">
        <strong>Success:</strong> <?php echo htmlspecialchars($success_message); ?>
    </div>
<?php } ?>

    <form method="post" action="/new_topic" id="new-topic-form">
        <fieldset>
            <legend>Compose Topic</legend>

            <label for="title">Title:</label>
            <input type="text" id="title" name="title" maxlength="255" style="width:100%;" value="<?php echo htmlspecialchars($title); ?>" />

            <label for="category_id">Category:</label>
            <select id="category_id" name="category_id">
<?php foreach($categories as $cat_id => $cat_name) { ?>
                <option value="<?php echo intval($cat_id); ?>"<?php if(intval($category_id) === intval($cat_id)) { echo ' selected="selected"'; } ?>><?php echo htmlspecialchars($cat_name); ?></option>
<?php } ?>
            </select>

            <label for="content">Content:</label>
            <textarea id="content" name="content" rows="12" style="width:100%;"><?php echo htmlspecialchars($content); ?></textarea>

            <label for="tags">Tags (comma or newline separated):</label>
            <input type="text" id="tags" name="tags" style="width:100%;" value="<?php echo htmlspecialchars($tags_input); ?>" />

            <label for="media">Media URLs (optional, comma or newline separated):</label>
            <textarea id="media" name="media" rows="3" style="width:100%;"><?php echo htmlspecialchars($media_input); ?></textarea>

            <label for="links">Attached links (optional, comma or newline separated):</label>
            <textarea id="links" name="links" rows="3" style="width:100%;"><?php echo htmlspecialchars($links_input); ?></textarea>

            <br/>
            <input type="submit" value="Post Topic" />
            <a href="/">[Cancel]</a>
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
