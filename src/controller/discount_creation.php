<?php

require_once('./router.php');
require_once('./controller/abc_page.php');
require_once('./module/db.php');
require_once('./model/merch_type.php');
require_once('./model/discount_code.php');

class DiscountCreationController extends ABCPage {

    function __construct($matches) {

        $this->matches = $matches;

        $conn = new DB();
        $this->merchTypes = MerchType::getAllMerchTypes($conn);
    }

    // Handle request
    public function handle() {
        if ($this->requestIsAsync()) {
            $this->handleAsync();
        }

        $this->renderView();
    }

    // Handle an async request made from Javascript. A view is not
    // expected in return.
    public function handleAsync() {

        $router = new Router();

        $router->addRoute('/creatediscount', function () {
            $this->createDiscount($_GET);
        });

        $router->run($this->matches[1]);
        exit();
    }

    // Render and return the view for this page.
    public function renderView() {
        $conn = new DB();
        $this->requireArtistLogin($conn);
        require_once('./view/discount_creation.php');

        $view = new DiscountCreationView(
            $this->getLoggedInUserId(),
            $this->merchTypes
        );

        $form = $view->getView();
        $this->renderBasePage(
            'Create Discount Code', 
            'Create Discount Code', 
            $form, 
            '/discount_codes/admin'
        );
    }

    // Takes the JSON object from a create discount request and uses
    // it to create a new discount code entry in the database.
    // Returns true on success, otherwise false.
    public function createDiscount($values) {

        $conn = new DB();
        $code = DiscountCode::constructFromPublicJsonValues($values);

        if ($code == null) {
            return false;
        }

        // If there is an artistId specified, make sure it's the current user's.
        // This prevents them from creating/updating another users' codes. 
        if ($code->artistId !== 0 && $code->artistId !== $this->getLoggedInUserId()){
            return false;
        }

        // TODO: add authentication for creating global codes (artistID = 0)

        $code->commit($conn);
        return true;

    }

}

?>