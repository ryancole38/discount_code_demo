<?php

    require_once('./controller/abc_page.php');
    require_once('./module/db.php');
    require_once('./model/user.php');

    class LoginController extends ABCPage {

        function __construct() {
            $this->error = '';
        }

        function setLoggedIn($user) {
            setcookie('user_id', strval($user->id));
        }

        function setLoggedOut() {
            // Set to expire 1 second after epoch so it expires immediately
            setcookie('user_id', '0', 1);
        }

        function handle($args) {
            // TODO: refactor this. This is ugly.
            if (isset($_POST['username']) && isset($_POST['password'])) {
                $conn = new DB();
                $username = $_POST['username'];
                $user = User::getByUsername($conn, $username);
                if (!$user) {
                    $this->error = 'Login failed.';
                    $this->renderView(); // This is stupid. Rework this logic.
                    return;
                }
                $this->setLoggedIn($user);
                if ($user->isArtist) {
                    $this->redirectTo('/discount_codes/admin');
                }
                $this->redirectTo('/checkout');
                // NOTE: redirectTo() should terminate
            } else if (isset($args['action']) && $args['action'] === 'logout') {
                $this->setLoggedOut();
                $this->redirectTo('/login'); // Redirect so the cookie is flushed and user is logged out
            }
            $this->renderView();
        }

        function renderView() {
            require_once('./view/login.php');
            $view = getView($this->error);
            $this->renderBasePage('Login', 'Log in', $view, null);
        }

    }

?>