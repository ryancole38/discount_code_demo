<?php

require_once('./router.php');
require_once('./module/db.php');
require_once('./controller/abc_page.php');
require_once('./model/cart.php');

class CheckoutController extends ABCPage {

    public $cart;

    function __construct($matches) {

        $this->requireLogin();
        $userId = $this->getLoggedInUserId();
        $conn = new DB();

        $this->matches = $matches;
        $this->cart = Cart::getCartByUserId($conn, $userId); 

    }

    public function handleAsync() {
        $router = new Router();

        $router->addRoute('/applydiscount', function() {
            echo 'discount';
        });

        $router->run($this->matches[1]);
        exit();

    }

    public function handle() {
        if($this->requestIsAsync()){
            $this->handleAsync();
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            require_once('./view/checkout.php');
            $view = new CheckoutView($this->cart);
            $contents = $view->getView();
            
            $this->renderBasePage('Checkout', 'Review Order', $contents, null);
        }
    }

}

?>