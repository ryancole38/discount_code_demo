<?php

abstract class ABCPage {

    public function requireLogin() {

        // If 0 is returned, there is no user login.
        if ($this->getLoggedInUserId() === 0){
            $this->redirectTo('/login');
        }

    }

    // In the real world where a session ID would be
    // used rather than a user ID, you would look
    // up the permissions of the ID rather than query
    // the user itself.
    public function requireArtistLogin($conn) {
        $this->requireLogin();
        $id = $this->getLoggedInUserId();
        $user = User::getById($conn, $id);
        if (!$user->isArtist) {
            $this->redirectTo('/error');
        }
        return $user;
    }

    public function getLoggedInUser($conn) {
        $id = $this->getLoggedInUserId();
        $user = null;
        if ($id > 0) {
            $user = User::getById($conn, $id);
        }
        return $user;
    }

    public function isLoggedIn() {
        return $this->getLoggedInUserId() > 0;
    }

    // Simple method that checks if the cookie for the user ID is set
    // and returns it.  Returns 0 if no user logged in.
    // In the real world, this should probably be a session ID for authentication.
    public function getLoggedInUserId() {
        if (isset($_COOKIE['user_id'])) {
            // TODO: error handling for if the user id is not a valid int
            return intval($_COOKIE['user_id']);
        }
        return 0;
    }

    public function redirectTo($route='/') {
        // TODO: check for valid link
        header(
            sprintf(
                'Location: http://%s%s', 
                $_SERVER['HTTP_HOST'],
                $route
            )
        );
        exit();

    }

    public function requestIsAsync() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    // TODO: remove backLink option. It's not used anymore.
    public function renderBasePage($title, $headerText, $content, $backLink) {
        require_once('./view/abc_page.php');
        // I hate to construct a DB instance. This needs to be refactored.
        // This function and its arguments are unwieldy.
        $conn = new DB();
        $loggedInUser = $this->getLoggedInUser($conn);
        $isArtist = !empty($loggedInUser) ? $loggedInUser->isArtist : false;
        echo ABCPageView::render($title, $headerText, $content, $this->isLoggedIn(), $isArtist);
    }

}

?>