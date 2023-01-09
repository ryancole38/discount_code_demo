<?php

require_once('./view/checkout.php');
require_once('./router.php');
require_once('./module/db.php');
require_once('./module/transaction_processor.php');
require_once('./module/discount_applicator.php');
require_once('./controller/abc_page.php');
require_once('./model/cart.php');
require_once('./model/discount_code.php');
require_once('./model/artist_discount.php');

class ApplyDiscountResponse {

    public $success;
    public $possibleArtists;
    public $view;

    function __construct($success) {
        $this->success = $success;
        $this->possibleArtists = [];
        $this->view = '';
    }

    function __toString() {
        $response = array("success" => $this->success);
        if ($this->success) {
            $response['view'] = $this->view;
        } 
        else {
            $response['possibleArtists'] = $this->possibleArtists;
        }
        return json_encode($response);
    }
}

class CheckoutController extends ABCPage {

    public $cart;

    function __construct($matches) {

        $this->requireLogin();
        $userId = $this->getLoggedInUserId();
        $conn = new DB();

        $this->matches = $matches;
        $this->cart = Cart::getCartByUserId($conn, $userId); 

        $processor = new TransactionProcessor($this->cart, null);

        $this->transaction_template = $processor->process();

    }

    public function handleAsync() {
        $router = new Router();

        $router->addRoute('/applydiscount', function() {
            echo $this->handleApplyDiscount($_GET);
        });

        $router->run($this->matches[1]);
        exit();

    }

    public function handle() {
        // I don't like this. handleAsync() should never return
        // since it calls exit, and this isn't explicitly clear.
        if($this->requestIsAsync()){
            $this->handleAsync();
        }
        $view = new CheckoutView($this->cart, $this->transaction_template);
        $contents = $view->getView();
        
        $this->renderBasePage('Checkout', 'Review Order', $contents, null);
    }

    public function handleApplyDiscount($request) : ApplyDiscountResponse {
        $response = new ApplyDiscountResponse(false);
        $conn = new DB();

        $applicator = new DiscountApplicator($this->cart);
        $applicator->discountCodeString = $request['code'];
        $processor = new TransactionProcessor($this->cart, null);
        $checkoutView = new CheckoutView($this->cart, null);

        // If 'artist' is provided, check to see if there is a discount code
        // that matches.
        if (isset($request['artist'])) {
            $applicator->artistName = $request['artist'];                
        } 

        $lookupResult = $applicator->performLookup($conn);

        if ($lookupResult->discountMatchFound()) {
            // Match found, set results.
            $response->success = true;
            $processor->discount = $lookupResult->discount;
        } else if ($lookupResult->multipleDiscountOptionsAvailable()) {
            // No match found, but artists found.
            $response->success = true;
            $response->possibleArtists = $lookupResult->possibleArtists;
        }
        else {
            // No matches are available.
            $checkoutView->error = "No discount code found.";
        }

        $checkoutView->transaction = $processor->process();
        $response->view = $checkoutView->getView();

        return $response;
    }
}

?>