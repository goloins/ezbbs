<?php
/* ezbbs - a simple bbs engine for the small web 
 *
 * Copyleft 2026 by the ezbbs contributors
 * 
 * init.php - pretty much everything to do with the default configuration goes here.
 * 
 */

//Sitewide defaults, these are used everywhere and are static. Change them if you want to customize your site.
$site = array(
    'site_name' => 'EzBBS',
    'site_description' => 'A simple bbs engine for the small web',
    'favicon_url' => 'assets/favicon.ico',
    'site_url' => 'https://localhost/',
    'rss_url' => $site['site_url'] . 'rss.xml', //note: site_url/catname/rss.xml works for cat specific feeds.
    'default_lemon_years' => 2, //number of years before lemon threads can be replied to
    'chanlike_reply_limit' => 100, //number of replies before chanlike threads are removed from the homepage and archived
    'topic_preview_length' => 20, //number of characters to show in the topic preview on the homepage and category pages.
    'topic_headline_length' => 50,
    'topic_poster_banned_prefix' => '<span class="help" title="USER WAS BANNED FOR THIS POST">[B]</span>',
    'admin_suffix' => ' [<a href=/admin>A</a>]' //suffix for topics posted by admins
    );

//Site SQL defaults, change these to match your sql credentials.    
$sql = array(
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'ezbbs'
);

//sql area, woo woo - no touching! 
$go_sql = new mysqli($sql['host'], $sql['username'], $sql['password'], $sql['database']); //change these to your sql credentials
if ($go_sql->connect_error) {
    die('womp womp, ezbbs died: ' . $go_sql->connect_error);
}


//User defaults, these are used for non-logged in users, and for new users. Change them if you want to customize the default user experience.
//buyer beware, adding more will require you to add more columns to the users table in the database, and also in any user-facing sql queries.
$user = array(
    'isloggedin' => false,
    'theme' => 'default',
    'isadmin' => false,
    'defaultlocation' => "Somewhere on the internet",
    'defaultbio' => "This user prefers to keep an air of mystery about them.",
    'defaultavatar' => 'assets/png/default_avatar.png',
    'awards' => json_encode(array()), // Array of award ids in the awards table that the user has received
    'isbanned' => false, // default on new users, but once banned will show bars on the profile pic.
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
    'usermsnescargot' => '', //for the few proud escargot users to share their msn doohickey
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
    
    // Insert the new user into the database with default values
    $stmt = $go_sql->prepare("INSERT INTO users (username, password, email, theme, isadmin, defaultlocation, defaultbio, defaultavatar, awards, isbanned, ismoderator, defaultsignature, sigbanners, userportrait, usernamecolor, usernamestyle, joindate, crackedportrait, duncecorner, userkudos, userkudostogive, userposts, userwebsite, usergemsite, userspacehey, userirchandleandnet, usersmsnescargot, profileprimarycolor, profilesecondarycolor, profileheadingtextcolor, profilelowerheadingcolor, profilehyperlinkcolor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssssssssssssssssss", $supplied_username, $hashed_password, $supplied_email, $user['theme'], $user['isadmin'], $user['defaultlocation'], $user['defaultbio'], $user['defaultavatar'], $user['awards'], $user['isbanned'], $user['ismoderator'], $user['defaultsignature'], $user['sigbanners'], $user['userportrait'], $user['usernamecolor'], $user['usernamestyle'], $user['joindate'], $user['crackedportrait'], $user['duncecorner'], $user['userkudos'], $user['userkudostogive'], $user['userposts'], $user['userwebsite'], $user['usergemsite'], $user['userspacehey'], $user['userirchandleandnet'], $user['usersmsnescargot'], $user['profileprimarycolor'], $user['profilesecondarycolor'], $user['profileheadingtextcolor'], $user['profilelowerheadingcolor'], $user['profilehyperlinkcolor']);
    $stmt->execute();
    return true;

}
//<ul id="main_menu" class="menu"><li class="hot_topics"><a href="/hot_topics">Hot</a></li><li class="topics"><a href="/topics">Topics</a></li><li class="bumps"><a href="/bumps">Bumps</a></li><li class="replies"><a href="/replies">Replies</a></li><li class="new_topic"><a href="/new_topic">New topic</a></li><li class="history"><a href="/history">History</a></li><li class="watchlist"><a href="/watchlist">Watchlist</a></li><li class="bulletins"><a href="/bulletins">Bulletins</a></li><li class="folks"><a href="/folks">Folks</a></li><li class="search"><a href="/search">Search</a></li><li class="stuff"><a href="/stuff">Stuff</a></li>    </ul>
$homepagemenu = array(
    'Hot' => '/hot_topics',
    'Topics' => '/topics',
    'Bumps' => '/bumps',
    'Replies' => '/replies',
    'New Topic' => '/new_topic');

for($i = 1; $i <= count($categories); $i++){
    $homepagemenu[$categories[$i]] = '/cat/' . $i;
} //intellisense literally read my mind here. nice.

$homepagemenu[] = array(
    'Folks' => '/folks',
    'Search' => '/search',
    'Stuff' => '/stuff'
);
global $homepagemenu;


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
// Category defaults - these are used for the categories on the site, more can be added in the database. It will start with these. todo!
$categories = array(1 => "General", 2 => "Sports", 3 => "Technology", 4 => "Gaming", 5 => "Music", 6 => "Miscellaneous", 7=> "Meta");


// Default Awards - any additional awards can be added to the awards table in the database, but these are the defaults that come with the site todo!
$awards = array(
    1 => array(
        'name' => 'First Post',
        'description' => 'Awarded for making your first post.',
        'icon' => 'assets/png/awards/first_post.png'
    ),
    2 => array(
        'name' => 'Kudos',
        'description' => 'Awarded for receiving 15 kudos from other users.',
        'icon' => 'assets/png/awards/kudos.png'
    ),
    3 => array(
        'name' => 'Veteran',
        'description' => 'Awarded for being a member for 1 year.',
        'icon' => 'assets/png/awards/veteran.png'
    ),
    4 => array(
        'name' => 'Out on Good Behavior',
        'description' => 'Awarded for being unbanned after a tempban',
        'icon' => 'assets/png/awards/out_on_good_behavior.png'
    ),
    5 => array(
        'name' => 'Crass Clown',
        'description' => 'Has been dunced by a moderator at least twice.',
        'icon' => 'assets/png/awards/crass_clown.png'
    ),
    6 => array(
        'name' => 'Moneybags',
        'description' => 'Donated to the website, all you get is the monopoly guy.',
        'icon' => 'assets/png/awards/moneybags.png'
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

function do_getTopics($page){
    global $go_sql;
    $topics_per_page = 20;
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



);

function fun_timeAgo($timestamp) {
    $timeDifference = time() - $timestamp;

    if ($timeDifference < 60) {
        return $timeDifference . ' seconds ago';
    } elseif ($timeDifference < 3600) {
        return floor($timeDifference / 60) . ' minutes ago';
    } elseif ($timeDifference < 86400) {
        return floor($timeDifference / 3600) . ' hours ago';
    } else {
        return floor($timeDifference / 86400) . ' days ago';
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


//login flow. core functions and checks to determine login status


$islogged = false;

if(!isset($_SESSION)) {
    session_start();
}

if(!isset($COOKIE['user_id'])) {
    $_SESSION['user_id'] = null; // Set to null for guests
    $_SESSION['isloggedin'] = false;
}

if(isset($_COOKIE['user_id']) && isset($_COOKIE['pw_hash'])){
    $cookie_user_id = $_COOKIE['user_id'];
    $cookie_pw_hash = $_COOKIE['pw_hash'];

    // Fetch the user from the database
    $stmt = $go_sql->prepare("SELECT id, password FROM users WHERE id = ?");
    $stmt->bind_param("i", $cookie_user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_user_id, $db_password_hash);
        $stmt->fetch();

        // Verify the password hash from the cookie against the database hash
        if (password_verify($cookie_pw_hash, $db_password_hash)) {
            $_SESSION['user_id'] = $db_user_id; // Log the user in by setting the session user_id
            $islogged = true;
        } else {
            // Invalid cookie, clear it
            setcookie('user_id', '', time() - 3600);
            setcookie('pw_hash', '', time() - 3600);
        }
    } else {
        // User not found, clear the cookie
        setcookie('user_id', '', time() - 3600);
        setcookie('pw_hash', '', time() - 3600);
    }
}

if($islogged){
    $_SESSION['user'] = $user; // Populate defaults
    $stmt = $go_sql->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $db_user_data = $result->fetch_assoc();
        // Merge database user data with default user data
        $_SESSION['user'] = array_merge($_SESSION['user'], $db_user_data);
    }
}