<?php
/* ezbbs - a simple bbs engine for the small web 
 *
 * Copyleft 2026 by the ezbbs contributors
 * 
 * user.php - main page of the site, showing all threads from all cats
 * 
 */

if(!isset($_GET['id'])) {
    header('Location: /404/user'); //specific 404 page for users, so we can say "user not found" instead of just "page not found"
    exit();
}


require_once 'init.php';
//now we get all the user shit

$userId = intval($_GET['id']);
$profileUser = do_getUserById($userId);
if(!$profileUser) {
    header('Location: /404/user'); //specific 404 page for users, so we can say "user not found" instead of just "page not found"
    exit();
}

//if it's the admin, send them to the admin page.
if($userId == 1) {
    header('Location: /admin');
    exit();
}

//so we should now have all of the user info.

?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<title>Latest threads — <?php echo 'Profile for ' . $profileUser['username'] . ' — ' . $site['site_name']; ?></title>
                    <meta name="description" content="<?php echo $site['site_description'];?>"/>
                <link rel="icon" type="image/gif" href="<?php echo $site['favicon_url'];?>" />
		<link rel="stylesheet" type="text/css" media="screen" href="assets/css/layout.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="assets/css/<?php echo $user['theme'];?>.css" />
		<link rel="stylesheet" type="text/css" media="screen" href="assets/css/vs.css" />
		        <link rel="canonical" href="<?php echo $site['site_url'];?>" />
		</head>
<body class="page-index desktop-mode">	<h1 class="top_text" id="logo">
		<a rel="index" href="/" class="help_cursor" title="<?php echo fun_getslogan();?>"><?php echo $site['site_name'];?></a></h1>
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
        <?php 
        $fullusername = "";
        if(isset($profileUser['usernamecolor'])) {
            $fullusername .= '<span style="color:' . $profileUser['usernamecolor'] . '">';
        }
        if(isset($profileUser['usernamestyle'])) {
            $fullusername .= '<span style="' . $profileUser['usernamestyle'] . '">';
        }
        $fullusername .= $profileUser['username'];
        if(isset($profileUser['usernamestyle'])) {
            $fullusername .= '</span>';
        }
        if(isset($profileUser['usernamecolor'])) {
            $fullusername .= '</span>';
        }
        echo 'Profile for ' . $fullusername;
        //now to figure out where to put their awards
        //maybe create a div with class "user_awards" and then put the awards in there as img tags with title attributes for the tooltip?
        //yeah we'll roll with that.
        //todo: user_awards css should probably fix this box to a certain size and 
        ?>
    <div class="user_awards">
        <?php 
        $awards = do_getAwardsByUserId($profileUser['id']);
        foreach($awards as $award) {
            echo '<img src="' . $award['image_url'] . '" alt="' . $award['name'] . '" title="' . $award['description'] . '"/>';
        }
        ?>
        </div>

</h2>
</div>
</html>