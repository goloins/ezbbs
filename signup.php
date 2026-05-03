<?php
require_once 'init.php';

if(do_isLoggedIn()) {
    header('Location: ' . do_getPostLoginRedirect());
    exit();
}

$error_message = '';
$username = '';
$email = '';
$next = do_getPostLoginRedirect();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    if($username === '' || $email === '' || $password === '' || $password_confirm === '') {
        $error_message = 'All fields are required.';
    } elseif(!preg_match('/^[a-zA-Z0-9_]{3,32}$/', $username)) {
        $error_message = 'Username must be 3-32 chars and use letters, numbers, or underscores.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif(strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } elseif($password !== $password_confirm) {
        $error_message = 'Password confirmation does not match.';
    } else {
        if(!do_insertNewUser($username, $password, $email)) {
            $error_message = 'Could not create account. Username may already be taken.';
        } else {
            $new_user_id = intval($go_sql->insert_id);
            if(!do_loginUser($new_user_id, false)) {
                $error_message = 'Account created, but login failed. Please log in manually.';
            } else {
                header('Location: ' . $next);
                exit();
            }
        }
    }
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id="top">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>Sign Up - <?php echo $site['site_name']; ?></title>
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
    <h2 id="body_title">Create Account</h2>

<?php if($error_message !== '') { ?>
    <div id="notice" style="background-color: #ffcccc; color: #cc0000;"><strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?></div>
<?php } ?>

    <form method="post" action="/signup">
        <fieldset>
            <legend>Join EzBBS</legend>
            <input type="hidden" name="next" value="<?php echo htmlspecialchars($next); ?>" />
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" maxlength="32" style="width:100%;" value="<?php echo htmlspecialchars($username); ?>" />

            <label for="email">Email:</label>
            <input type="text" id="email" name="email" maxlength="255" style="width:100%;" value="<?php echo htmlspecialchars($email); ?>" />

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" maxlength="255" style="width:100%;" />

            <label for="password_confirm">Confirm password:</label>
            <input type="password" id="password_confirm" name="password_confirm" maxlength="255" style="width:100%;" />

            <br/>
            <input type="submit" value="Create Account" />
            <a href="/login">[Already have an account?]</a>
        </fieldset>
    </form>
</div>
</body>
</html>
