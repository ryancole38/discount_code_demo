<?php


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Login</title>
    </head>
    <body>
        <?php
        echo $this->error;
        ?>
        <form action="/login" method="post">
        Username: <input type="text" name="username"/><br>
        Password: <input type="password" name="password"/><br>
        <input type="submit">
        <form>
    </body>
</html>