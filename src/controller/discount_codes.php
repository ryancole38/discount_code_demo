<?php
    require_once('./controller/abc_page.php');
    require_once('./module/db.php');
    require_once('./model/discount_code.php');
    require_once('./model/user.php');

    class DiscountCodesController extends ABCPage {

        function __construct($matches) {

            $this->matches = $matches;

            $conn = new DB();
            $this->artist = $this->requireArtistLogin($conn);

            $artistId = $this->artist->id;

            $this->discountCodes = DiscountCode::getAllByArtistId($conn, $artistId);
        }

        public function handle() {
            if ($this->requestIsAsync()) {
                $this->handleAsync();
            }

            $this->renderView();
        }

        public function handleAsync() {

            $router = new Router();

            $router->addRoute('/delete', function () {
                if (isset($_GET['id'])) {
                    $success = $this->deleteDiscountCode($_GET['id']);
                    echo json_encode($success);
                }
            });

            $router->run($this->matches[1]);
            exit();
        }

        function renderView() {
            require_once('./view/discount_codes.php');
            $view = getView($this->discountCodes);
            $this->renderBasePage(
                sprintf("%s's Discount Codes", $this->artist->username),
                'Codes',
                $view,
                null
            );
        }

        // Delete the discount code specified by $id if it exists
        // and is owned by the currently authenticated user. Returns 
        // true if successful, otherwise false.
        function deleteDiscountCode($id) {
            $conn = new DB();
            $toDelete = DiscountCode::getById($conn, $id);

            // Can't delete something that doesn't exist
            if (!$toDelete) {
                return false;
            }

            // If the logged in user does not own the discount code,
            // then they should not be able to delete it.
            if ($toDelete->artistId !== $this->getLoggedInUserId()) {
                return false;
            }

            // TODO: look into how SQL results can be used to tell
            // if there was an error.
            //$toDelete->delete($conn);

            // Return true to indicate success.
            return true;
        }
    }

?>