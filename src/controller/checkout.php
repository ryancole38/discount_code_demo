<?php

require_once('./module/db.php');
require_once('./controller/abc_page.php');
require_once('./model/cart.php');

class CheckoutController extends ABCPage {

    public $cart;

    function __construct() {

        $this->requireLogin();
        $userId = $this->getLoggedInUserId();
        $conn = new DB();

        $this->cart = Cart::getCartByUserId($conn, $userId); 

    }

    public function handle() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'){
            echo 'Hello from ajax!';
            exit();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require_once('./view/checkout.php');
        }
    }

}

?>