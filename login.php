<?php
require_once 'init.php';

if(do_isLoggedIn()) {
    header('Location: ' . do_getPostLoginRedirect());
    exit();
}

$error_message = '';
$identifier = '';
$remember = false;
$next = do_getPostLoginRedirect();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === '1';

    if($identifier === '' || $password === '') {
        $error_message = 'Username/email and password are required.';
    } elseif(!do_attemptLoginByCredentials($identifier, $password, $remember)) {
        $error_message = 'Invalid login credentials.';
    } else {
        header('Location: ' . $next);
        exit();
    }
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>Login - <?php echo $site['site_name']; ?></title>
        <meta name="description" content="<?php echo $site['site_description']; ?>"/>
        <link rel="icon" type="image/gif" href="<?php echo $site['favicon_url']; ?>" />
        <link rel="stylesheet" type="text/css" media="screen" href="/assets/css/layout.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="/assets/css/<?php echo $user['theme']; ?>.css" />
    </head>
<body class="page-reply desktop-mode">
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
    <h2 id="body_title">Login</h2>

<?php if($error_message !== '') { ?>
    <div id="notice" style="background-color: #ffcccc; color: #cc0000;"><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></div>
<?php } ?>

    <form method="post" action="/login">
        <fieldset>
            <legend>Welcome Back</legend>
            <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>" />
            <label for="identifier">Username or email:</label>
            <input type="text" id="identifier" name="identifier" maxlength="255" style="width:100%;" value="<?php echo htmlspecialchars($identifier); ?>" />

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" maxlength="255" style="width:100%;" />

            <label for="remember" class="inline">Remember me for 30 days</label>
            <input type="checkbox" id="remember" name="remember" value="1"<?php if($remember) { echo ' checked="checked"'; } ?> />

            <br/>
            <input type="submit" value="Login" />
            <a href="/signup">[Need an account?]</a>
        </fieldset>
    </form>
</div>
</body>
</html>
