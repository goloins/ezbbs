<?php
/* ezbbs - a simple bbs engine for the small web 
 *
 * Copyleft 2026 by the ezbbs contributors
 * 
 * This is part of the ezbbs test suite. during install, you must delete this folder.
 * The functions referrenced in this file are not protected by any sort of permissions
 * because of the immaturity and alpha status of the software. that being said,
 * 
 *____  _____ _     _____ _____ _____   _____ ___  _     ____  _____ ____  
 *|  _ \| ____| |   | ____|_   _| ____| |  ___/ _ \| |   |  _ \| ____|  _ \ 
 *| | | |  _| | |   |  _|   | | |  _|   | |_ | | | | |   | | | |  _| | |_) |
 *| |_| | |___| |___| |___  | | | |___  |  _|| |_| | |___| |_| | |___|  _ < 
 *|____/|_____|_____|_____|_|_| |_____| |_| _ \___/|_____|____/|_____|_| \_\
 *| __ )| ____|  ___/ _ \|  _ \| ____| | | | / ___|| ____| |                
 *|  _ \|  _| | |_ | | | | |_) |  _|   | | | \___ \|  _| | |                
 *| |_) | |___|  _|| |_| |  _ <| |___  | |_| |___) | |___|_|                
 *|____/|_____|_|   \___/|_| \_\_____|  \___/|____/|_____(_)               
 * 
 * 
 * /tests/adminlogin.php - logs you in as the admin for testing purposes. 
 * We basically set a cookie for the default admin in the sql file.
 * 
 */


include('../init.php');



//set the session cookie for the admin user, which is user ID 1 by default.
$_SESSION['user_id'] = 1;
$_SESSION['username'] = get_UserNameForID(1);
$_SESSION['theme'] = $site['default_theme'];
$_SESSION['isloggedin'] = true;

$admin_user = do_getUserById(1);
if($admin_user) {
	$_SESSION['user'] = array_merge($user, $admin_user);
}

global $islogged;
$islogged = true;

do_sendnotification(1, "feedback", array("message" => "You have been logged in as admin for testing purposes. Please delete the /tests/ folder when you are done testing."));
do_logentry("Notice", "Admin user logged in for testing purposes. Remember to delete the /tests/ folder when done.", $modlog=true);

header('Location: /');