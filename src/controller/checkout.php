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

class SubmitOrderResponse {

    public $success;

    function __construct($success) {
        $this->success = $success;
    }

    function __toString() {
        $response = array("success" => $this->success);
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

        $router->addRoute('/submit', function() {
            echo $this->handleSubmitOrder($_GET);
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
        
        // TODO: these are duplicated from the constructor, I think.
        // Shouldn't need to do these.
        $applicator = new DiscountApplicator($this->cart);
        $processor = new TransactionProcessor($this->cart, null);
        $checkoutView = new CheckoutView($this->cart, null);

        // If code isn't set, return an empty view.
        if (!isset($request['code'])) {
            $checkoutView->transaction = $this->transaction_template;
            $response->success = true;
            $response->view = $checkoutView->getView();
            return $response;
        }

        $applicator->discountCodeString = $request['code'];

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

            // Provide the view with what code we're using
            $checkoutView->discountCode = $lookupResult->discount->codeString;
            $checkoutView->artistName = $lookupResult->artistName;
        } else if ($lookupResult->multipleDiscountOptionsAvailable()) {
            // No match found, but artists found.
            $response->success = true;
            $response->possibleArtists = $lookupResult->possibleArtists;

            // Provide the view with the originally specified discount string
            $checkoutView->discountCode = $lookupResult->providedDiscountString;
            $checkoutView->possibleArtists = $lookupResult->possibleArtists;
        }
        else {
            // No matches are available.
            $checkoutView->error = "No discount code found.";
        }

        $checkoutView->transaction = $processor->process();
        $response->view = $checkoutView->getView();

        return $response;
    }

    public function handleSubmitOrder($request) {
        $conn = new DB();
        $response = new SubmitOrderResponse(false);
        $discount = null; 
        echo var_dump($request);
        if (isset($request['code']) && isset($request['artist'])) {
            $applicator = new DiscountApplicator($this->cart);
            $applicator->discountCodeString = $request['code'];
            $applicator->artistName = $request['artist']; 

            $lookupResult = $applicator->performLookup($conn);
            // The user did not give us a valid discount code / artist combo!
            if (!$lookupResult->discountMatchFound()) {
                echo 'no discount';
                return $response;
            }
            $discount = $lookupResult->discount;
            echo var_dump($discount);
        }
        echo 'got here';

        $processor = new TransactionProcessor($this->cart, $discount);
        $transaction = $processor->process();

        // TODO: add a way to check if transaction was successful
        $transaction->commit($conn);

        $response->success = true;
        return $response;
    }
}

?>