<?php

class OrderConfirmationView {

    function __construct() {

    }

    function getView() {
        ob_start();
        ?>
        <p>Insert order confirmation here!</p>
        <?php
        return ob_get_clean();
    }

}

?>