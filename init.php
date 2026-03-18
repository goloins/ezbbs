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


//User defaults, these are used for non-logged in users, and for new users. Change them if you want to customize the default user experience. 
$user = array(
    'theme' => 'default',
    'isadmin' => false,
    'defaultlocation' => "Somewhere on the internet",
    'defaultbio' => "This user prefers to keep an air of mystery about them.",
    'defaultavatar' => 'assets/png/default_avatar.png',
    'awards' => array(), // Array of award ids in the awards table that the user has received
    'isbanned' => false, // default on new users, but once banned will show bars on the profile pic.
    'ismoderator' => array(), // Array of category ids that the user is a moderator of
    'defaultsignature' => "", 
    'sigbanners' => array(), // Array of ids in the sigbanners table that the user has chosen to display in their signature
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
    'userirchandleandnet' => '' //for example john@irc.mycool.net
);

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