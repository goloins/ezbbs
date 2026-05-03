<?php
require_once 'init.php';

$kind = isset($_GET['kind']) ? $_GET['kind'] : 'page';
$messages = array(
    'thread' => 'That thread could not be found.',
    'user' => 'That user profile could not be found.',
    'page' => 'That page could not be found.'
);

$message = isset($messages[$kind]) ? $messages[$kind] : $messages['page'];
http_response_code(404);
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>Not Found — <?php echo $site['site_name']; ?></title>
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
        <h2 id="body_title">Not Found</h2>
        <p><?php echo htmlspecialchars($message); ?></p>
        <p><a href="/">Return to the homepage</a></p>
    </div>

    <div id="footer">
        <br/><div style="text-align:center" class="unimportant">
            <span><?php echo $site['disclaimer']; ?></span><br/><span>ezbbs, bby. <a href="https://github.com/goloins/ezbbs">Contribute on GitHub or get your own copy!</a></span></div><br/>
        <noscript><br /><span class="unimportant">Note: Your browser's JavaScript is disabled; some site features may not fully function, but don't worry, we're trying to get rid of all the js :^)</span></noscript>
    </div>
</body>
</html>