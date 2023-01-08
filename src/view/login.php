<?php

class LoginView {

        function getView($error) {
                ob_start();
                ?>
        <div id='login-container'>
                <h2>Log In</h2></br>
                <p class="error"><?php echo $error; ?></p>
                <form action="/login" method="post">
                <input class="input-rounded" type="text" name="username" placeholder="Username"/></br></br>
                <input class="input-rounded" type="password" name="password" placeholder="Password"/></br></br>
                <input class="button" type="submit">
                <form>
        </div>

                <?php
                return ob_get_clean();
        }

}

