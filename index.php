<?php
/* ezbbs - a simple bbs engine for the small web
 *
 * Copyleft 2026 by the ezbbs contributors
 *
 * index.php - primary route surface for topic lists and utility pages.
 */

require_once 'init.php';

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if($page < 1) {
    $page = 1;
}

$url = isset($_GET['url']) ? trim($_GET['url']) : 'topics';
if($url === '') {
    $url = 'topics';
}

$route_mode = $url;
$route_cat_id = null;
$route_tag = '';

if(strpos($url, 'cat/') === 0) {
    $route_mode = 'cat';
    $route_cat_id = intval(substr($url, 4));
} elseif(strpos($url, 'tag/') === 0) {
    $route_mode = 'tag';
    $route_tag = trim(substr($url, 4));
}

if(isset($_GET['tag']) && trim($_GET['tag']) !== '') {
    $route_mode = 'tag';
    $route_tag = trim($_GET['tag']);
}

$notice_error = '';
$notice_success = '';

if($route_mode === 'new_topic' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
        $notice_error = 'You must be logged in to create a topic.';
    } else {
        $new_title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $new_content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $new_category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

        if($new_title === '' || $new_content === '') {
            $notice_error = 'Title and content are required.';
        } elseif(!isset($categories[$new_category_id])) {
            $notice_error = 'Please select a valid category.';
        } elseif(!chk_UserCanPostOutboundLinks($_SESSION['user_id'], $new_content)) {
            $notice_error = 'You need more posts before including outbound links.';
        } else {
            if(post_Topic($new_title, $new_content, $_SESSION['user_id'], $new_category_id)) {
                $new_topic_id = intval($go_sql->insert_id);
                header('Location: /topic/' . $new_topic_id);
                exit();
            } else {
                $notice_error = 'Could not create topic. Please try again.';
            }
        }
    }
}

function ezbbs_get_visit_link_for_topic($topic) {
    if(!isset($topic['attached_links'])) {
        return '';
    }
    $links = json_decode($topic['attached_links'], true);
    if(!is_array($links) || count($links) === 0) {
        return '';
    }
    $primary = trim((string)$links[0]);
    if($primary === '') {
        return '';
    }
    if(strpos($primary, 'http://') !== 0 && strpos($primary, 'https://') !== 0) {
        $primary = 'https://' . ltrim($primary, '/');
    }
    return $primary;
}

function ezbbs_render_topic_row($topic, $site, $user, $categories) {
    $poster = do_getUserById($topic['poster_id']);
    if(!$poster) {
        return;
    }

    $posterProfileLink = '/user/' . $poster['id'];
    $posterUsernameColor = isset($poster['usernamecolor']) ? $poster['usernamecolor'] : $user['usernamecolor'];
    $posterUsernameStyle = isset($poster['usernamestyle']) ? $poster['usernamestyle'] : $user['usernamestyle'];
    $posterIsAdmin = !empty($poster['isadmin']);
    $posterBio = isset($poster['defaultbio']) ? $poster['defaultbio'] : '';

    $isLemoned = chk_ThreadLemoned($topic['id']);
    $isParty = chk_ThreadParty($topic['id']);
    $isPinned = chk_ThreadPinned($topic['id']);
    $isArchived = chk_ThreadArchived($topic['id']);
    $isLocked = chk_ThreadLocked($topic['id']);
    $isChanlike = chk_ThreadChanlike($topic['id']);
    $topic_id = intval($topic['id']);
    $thread_url = '/topic/' . $topic_id;
    $visit_url = ezbbs_get_visit_link_for_topic($topic);

    $current_user_id = do_getCurrentUserId();
    $can_vote_flairs = do_isLoggedIn() && intval($topic['poster_id']) !== intval($current_user_id);
    $flair_breakdown = do_getFlairBreakdownForPost($topic_id);
    $user_flair_votes = $can_vote_flairs ? do_getUserFlairVotesForThread($topic_id, $current_user_id) : array();
    $user_has_flair_vote = count($user_flair_votes) > 0;

    $replies_count = intval($topic['replies_count']);
    $visits_count = intval($topic['visits_count']);
    $time_ago = fun_timeAgo(intval($topic['last_bump']));
    $last_bump_full = date('Y-m-d H:i:s', intval($topic['last_bump']));

    $topic_title = substr(htmlspecialchars($topic['title']), 0, $site['topic_headline_length']);
    if(chk_PosterIsBanned($topic['poster_id'])) {
        $topic_title = '<b><font color="red">' . $site['topic_poster_banned_prefix'] . '</font></b>' . $topic_title;
    }

    $poster_display_name = htmlspecialchars($poster['username']);
    if($posterIsAdmin) {
        $poster_display_name .= $site['admin_suffix'];
    }

    $poster_open = '';
    $poster_close = '';

    if($posterUsernameColor) {
        $poster_open .= '<span style="color:' . htmlspecialchars($posterUsernameColor) . '">';
        $poster_close = '</span>' . $poster_close;
    }

    if($posterUsernameStyle === 'bold') {
        $poster_open .= '<b>';
        $poster_close = '</b>' . $poster_close;
    } elseif($posterUsernameStyle === 'italic') {
        $poster_open .= '<i>';
        $poster_close = '</i>' . $poster_close;
    } elseif($posterUsernameStyle === 'underline') {
        $poster_open .= '<u>';
        $poster_close = '</u>' . $poster_close;
    }

    $poster_rendered = $poster_open . $poster_display_name . $poster_close;
    if($posterBio !== '') {
        $poster_rendered = '<span class="help" title="' . htmlspecialchars($posterBio) . '">' . $poster_rendered . '</span>';
    }

    $category_name = isset($categories[$topic['category_id']]) ? $categories[$topic['category_id']] : 'Uncategorized';

    echo '<tr>';
    echo '<td class="minimal">';
    if($isLemoned) {
        echo '<span class="help" title="This thread is lemoned."><img src="/assets/icons/lemon.png" alt="Lemon icon" /></span>';
    }
    if($isParty) {
        echo '<span class="help" title="This thread is marked as party."><img src="/assets/icons/partyhat.png" alt="Party hat icon" /></span>';
    }
    if($isArchived) {
        echo '<span class="help" title="This thread is archived."><img src="/assets/icons/tombstone.png" alt="Tombstone icon" /></span>';
    }
    if($isLocked) {
        echo '<span class="help" title="This thread is locked."><img src="/assets/icons/lock.png" alt="Lock icon" /></span>';
    }
    if($isChanlike) {
        echo '<span class="help" title="This thread is chanlike."><img src="/assets/icons/fire.png" alt="Fire icon" /></span>';
    }
    echo '</td>';

    $topic_headline = '<a title="' . htmlspecialchars(substr($topic['title'], 0, $site['topic_preview_length'])) . '" href="' . $thread_url . '">' . $topic_title . '</a>';
    if($isPinned) {
        $topic_headline = '<span class="topic-pin help" title="Pinned topic">&#128204;</span> ' . $topic_headline;
    }

    $share_url = $thread_url;
    $email_url = 'mailto:?subject=' . rawurlencode('Topic: ' . $topic['title']) . '&body=' . rawurlencode($site['site_url'] . ltrim($thread_url, '/'));

    $meta_left = '<a href="' . $share_url . '">share</a> <span class="meta-sep">|</span> <a href="' . $email_url . '">email</a> <span class="meta-sep">|</span> <a href="/new_reply/' . $topic_id . '">reply</a>';
    if($visit_url !== '') {
        $meta_left .= ' <span class="meta-sep">|</span> <a href="' . htmlspecialchars($visit_url) . '" target="_blank" rel="noopener noreferrer">visit</a>';
    } else {
        $meta_left .= ' <span class="meta-sep">|</span> <span class="unimportant">visit</span>';
    }

    $meta_right = '';
    if(count($flair_breakdown) > 0) {
        foreach($flair_breakdown as $flair_option) {
            $fid = intval($flair_option['flair_id']);
            $already_voted = isset($user_flair_votes[$fid]);
            $chip_tone = !empty($flair_option['positive']) ? 'flair-chip-positive' : 'flair-chip-negative';
            $chip_title = htmlspecialchars($flair_option['name'] . ': ' . $flair_option['description'] . ' (' . intval($flair_option['count']) . ')');
            $chip_inner = '<span class="flair-chip-icon" aria-hidden="true">' . $flair_option['icon'] . '</span><span class="flair-chip-count">' . intval($flair_option['count']) . '</span>';

            if($can_vote_flairs && !$user_has_flair_vote) {
                $meta_right .= '<a class="flair-chip flair-chip-link ' . $chip_tone . '" href="/do/flair/' . $topic_id . '/' . $fid . '" title="' . $chip_title . '">' . $chip_inner . '</a>';
            } else {
                $chip_state = $already_voted ? 'flair-chip-voted' : 'flair-chip-locked';
                $meta_right .= '<span class="flair-chip ' . $chip_tone . ' ' . $chip_state . '" title="' . $chip_title . '">' . $chip_inner . '</span>';
            }
        }
    }

    echo '<td class="topic_headline">' . $topic_headline;
    echo '<div class="topic-row-subline"><span class="topic-row-actions">' . $meta_left . '</span><span class="topic-row-flairs">' . trim($meta_right) . '</span></div>';
    echo '</td>';
    echo '<td class="minimal"><a href="' . $posterProfileLink . '">' . $poster_rendered . '</a></td>';
    echo '<td class="minimal"><strong>' . $replies_count . '</strong></td>';
    echo '<td class="minimal">' . $visits_count . '</td>';
    echo '<td class="minimal"><span class="help" title="' . $last_bump_full . '">' . htmlspecialchars($time_ago) . '</span></td>';
    echo '<td class="minimal"><a href="/cat/' . intval($topic['category_id']) . '">' . htmlspecialchars($category_name) . '</a></td>';
    echo '</tr>';
}

$body_title = do_determineCurrentPageorCat();
$topics_result = null;
$users_result = null;
$recent_replies_result = null;
$search_topics_result = null;
$search_users_result = null;
$site_stats = null;
$tag_cloud = array();
$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';

if($route_mode === 'topics') {
    $body_title = 'All Topics';
    $topics_result = do_getTopics($page);
} elseif($route_mode === 'hot_topics') {
    $body_title = 'Hot Topics';
    $stmt = $go_sql->prepare('SELECT * FROM topics ORDER BY isPinned DESC, (replies_count * 2 + visits_count) DESC, last_bump DESC LIMIT 20');
    $stmt->execute();
    $topics_result = $stmt->get_result();
} elseif($route_mode === 'bumps') {
    $body_title = 'Recent Bumps';
    $stmt = $go_sql->prepare('SELECT * FROM topics ORDER BY isPinned DESC, last_bump DESC LIMIT 20');
    $stmt->execute();
    $topics_result = $stmt->get_result();
} elseif($route_mode === 'replies') {
    $body_title = 'Most Replied Topics';
    $stmt = $go_sql->prepare('SELECT * FROM topics ORDER BY isPinned DESC, replies_count DESC, last_bump DESC LIMIT 20');
    $stmt->execute();
    $topics_result = $stmt->get_result();

    $reply_stmt = $go_sql->prepare('SELECT r.id, r.thread_id, r.poster_id, r.content, r.created_at, t.title AS thread_title FROM replies r INNER JOIN topics t ON t.id = r.thread_id WHERE r.isHidden = 0 ORDER BY r.created_at DESC LIMIT 15');
    $reply_stmt->execute();
    $recent_replies_result = $reply_stmt->get_result();
} elseif($route_mode === 'cat') {
    $category_name = isset($categories[$route_cat_id]) ? $categories[$route_cat_id] : 'Unknown Category';
    $body_title = 'Category: ' . $category_name;
    $topics_result = do_getAllThreadsInCategory($route_cat_id, $page);
} elseif($route_mode === 'tag') {
    $body_title = 'Tag: #' . $route_tag;
    $topics_result = do_getAllThreadsInTag($route_tag);
} elseif($route_mode === 'folks') {
    $body_title = 'Folks';
    $stmt = $go_sql->prepare('SELECT id, username, userposts, userkudos, joindate, isbanned FROM users ORDER BY userposts DESC, joindate ASC LIMIT 100');
    $stmt->execute();
    $users_result = $stmt->get_result();
} elseif($route_mode === 'search') {
    $body_title = 'Search';
    if($search_term !== '') {
        $stmt_topics = $go_sql->prepare('SELECT * FROM topics WHERE title LIKE CONCAT("%", ?, "%") OR content LIKE CONCAT("%", ?, "%") ORDER BY isPinned DESC, last_bump DESC LIMIT 50');
        $stmt_topics->bind_param('ss', $search_term, $search_term);
        $stmt_topics->execute();
        $search_topics_result = $stmt_topics->get_result();

        $stmt_users = $go_sql->prepare('SELECT id, username, userposts, userkudos, joindate FROM users WHERE username LIKE CONCAT("%", ?, "%") ORDER BY userposts DESC LIMIT 50');
        $stmt_users->bind_param('s', $search_term);
        $stmt_users->execute();
        $search_users_result = $stmt_users->get_result();
    }
} elseif($route_mode === 'stuff') {
    $body_title = 'Stuff';
    $stats_stmt = $go_sql->prepare('SELECT (SELECT COUNT(*) FROM topics) AS topic_count, (SELECT COUNT(*) FROM replies) AS reply_count, (SELECT COUNT(*) FROM users) AS user_count');
    $stats_stmt->execute();
    $site_stats = $stats_stmt->get_result()->fetch_assoc();
    $tag_cloud = do_generateTagCloudFromTopics(40, 1);
} elseif($route_mode === 'new_topic') {
    $body_title = 'New Topic';
} else {
    $body_title = 'Latest Threads';
    $topics_result = do_getTopics($page);
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title><?php echo htmlspecialchars($body_title); ?> - <?php echo $site['site_name']; ?></title>
        <meta name="description" content="<?php echo $site['site_description']; ?>"/>
        <link rel="icon" type="image/gif" href="<?php echo $site['favicon_url']; ?>" />
        <link rel="stylesheet" type="text/css" media="screen" href="/assets/css/layout.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="/assets/css/<?php echo $user['theme']; ?>.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="/assets/css/vs.css" />
        <link rel="canonical" href="<?php echo $site['site_url']; ?>" />
    </head>
<body class="page-index desktop-mode">
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
    <h2 id="body_title"><?php echo htmlspecialchars($body_title); ?></h2>

<?php if($notice_error !== '') { ?>
    <div id="notice" style="background-color: #ffcccc; color: #cc0000;"><strong>Error:</strong> <?php echo htmlspecialchars($notice_error); ?></div>
<?php } ?>
<?php if($notice_success !== '') { ?>
    <div id="notice" style="background-color: #ccffcc; color: #00cc00;"><strong>Success:</strong> <?php echo htmlspecialchars($notice_success); ?></div>
<?php } ?>

<?php if($route_mode === 'new_topic') { ?>
    <form method="post" action="/new_topic" id="new-topic-form">
        <fieldset>
            <legend>Create a Topic</legend>
            <label for="title">Title:</label><br/>
            <input type="text" id="title" name="title" maxlength="255" style="width:100%;" /><br/><br/>

            <label for="category_id">Category:</label><br/>
            <select id="category_id" name="category_id">
<?php foreach($categories as $cat_id => $cat_name) { ?>
                <option value="<?php echo intval($cat_id); ?>"><?php echo htmlspecialchars($cat_name); ?></option>
<?php } ?>
            </select><br/><br/>

            <label for="content">Content:</label><br/>
            <textarea id="content" name="content" rows="12" style="width:100%;"></textarea><br/><br/>

            <input type="submit" value="Post Topic" />
        </fieldset>
    </form>

<?php } elseif($route_mode === 'folks') { ?>
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th class="minimal">Posts</th>
                <th class="minimal">Kudos</th>
                <th class="minimal">Joined</th>
            </tr>
        </thead>
        <tbody>
<?php
if($users_result && $users_result->num_rows > 0) {
    while($u = $users_result->fetch_assoc()) {
        echo '<tr>';
        echo '<td><a href="/user/' . intval($u['id']) . '">' . htmlspecialchars($u['username']) . '</a>';
        if(intval($u['isbanned']) === 1) {
            echo ' <span class="unimportant">[banned]</span>';
        }
        echo '</td>';
        echo '<td class="minimal">' . intval($u['userposts']) . '</td>';
        echo '<td class="minimal">' . intval($u['userkudos']) . '</td>';
        echo '<td class="minimal">' . date('Y-m-d', intval($u['joindate'])) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4" class="unimportant">No users found.</td></tr>';
}
?>
        </tbody>
    </table>

<?php } elseif($route_mode === 'search') { ?>
    <form method="get" action="/search" id="search-form">
        <input type="hidden" name="url" value="search" />
        <label for="q">Search topics and users:</label>
        <input type="text" id="q" name="q" value="<?php echo htmlspecialchars($search_term); ?>" />
        <input type="submit" value="Search" />
    </form>

<?php if($search_term !== '') { ?>
    <h3>Topic Results</h3>
    <table>
        <thead>
            <tr>
                <th>Headline</th>
                <th class="minimal">Category</th>
                <th class="minimal">Last bump</th>
            </tr>
        </thead>
        <tbody>
<?php
if($search_topics_result && $search_topics_result->num_rows > 0) {
    while($topic = $search_topics_result->fetch_assoc()) {
        $cat_name = isset($categories[$topic['category_id']]) ? $categories[$topic['category_id']] : 'Uncategorized';
        echo '<tr>';
        echo '<td><a href="/topic/' . intval($topic['id']) . '">' . htmlspecialchars($topic['title']) . '</a></td>';
        echo '<td class="minimal">' . htmlspecialchars($cat_name) . '</td>';
        echo '<td class="minimal">' . htmlspecialchars(fun_timeAgo(intval($topic['last_bump']))) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="3" class="unimportant">No matching topics.</td></tr>';
}
?>
        </tbody>
    </table>

    <h3>User Results</h3>
    <ul>
<?php
if($search_users_result && $search_users_result->num_rows > 0) {
    while($u = $search_users_result->fetch_assoc()) {
        echo '<li><a href="/user/' . intval($u['id']) . '">' . htmlspecialchars($u['username']) . '</a> <span class="unimportant">(' . intval($u['userposts']) . ' posts)</span></li>';
    }
} else {
    echo '<li class="unimportant">No matching users.</li>';
}
?>
    </ul>
<?php } ?>

<?php } elseif($route_mode === 'stuff') { ?>
    <h3>Site Stats</h3>
    <ul>
        <li>Total Topics: <?php echo isset($site_stats['topic_count']) ? intval($site_stats['topic_count']) : 0; ?></li>
        <li>Total Replies: <?php echo isset($site_stats['reply_count']) ? intval($site_stats['reply_count']) : 0; ?></li>
        <li>Total Users: <?php echo isset($site_stats['user_count']) ? intval($site_stats['user_count']) : 0; ?></li>
    </ul>

    <h3>Tag Cloud</h3>
    <p>
<?php
if(count($tag_cloud) > 0) {
    foreach($tag_cloud as $tag_name => $tag_count) {
        echo '<a href="/tag/' . rawurlencode($tag_name) . '" class="help" title="' . intval($tag_count) . ' topic(s)">#' . htmlspecialchars($tag_name) . '</a> ';
    }
} else {
    echo '<span class="unimportant">No tags yet.</span>';
}
?>
    </p>

<?php } else { ?>
    <table>
        <thead>
            <tr>
                <th class="minimal"></th>
                <th class="headline">Headline</th>
                <th class="minimal author">Author</th>
                <th class="minimal replies">Replies</th>
                <th class="minimal visits">Visits</th>
                <th class="minimal">Last bump</th>
                <th class="minimal">Category</th>
            </tr>
        </thead>
        <tbody>
<?php
if($topics_result && $topics_result->num_rows > 0) {
    while($topic = $topics_result->fetch_assoc()) {
        ezbbs_render_topic_row($topic, $site, $user, $categories);
    }
} else {
    echo '<tr><td colspan="7" class="unimportant">No topics found for this view.</td></tr>';
}
?>
        </tbody>
    </table>

<?php if($route_mode === 'replies' && $recent_replies_result) { ?>
    <h3>Recent Replies</h3>
    <ul>
<?php
while($reply = $recent_replies_result->fetch_assoc()) {
    $snippet = substr(trim($reply['content']), 0, 120);
    echo '<li><a href="/thread/' . intval($reply['thread_id']) . '#reply_' . intval($reply['id']) . '">';
    echo htmlspecialchars($reply['thread_title']);
    echo '</a> by ' . do_getFullyFormattedUsername($reply['poster_id']) . ' <span class="unimportant">(' . htmlspecialchars(fun_timeAgo(intval($reply['created_at']))) . ')</span>';
    if($snippet !== '') {
        echo '<br/><span class="unimportant">' . htmlspecialchars($snippet) . '</span>';
    }
    echo '</li>';
}
?>
    </ul>
<?php } ?>
<?php } ?>

    <ul class="menu"><li><span class="reply_id unimportant"><a href="#top">[Top]</a></span></li></ul>
</div>

<div id="footer">
    <br/><div style="text-align:center" class="unimportant">
    <span><?php echo $site['disclaimer']; ?></span><br/><span>ezbbs, bby. <a href="https://github.com/goloins/ezbbs">Contribute on GitHub or get your own copy!</a></span></div><br/>
    <noscript><br /><span class="unimportant">Note: Your browser's JavaScript is disabled; some site features may not fully function, but don't worry, we're trying to get rid of all the js :^)</span></noscript>
    <div id="quotePreview"></div>
</div>

</body>
</html>
