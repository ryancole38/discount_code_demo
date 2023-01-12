<?php

class ErrorView {

    function __construct() {

    }

    function getView() {
        ob_start();
        ?>

        <p>An error occurred! This page probably doesn't exist.</p>

        <?php
        return ob_get_clean();
    }
}

?>