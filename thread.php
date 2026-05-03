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

function ezbbs_pathOrDefault($path, $fallback){
    $path = trim((string)$path);
    if($path === '') {
        return $fallback;
    }
    if(strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0 || strpos($path, '/') === 0) {
        return $path;
    }
    return '/' . ltrim($path, '/');
}

function ezbbs_renderUserPanel($author, $site){
    $author_id = intval($author['id']);
    $avatar_url = ezbbs_pathOrDefault(isset($author['defaultavatar']) ? $author['defaultavatar'] : '', '/assets/png/default_avatar.png');
    $portrait_url = ezbbs_pathOrDefault(isset($author['userportrait']) ? $author['userportrait'] : '', '/assets/png/blank_portrait.png');
    $joined_at = isset($author['joindate']) ? intval($author['joindate']) : 0;

    echo '<aside class="thread-user-panel">';
    echo '<div class="thread-user-photo-stack">';
    echo '<img class="thread-user-avatar" src="' . htmlspecialchars($avatar_url) . '" alt="user photo" />';
    echo '<img class="thread-user-portrait" src="' . htmlspecialchars($portrait_url) . '" alt="user portrait overlay" />';
    echo '</div>';

    echo '<div class="thread-user-name">' . do_getFullyFormattedUsername($author_id);
    if(!empty($author['isadmin'])) {
        echo $site['admin_suffix'];
    }
    if(!empty($author['isbanned'])) {
        echo ' <span class="unimportant">[banned]</span>';
    }
    echo '</div>';

    echo '<ul class="thread-user-meta unimportant">';
    if($joined_at > 0) {
        echo '<li>Joined: ' . date('Y-m-d', $joined_at) . '</li>';
    }
    echo '<li>Posts: ' . intval(isset($author['userposts']) ? $author['userposts'] : 0) . '</li>';
    echo '<li>Kudos: ' . intval(isset($author['userkudos']) ? $author['userkudos'] : 0) . '</li>';
    if(isset($author['defaultlocation']) && trim($author['defaultlocation']) !== '') {
        echo '<li>From: ' . htmlspecialchars($author['defaultlocation']) . '</li>';
    }
    if(isset($author['userwebsite']) && trim($author['userwebsite']) !== '') {
        $website = trim($author['userwebsite']);
        if(strpos($website, 'http://') !== 0 && strpos($website, 'https://') !== 0) {
            $website = 'https://' . $website;
        }
        echo '<li><a href="' . htmlspecialchars($website) . '" target="_blank" rel="noopener noreferrer">website</a></li>';
    }
    echo '</ul>';
    echo '</aside>';
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<title><?php echo htmlspecialchars($thread['title']); ?> — <?php echo $site['site_name'];?></title>
                    <meta name="description" content="<?php echo $site['site_description'];?>"/>
                <link rel="icon" type="image/gif" href="<?php echo $site['favicon_url'];?>" />
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/layout.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/<?php echo $user['theme'];?>.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="/assets/css/vs.css" />
		        <link rel="canonical" href="<?php echo $site['site_url'];?>" />
		</head>
<body class="page-index desktop-mode">	<h1 class="top_text" id="logo">
		<a rel="index" href="/" class="help_cursor" title="<?php echo fun_getslogan();?>"><?php echo $site['site_name'];?></a></h1>
<?php echo do_renderLoginStatusChip(); ?>
<ul id="main_menu" class="menu">
<?php 
foreach(do_getHomePageMenu() as $menu_item) {
    echo '<li><a href="' . $menu_item['url'] . '">' . $menu_item['name'] . '</a></li>';
}
?>
</ul>
</div>
<div id="body_wrapper">
    <h2 id="body_title">
		<span class="pre_topic">Topic:</span> <?php echo htmlspecialchars($thread['title']); ?></h2>

<?php $topic_author = do_getUserById($thread['poster_id']); ?>
    <div class="thread-post" id="topic_<?php echo $thread_id; ?>">
<?php ezbbs_renderUserPanel($topic_author, $site); ?>
        <div class="thread-post-main">
            <h3 class="c">
                <span class="joined help" title="This poster started the topic.">+</span>
                <?php echo do_getFullyFormattedUsername($thread['poster_id']); ?>
                <strong>
                    <span class="help" title="<?php echo date('Y-m-d H:i:s \U\T\C — l \t\h\e jS \o\f F Y, g:i A', $thread['created_at']); ?>"><?php echo fun_timeAgo($thread['created_at']); ?></span>
                </strong>
                <span class="reply_id unimportant">
                    <a href="/cat/<?php echo intval($thread['category_id']); ?>"><?php echo htmlspecialchars($categories[$thread['category_id']]); ?></a>
<?php if(!chk_DoesPostHaveFlairYet($thread_id)) {
    echo ' | No Consensus';
} else {
    $consensus = do_getStandoutFlairsForPost($thread_id);
    if($consensus && isset($consensus['flair_name'])) {
        $plmn = '+';
        if(intval($consensus['flair_count']) < 0) {
            $plmn = '-';
        }
        echo ' | ' . htmlspecialchars($consensus['flair_name']) . ' (' . $plmn . intval(abs($consensus['flair_count'])) . ')';
    }
} ?>
                </span>
            </h3>

            <div class="body"><?php echo do_RenderTopicContent($thread['content']); ?>
                <ul class="menu"><li><?php
        if(do_isLoggedIn()) {
            echo '<a href="/compose_message/topic/' . $thread_id . '">PM</a></li>';
            $is_watched = chk_IsThreadWatchedByUser(do_getCurrentUserId(), $thread_id);
            if($is_watched) {
                echo '<li><a href="/forget_thread/' . $thread_id . '">Forget Thread</a></li>';
            } else {
                echo '<li><a href="/watch_topic/' . $thread_id . '">Watch</a></li>';
            }
            echo '<li><a href="/new_reply/' . $thread_id . '/quote_topic">Quote</a></li>';
            echo '<li><a href="/new_reply/' . $thread_id . '" onclick="window.open(this.href,\'targetWindow\',\'width=700px,height=700px\'); return false;">Reply</a></li>';
        } else {
            echo '<a href="/login?next=' . rawurlencode('/thread/' . $thread_id) . '">Login to reply</a></li>';
        }


//display all tags for the thread.
        $gettags = json_decode($thread['tags'], true);
        if(is_array($gettags) && count($gettags) > 0) {
            foreach($gettags as $tag) { 
                echo '<li><a href="/tag/' . $tag . '" class="help" title="18 replies">#' . $tag . ' </a></li>';
            }
        }
        ?>   
                </ul>
            </div>
        </div>
    </div>

    <!-- Replies section -->
    <div id="replies"> 
        <?php
        $replies = do_getRepliesForThread($thread_id);
        if($replies && $replies->num_rows > 0) {
            while($reply = $replies->fetch_assoc()) {
                $reply_author = do_getUserById($reply['poster_id']);
                echo '<div class="thread-post reply" id="reply_' . intval($reply['id']) . '">';
                ezbbs_renderUserPanel($reply_author, $site);
                echo '<div class="thread-post-main">';
                echo '<h3 class="c">';
                echo do_getFullyFormattedUsername($reply['poster_id']);
                echo ' replied about <b><span class="help" title="' . date('Y-m-d H:i:s \\U\\T\\C — l \\t\\h\\e jS \\o\\f F Y, g:i A', $reply['created_at']) . '">' . htmlspecialchars(fun_timeAgo($reply['created_at'])) . '</b></span>';
                echo ' <span class="reply_id unimportant">#' . intval($reply['id']) . '</span>';
                echo '</h3>';
                echo '<div class="body">' . do_RenderReplyText($reply['content'], $reply['id']) . '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p class="unimportant">No replies yet.</p>';
        }
        ?>
    </div>

    <div class="reply-action">
<?php if(do_isLoggedIn()) { ?>
        <a href="/new_reply/<?php echo $thread_id; ?>" class="button">Reply to this thread</a>
<?php } else { ?>
        <a href="/login?next=<?php echo rawurlencode('/thread/' . $thread_id); ?>" class="button">Login to reply</a>
<?php } ?>
    </div>

</div><!-- body_wrapper -->

<div id="footer">
    <br/><div style="text-align:center" class="unimportant">
        <span><?php echo $site['disclaimer']; ?></span><br/><span>ezbbs, bby. <a href="https://github.com/goloins/ezbbs">Contribute on GitHub or get your own copy!</a></span></div><br/>
        <noscript><br /><span class="unimportant">Note: Your browser's JavaScript is disabled; some site features may not fully function, but don't worry, we're trying to get rid of all the js :^)</span></noscript>
    <div id="quotePreview"></div>
</div>

</body>
</html>
