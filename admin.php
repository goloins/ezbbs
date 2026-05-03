<?php
require_once 'init.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
    header('Location: /');
    exit();
}

if(!chk_IsUserAdmin($_SESSION['user_id'])) {
    http_response_code(403);
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>Admin - <?php echo $site['site_name']; ?></title>
        <meta name="description" content="<?php echo $site['site_description']; ?>"/>
        <link rel="icon" type="image/gif" href="<?php echo $site['favicon_url']; ?>" />
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/layout.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="assets/css/<?php echo $user['theme']; ?>.css" />
    </head>
<body class="page-index desktop-mode">
    <h1 class="top_text" id="logo">
        <a rel="index" href="/" class="help_cursor" title="<?php echo fun_getslogan(); ?>"><?php echo $site['site_name']; ?></a>
    </h1>
<ul id="main_menu" class="menu">
<?php
foreach(do_getHomePageMenu() as $menu_item) {
    echo '<li><a href="' . $menu_item['url'] . '">' . $menu_item['name'] . '</a></li>';
}
?>
</ul>

<div id="body_wrapper">
    <h2 id="body_title">Admin</h2>
<?php if(!chk_IsUserAdmin($_SESSION['user_id'])) { ?>
    <p class="unimportant">You do not have permission to view admin tools.</p>
<?php } else { ?>
    <p class="unimportant">Admin tools are not implemented yet.</p>
<?php } ?>
</div>

<div id="footer">
    <br/><div style="text-align:center" class="unimportant">
        <span><?php echo $site['disclaimer']; ?></span><br/><span>ezbbs, bby. <a href="https://github.com/goloins/ezbbs">Contribute on GitHub or get your own copy!</a></span></div><br/>
</div>
</body>
</html>
