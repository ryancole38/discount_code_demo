<?php

require_once('./view/checkout.php');
require_once('./router.php');
require_once('./module/db.php');
require_once('./module/transaction_processor.php');
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
        try {
            $conn = new DB();
            $discountCode = $request['code'];

            // If 'artist' is provided, check to see if there is a discount code
            // that matches.
            if (isset($request['artist'])) {
                $artistName = $request['artist'];
                //$discount = DiscountCode::getByArtistAndCode();
            } 
            else {
                // If there isn't an artist, try to see if this code is unique.
                // If it isn't, get all artists who have that code.
                $discountResults = DiscountCode::getAllByCodeString($conn, $discountCode);
                if (count($discountResults) === 1) {
                    // Exactly the code we were looking for.
                    $processor = new TransactionProcessor($this->cart, $discountResults[0]);
                    $checkoutView = new CheckoutView($this->cart, $processor->process());
                    $response->success = true;
                    $response->view = $checkoutView->getView();
                } else {
                    // Returned more than one, return artist names.
                    // Or, returned none so return empty array.
                    $response->success = true;
                    $response->possibleArtists = getArtistsWithDiscountCodeMatching($discountCode);

                }
            }

        } catch (Exception $e) {
            // TODO: log $e
        }
        return $response;
    }

    private function getArtistsWithDiscountCodeMatching($discountCode) {
        $conn = new DB();
        $discounts = ArtistDiscount::getAllByCode($conn, $discountCode);
        // Reduce artist discounts down to just artist names
        $artists = array_map(
            function($artistDiscount) {
                return $artistDiscount->artistName;
            },
            $discounts
        );

        return $artists;
    }

}

?>