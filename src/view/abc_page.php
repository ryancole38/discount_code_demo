<?php

class ABCPageView {

    static function renderLink($text, $link) {
        return sprintf('<a href="http://%s%s">%s</a>',
            $_SERVER['HTTP_HOST'],
            $link,
            $text 
        );
    }

    static function getLogInOrLogOutLink($isLoggedIn) {
        if($isLoggedIn) {
            return ABCPageView::renderLink('Log out', '/login?action=logout');
        }
        return ABCPageView::renderLink('Log In', '/login');
    }

    static function render($title, $headerText, $content, $loggedIn, $backLink='') {
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
                    <li><?php echo ABCPageView::renderLink('Home', '/'); ?></li>
                </ul>
                <ul>
                    <li><?php echo ABCPageView::renderLink('Merch', '/'); ?><li>
                </ul>
                <ul>
                    <li class="nav-right">
                        <?php echo ABCPageView::getLoginOrLogOutLink($loggedIn)?>
                    </li>
                </ul>
                <ul>
                    <li class="nav-right">
                        <?php echo ABCPageView::renderLink('Cart', '/checkout'); ?>
                    </li>
                </ul>
            </div>
        </header>
        <div id='content-body'>
            <div id='content-container'>
                <div id='content'>
                    <h2><?php echo $headerText; ?></h2>
                    <?php
                    if (!empty($backLink)) {
                        echo ABCPageView::renderLink('<< Back', $backLink);
                    }
                    ?>
                    </br></br>
                    <div id='view'>
                        <?php echo $content;?>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="/static/js/events.js"></script>
</html> 

        <?php
        return ob_get_clean();
    }
}






?>