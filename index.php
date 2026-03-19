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
    	<h2 id="body_title"> <!-- all above is universal header and menu, now we get to the page specific content. -->
		<?php echo do_determineCurrentPageorCat(); ?>	</h2>

        <table><thead>
            <tr> 
            <th class="minimal"></th> <!-- for thread icons -->
            <th class="headline">Headline</th> 
            <th class="minimal author">Author</th> 
            <th class="minimal replies">Replies</th> 
            <th class="minimal visits">Visits</th> 
            <th class="minimal last_bump_▼">Last bump ▼</th>
            <th class="minimal category">Category</th></tr></thead>
            <tbody><tr class="">
            <?php $topics = do_getTopics('all');?>
            <?php
            $counter = 0;
            while($topic = $topics->fetch_assoc()) {
                $counter++;
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

                //checking thread conditions. this can be improved by checking these conditions when fetching the topic bools
                $isLemoned = chk_ThreadLemoned($topic['id']); //adds a lemon icon.
                $isParty = chk_ThreadParty($topic['id']); //adds party hat
                $isArchived = chk_ThreadArchived($topic['id']); //shows a tombstone and prevents replies
                $isLocked = chk_ThreadLocked($topic['id']); //shows a lock icon and prevents replies
                $isChanlike = chk_ThreadChanlike($topic['id']); //if under limit, shows fire icon. if over, hides.

                //more thread metadata
                $replies = $topic['reply_count'];
                $visits = $topic['visit_count']; //todo: track in three columns based on time to check hotness.

                $lastReplyTime = $topic['last_bump']; 
                $formattedLastReplyTime = date('Y-m-d H:i:s', $lastReplyTime); //for tooltip
                $timeAgo = fun_timeAgo($lastReplyTime); //for display


                //determine even or odd for styling purposes, and then output the row with all the prepared data.
                $rowClass = ($counter % 2 == 0) ? '' : 'odd';  //even doesn't need to be specified, but odd gets the "odd" class for styling.

                echo '<tr class="' . $rowClass . '">';
                ?>
                <td class="minimal">
                    <?php 
                    if($isLemoned) {
                        echo '<span class="help" title="This thread is lemoned, meaning you must have been posting for '.$site['default_lemon_years'].' years to reply."><img src="/assets/icons/lemon.png" alt="Lemon icon" /></span>';
                    }
                    if($isParty) {
                        echo '<span class="help" title="This thread is a party thread, meaning it is special and may have unique rules or content."><img src="/assets/icons/partyhat.png" alt="Party hat icon" /></span>';
                    }
                    if($isArchived) {
                        echo '<span class="help" title="This thread is archived, meaning it is closed for new replies (old/chanlike) but can still be viewed."><img src="/assets/icons/tombstone.png" alt="Tombstone icon" /></span>';
                    }
                    if($isLocked) {
                        echo '<span class="help" title="This thread is locked, meaning it cannot be replied to (because we said so) but can still be viewed."><img src="/assets/icons/lock.png" alt="Lock icon" /></span>';
                    }
                    if($isChanlike) {
                        echo '<span class="help" title="This thread is chanlike, meaning it will be removed from the homepage after ' . $site['chanlike_reply_limit'] . ' replies but can still be viewed."><img src="/assets/icons/fire.png" alt="Fire icon" /></span>';
                    }
                    ?>
                <td class="topic_headline">
                    <a class="" title="<?php echo substr(htmlspecialchars($topic['title']), 0, $site['topic_preview_length']); ?>" 
                    href="/topic/<?php echo $topic['id']; ?>">
                    <?php 
                    $topicPrefix = "";
                    $topictitle = substr(htmlspecialchars($topic['title']), 0, $site['topic_headline_length']);
                    if(chk_PosterisBanned($topic['poster_id'])) {
                        $topicPrefix = '<b><font color="red">' . $site['topic_poster_banned_prefix'] . '</font></b>';
                    }
                    $topictitle = $topicPrefix . $topictitle;
                    echo $topictitle;
                    ?></a></td>

                    <td class="minimal">
                    <?php

                    //special little poster flair setup.

                    $posterOpeningTags = '';
                    $posterClosingTags = '';

                    //adding admin suffix if poster is admin
                    $posterDisplayName = $posterName;
                    if($posterIsAdmin) {
                        $posterDisplayName .= $site['admin_suffix'];
                    }
                    //formatting all the fancy colors and styles for the poster name
                    if($posterUsernameColor) {
                        $posterOpeningTags .= '<span style="color:' . htmlspecialchars($posterUsernameColor) . '">';
                        $posterClosingTags .= '</span>' . $posterClosingTags;
                    }
                    switch($posterUsernameStyle){  
                        case $posterUsernameStyle == 'bold':
                            $posterOpeningTags .= '<b>';
                            $posterClosingTags = '</b>' . $posterClosingTags;
                            break;
                        case $posterUsernameStyle == 'italic':
                            $posterOpeningTags .= '<i>';
                            $posterClosingTags = '</i>' . $posterClosingTags;
                            break;
                        case $posterUsernameStyle == 'underline':
                            $posterOpeningTags .= '<u>';
                            $posterClosingTags = '</u>' . $posterClosingTags;
                            break;
                    }
                    $postertruename = $posterOpeningTags . htmlspecialchars($posterDisplayName) . $posterClosingTags;
                    if($posterBio) {
                        $postertruename = '<span class="help" title="' . htmlspecialchars($posterBio) . '">' . $postertruename . '</span>';
                    }
                    echo '<a href="' . $posterProfileLink . '">' . $postertruename . '</a>';
                    ?>
                    </td>
                    <td class="minimal"><strong><?php echo $replies; ?></strong></td>
                    <td class="minimal"><?php echo $visits; ?></td>
                    <td class="minimal"><span class="help" title="<?php echo $formattedLastReplyTime; ?>"><?php echo $timeAgo; ?></span></td>
                    <td class="minimal"><a href="/cat/<?php echo $topic['category_id']; ?>">in <?php echo $topic['category_name']; ?></a></td>
            </tr><?php } ?>
        </tbody></table>

        <li><span class="reply_id unimportant"><a href="#top">[Top]</a></span></li></ul>

<script type="text/javascript">
    window.ignore_list = false;
</script>

</div><div id="footer">

<br/><div style="text-align:center" class="unimportant">
    <span><?php echo $site['disclaimer']; ?></span><br/><span>ezbbs, bby. <a href="https://github.com/goloins/ezbbs">Contribute on GitHub or get your own copy!</a></span></div><br/>
    		<noscript><br /><span class="unimportant">Note: Your browser's JavaScript is disabled; some site features may not fully function, but don't worry, we're trying to get rid of all the js :^)</span></noscript>
	<div id="quotePreview"></div>
    </div>
    
</body>
</html>
