<?php

function getView($error) {
        ob_start();
        ?>
        <?php echo $error;?>
        <form action="/login" method="post">
        Username: <input type="text" name="username"/><br>
        Password: <input type="password" name="password"/><br>
        <input type="submit">
        <form>

        <?php
        return ob_get_clean();
}