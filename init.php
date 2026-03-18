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




// Category defaults - these are used for the categories on the site, more can be added in the database. It will start with these. 
$categories = array(1 => "General", 2 => "Sports", 3 => "Technology", 4 => "Gaming", 5 => "Music", 6 => "Miscellaneous", 7=> "Meta");


// Default Awards - any additional awards can be added to the awards table in the database, but these are the defaults that come with the site
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