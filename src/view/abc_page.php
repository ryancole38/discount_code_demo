<?php

function renderLink($text, $link) {
    return sprintf('<a href="http://%s%s">%s</a>',
        $_SERVER['HTTP_HOST'],
        $link,
        $text 
    );
}

function getLogInOrLogOutLink($isLoggedIn) {
    if($isLoggedIn) {
        return renderLink('Log out', '/login?action=logout');
    }
    return renderLink('Log In', '/login');
}

function render($title, $headerText, $content, $loggedIn, $backLink='') {
    ob_start();
    ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo $title;?></title>
        <link rel="stylesheet" href="/static/css/style.css"/>
    </head>
    <body>
        <header>
            <div id='nav-bar'>
                <ul>
                    <li>Hello, world</li>
                </ul>
                <ul>
                    <li><?php echo getLoginOrLogOutLink($loggedIn)?></li>
                </ul>
            </div>
        </header>
        <div id='content-container'>
            <div id='content'>
                <div id='view'>
                    <h2><?php echo $headerText; ?></h2>
                    <?php
                    if (!empty($backLink)) {
                        echo renderLink('<< Back', $backLink);
                    }
                    ?>
                    </br></br>
                    <?php echo $content;?>
                </div>
            </div>
        </div>
    </body>
</html> 

    <?php
    return ob_get_clean();
}

?>