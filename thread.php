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
        <?php if(intval($thread['isPinned']) === 1) { ?><span class="thread-pin help" title="Pinned topic">&#128204;</span> <?php } ?>
		<span class="pre_topic">Topic:</span> <?php echo htmlspecialchars($thread['title']); ?></h2>

<?php $topic_author = do_getUserById($thread['poster_id']); ?>
<?php $current_user_id = do_getCurrentUserId(); ?>
<?php $can_modify_topic = do_canUserModifyPost($thread['poster_id'], $current_user_id); ?>
<?php $topic_edit_label = do_isPostWithinEditWindow($thread['created_at']) ? 'Edit' : 'Append'; ?>
<?php $thread_flair_breakdown = do_getFlairBreakdownForPost($thread_id); ?>
<?php $thread_user_flair_votes = do_isLoggedIn() ? do_getUserFlairVotesForThread($thread_id, $current_user_id) : array(); ?>
<?php $thread_is_party = intval($thread['isParty']) === 1; ?>
<?php
$thread_consensus_label = 'No Consensus';
if(chk_DoesPostHaveFlairYet($thread_id)) {
    $consensus = do_getStandoutFlairsForPost($thread_id);
    if($consensus && isset($consensus['flair_name'])) {
        $plmn = '+';
        if(intval($consensus['flair_count']) < 0) {
            $plmn = '-';
        }
        $thread_consensus_label = $consensus['flair_name'] . ' (' . $plmn . intval(abs($consensus['flair_count'])) . ')';
    }
}
?>
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
                    <span class="meta-sep">|</span>
                    <span class="thread-inline-flairs">
<?php foreach($thread_flair_breakdown as $flair_option) {
    $fid = intval($flair_option['flair_id']);
    $already_voted = isset($thread_user_flair_votes[$fid]);
    $vote_url = '/do/flair/' . $thread_id . '/' . $fid;
    $chip_tone = !empty($flair_option['positive']) ? 'flair-chip-positive' : 'flair-chip-negative';
    $chip_title = htmlspecialchars($flair_option['name'] . ': ' . $flair_option['description'] . ' (' . intval($flair_option['count']) . ')');
    $chip_inner = '<span class="flair-chip-icon" aria-hidden="true">' . htmlspecialchars((string)$flair_option['icon']) . '</span><span class="flair-chip-count">' . intval($flair_option['count']) . '</span>';
    $chip_state = $already_voted ? 'flair-chip-voted' : '';
    echo '<a class="flair-chip flair-chip-link ' . $chip_tone . ' ' . $chip_state . '" href="' . $vote_url . '" title="' . $chip_title . '">' . $chip_inner . '</a>';
} ?>
                    </span>
                </span>
            </h3>

            <div class="body"><?php
$topic_body_html = do_RenderTopicContent($thread['content']);
$topic_body_html = str_replace('---<br />', do_getAppendSeparatorHtml() . '<br />', $topic_body_html);
echo $topic_body_html;
?>
<?php echo do_getPostRevisionNoteHtml(isset($thread['is_edited']) ? $thread['is_edited'] : 0, isset($thread['edited_at']) ? $thread['edited_at'] : 0, isset($thread['poster_id']) ? $thread['poster_id'] : 0); ?>
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
            echo '<li><span class="unimportant">Consensus: ' . htmlspecialchars($thread_consensus_label) . '</span></li>';
            if($can_modify_topic) {
                echo '<li><a href="/edit_post/topic/' . $thread_id . '">' . $topic_edit_label . '</a></li>';
            }
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
<?php if($thread_is_party) { ?><div class="party-ban-warning">USER WAS BANNED FOR THIS POST</div><?php } ?>
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
                $can_modify_reply = do_canUserModifyPost($reply['poster_id'], $current_user_id);
                $reply_edit_label = do_isPostWithinEditWindow($reply['created_at']) ? 'Edit' : 'Append';
                echo '<div class="thread-post reply" id="reply_' . intval($reply['id']) . '">';
                ezbbs_renderUserPanel($reply_author, $site);
                echo '<div class="thread-post-main">';
                echo '<h3 class="c">';
                echo do_getFullyFormattedUsername($reply['poster_id']);
                echo ' replied about <strong><span class="help" title="' . date('Y-m-d H:i:s \\U\\T\\C — l \\t\\h\\e jS \\o\\f F Y, g:i A', $reply['created_at']) . '">' . htmlspecialchars(fun_timeAgo($reply['created_at'])) . '</span></strong>';
                echo ' <span class="reply_id unimportant">#' . intval($reply['id']) . '</span>';
                echo '</h3>';
                $reply_body_html = do_RenderReplyText($reply['content'], $reply['id']);
                $reply_body_html = str_replace('---<br />', do_getAppendSeparatorHtml() . '<br />', $reply_body_html);
                echo '<div class="body">' . $reply_body_html;
                echo do_getPostRevisionNoteHtml(isset($reply['is_edited']) ? $reply['is_edited'] : 0, isset($reply['edited_at']) ? $reply['edited_at'] : 0, isset($reply['poster_id']) ? $reply['poster_id'] : 0);
                if(do_isLoggedIn()) {
                    echo '<ul class="menu">';
                    echo '<li><a href="/new_reply/' . $thread_id . '/quote_reply/' . intval($reply['id']) . '">Quote</a></li>';
                    if($can_modify_reply) {
                        echo '<li><a href="/edit_post/reply/' . intval($reply['id']) . '">' . $reply_edit_label . '</a></li>';
                    }
                    echo '</ul>';
                }
                if($thread_is_party) {
                    echo '<div class="party-ban-warning">USER WAS BANNED FOR THIS POST</div>';
                }
                echo '</div>';
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
