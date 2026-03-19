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
    'favicon_url' => 'assets/favicon.ico',
    'site_url' => 'https://localhost/',
    'rss_url' => $site['site_url'] . 'rss.xml', //note: site_url/catname/rss.xml works for cat specific feeds.
    'default_lemon_years' => 2, //number of years before lemon threads can be replied to
    'chanlike_reply_limit' => 100, //number of replies before chanlike threads are removed from the homepage and archived
    'topic_preview_length' => 20, //number of characters to show in the topic preview on the homepage and category pages.
    'topic_headline_length' => 50,
    'topic_poster_banned_prefix' => '<span class="help" title="USER WAS BANNED FOR THIS POST">[B]</span>',
    'admin_suffix' => ' [<a href=/admin>A</a>]', //suffix for topics posted by admins
    'minimum_posts_for_outbound_links' => 5,
    'disclaimer' => 'All trademarks and copyrights on this site are owned by their respective parties. All uploaded files and comments are the responsibility of their own posters.', //site disclaimer
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
    $stmt = $go_sql->prepare("INSERT INTO users (username, password, email, theme, isadmin, defaultlocation, defaultbio, defaultavatar, awards, isbanned, ban_length, ban_reason, ismoderator, defaultsignature, sigbanners, userportrait, usernamecolor, usernamestyle, joindate, crackedportrait, duncecorner, userkudos, userkudostogive, userposts, userwebsite, usergemsite, userspacehey, userirchandleandnet, usersmsnescargot, profileprimarycolor, profilesecondarycolor, profileheadingtextcolor, profilelowerheadingcolor, profilehyperlinkcolor) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssssssssssssssssssssss", $supplied_username, $hashed_password, $supplied_email, $user['theme'], $user['isadmin'], $user['defaultlocation'], $user['defaultbio'], $user['defaultavatar'], $user['awards'], $user['isbanned'], $user['ban_length'], $user['ban_reason'], $user['ismoderator'], $user['defaultsignature'], $user['sigbanners'], $user['userportrait'], $user['usernamecolor'], $user['usernamestyle'], $user['joindate'], $user['crackedportrait'], $user['duncecorner'], $user['userkudos'], $user['userkudostogive'], $user['userposts'], $user['userwebsite'], $user['usergemsite'], $user['userspacehey'], $user['userirchandleandnet'], $user['usersmsnescargot'], $user['profileprimarycolor'], $user['profilesecondarycolor'], $user['profileheadingtextcolor'], $user['profilelowerheadingcolor'], $user['profilehyperlinkcolor']);
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
global $categories;

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
        positive => 'assets/png/awards/first_post.png'
    ),
    2 => array(
        'name' => 'Kudos',
        'description' => 'Awarded for receiving 15 kudos from other users.',
        positive => 'assets/png/awards/kudos.png'
    ),
    3 => array(
        'name' => 'Veteran',
        'description' => 'Awarded for being a member for 1 year.',
        positive => 'assets/png/awards/veteran.png'
    ),
    4 => array(
        'name' => 'Out on Good Behavior',
        'description' => 'Awarded for being unbanned after a tempban',
        positive => 'assets/png/awards/out_on_good_behavior.png'
    ),
    5 => array(
        'name' => 'Crass Clown',
        'description' => 'Has been dunced by a moderator at least twice.',
        positive => 'assets/png/awards/crass_clown.png'
    ),
    6 => array(
        'name' => 'Moneybags',
        'description' => 'Donated to the website, all you get is the monopoly guy.',
        positive => 'assets/png/awards/moneybags.png'
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
    



//this one is going to be fun, it'll be used with the tag cloud and tag search.
//it'll need to break apart the tags json array and search for the tag in there, then return all threads with that tag. fun!
//the smart move will be just to pull the top 20. maybe we'll have a "more" or "random" option.
function do_getAllThreadsInTag($tag){
    global $go_sql;
    $stmt = $go_sql->prepare("SELECT * FROM topics WHERE JSON_CONTAINS(tags, '\"' ? '\"') ORDER BY last_bump DESC LIMIT 20");
    $stmt->bind_param("s", $tag);
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
    $stmt = $go_sql->prepare("INSERT INTO replies (thread_id, poster_id, content, media, attached_links, poll_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssii", $thread_id, $poster_id, $content, $media_json, $links_json, $poll);
    if($stmt->execute()){
        // Update the replies count and last bump time in the topics table
        $stmt = $go_sql->prepare("UPDATE topics SET replies_count = replies_count + 1, last_bump = ? WHERE id = ?");
        $current_time = time();
        $stmt->bind_param("ii", $current_time, $thread_id);
        $stmt->execute();
        return true;
    } else {
        return false;
    }
}

function post_Topic($title, $content, $poster_id, $category_id, $media = array(), $attached_links = array()){
    global $go_sql;
    //check for presence of poll in content at post time, if there is a poll, we'll need to create the poll first and then link it to the reply.
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
    $stmt = $go_sql->prepare("INSERT INTO topics (title, content, poster_id, category_id, media, attached_links, poll_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiissi", $title, $content, $poster_id, $category_id, $media_json, $links_json, $poll);
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
        $text = preg_replace_callback('/>>(\d+)/', function($matches) {
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
        }, htmlspecialchars($text));
    }

    
}

function do_AddTooltipWithReplySnippet($reply_content, $reply_id){
   //When a user replies @username, we want to add a snippet of username's last post
   //in the thread (the one presumably being replied to) as a tooltip on the @username text. This function will be called when rendering the reply text.
    if(preg_match('/@(\w+)/', $reply_content)){
        $reply_content = preg_replace_callback('/@(\w+)/', function($matches) use ($reply_id) {
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
                        do_sendnotification($mentioned_user_id, 'You were mentioned in a reply', '/thread/' . get_ThreadIdByReplyId($reply_id) . '#reply');

                    return '@' . htmlspecialchars($username); 
                    
                }
            } else {
                return '@' . htmlspecialchars($username); // If user not found, just return the original text
            }
        }, htmlspecialchars($reply_content));
    }
 
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
    return nl2br($topic_content); // convert newlines to <br> for HTML rendering
}


function do_RenderReplyText($reply_content, $reply_id){
    //this will be the main function to call when rendering the reply text, it will call the other functions to render links, formatting, and add tooltips.
    $reply_content = do_RenderMarkdownLinksInText($reply_content);
    $reply_content = do_RenderMarkdownFormattingInText($reply_content);
    $reply_content = do_RenderMarkdownCodeInText($reply_content);
    $reply_content = do_RenderThreadLinkInReplyText($reply_content);
    $reply_content = do_AddTooltipWithReplySnippet($reply_content, $reply_id);
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

// (emoji stars) "algorithm" (emoji stars)
function do_getStandoutFlairsforPost($thread_id){
    $flairs = do_fetchFlairsbyNameforPost($thread_id);
    $standout_flairs = array();
    $standout_value = 0;
    // get an average of all flair counts to determine a threshold for standout flairs
    // this will weight positive flairs more highly than negative flairs unless its overwhelmingly negative.   
    $total_positive_flairs = 0;
    $total_negative_flairs = 0;
    foreach($flairs as $flair_name => $count) {
        if(strpos($flair_name, 'Hell nah') !== false || strpos($flair_name, 'Wut') !== false) {
            $total_negative_flairs += $count;
        } else {
            $total_positive_flairs += $count;
        }
    } 
    if($total_positive_flairs > 0) {
        $standout_value = $total_positive_flairs / count($flairs); // average positive flair count
    } else {
        $standout_value = 0;
    }
    if($total_negative_flairs > $total_positive_flairs) {
        $standout_value = $total_negative_flairs / count($flairs); // if negative flairs outweigh positive, use average negative flair count as threshold
    }


    foreach($flairs as $flair_name => $count) {
        if($count >= $standout_value) { // arbitrary threshold for standout flairs
            $standout_flairs[$flair_name] = $count;
        }
    }
    return array($standout_flairs, $standout_value); //we get the flair names, and their average score, slashdot style
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
    $stmt = $go_sql->prepare("SELECT * FROM awards WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
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
function do_HandleNotifs($notiftype, $notifdata){
    // This function will take a notification type and data
    // each type of notif works differently:
    // mention: data will include the thread/reply id and a link. 
    // trophy: data will include the name of the trophy and a link to the user's profile
    // ban: data will include the reason for the ban and its duration.

    //we will be using the typical atbbs notif format,
    //which is a bar at the top of the page thats persistence depends on the format.
    /*
        bans will persist no matter what, showing a countdown. this overrides all other 
        notifs and will be the only one shown until the ban expires.

        trophy notifs will persist until the user clicks on them to view the trophy. 
        they will dissappear after being clicked on, and only override the mention notifs.

        mention notifs are least persistent, but can be dismissed. If there are multiple,
        rather than stacking them, we'll arrange them in a marquee ticker that auto scrolls like this:

        [New mention in Thread Title 1] (small text)[dismiss], etc and they will scroll.
            the dismiss will be a hyperlink that goes to /dismissnotif/notif_id and then back to
            the previous page. The remaining notifs will continue to scroll in the ticker.

            the notif bar isn't persistent and doesn't need to be. it is written:
                <div id="notice"><strong>Notice:</strong> 
                <strong>New mention in Thread 1</strong> <a href=/thread/1#reply_anchorpoint> [view] <a href="/dismissnotif/123">[dismiss]</a></div>

            if there are over 1 notifs, a "|" will separate them in the ticker.

            if the length of the notifs exceeds the width of the page, it will scroll like a marquee, otherwise it will just sit there.


            the notifs sql table will looks like
            id (int, primary key)
            user_id (int, foreign key to users table)
            type (varchar) - the type of notification, e.g. "mention", "trophy", "ban", etc.
            data (text) - a json_encoded array of data relevant to the notification, e.g. for a mention it might include the thread_id and a link to the post, for a trophy it might include the trophy name and a link to the trophy page, for a ban it might include the reason and duration, etc.
            is_read (boolean) -

    */
    global $notifcategories;
    $allnotifs = do_fetchAnyNotifs(); // fetch all unread notifs for the user
    $mentions = array();
    foreach($allnotifs as $notif) {
        // we'll loop through the notifs and display them in the notif bar according to their type and data.
        // for simplicity, we'll just handle one notif at a time in this function, but in practice you would want to handle multiple notifs and arrange them in the ticker as described above.
        $notiftype = $notif['type'];
        $notifdata = json_decode($notif['data'], true);
        $notif_id = $notif['id'];

        
        $notifbartext = '';
        if($notiftype == 'feedback'){
            $notifbartext = '<strong>' . $notifcategories['feedback'] . '</strong><br>' . htmlspecialchars($notifdata['message']);
            // This will be the only notif shown, so we can return it immediately.
            return $notifbartext;
        }
        elseif($notiftype == 'ban'){
            $notifbartext = '<strong>' . $notifcategories['ban'] . '</strong><br>Reason: ' . htmlspecialchars($notifdata['reason']) . '<br>Duration: ' . fun_secondsToHumanReadable(htmlspecialchars($notifdata['duration']));
            // This will be the only notif shown, so we can return it immediately.
            return $notifbartext;
        } elseif($notiftype == 'trophy' && isset($_SESSION['user_id'])){
            $notifbartext = '<strong>' . $notifcategories['trophy'] . '</strong><br>You earned the "' . htmlspecialchars($notifdata['trophy_name']) . '" trophy! <a href="/user/' . htmlspecialchars($_SESSION['user_id']) . '">[view]</a> <a href="/do/dismissnotif/' . $notif_id . '">[dismiss]</a>';    
            return $notifbartext; // trophy notifs override mention notifs, but can be dismissed by clicking on them to view the trophy. so we return it immediately to be displayed in the notif bar.
        }   elseif($notiftype == 'mention'){
            if(empty($notifbartext)){
                $notifbartext = '<strong>' . $notifcategories['mention'] . '</strong><br><a href="' . htmlspecialchars($notifdata['link']) . '">[view]</a><a href="/do/dismissnotif/' . $notif_id . '">[dismiss]</a>';
            } else {
                $notifbartext .= '| <strong>' . $notifcategories['mention'] . '</strong><br><a href="' . htmlspecialchars($notifdata['link']) . '">[view]</a><a href="/do/dismissnotif/' . $notif_id . '">[dismiss]</a>';
            }
            if(strlen($notifbartext) > 150){ // if the notif bar text exceeds 100 characters, we will just show the number of notifs instead to prevent overflow. this is a simple solution to the problem of too many notifs, but it works for our purposes.
                $notifbartext = '<marquee>'.$notifbartext.'</marquee>'; // if the notif bar text exceeds 150 characters, we will make it scroll like a marquee to prevent overflow. this is a simple solution to the problem of too many notifs, but it works for our purposes.
            }
        }
        return $notifbartext; // return the formatted notification text to be displayed in the notif bar.
    }       
}

function do_notificationflow(){
    // this function will be called on each page load to check for any notifications for the logged in user and return the formatted notification text to be displayed in the notif bar.
    if(isset($_SESSION['user_id']) && $_SESSION['user_id'] !== null){
        $herewego = do_HandleNotifs(); // this will fetch the notifs and format them for display in the notif bar.
        echo '<div id="notice">'.$herewego.'</div>'; // this will display the notif bar with the formatted notification text.
    } else {
        return null; // no user logged in, so no notifications to display
    }
}

function do_setnotifread($notif_id){
    global $go_sql;
    $stmt = $go_sql->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notif_id);
    $stmt->execute();
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
    //right now, todo: make it do something. in the future
    //this should log somewhere. right now it's going to just sit here
    //as a stub so I can finish adding it to all the /do/ framework files
    // $error is null by default because I feel like most shit is notices
    //we've also added the modlogging option which will be called only
    //mod /do/ actions. 

    $severities = array("Notice", "Warning", "Error");
}

function do_setuserbanned($user_id, $ban_length, $ban_reason){
    global $go_sql;
    $stmt = $go_sql->prepare("UPDATE users SET isbanned = 1, ban_length = ?, ban_reason = ? WHERE id = ?");
    $stmt->bind_param("isi", $ban_length, $ban_reason, $user_id);
    $stmt->execute();

    //L A N K : let a neerdowell know!
    do_sendnotification($user_id, "ban", array("length" => $ban_length, "reason" => $ban_reason));
}

function do_setuserunbanned($user_id){
    global $go_sql;
    $stmt = $go_sql->prepare("UPDATE users SET isbanned = 0, ban_length = 0, ban_reason = '' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    //L A N K : let a neerdowell know!
    do_clearUserBanNotifs($user_id);
    do_sendnotification($user_id, "feedback", array("message" => "You have been unbanned manually by a mod, congrats I guess."));
}

function do_clearUserBanNotifs($user_id){
    global $go_sql;
    $stmt = $go_sql->prepare("DELETE FROM notifications WHERE user_id = ? AND type = 'ban'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

//login flow. core functions and checks to determine login status

function do_logout(){
    //this will log the user out by clearing their session and cookies.
    if(!isset($_SESSION)) {
        session_start();
    }
    session_unset();
    session_destroy();
    setcookie('user_id', '', time() - 3600);
    setcookie('pw_hash', '', time() - 3600);
}

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
