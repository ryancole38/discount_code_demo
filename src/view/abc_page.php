<?php

class ABCPageView {

    function __construct() {

    }

    static function renderLink($text, $link) {
        return sprintf('<a href="http://%s%s">%s</a>',
            $_SERVER['HTTP_HOST'],
            $link,
            $text 
        );
    }

    static function getLogInOrLogOutLink($isLoggedIn) {
        if($isLoggedIn) {
            return ABCPageView::renderAccountDropdown();
        }
        return ABCPageView::renderLink('Log In', '/login');
    }

    static function renderAccountDropdown() {
        return ABCPageView::renderLink('Log out', '/login?action=logout');
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
        <div class='grid-container'>
            <div class='nav'>
                <ul>
                    <li><?php echo ABCPageView::renderLink('Home', '/'); ?></li>
                    <li><?php echo ABCPageView::renderLink('Merch', '/'); ?><li>
                    <li class="nav-right">
                        <?php echo ABCPageView::getLoginOrLogOutLink($loggedIn)?>
                    </li>
                    <li class="nav-right">
                        <?php echo ABCPageView::renderLink('Cart', '/checkout'); ?>
                    </li>
                </ul>
            </div>
            <div class='view'>
                <h2><?php echo $headerText; ?></h2>
                <div id='content' class='content'>
                    <?php echo $content;?>
                </div>
            </div>
            <div class='footer'>
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