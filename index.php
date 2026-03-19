<?php
/* ezbbs - a simple bbs engine for the small web 
 *
 * Copyleft 2026 by the ezbbs contributors
 * 
 * index.php - main page of the site, showing all threads from all cats
 * 
 */



require_once 'init.php';
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
		<title>Latest threads — <?php echo $site['site_name'];?></title>
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
foreach($homepagemenu as $menu_item) {
    echo '<li><a href="' . $menu_item['url'] . '">' . $menu_item['name'] . '</a></li>';
}
?>
</ul>
</div>
<div id="body_wrapper">
    	<h2 id="body_title">
		<?php echo do_determineCurrentPageorCat(); ?>	</h2>

        <table><thead>
            <tr> <th class="headline">Headline</th> 
            <th class="minimal author">Author</th> 
            <th class="minimal replies">Replies</th> 
            <th class="minimal visits">Visits</th> 
            <th class="minimal last_bump_▼">Last bump ▼</th>
            <th class="minimal category">Category</th></tr></thead>
            <tbody><tr class="">
            <?php $topics = do_getTopics('all');?>
            <?php
            while($topic = $topics->fetch_assoc()) {

                //prepare username flair and colors 
                $poster = do_getUserById($topic['poster_id']);
                $posterName = $poster['username'];
                $posterProfileLink = '/user/' . $poster['id'];
                $posterUsernameColor = isset($poster['usernamecolor']) ? $poster['usernamecolor'] : $user['usernamecolor'];
                $posterUsernameStyle = isset($poster['usernamestyle']) ? $poster['usernamestyle'] : $user['usernamestyle'];
                $posterisBanned = $poster['isbanned']; // adds a red strikethrough to their name and an * on either side
                $posterKudos = $poster['userkudos']; // in parentheses next to their name
                $posterIsAdmin = $poster['isadmin']; // adds a little [A] next to their name
                $posterBio = $poster['defaultbio']; // in tooltip

                //prep category info
                $categoryName = isset($categories[$topic['category_id']]) ? $categories[$topic['category_id']] : 'Uncategorized';
                $categoryLink = '/cat/' . $topic['category_id'];

                //checking thread conditions
                $isLemoned = $topic['isLemoned']; //adds a lemon icon.
                $isParty = chk_ThreadParty($topic['id']); //adds party hat
                $isArchived = chk_ThreadArchived($topic['id']); //shows a tombstone and prevents replies
                $isLocked = chk_ThreadLocked($topic['id']); //shows a lock icon and prevents replies
                $isChanlike = chk_ThreadChanlike($topic['id']); //if under limit, shows fire icon. if over, hides.

                echo '<tr class="">';
                ?>
                <td class="topic_headline">
                    <a class="" title="Topic text preview in a tooltip" 
                    href="/topic/68467">Topic title, affected by poster conditions</a></td>
                    <td class="minimal"><strong>Poster Name</strong></td>
                    <td class="minimal"><strong>Number of Replies (int)</strong></td>
                    <td class="minimal">Number of Views (int)</td>
                    <td class="minimal"><span class="help" title="Full UTC Date complete with stool sample">Time ago</span></td>
                    <td class="minimal"><a href="/cat/1">Category name</a></td>
            </tr>
        <!-- each odd tr will have the class "odd" for styling purposes-->
        </tbody></table>