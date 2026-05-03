<?php
/* ezbbs - a simple bbs engine for the small web 
 *
 * Copyleft 2026 by the ezbbs contributors
 * 
 * init.php - pretty much everything to do with everything goes here.
 * 
 * one day, I'd like to split this up into multiple files, but that
 * day is not today. or tomorrow. or the next day. or the next. maybe the next.
 * 
 */

//Sitewide defaults, these are used everywhere and are static. Change them if you want to customize your site.
$site = array(
    'site_name' => 'EzBBS',
    'site_description' => 'A simple bbs engine for the small web',
    'favicon_url' => '/assets/favicon.ico',
    'site_url' => 'https://localhost/',
    'rss_url' => 'https://localhost/rss.xml', //note: site_url/catname/rss.xml works for cat specific feeds.
    'default_lemon_years' => 2, //number of years before lemon threads can be replied to
    'chanlike_reply_limit' => 100, //number of replies before chanlike threads are removed from the homepage and archived
    'topic_preview_length' => 20, //number of characters to show in the topic preview on the homepage and category pages.
    'topic_headline_length' => 50,
    'topic_poster_banned_prefix' => '<span class="help" title="USER WAS BANNED FOR THIS POST">[B]</span>',
    'admin_suffix' => ' [<a href=/admin>A</a>]', //suffix for topics posted by admins
    'minimum_posts_for_outbound_links' => 5,
    'edit_window_seconds' => 1800,
    'edit_text' => '$username <b>fucked around with</b> this post <i>$seconds</i>',
    'append_text' => '$username <b>remembered to add</b> some shit they forgot <i>$seconds</i>',
    'append_separator_text' => 'OP Updated',
    'flair_consensus_min_votes' => 3,
    'disclaimer' => 'All trademarks and copyrights on this site are owned by their respective parties. All uploaded files and comments are the responsibility of their own posters.', //site disclaimer
    );

//Site SQL defaults, change these to match your sql credentials.    
$sql = array(
    'host' => 'localhost',
    'username' => 'ezbbs',
    'password' => 'password',
    'database' => 'ezbbs'
);

//sql area, woo woo - no touching! 
$go_sql = new mysqli($sql['host'], $sql['username'], $sql['password'], $sql['database']); //change these to your sql credentials
if ($go_sql->connect_error) {
    do_logentry("Error", "Database connection failed: " . $go_sql->connect_error);
    die('womp womp, ezbbs died: ' . $go_sql->connect_error);
}


//User defaults, these are used for non-logged in users, and for new users. Change them if you want to customize the default user experience.
//buyer beware, adding more will require you to add more columns to the users table in the database, and also in any user-facing sql queries.
$user = array(
    'user_id' => 'guest',
    'isloggedin' => false,
    'theme' => 'default',
    'isadmin' => false,
    'defaultlocation' => "Somewhere on the internet",
    'defaultbio' => "This user prefers to keep an air of mystery about them.",
    'defaultavatar' => 'assets/png/default_avatar.png',
    'awards' => json_encode(array()), // Array of award ids in the awards table that the user has received
    'isbanned' => false, // default on new users, but once banned will show bars on the profile pic.
    'ban_length' => 0, // in seconds, 0 for permanent only if isbanned is true
    'ban_reason' => '', // Reason for the ban
    'ismoderator' => json_encode(array()), // Array of category ids that the user is a moderator of
    'defaultsignature' => "", 
    'sigbanners' => json_encode(array()), // Array of ids in the sigbanners table that the user has chosen to display in their signature
    'userportrait' => 'assets/png/blank_portrait.png',
    'usernamecolor' => '#000000', // Default username color
    'usernamestyle' => 'normal', // Default username style (normal, italic, bold)
    'joindate' => time(), // checked in a year for party baloons on their post (annoying but makes me chuckle)
    'crackedportrait' => false, // Whether the user has a crack on their portrait. (mod enabled)
    'duncecorner' => false, // Whether the user is in the dunce corner, adds dunce hat to portrait. (mod enabled)
    'userkudos' => 0,   //kudos motherfucker, do you have them?
    'userkudostogive' => 1, // Every user starts with 1 kudos to give to others, even logged out users.
    'userposts' => 0, //obv 0 on new users
    'userwebsite' => '', //well let you plug your own site in here
    'usergemsite' => '', //if you have a gemlog or something you want to plug in here
    'userspacehey' => '', //if you've got a spacehey account, going for the 2000s vibe
    'userirchandleandnet' => '', //for example john@irc.mycool.net
    'usersmsnescargot' => '', //for the few proud escargot users to share their msn doohickey
    'profileprimarycolor' => '#FFFFFF', // Default profile primary color
    'profilesecondarycolor' => '#CCCCCC', // Default profile secondary color
    'profileheadingtextcolor' => '#000000', // Default profile heading text color
    'profilelowerheadingcolor' => '#999999', // Default profile lower heading color
    'profilehyperlinkcolor' => '#0000FF' // Default profile hyperlink color
);


function chk_PosterIsBanned($poster_id) {
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT isbanned FROM users WHERE id = ?");
    $stmt->bind_param("i", $poster_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['isbanned'];
    } else {
        return false; // or some default value
    }
}


$social_icons = array(
    'userwebsite' => 'assets/png/social/website.png',
    'usergemsite' => 'assets/png/social/gemlog.png',
    'userspacehey' => 'assets/png/social/spacehey.png',
    'userirchandleandnet' => 'assets/png/social/irc.png',
    'usersmsnescargot' => 'assets/png/social/msn.png'
);


function get_UserNameForID($user_id) {
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['username'];
    } else {
        return 'Unknown User'; // or some default value
    }
}

function do_getUserByUsername($username){
    global $go_sql;
    $username = trim($username);
    if($username === '') {
        return null;
    }

    $stmt = $go_sql->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}

function do_getUserByEmail($email){
    global $go_sql;
    $email = trim($email);
    if($email === '') {
        return null;
    }

    $stmt = $go_sql->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}


function do_insertNewUser($supplied_username, $supplied_password, $supplied_email) {
    global $go_sql, $user;
    // Check if the username already exists
    $stmt = $go_sql->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $supplied_username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Username already exists
        return false;
    }
    
    // Hash the password before storing it
    $hashed_password = password_hash($supplied_password, PASSWORD_DEFAULT);
    
    // Keep account creation minimal and rely on DB defaults for the rest.
    $stmt = $go_sql->prepare("INSERT INTO users (username, password, email, theme, joindate) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $supplied_username, $hashed_password, $supplied_email, $user['theme'], $user['joindate']);
    return $stmt->execute();

}
// Category defaults - these are used for the categories on the site, more can be added in the database. It will start with these. todo!
$categories = array(1 => "General", 2 => "Sports", 3 => "Technology", 4 => "Gaming", 5 => "Music", 6 => "Miscellaneous", 7=> "Meta");

//<ul id="main_menu" class="menu"><li class="hot_topics"><a href="/hot_topics">Hot</a></li><li class="topics"><a href="/topics">Topics</a></li><li class="bumps"><a href="/bumps">Bumps</a></li><li class="replies"><a href="/replies">Replies</a></li><li class="new_topic"><a href="/new_topic">New topic</a></li><li class="history"><a href="/history">History</a></li><li class="watchlist"><a href="/watchlist">Watchlist</a></li><li class="bulletins"><a href="/bulletins">Bulletins</a></li><li class="folks"><a href="/folks">Folks</a></li><li class="search"><a href="/search">Search</a></li><li class="stuff"><a href="/stuff">Stuff</a></li>    </ul>
$homepagemenu = array(
    array('name' => 'Hot', 'url' => '/hot_topics'),
    array('name' => 'Topics', 'url' => '/topics'),
    array('name' => 'Bumps', 'url' => '/bumps'),
    array('name' => 'Replies', 'url' => '/replies'),
    array('name' => 'New Topic', 'url' => '/new_topic')
);

foreach($categories as $category_id => $category_name){
    $homepagemenu[] = array('name' => $category_name, 'url' => '/cat/' . $category_id);
}

$homepagemenu[] = array('name' => 'Folks', 'url' => '/folks');
$homepagemenu[] = array('name' => 'Inbox', 'url' => '/inbox');
$homepagemenu[] = array('name' => 'Search', 'url' => '/search');
$homepagemenu[] = array('name' => 'Stuff', 'url' => '/stuff');

global $homepagemenu;
global $categories;

function do_getUnreadPrivateMessageCount($user_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT COUNT(*) AS unread_count FROM private_messages WHERE to_user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return intval($row['unread_count']);
    }
    return 0;
}

function do_getHomePageMenu(){
    global $homepagemenu;
    $menu = $homepagemenu;

    if(do_isLoggedIn()) {
        $unread = do_getUnreadPrivateMessageCount(intval($_SESSION['user_id']));
        if($unread > 0) {
            foreach($menu as &$item) {
                if(isset($item['url']) && $item['url'] === '/inbox') {
                    $item['name'] = 'Inbox (' . $unread . ')';
                    break;
                }
            }
            unset($item);
        }

        $menu[] = array('name' => 'Logout', 'url' => '/do/logout');
    } else {
        $menu = array_values(array_filter($menu, function($item) {
            if(!isset($item['url'])) {
                return true;
            }
            return $item['url'] !== '/new_topic' && $item['url'] !== '/inbox';
        }));
        $menu[] = array('name' => 'Login', 'url' => '/login');
        $menu[] = array('name' => 'Sign Up', 'url' => '/signup');
    }

    return $menu;
}

function do_renderLoginStatusChip(){
    if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
        return '';
    }

    $username = '';
    if(isset($_SESSION['user']) && is_array($_SESSION['user']) && isset($_SESSION['user']['username'])) {
        $username = $_SESSION['user']['username'];
    }
    if($username === '') {
        $username = get_UserNameForID(intval($_SESSION['user_id']));
    }

    if($username === '' || $username === 'Unknown User') {
        return '';
    }

    return '<div class="login_status_chip">Welcome, <a href="/user/' . intval($_SESSION['user_id']) . '">' . htmlspecialchars($username) . '</a></div>';
}

function do_determineCurrentPageorCat(){
    if(isset($_GET['url'])){
        $url = $_GET['url'];
        if(strpos($url, 'cat/') === 0){
            $cat_id = intval(substr($url, 4));
            global $categories;
            return isset($categories[$cat_id]) ? $categories[$cat_id] : 'Unknown Category';
        } else {
            return determine_current_page($url);
        }
    } else {
        return 'Latest Threads';
    }
}

// Helper to determine page title from URL slug
function determine_current_page($url_slug){
    $page_titles = array(
        'hot_topics' => 'Hot Topics',
        'topics' => 'All Topics',
        'bumps' => 'Recent Bumps',
        'replies' => 'Latest Replies',
        'new_topic' => 'New Topic',
        'folks' => 'Users',
        'search' => 'Search',
        'stuff' => 'Stuff'
    );
    return isset($page_titles[$url_slug]) ? $page_titles[$url_slug] : ucfirst(str_replace('_', ' ', $url_slug));
}
// Default Awards - any additional awards can be added to the awards table in the database, but these are the defaults that come with the site todo!
$awards = array(
    1 => array(
        'name' => 'First Post',
        'description' => 'Awarded for making your first post.',
        'image_url' => 'assets/png/awards/first_post.png'
    ),
    2 => array(
        'name' => 'Kudos',
        'description' => 'Awarded for receiving 15 kudos from other users.',
        'image_url' => 'assets/png/awards/kudos.png'
    ),
    3 => array(
        'name' => 'Veteran',
        'description' => 'Awarded for being a member for 1 year.',
        'image_url' => 'assets/png/awards/veteran.png'
    ),
    4 => array(
        'name' => 'Out on Good Behavior',
        'description' => 'Awarded for being unbanned after a tempban',
        'image_url' => 'assets/png/awards/out_on_good_behavior.png'
    ),
    5 => array(
        'name' => 'Crass Clown',
        'description' => 'Has been dunced by a moderator at least twice.',
        'image_url' => 'assets/png/awards/crass_clown.png'
    ),
    6 => array(
        'name' => 'Moneybags',
        'description' => 'Donated to the website, all you get is the monopoly guy.',
        'image_url' => 'assets/png/awards/moneybags.png'
    ),
);

//topic and poster related functions

// this fetches specific information about the poster

function do_getUserById($user_id) {
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        global $user;
        $newser = $user;
        $newser['username'] = 'Unknown User';
        return $newser; // or some default value
    }
}

function do_fetchUserAttribute($user_id, $attribute) {
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT $attribute FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row[$attribute];
    }else{
        global $user;
        return $user[$attribute]; // or some default value
    }
}

function chk_IsUserModeratorOrAdmin($user_id) {
    $u = do_getUserById($user_id);
    if(!$u) {
        return false;
    }
    if(!empty($u['isadmin'])) {
        return true;
    }
    if(isset($u['ismoderator'])) {
        $mod_scope = json_decode($u['ismoderator'], true);
        return is_array($mod_scope) && count($mod_scope) > 0;
    }
    return false;
}

function chk_IsUserAdmin($user_id){
    $u = do_getUserById($user_id);
    return $u && !empty($u['isadmin']);
}

function do_canUserModifyPost($post_owner_id, $viewer_user_id){
    $viewer_user_id = intval($viewer_user_id);
    if($viewer_user_id <= 0) {
        return false;
    }
    return intval($post_owner_id) === $viewer_user_id;
}

function do_isPostWithinEditWindow($created_at){
    global $site;
    $window = intval($site['edit_window_seconds']);
    if($window <= 0) {
        return false;
    }
    return (time() - intval($created_at)) <= $window;
}

function do_renderRevisionTemplateText($template, $owner_user_id, $edited_at){
    $owner_username = get_UserNameForID(intval($owner_user_id));
    if($owner_username === '' || $owner_username === 'Unknown User') {
        $owner_username = 'OP';
    }

    $display_username = $owner_username;
    $display_time_ago = fun_timeAgo(intval($edited_at));
    $display_time_full = date('Y-m-d H:i:s', intval($edited_at));

    $msg = strtr($template, array(
        '$username' => $display_username,
        '$seconds' => $display_time_ago,
        '$datetime' => $display_time_full
    ));

    // Backward compatibility for old %s-based templates.
    if(strpos($msg, '%s') !== false) {
        $msg = preg_replace('/%s/', $display_username, $msg, 1);
        $msg = preg_replace('/%s/', $display_time_ago, $msg, 1);
        $msg = preg_replace('/%s/', $display_time_full, $msg, 1);
    }

    // Appended content should store a plain-text prefix so it renders consistently
    // in both topic and reply renderers regardless of HTML template formatting.
    $msg = html_entity_decode(strip_tags($msg), ENT_QUOTES, 'UTF-8');
    $msg = preg_replace('/\s+/', ' ', $msg);
    return trim($msg);
}

function do_getAppendSeparatorMarker(){
    return '[__APPEND_SEPARATOR__]';
}

function do_getAppendSeparatorHtml(){
    global $site;
    $separator_text = 'OP Updated';
    if(isset($site['append_separator_text']) && trim((string)$site['append_separator_text']) !== '') {
        $separator_text = trim((string)$site['append_separator_text']);
    }
    $separator_text = htmlspecialchars($separator_text, ENT_QUOTES, 'UTF-8');
    return '--- <span class="edited-indicator">*</span> ' . $separator_text . ' <span class="edited-indicator">*</span> ---';
}

function do_getPostRevisionNoteHtml($is_edited, $edited_at, $owner_user_id = 0){
    global $site;
    $mode = intval($is_edited);
    if($mode !== 1 && $mode !== 2) {
        return '';
    }

    $edited_at = intval($edited_at);
    if($edited_at <= 0) {
        $edited_at = time();
    }

    $owner_username = get_UserNameForID(intval($owner_user_id));
    if($owner_username === '' || $owner_username === 'Unknown User') {
        $owner_username = 'OP';
    }

    $display_username = htmlspecialchars($owner_username, ENT_QUOTES, 'UTF-8');
    $display_time_ago = htmlspecialchars(fun_timeAgo($edited_at), ENT_QUOTES, 'UTF-8');
    $display_time_full = htmlspecialchars(date('Y-m-d H:i:s', $edited_at), ENT_QUOTES, 'UTF-8');
    $display_time_ago_with_hover = '<span class="help" title="' . $display_time_full . '">' . $display_time_ago . '</span>';

    $template = ($mode === 2) ? $site['append_text'] : $site['edit_text'];
    $msg = strtr($template, array(
        '$username' => $display_username,
        '$seconds' => $display_time_ago_with_hover,
        '$datetime' => $display_time_full
    ));

    // Backward compatibility for old %s-based templates.
    if(strpos($msg, '%s') !== false) {
        $msg = preg_replace('/%s/', $display_username, $msg, 1);
        $msg = preg_replace('/%s/', $display_time_ago_with_hover, $msg, 1);
        $msg = preg_replace('/%s/', $display_time_full, $msg, 1);
    }

    return '<div class="unimportant post-revision-note"><span class="edited-indicator" title="edited/updated post" aria-hidden="true">*</span>' . $msg . '</div>';
}


function do_getFullyFormattedUsername($user_id) {
    global $go_sql, $site;
    $stmt = $go_sql->prepare("SELECT username, usernamecolor, usernamestyle FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $formatted_username = htmlspecialchars($row['username']);
        if ($row['usernamecolor']) {
            $formatted_username = '<span style="color: ' . htmlspecialchars($row['usernamecolor']) . ';">' . $formatted_username . '</span>';
        }
        if ($row['usernamestyle'] == 'italic') {
            $formatted_username = '<em>' . $formatted_username . '</em>';
        } elseif ($row['usernamestyle'] == 'bold') {
            $formatted_username = '<strong>' . $formatted_username . '</strong>';
        }
        return $formatted_username;
    } else {
        return '<strong>Unknown User</strong>'; // or some default value
    }
}

function do_getTopics($page){
    global $go_sql;
    $topics_per_page = 20;
    $page = intval($page);
    if($page < 1) {
        $page = 1;
    }
    $offset = ($page - 1) * $topics_per_page;
    $stmt = $go_sql->prepare("SELECT * FROM topics ORDER BY last_bump DESC LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $topics_per_page);
    $stmt->execute();
    return $stmt->get_result();
}

// thread checkers, simple boolean functions that check various thread states.

function chk_ThreadPinned($topic_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT isPinned FROM topics WHERE id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['isPinned'];
    } else {
        return false; // or some default value
    }
}

function chk_ThreadHidden($topic_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT isHidden FROM topics WHERE id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['isHidden'];
    } else {
        return false; // or some default value
    }
}

function chk_ThreadLemoned($topic_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT isLemoned FROM topics WHERE id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['isLemoned'];
    } else {
        return false; // or some default value
    }
}

function chk_ThreadParty($topic_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT isParty FROM topics WHERE id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['isParty'];
    } else {
        return false; // or some default value
    }
}

function chk_ThreadArchived($topic_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT isArchived FROM topics WHERE id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['isArchived'];
    } else {
        return false; // or some default value
    }
}

function chk_ThreadLocked($topic_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT isLocked FROM topics WHERE id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['isLocked'];
    } else {
        return false; // or some default value
    }
}

function chk_ThreadChanlike($topic_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT isChanlike FROM topics WHERE id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['isChanlike'];
    } else {
        return false; // or some default value
    }
}


function chk_TopicHasMediaOrLink($topic_id){ //text only post detection.
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT media, attached_links FROM topics WHERE id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return !empty($row['media']) || !empty($row['attached_links']);
    } else {
        return false; // or some default value
    }
}

//sample here to help inteli.
$sampletopic = array(
    'id' => 1,
    'title' => 'Welcome to EzBBS!',
    'content' => 'This is the first topic on EzBBS. Feel free to explore and join the discussion!',
    'poster_id' => 1,
    'category_id' => 1,
    'created_at' => time(),
    'last_bump' => time(),
    'replies_count' => 0,
    'visits_count' => 0,
    'media' => json_encode(array()),
    'attached_links' => json_encode(array()),
    'isPinned' => false,
    'isHidden' => false,
    'isParty' => false, //special threads.
    'isArchived' => false,
    'isLocked' => false,
    'isLemoned' => false, //a lemon party, for old users as defined in $site
    'isChanlike' => false, //chanlike threads dissappear from the homepage after a certain number of replies and will be archived.
    'hasFlairs' => json_encode(array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0)), //will all be set to zero on new threads. nb: if adding flairs, update here.
    'isShitpost' => false, // todo: shitpost detection. maybe it'll just be a toggle for mods, maybe we'll factor in a trolliness score on the user
    'hasPoll' => 0, // if the thread has a poll, this will be the poll id
    'tags' => json_encode(array()), // array of user-submitted tags for the thread. think tumblr. 



);



function do_getAllThreadsInCategory($category_id, $page){
    global $go_sql;
    $topics_per_page = 20;
    $offset = ($page - 1) * $topics_per_page;
    $stmt = $go_sql->prepare("SELECT * FROM topics WHERE category_id = ? ORDER BY last_bump DESC LIMIT ?, ?");
    $stmt->bind_param("iii", $category_id, $offset, $topics_per_page);
    $stmt->execute();
    return $stmt->get_result();
}

 //call this on init to generate the tag cloud data from the existing threads. 

function do_generateTagCloudFromTopics($numberoftags, $minoccurrence){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT tags FROM topics");
    $stmt->execute();
    $result = $stmt->get_result();
    $tag_counts = array();
    while($row = $result->fetch_assoc()){
        $tags = json_decode($row['tags'], true);
        if(is_array($tags)){
            foreach($tags as $tag){
                if(isset($tag_counts[$tag])){
                    $tag_counts[$tag]++;
                } else {
                    $tag_counts[$tag] = 1;
                }
            }
        }
    }
    // Filter out tags that don't meet the minimum occurrence threshold
    $filtered_tags = array_filter($tag_counts, function($count) use ($minoccurrence) {
        return $count >= $minoccurrence;
    });
    // Sort tags by occurrence count in descending order
    arsort($filtered_tags);
    // Return the top N tags
    return array_slice($filtered_tags, 0, $numberoftags, true);
}
    

function do_getAllTagsForThread($thread_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT tags FROM topics WHERE id = ?");
    $stmt->bind_param("i", $thread_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return json_decode($row['tags'], true);
    } else {
        return array(); // or some default value
    }
}

//this one is going to be fun, it'll be used with the tag cloud and tag search.
//it'll need to break apart the tags json array and search for the tag in there, then return all threads with that tag. fun!
//the smart move will be just to pull the top 20. maybe we'll have a "more" or "random" option.
function do_getAllThreadsInTag($tag){
    global $go_sql;
    // JSON_CONTAINS searches for a value within a JSON document
    // tags is a JSON array of strings like ["tag1", "tag2"], so we search for the tag as a JSON string
    $tag_json = json_encode($tag); // convert tag to JSON format for the search
    $stmt = $go_sql->prepare("SELECT * FROM topics WHERE JSON_CONTAINS(tags, ?) ORDER BY last_bump DESC LIMIT 20");
    $stmt->bind_param("s", $tag_json);
    $stmt->execute();
    return $stmt->get_result();
}

function getThreadById($thread_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT * FROM topics WHERE id = ?");
    $stmt->bind_param("i", $thread_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null; // or some default value
    }
}

// canonical helper name used by page files.
function do_getThreadById($thread_id){
    return getThreadById($thread_id);
}

function do_getReplyById($reply_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT * FROM replies WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $reply_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function do_updateTopicPostContent($thread_id, $editor_id, $submitted_content, $mode = 'edit'){
    global $go_sql, $site;
    $thread_id = intval($thread_id);
    $editor_id = intval($editor_id);
    $mode = ($mode === 'append') ? 'append' : 'edit';

    $topic = do_getThreadById($thread_id);
    if(!$topic) {
        return false;
    }
    if(!do_canUserModifyPost(intval($topic['poster_id']), $editor_id)) {
        return false;
    }

    $submitted_content = trim($submitted_content);
    if($submitted_content === '') {
        return false;
    }

    $current_content = isset($topic['content']) ? $topic['content'] : '';
    $edited_at = time();
    if($mode === 'edit') {
        if(!do_isPostWithinEditWindow(intval($topic['created_at']))) {
            return false;
        }
        $new_content = $submitted_content;
        $edit_state = 1;
    } else {
        $append_prefix = do_renderRevisionTemplateText($site['append_text'], $editor_id, $edited_at);
        if($append_prefix === '') {
            $append_prefix = 'appended';
        }
        $new_content = rtrim($current_content) . "\n\n" . do_getAppendSeparatorMarker() . "\n" . $append_prefix . ": " . $submitted_content;
        $edit_state = 2;
    }

    $stmt = $go_sql->prepare("UPDATE topics SET content = ?, is_edited = ?, edited_at = ? WHERE id = ?");
    $stmt->bind_param("siii", $new_content, $edit_state, $edited_at, $thread_id);
    return $stmt->execute();
}

function do_updateReplyPostContent($reply_id, $editor_id, $submitted_content, $mode = 'edit'){
    global $go_sql, $site;
    $reply_id = intval($reply_id);
    $editor_id = intval($editor_id);
    $mode = ($mode === 'append') ? 'append' : 'edit';

    $reply = do_getReplyById($reply_id);
    if(!$reply) {
        return false;
    }
    if(!do_canUserModifyPost(intval($reply['poster_id']), $editor_id)) {
        return false;
    }

    $submitted_content = trim($submitted_content);
    if($submitted_content === '') {
        return false;
    }

    $current_content = isset($reply['content']) ? $reply['content'] : '';
    $edited_at = time();
    if($mode === 'edit') {
        if(!do_isPostWithinEditWindow(intval($reply['created_at']))) {
            return false;
        }
        $new_content = $submitted_content;
        $edit_state = 1;
    } else {
        $append_prefix = do_renderRevisionTemplateText($site['append_text'], $editor_id, $edited_at);
        if($append_prefix === '') {
            $append_prefix = 'appended';
        }
        $new_content = rtrim($current_content) . "\n\n" . do_getAppendSeparatorMarker() . "\n" . $append_prefix . ": " . $submitted_content;
        $edit_state = 2;
    }

    $stmt = $go_sql->prepare("UPDATE replies SET content = ?, is_edited = ?, edited_at = ? WHERE id = ?");
    $stmt->bind_param("siii", $new_content, $edit_state, $edited_at, $reply_id);
    return $stmt->execute();
}

function do_getThreadOwnerId($thread_id){
    $thread = do_getThreadById($thread_id);
    if(!$thread) {
        return null;
    }
    return intval($thread['poster_id']);
}

function do_watchThread($user_id, $thread_id){
    global $go_sql;
    $created_at = time();
    $stmt = $go_sql->prepare("INSERT INTO watchlist (user_id, thread_id, created_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE created_at = VALUES(created_at)");
    $stmt->bind_param("iii", $user_id, $thread_id, $created_at);
    return $stmt->execute();
}

function do_forgetThread($user_id, $thread_id){
    global $go_sql;
    $stmt = $go_sql->prepare("DELETE FROM watchlist WHERE user_id = ? AND thread_id = ?");
    $stmt->bind_param("ii", $user_id, $thread_id);
    return $stmt->execute();
}

function chk_IsThreadWatchedByUser($user_id, $thread_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT id FROM watchlist WHERE user_id = ? AND thread_id = ? LIMIT 1");
    $stmt->bind_param("ii", $user_id, $thread_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function do_sendPrivateMessage($from_user_id, $to_user_id, $subject, $content, $thread_id = 0){
    global $go_sql;
    $subject = trim($subject);
    $content = trim($content);
    if($subject === '' || $content === '') {
        return false;
    }
    $created_at = time();
    $stmt = $go_sql->prepare("INSERT INTO private_messages (from_user_id, to_user_id, thread_id, subject, content, created_at, is_read) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("iiissi", $from_user_id, $to_user_id, $thread_id, $subject, $content, $created_at);
    return $stmt->execute();
}

function do_getPrivateMessageConversations($user_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT CASE WHEN from_user_id = ? THEN to_user_id ELSE from_user_id END AS peer_id, MAX(id) AS last_message_id, MAX(created_at) AS last_message_at, SUM(CASE WHEN to_user_id = ? AND is_read = 0 THEN 1 ELSE 0 END) AS unread_count FROM private_messages WHERE from_user_id = ? OR to_user_id = ? GROUP BY peer_id ORDER BY last_message_at DESC");
    $stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $conversations = array();
    while($row = $result->fetch_assoc()) {
        $peer_id = intval($row['peer_id']);
        $latest_message = null;
        $latest_stmt = $go_sql->prepare("SELECT from_user_id, to_user_id, subject, content, created_at FROM private_messages WHERE id = ? LIMIT 1");
        $last_message_id = intval($row['last_message_id']);
        $latest_stmt->bind_param("i", $last_message_id);
        $latest_stmt->execute();
        $latest_result = $latest_stmt->get_result();
        if($latest_result->num_rows > 0) {
            $latest_message = $latest_result->fetch_assoc();
        }

        $peer = do_getUserById($peer_id);
        $conversations[] = array(
            'peer_id' => $peer_id,
            'peer_username' => $peer ? $peer['username'] : 'Unknown User',
            'last_message_at' => intval($row['last_message_at']),
            'unread_count' => intval($row['unread_count']),
            'last_subject' => $latest_message ? $latest_message['subject'] : '',
            'last_content' => $latest_message ? $latest_message['content'] : ''
        );
    }
    return $conversations;
}

function do_getPrivateMessageThread($user_id, $peer_id, $limit = 100){
    global $go_sql;
    $limit = intval($limit);
    if($limit < 1) {
        $limit = 100;
    }
    $stmt = $go_sql->prepare("SELECT id, from_user_id, to_user_id, thread_id, subject, content, created_at, is_read FROM private_messages WHERE (from_user_id = ? AND to_user_id = ?) OR (from_user_id = ? AND to_user_id = ?) ORDER BY created_at ASC, id ASC LIMIT ?");
    $stmt->bind_param("iiiii", $user_id, $peer_id, $peer_id, $user_id, $limit);
    $stmt->execute();
    return $stmt->get_result();
}

function do_getPrivateMessageThreadCount($user_id, $peer_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT COUNT(*) AS message_count FROM private_messages WHERE (from_user_id = ? AND to_user_id = ?) OR (from_user_id = ? AND to_user_id = ?)");
    $stmt->bind_param("iiii", $user_id, $peer_id, $peer_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return intval($row['message_count']);
    }
    return 0;
}

function do_getPrivateMessageThreadPage($user_id, $peer_id, $page = 1, $per_page = 30){
    global $go_sql;
    $page = intval($page);
    $per_page = intval($per_page);
    if($page < 1) {
        $page = 1;
    }
    if($per_page < 1) {
        $per_page = 30;
    }

    $offset = ($page - 1) * $per_page;
    $stmt = $go_sql->prepare("SELECT id, from_user_id, to_user_id, thread_id, subject, content, created_at, is_read FROM private_messages WHERE (from_user_id = ? AND to_user_id = ?) OR (from_user_id = ? AND to_user_id = ?) ORDER BY created_at DESC, id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("iiiiii", $user_id, $peer_id, $peer_id, $user_id, $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = array();
    while($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    return array_reverse($messages);
}

function do_markPrivateMessagesRead($user_id, $peer_id){
    global $go_sql;
    $stmt = $go_sql->prepare("UPDATE private_messages SET is_read = 1 WHERE to_user_id = ? AND from_user_id = ? AND is_read = 0");
    $stmt->bind_param("ii", $user_id, $peer_id);
    return $stmt->execute();
}

function do_getRepliesForThread($thread_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT * FROM replies WHERE thread_id = ? AND isHidden = 0 ORDER BY created_at ASC, id ASC");
    $stmt->bind_param("i", $thread_id);
    $stmt->execute();
    return $stmt->get_result();
}


$samplereply = array(
    'id' => 1,
    'thread_id' => 1,
    'content' => 'This is a sample reply to the first topic. Welcome to the discussion!',
    'poster_id' => 2,
    'created_at' => time(),
    'media' => json_encode(array()),
    'attached_links' => json_encode(array()),
    'isShitpost' => false,
    'hasPoll' => 0, // if the reply has a poll, this will be the poll id
    'isHidden' => false, //gotta figure out a way to render this, maybe a [H] tag and a [+]/[-] to see it.
    'isModerator' => false, //togglable by mods, will add a [M] and style. equivalent of the cops showing up for a noise complaint
    'contentSpoilered' => false, //if the content is spoilered, we'll render a different media until clicked on. prevents spoilers
    'causedBan' => false //if the reply caused a ban. only triggered from mod tools used on that post. "UWBFTP", etc.


);

// checking if a thread is lemoned, chanlike, or archived by thread id to determind if poll can be voted in.
function chk_IsPollLemonedChannedOrArchivedByThreadId($thread_id){
    return chk_ThreadLemoned($thread_id) || chk_ThreadChanlike($thread_id) || chk_ThreadArchived($thread_id);
}


//meat and potatoes
function post_Reply($thread_id, $poster_id, $content, $media = array(), $attached_links = array(), $poll = 0){
    global $go_sql;
    //check for presence of poll in content at post time, if there is a poll, we'll need to create the poll first and then link it to the reply.
    $poll_id = 0;
    if($poll > 0){
        //create the poll and get the poll id
        $stmt = $go_sql->prepare("INSERT INTO polls (thread_id, reply_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $thread_id, $poll);
        if($stmt->execute()){
            $poll_id = $stmt->insert_id;
            //now we have the poll id, we can link it to the reply when we create the reply.
        } else {
            return false; //failed to create poll, so we can't create the reply.
        }
    }
    $media_json = json_encode($media);
    $links_json = json_encode($attached_links);
    $created_at = time();
    $stmt = $go_sql->prepare("INSERT INTO replies (thread_id, poster_id, content, created_at, media, attached_links, poll_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisissi", $thread_id, $poster_id, $content, $created_at, $media_json, $links_json, $poll_id);
    if($stmt->execute()){
        $new_reply_id = intval($go_sql->insert_id);
        // Update the replies count and last bump time in the topics table
        $stmt = $go_sql->prepare("UPDATE topics SET replies_count = replies_count + 1, last_bump = ? WHERE id = ?");
        $current_time = time();
        $stmt->bind_param("ii", $current_time, $thread_id);
        $stmt->execute();
        return $new_reply_id;
    } else {
        return false;
    }
}

function post_Topic($title, $content, $poster_id, $category_id, $media = array(), $attached_links = array(), $poll = 0, $tags = array()){
    global $go_sql;
    // For now, treat $poll as an existing poll id and store it directly on the topic.
    $poll_id = intval($poll);
    $media_json = json_encode($media);
    $links_json = json_encode($attached_links);
    $default_flairs = json_encode(array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0));
    $default_tags = json_encode(is_array($tags) ? array_values($tags) : array());
    $current_time = time();
    $stmt = $go_sql->prepare("INSERT INTO topics (title, content, poster_id, category_id, created_at, last_bump, media, attached_links, hasPoll, hasFlairs, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiiiississ", $title, $content, $poster_id, $category_id, $current_time, $current_time, $media_json, $links_json, $poll_id, $default_flairs, $default_tags);
    return $stmt->execute();
}

function chk_DoesUserHaveKudosToGive($user_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT userkudostogive FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['userkudostogive'] > 0;
    } else {
        return false; // or some default value
    }
}

// so to prevent some sort of spam, we're going to check for:
// both links in the text of the post (naked) and links written with markdown ()[] style.
// second we'll check if the user posting has the minimum posts to do such. 
// we're return true/false based on if the post is allowed or not, and then we'll handle the error message in the thread/reply submission code.
function chk_UserCanPostOutboundLinks($user_id, $post_content){
    global $go_sql, $site;

    if(chk_IsUserAdmin($user_id)) {
        return true;
    }

    // Check for markdown links
    if(preg_match('/\[[^\]]+\]\((https?:\/\/[^\s]+)\)/', $post_content) || preg_match('/https?:\/\/[^\s]+/', $post_content)){
        // User is trying to post a link, check if they have enough posts
        $stmt = $go_sql->prepare("SELECT userposts FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['userposts'] >= $site['minimum_posts_for_outbound_links'];
        } else {
            return false; // or some default value
        }
    } else {
        // No links detected, allow the post
        return true;
    }
}

function do_RenderMarkdownLinksInText($text){
    //this will render markdown links in the text of the post, so that they show up as clickable links instead of just plain text. 
    // This should be called when rendering the post content.
    $text = preg_replace('/\[(.*?)\]\((https?:\/\/[^\s]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>', $text);
    return $text;
}

function do_RenderMarkdownFormattingInText($text){
    //this will render markdown formatting in the text of the post, so that *bold* and _italic_ and ~strikethrough~ show up correctly. 
    // This should be called when rendering the post content, after rendering links.
    $text = preg_replace('/\*(.*?)\*/', '<strong>$1</strong>', $text); // bold
    $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text); // italic
    $text = preg_replace('/~(.*?)~/', '<del>$1</del>', $text); // strikethrough
    return $text;
}

function do_RenderMarkdownCodeInText($text){
    //this will render markdown code formatting in the text of the post, so that `code` shows up correctly. 
    // This should be called when rendering the post content, after rendering links and formatting.
    $text = preg_replace('/`(.*?)`/', '<code>$1</code>', $text); // code
    return $text;
}


function do_RenderThreadLinkInReplyText($text){
    //replying to users is done with @username, but threads are referred to with ">>"
    //so if the text contains ">>" followed by a number, we'll assume it's a thread link.
    //this should create a link to the thread when rendering the reply and have 
    //a tooltip of the thread title when hovered over.
    if(preg_match('/>>\d+/', $text)){
        return preg_replace_callback('/>>(\d+)/', function($matches) {
            $thread_id = $matches[1];
            global $go_sql;
            $stmt = $go_sql->prepare("SELECT title FROM topics WHERE id = ?");
            $stmt->bind_param("i", $thread_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $thread_title = htmlspecialchars($row['title']);
                return '<a href="/thread/' . $thread_id . '" class="thread-link" title="' . $thread_title . '">>>' . $thread_id . '</a>';
            } else {
                return '>>' . $thread_id; // If thread not found, just return the original text
            }
        }, $text);
    }

    return $text;
}

function do_AddTooltipWithReplySnippet($reply_content, $reply_id){
   //When a user replies @username, we want to add a snippet of username's last post
   //in the thread (the one presumably being replied to) as a tooltip on the @username text. This function will be called when rendering the reply text.
    if(preg_match('/@(\w+)/', $reply_content)){
        return preg_replace_callback('/@(\w+)/', function($matches) use ($reply_id) {
            $username = $matches[1];
            global $go_sql;
            // First, we need to get the user id of the username being mentioned
            $stmt = $go_sql->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $mentioned_user_id = $row['id'];
                // Now we need to get the last reply from that user in the same thread as the current reply
                $stmt = $go_sql->prepare("SELECT content FROM replies WHERE poster_id = ? AND thread_id = (SELECT thread_id FROM replies WHERE id = ?) ORDER BY created_at DESC LIMIT 1");
                $stmt->bind_param("ii", $mentioned_user_id, $reply_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $last_reply_snippet = htmlspecialchars(substr($row['content'], 0, 100)); // Get a snippet of the last reply
                    return '<span class="user-mention" title="' . $last_reply_snippet . '">@' . htmlspecialchars($username) . '</span>';
                } else {
                    return '@' . htmlspecialchars($username); 
                    
                }
            } else {
                return '@' . htmlspecialchars($username); // If user not found, just return the original text
            }
        }, $reply_content);
    }

    return $reply_content;
}

//time machine shit: this is where we'll make sure certain modernisms are turned off.
//i.e. we need to convert the most common unicode emoji to their emoticon equivalents. we're 2003 in here.
// for example "emoji heart (any color)" -> ":heart:" the emoji description should be used
// and we'll surround it with ::'s so that in the future if we add old-school forum emotes,
// we can just match them to an array of suitable images.

function fun_EmojiTimeMachine($text){
    // Implementation for converting unicode emoji to emoticons
    // I'll probably need the entire list of emoji mappings and descriptions 
    //to build this with.
    return $text;
}

function fun_secondsToHumanReadable($seconds) {
    if ($seconds >= 86400) {
        $days = floor($seconds / 86400);
        return $days === 1 ? "$days day" : "$days days";
    } elseif ($seconds >= 3600) {
        $hours = floor($seconds / 3600);
        return $hours === 1 ? "$hours hour" : "$hours hours";
    } elseif ($seconds >= 60) {
        $minutes = floor($seconds / 60);
        return $minutes === 1 ? "$minutes minute" : "$minutes minutes";
    } else {
        return "$seconds seconds";
    }
}




//main rendering functions
function do_RenderTopicContent($topic_content){
    //this will be the main function to call when rendering the topic text, it will call the other functions to render links, formatting, and add tooltips.
    $topic_content = do_RenderMarkdownLinksInText($topic_content);
    $topic_content = do_RenderMarkdownFormattingInText($topic_content);
    $topic_content = do_RenderMarkdownCodeInText($topic_content);
    $topic_content = str_replace(do_getAppendSeparatorMarker(), do_getAppendSeparatorHtml(), $topic_content);
    return nl2br($topic_content); // convert newlines to <br> for HTML rendering
}


function do_RenderReplyText($reply_content, $reply_id){
    //this will be the main function to call when rendering the reply text, it will call the other functions to render links, formatting, and add tooltips.
    $reply_content = htmlspecialchars($reply_content, ENT_QUOTES, 'UTF-8');
    $reply_content = do_RenderMarkdownLinksInText($reply_content);
    $reply_content = do_RenderMarkdownFormattingInText($reply_content);
    $reply_content = do_RenderMarkdownCodeInText($reply_content);
    $reply_content = do_RenderThreadLinkInReplyText($reply_content);
    $reply_content = do_AddTooltipWithReplySnippet($reply_content, $reply_id);
    $reply_content = str_replace(do_getAppendSeparatorMarker(), do_getAppendSeparatorHtml(), $reply_content);
    return nl2br($reply_content); // convert newlines to <br> for HTML rendering
}

//i wonder if I should do polls as yet another array or an object.
/*
poll scratch thinking area, ignore this

could just be an array of the options with their votes. how to track per-user voting? maybe 
a dedicated table? or could I just throw all the use ids into a json array. could make it truly anon
by just storing user ids and the iterating the option they voted for in the same array. for example:
poll = array(
    'question' => 'What is your favorite color?',
    'options' => array(
        1 => array(
            'option' => 'Red',
            'votes' => 10
        ),
        2 => array(
            'option' => 'Blue',
            'votes' => 15
        ),
        3 => array(
            'option' => 'Green',
            'votes' => 5
        )
    ),
    'voted' => json_encode(array(1, 2, 3)) // user_id, user_id, user_id. 
);

maybe we roll with this. 


table: `polls`
id (int, primary key)
option_1 (varchar)
option_2 (varchar)
option_3 (varchar)
option_4 (varchar)
option_5 (varchar)
votes_1 (int)
votes_2 (int)
votes_3 (int)
votes_4 (int)
votes_5 (int)
voted (text) - json_encoded array of user ids that have voted in this poll,
question (varchar)

*/

//this will be called during either thread or reply submission if polls are enabled for either.
function do_createPoll($question, $options){
    global $go_sql;
    // $options should be an array of up to 5 options for the poll.
    $option_1 = isset($options[0]) ? $options[0] : null;
    $option_2 = isset($options[1]) ? $options[1] : null;
    $option_3 = isset($options[2]) ? $options[2] : null;
    $option_4 = isset($options[3]) ? $options[3] : null;
    $option_5 = isset($options[4]) ? $options[4] : null;

    $stmt = $go_sql->prepare("INSERT INTO polls (question, option_1, option_2, option_3, option_4, option_5, votes_1, votes_2, votes_3, votes_4, votes_5, voted) VALUES (?, ?, ?, ?, ?, ?, 0, 0, 0, 0, 0, ?)");
    $empty_voted = json_encode(array());
    $stmt->bind_param("sssssss", $question, $option_1, $option_2, $option_3, $option_4, $option_5, $empty_voted);
    $stmt->execute();
    return $stmt->insert_id; // return the id of the newly created poll
}

//this will figure out which topic/reply the poll is attached to.
function do_GetPollOwnerPostorThread($poll_id, $user_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT thread_id, reply_id FROM polls WHERE id = ?");
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if($row['thread_id'] > 0){
            return array('type' => 'thread', 'id' => $row['thread_id']);
        } else if($row['reply_id'] > 0){
            return array('type' => 'reply', 'id' => $row['reply_id']);
        } else {
            return null; // Poll is not attached to any thread or reply
        }
    } else {
        return null; // Poll not found
    }
}


//this bad boys gonna run every time a user votes in a poll, so we need to make sure it's efficient by thrashing the db (lol)
function do_pollvote($poll_id, $option_id, $user_id){
    global $go_sql;
    // sanity checks: is the poll alive? and is the user allowed to vote in it?
    if (chk_IsPollLemonedChannedOrArchivedByThreadId(do_GetPollOwnerPostorThread($poll_id, $user_id)['id'])) {
        return false; // Poll is no longer active
    }
    if (do_checkIfUserVotedInPoll($poll_id, $user_id)) {
        return false; // User has already voted
    }
    $stmt = $go_sql->prepare("SELECT voted FROM polls WHERE id = ?");
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $voted = json_decode($row['voted'], true);
        if (in_array($user_id, $voted)) {
            return false; // User has already voted
        } else {
            // User has not voted, so we can proceed with recording the vote.
            $voted[] = $user_id; // Add the user to the voted array
            $voted_json = json_encode($voted);
            // Update the vote count for the selected option
            $option_votes_column = 'votes_' . $option_id;
            $stmt = $go_sql->prepare("UPDATE polls SET $option_votes_column = $option_votes_column + 1, voted = ? WHERE id = ?");
            $stmt->bind_param("si", $voted_json, $poll_id);
            $stmt->execute();
            return true; // Vote recorded successfully
       }
    }
}

function do_checkIfUserVotedInPoll($poll_id, $user_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT voted FROM polls WHERE id = ?");
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $voted = json_decode($row['voted'], true);
        return in_array($user_id, $voted);
    } else {
        return false; // or some default value
    }
}

function do_fetchPollById($poll_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT * FROM polls WHERE id = ?");
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc(); // array of poll info and votes
}

function do_getSortedPollResults($poll_id){
    $poll = do_fetchPollById($poll_id);
    $options = array(
        1 => array('option' => $poll['option_1'], 'votes' => $poll['votes_1']),
        2 => array('option' => $poll['option_2'], 'votes' => $poll['votes_2']),
        3 => array('option' => $poll['option_3'], 'votes' => $poll['votes_3']),
        4 => array('option' => $poll['option_4'], 'votes' => $poll['votes_4']),
        5 => array('option' => $poll['option_5'], 'votes' => $poll['votes_5']),
    );
    // Sort options by votes in descending order
    usort($options, function($a, $b) {
        return $b['votes'] - $a['votes'];
    });
    return $options; // returns options sorted by votes
}

function do_getPollTotalVotes($poll_id){
    $poll = do_fetchPollById($poll_id);
    $total_votes = $poll['votes_1'] + $poll['votes_2'] + $poll['votes_3'] + $poll['votes_4'] + $poll['votes_5'];
    return $total_votes;
}


function do_getPollWinnerWithPercentage($poll_id){
    $poll = do_fetchPollById($poll_id);
    $total_votes = do_getPollTotalVotes($poll_id);
    if ($total_votes == 0) {
        return null; // No votes cast
    }
    $options = array(
        1 => array('option' => $poll['option_1'], 'votes' => $poll['votes_1']),
        2 => array('option' => $poll['option_2'], 'votes' => $poll['votes_2']),
        3 => array('option' => $poll['option_3'], 'votes' => $poll['votes_3']),
        4 => array('option' => $poll['option_4'], 'votes' => $poll['votes_4']),
        5 => array('option' => $poll['option_5'], 'votes' => $poll['votes_5']),
    );
    // Find the option with the most votes
    usort($options, function($a, $b) {
        return $b['votes'] - $a['votes'];
    });
    $winner = $options[0];
    $percentage = ($winner['votes'] / $total_votes) * 100;
    return array('winner' => $winner['option'], 'percentage' => round($percentage, 2));
}

function do_getPollResultsAsciiBarChart($poll_id){
    $poll = do_fetchPollById($poll_id);
    $total_votes = do_getPollTotalVotes($poll_id);
    if ($total_votes == 0) {
        return null; // No votes cast
    }
    $options = array(
        1 => array('option' => $poll['option_1'], 'votes' => $poll['votes_1']),
        2 => array('option' => $poll['option_2'], 'votes' => $poll['votes_2']),
        3 => array('option' => $poll['option_3'], 'votes' => $poll['votes_3']),
        4 => array('option' => $poll['option_4'], 'votes' => $poll['votes_4']),
        5 => array('option' => $poll['option_5'], 'votes' => $poll['votes_5']),
    );
    // Generate ASCII bar chart
    foreach ($options as &$option) {
        $percentage = ($option['votes'] / $total_votes) * 100;
        $bar_length = round($percentage / 2); // Scale the bar length
        $option['bar'] = str_repeat('#', $bar_length) . ' (' . round($percentage, 2) . '%)';
    }
    return $options; // returns options with ASCII bars
}



$samplePostFlairs = array("Funny" => 10, "Informative" => 5, "Insightful" => 3, "Hell nah" => 1, "Wut" => 4); 
//this is just a sample to help intellisense, the actual flairs and their counts would be stored in the database and fetched as needed.
//the keys are the flair names, and the values are the counts of how many times that flair has been given to that post.
//this is stored in $topic['hasFlairs'] as a json_encoded array of flair_id => count.

function do_getFlairNameById($flair_id){
    global $postFlairs;
    if(isset($postFlairs[$flair_id])) {
        return $postFlairs[$flair_id]['name'];
    }
    return 'Unknown';
}

function do_fetchFlairsbyNameforPost($thread_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT flair_id, COUNT(*) as count FROM post_flairs WHERE thread_id = ? GROUP BY flair_id");
    $stmt->bind_param("i", $thread_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $flairs = array();
    while($row = $result->fetch_assoc()) {
        // Assuming you have a function to get flair name by id
        $flair_name = do_getFlairNameById($row['flair_id']);
        $flairs[$flair_name] = $row['count'];
    }
    return $flairs; // returns an array of flair_name => count for the given thread_id
}

function do_getFlairCountsByIdForPost($thread_id){
    global $go_sql, $postFlairs;
    $thread_id = intval($thread_id);
    $counts = array();
    foreach($postFlairs as $flair_id => $meta) {
        $counts[intval($flair_id)] = 0;
    }

    $stmt = $go_sql->prepare("SELECT flair_id, COUNT(*) as count FROM post_flairs WHERE thread_id = ? GROUP BY flair_id");
    $stmt->bind_param("i", $thread_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $fid = intval($row['flair_id']);
        if(isset($counts[$fid])) {
            $counts[$fid] = intval($row['count']);
        }
    }

    return $counts;
}

function do_getFlairBreakdownForPost($thread_id){
    global $postFlairs;
    $counts = do_getFlairCountsByIdForPost($thread_id);
    $rows = array();
    foreach($postFlairs as $flair_id => $meta) {
        $fid = intval($flair_id);
        $count = isset($counts[$fid]) ? intval($counts[$fid]) : 0;
        $rows[] = array(
            'flair_id' => $fid,
            'name' => $meta['name'],
            'description' => $meta['description'],
            'positive' => !empty($meta['positive']),
            'count' => $count
        );
    }
    return $rows;
}

function do_getUserFlairVotesForThread($thread_id, $user_id){
    global $go_sql;
    $votes = array();
    $thread_id = intval($thread_id);
    $user_id = intval($user_id);
    if($thread_id <= 0 || $user_id <= 0) {
        return $votes;
    }

    $stmt = $go_sql->prepare("SELECT flair_id FROM post_flairs WHERE thread_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $thread_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        $votes[intval($row['flair_id'])] = true;
    }
    return $votes;
}

function do_voteFlairForThread($thread_id, $flair_id, $user_id){
    global $go_sql, $postFlairs;
    $thread_id = intval($thread_id);
    $flair_id = intval($flair_id);
    $user_id = intval($user_id);

    if($thread_id <= 0 || $flair_id <= 0 || $user_id <= 0) {
        return false;
    }
    if(!isset($postFlairs[$flair_id])) {
        return false;
    }

    $thread = do_getThreadById($thread_id);
    if(!$thread) {
        return false;
    }
    if(intval($thread['poster_id']) === $user_id) {
        return false;
    }

    $existing_stmt = $go_sql->prepare("SELECT id FROM post_flairs WHERE thread_id = ? AND user_id = ? LIMIT 1");
    $existing_stmt->bind_param("ii", $thread_id, $user_id);
    $existing_stmt->execute();
    $existing_result = $existing_stmt->get_result();
    if($existing_result->num_rows > 0) {
        return false;
    }

    $created_at = time();
    $stmt = $go_sql->prepare("INSERT INTO post_flairs (thread_id, flair_id, user_id, created_at) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE created_at = VALUES(created_at)");
    $stmt->bind_param("iiii", $thread_id, $flair_id, $user_id, $created_at);
    return $stmt->execute();
}


function chk_DoesPostHaveFlairYet($thread_id){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT COUNT(*) as count FROM post_flairs WHERE thread_id = ?");
    $stmt->bind_param("i", $thread_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'] > 0; // returns true if there is at least one flair for the post
    } else {
        return false; // or some default value
    }
}


// (emoji stars) "algorithm" (emoji stars)
function do_getStandoutFlairListForPost($thread_id){
    global $postFlairs, $site;
    $flairs = do_getFlairCountsByIdForPost($thread_id);
    $standout_flairs = array();
    $standout_value = 1;
    $total_votes = 0;
    // get an average of all flair counts to determine a threshold for standout flairs
    // this will weight positive flairs more highly than negative flairs unless its overwhelmingly negative.   
    $total_positive_flairs = 0;
    $total_negative_flairs = 0;
    foreach($flairs as $flair_id => $count) {
        $count = intval($count);
        $total_votes += $count;
        $is_positive = isset($postFlairs[$flair_id]) ? !empty($postFlairs[$flair_id]['positive']) : true;
        if(!$is_positive) {
            $total_negative_flairs += $count;
        } else {
            $total_positive_flairs += $count;
        }
    }

    $min_votes = isset($site['flair_consensus_min_votes']) ? intval($site['flair_consensus_min_votes']) : 3;
    if($total_votes < $min_votes) {
        return array();
    }

    if(count($flairs) > 0 && $total_positive_flairs > 0) {
        $standout_value = $total_positive_flairs / count($flairs); // average positive flair count
    }
    if($total_negative_flairs > $total_positive_flairs) {
        $standout_value = $total_negative_flairs / count($flairs); // if negative flairs outweigh positive, use average negative flair count as threshold
    }

    foreach($flairs as $flair_id => $count) {
        if($count >= $standout_value) { // arbitrary threshold for standout flairs
            $flair_name = do_getFlairNameById($flair_id);
            $is_positive = isset($postFlairs[$flair_id]) ? !empty($postFlairs[$flair_id]['positive']) : true;
            $signed_count = $is_positive ? intval($count) : intval($count) * -1;
            $standout_flairs[$flair_name] = $signed_count;
        }
    }
    return $standout_flairs; // return array of flair_name => count for all standout flairs
}

// Canonical function name wrapper for thread page consensus display
function do_getStandoutFlairsForPost($thread_id){
    $standout_flairs = do_getStandoutFlairListForPost($thread_id);
    if(empty($standout_flairs)) {
        return null; // no consensus
    }
    // Return the most-voted standout flair as a single consensus item
    arsort($standout_flairs); // sort by count descending
    $top_flair = reset($standout_flairs);
    $top_flair_name = key($standout_flairs);
    $flair_count = $top_flair;
    return array('flair_name' => $top_flair_name, 'flair_count' => $flair_count);
}


$postFlairs = array(
    1 => array(
        'name' => 'Funny',
        'description' => 'This post is funny.',
        'positive' => true
    ),
    2 => array(
        'name' => 'Informative',
        'description' => 'This post is informative.',
        'positive' => true
    ),
    3 => array(
        'name' => 'Insightful',
        'description' => 'This post is insightful.',
        'positive' => true
    ),  
    
    4 => array(
        'name' => 'Hell nah',
        'description' => 'I disagree with this post.',
        'positive' => false
    ),
    5 => array(
        'name' => 'Wut',
        'description' => 'I am confused by this post.',
        'positive' => false
    ),
);





function do_getAwardsByUserId($user_id) {
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT a.award_id, c.name, c.description, c.image_url, a.created_at FROM awards a INNER JOIN award_catalog c ON c.id = a.award_id WHERE a.user_id = ? ORDER BY a.created_at ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $earned = array();
    while($row = $result->fetch_assoc()) {
        $earned[] = array(
            'award_id' => intval($row['award_id']),
            'name' => $row['name'],
            'description' => $row['description'],
            'image_url' => $row['image_url'],
            'created_at' => intval($row['created_at'])
        );
    }
    return $earned;
}

function fun_timeAgo($timestamp) {
    $timeDifference = time() - $timestamp;

    if ($timeDifference < 60) {
        return $timeDifference . ' seconds ago';
    } elseif ($timeDifference < 3600) {
        return floor($timeDifference / 60) . ' minutes ago';
    } elseif ($timeDifference < 86400) {
        return floor($timeDifference / 3600) . ' hours ago';
    } elseif ($timeDifference < 604800) {
        return floor($timeDifference / 86400) . ' days ago';
    }else{
        return 'a long time ago';
    }
}

//slogan stuff. it changes on each page load, just for fun. you can add as many slogans as you want

$slogans = array(
    'Welcome to EzBBS!',
    'A simple bbs engine for the small web.',
    'Enjoy your stay at EzBBS!',
    'EzBBS - where discussions happen.',
    'Join the conversation at EzBBS!',
    'EzBBS - your friendly neighborhood bbs.',
    'Discover new topics at EzBBS!',
    'EzBBS - connecting people through discussions.',
    'Share your thoughts at EzBBS!',
    'EzBBS - the place for great discussions.'
);

function fun_getslogan() {
    global $slogans;
    return $slogans[array_rand($slogans)];
}



$notifcategories = array(
    'feedback' => 'It Worked!',
    'mention' => 'HEY! LOOK! LISTEN!',
    'trophy' => 'You earned a trophy, congrats!',
    'ban' => 'You have been banned.',
    // Add more categories as needed
);

function do_fetchAnyNotifs(){
    // This function will check for any notifications for the logged in user and return them to be displayed in the UI.
    global $go_sql;
    if(isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null){
        $user_id = $_SESSION['user_id'];
        $stmt = $go_sql->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result(); // This will return a result set of unread notifications for the user
    }
    else {
        return null; // No user logged in, so no notifications to display   
    }
}
function do_HandleNotifs(){
    // Display the highest-priority unread notification for the logged in user
    // Priority: ban > trophy > feedback > mention
    global $notifcategories, $go_sql;
    
    if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
        return null; // not logged in
    }
    
    // Check for ban first (highest priority)
    $user_id = $_SESSION['user_id'];
    $stmt = $go_sql->prepare("SELECT * FROM notifications WHERE user_id = ? AND type = 'ban' AND is_read = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $notif = $result->fetch_assoc();
        $notifdata = json_decode($notif['data'], true);
        $notif_id = $notif['id'];
        $ban_length = isset($notifdata['duration']) ? intval($notifdata['duration']) : (isset($notifdata['length']) ? intval($notifdata['length']) : 0);
        return '<strong>' . $notifcategories['ban'] . '</strong><br>Reason: ' . htmlspecialchars($notifdata['reason']) . '<br>Duration: ' . fun_secondsToHumanReadable($ban_length) . ' <a href="/do/dismissnotif/' . $notif_id . '">[dismiss]</a>';
    }
    
    // Check for trophy (second priority)
    $stmt = $go_sql->prepare("SELECT * FROM notifications WHERE user_id = ? AND type = 'trophy' AND is_read = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $notif = $result->fetch_assoc();
        $notifdata = json_decode($notif['data'], true);
        $notif_id = $notif['id'];
        return '<strong>' . $notifcategories['trophy'] . '</strong><br>You earned the "' . htmlspecialchars($notifdata['trophy_name']) . '" trophy! <a href="/user/' . $user_id . '">[view]</a> <a href="/do/dismissnotif/' . $notif_id . '">[dismiss]</a>';
    }
    
    // Check for feedback (third priority)
    $stmt = $go_sql->prepare("SELECT * FROM notifications WHERE user_id = ? AND type = 'feedback' AND is_read = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $notif = $result->fetch_assoc();
        $notifdata = json_decode($notif['data'], true);
        return '<strong>' . $notifcategories['feedback'] . '</strong><br>' . htmlspecialchars($notifdata['message']);
    }
    
    // Check for mention (lowest priority)
    $stmt = $go_sql->prepare("SELECT * FROM notifications WHERE user_id = ? AND type = 'mention' AND is_read = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $notif = $result->fetch_assoc();
        $notifdata = json_decode($notif['data'], true);
        $notif_id = $notif['id'];
        return '<strong>' . $notifcategories['mention'] . '</strong><br><a href="' . htmlspecialchars($notifdata['link']) . '">[view]</a> <a href="/do/dismissnotif/' . $notif_id . '">[dismiss]</a>';
    }
    
    return null; // no notifications
}

function do_notificationflow(){
    // this function will be called on each page load to check for any notifications for the logged in user and display them in the notif bar.
    if(isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null){
        $notif_html = do_HandleNotifs(); // this will fetch and format the highest-priority notification
        if($notif_html) {
            echo '<div id="notice">' . $notif_html . '</div>'; // display the notification bar
        }
    }
}

function do_setnotifread($notif_id){
    global $go_sql;
    $stmt = $go_sql->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notif_id);
    return $stmt->execute();
}

function do_sendnotification($user_id, $type, $data){
    global $go_sql;
    if(!isset($_SESSION['user_id'])|| $user_id == "guest"){
       // do_cookieNotif($type, $data); 
        return null;
    }
    $data_json = json_encode($data);
    $stmt = $go_sql->prepare("INSERT INTO notifications (user_id, type, data, is_read) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("iss", $user_id, $type, $data_json);
    $stmt->execute();
}



function do_logentry($severity, $message, $modlog = false, $error = null){
    // Log to file with severity, timestamp, and optional context
    $severities = array("Notice", "Warning", "Error");
    $severity = in_array($severity, $severities) ? $severity : "Notice";
    
    $timestamp = date('Y-m-d H:i:s');
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest';
    
    $log_entry = sprintf("[%s] %s | User: %s | %s", $timestamp, $severity, $user_id, $message);
    if($error !== null) {
        $log_entry .= " | Error: " . $error;
    }
    if($modlog) {
        $log_entry .= " [MODLOG]";
    }
    
    // Write to log file (create logs directory if it doesn't exist)
    $log_dir = __DIR__ . '/logs';
    if(!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/ezbbs.log';
    error_log($log_entry . PHP_EOL, 3, $log_file);
}

function do_setuserbanned($user_id, $ban_length, $ban_reason){
    global $go_sql;
    $stmt = $go_sql->prepare("UPDATE users SET isbanned = 1, ban_length = ?, ban_reason = ? WHERE id = ?");
    $stmt->bind_param("isi", $ban_length, $ban_reason, $user_id);
    if(!$stmt->execute()) {
        return false;
    }

    //L A N K : let a neerdowell know!
    do_sendnotification($user_id, "ban", array("length" => $ban_length, "reason" => $ban_reason));
    return true;
}

function do_setuserunbanned($user_id){
    global $go_sql;
    $stmt = $go_sql->prepare("UPDATE users SET isbanned = 0, ban_length = 0, ban_reason = '' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if(!$stmt->execute()) {
        return false;
    }

    //L A N K : let a neerdowell know!
    do_clearUserBanNotifs($user_id);
    do_sendnotification($user_id, "feedback", array("message" => "You have been unbanned manually by a mod, congrats I guess."));
    return true;
}

function do_clearUserBanNotifs($user_id){
    global $go_sql;
    $stmt = $go_sql->prepare("DELETE FROM notifications WHERE user_id = ? AND type = 'ban'");
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

//login flow. core functions and checks to determine login status

function do_isLoggedIn(){
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null;
}

function do_getCurrentUserId(){
    if(!do_isLoggedIn()) {
        return null;
    }
    return intval($_SESSION['user_id']);
}

function do_getPostLoginRedirect(){
    $next = isset($_GET['next']) ? trim($_GET['next']) : '';
    if($next === '' && isset($_POST['next'])) {
        $next = trim($_POST['next']);
    }
    if($next === '' || strpos($next, '/') !== 0 || strpos($next, '//') === 0) {
        return '/';
    }
    return $next;
}

function do_requireLogin($fallback = '/login'){
    if(do_isLoggedIn()) {
        return;
    }
    $next = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $target = $fallback;
    if(strpos($target, '?') === false) {
        $target .= '?next=' . rawurlencode($next);
    } else {
        $target .= '&next=' . rawurlencode($next);
    }
    header('Location: ' . $target);
    exit();
}

function do_supportsRememberTokens(){
    global $go_sql;
    $result = $go_sql->query("SHOW TABLES LIKE 'auth_tokens'");
    return $result && $result->num_rows > 0;
}

function do_createRememberToken($user_id){
    global $go_sql;
    if(!do_supportsRememberTokens()) {
        return;
    }

    $selector = bin2hex(random_bytes(12));
    $token = bin2hex(random_bytes(32));
    $token_hash = hash('sha256', $token);
    $expires_at = time() + (60 * 60 * 24 * 30);

    $stmt = $go_sql->prepare("INSERT INTO auth_tokens (user_id, selector, token_hash, expires_at, created_at) VALUES (?, ?, ?, ?, ?)");
    $created_at = time();
    $stmt->bind_param("issii", $user_id, $selector, $token_hash, $expires_at, $created_at);
    if($stmt->execute()) {
        setcookie('remember_selector', $selector, $expires_at, '/', '', false, true);
        setcookie('remember_token', $token, $expires_at, '/', '', false, true);
    }
}

function do_clearRememberTokenCookies(){
    setcookie('remember_selector', '', time() - 3600, '/', '', false, true);
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    // legacy insecure cookies cleanup
    setcookie('user_id', '', time() - 3600, '/');
    setcookie('pw_hash', '', time() - 3600, '/');
}

function do_revokeRememberTokensForUser($user_id){
    global $go_sql;
    if(!$user_id || !do_supportsRememberTokens()) {
        return;
    }
    $stmt = $go_sql->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

function do_hydrateSessionUser($user_id){
    global $go_sql, $user;
    $stmt = $go_sql->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows === 0) {
        return false;
    }

    $db_user_data = $result->fetch_assoc();
    $_SESSION['user_id'] = intval($db_user_data['id']);
    $_SESSION['isloggedin'] = true;
    $_SESSION['user'] = array_merge($user, $db_user_data);
    return true;
}

function do_loginUser($user_id, $remember = false){
    if(session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    session_regenerate_id(true);
    if(!do_hydrateSessionUser(intval($user_id))) {
        return false;
    }

    do_clearRememberTokenCookies();
    if($remember) {
        do_createRememberToken(intval($user_id));
    }
    return true;
}

function do_tryRememberLogin(){
    global $go_sql;
    if(!do_supportsRememberTokens()) {
        return false;
    }
    if(!isset($_COOKIE['remember_selector']) || !isset($_COOKIE['remember_token'])) {
        return false;
    }

    $selector = trim($_COOKIE['remember_selector']);
    $token = trim($_COOKIE['remember_token']);
    if($selector === '' || $token === '') {
        do_clearRememberTokenCookies();
        return false;
    }

    $stmt = $go_sql->prepare("SELECT user_id, token_hash, expires_at FROM auth_tokens WHERE selector = ? LIMIT 1");
    $stmt->bind_param("s", $selector);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows === 0) {
        do_clearRememberTokenCookies();
        return false;
    }

    $row = $result->fetch_assoc();
    if(intval($row['expires_at']) < time()) {
        $delete_stmt = $go_sql->prepare("DELETE FROM auth_tokens WHERE selector = ?");
        $delete_stmt->bind_param("s", $selector);
        $delete_stmt->execute();
        do_clearRememberTokenCookies();
        return false;
    }

    $token_hash = hash('sha256', $token);
    if(!hash_equals($row['token_hash'], $token_hash)) {
        do_clearRememberTokenCookies();
        return false;
    }

    return do_loginUser(intval($row['user_id']), true);
}

function do_attemptLoginByCredentials($identifier, $password, $remember = false){
    $identifier = trim($identifier);
    if($identifier === '' || $password === '') {
        return false;
    }

    $candidate = do_getUserByUsername($identifier);
    if(!$candidate && strpos($identifier, '@') !== false) {
        $candidate = do_getUserByEmail($identifier);
    }
    if(!$candidate) {
        return false;
    }
    if(!password_verify($password, $candidate['password'])) {
        return false;
    }

    return do_loginUser(intval($candidate['id']), $remember);
}

function do_logout(){
    //this will log the user out by clearing their session and cookies.
    if(session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $logout_user_id = do_getCurrentUserId();
    do_revokeRememberTokensForUser($logout_user_id);

    session_unset();
    session_destroy();
    do_clearRememberTokenCookies();
}

$islogged = false;

if(session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if(do_isLoggedIn()) {
    $islogged = true;
}

if(!$islogged && do_tryRememberLogin()) {
    $islogged = true;
}

if(!$islogged) {
    $_SESSION['user_id'] = null;
    $_SESSION['isloggedin'] = false;
}

if($islogged){
    if(!do_hydrateSessionUser(intval($_SESSION['user_id']))) {
        $_SESSION['user_id'] = null;
        $_SESSION['isloggedin'] = false;
        $islogged = false;
    }
}

if(isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $user = array_merge($user, $_SESSION['user']);
}
