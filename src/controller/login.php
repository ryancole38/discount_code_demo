<?php

    require_once('./module/db.php');
    require_once('./model/user.php');

    class LoginController {

        function __construct() {
            $this->error = '';
            if (isset($_POST['username']) && isset($_POST['password'])) {
                $conn = new DB();
                $username = $_POST['username'];
                $user = User::getByUsername($conn, $username);
                if (!$user) {
                    $this->error = 'Login failed.';
                    return;
                }
                $this->error = 'Login successful';
                if ($user->isArtist) {
                    setcookie('user_id', strval($user->id));
                    header('Location: http://localhost:8000/discount_codes');
                    exit();
                }
                header('Location: http://localhost:8000/cart');
                exit();
            }

        }

        function renderView() {
            require_once('./view/login.php');
        }

    }

?>