<?php

class HomeView {

    function __construct() {

    }

    function getView() {
        ob_start();
        ?>

        <p>Insert Merch Here</p>

        <?php
        return ob_get_clean();
    }
}

?>