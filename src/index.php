<?php
require_once('./router.php');
require_once('./module/db.php');
require_once('./controller/discount_codes.php');
require_once('./controller/code_detail.php');
require_once('./controller/discount_creation.php');
require_once('./controller/checkout.php');
require_once('./controller/order_confirmation.php');
require_once('./controller/login.php');

$router = new Router();

// The function given for a route will be given two arguments:
// $parsed_params: The URL parameters provided parsed into a key:value array
// $matches: The regex matches for the provided route, that start at index 1.
//      Index 0 is the string that matched the pattern, which is the whole route. 

$router->addRoute('/discount_codes/admin(/[a-zA-Z]+)?', function($args, $matches) {
    $page = new DiscountCodesController($matches);
    $page->handle();
});

$router->addRoute('/discount_codes/code', function($args) {
    $page = new CodeDetailController($args);
    $page->renderView();
});

$router->addRoute('/discount_codes/create(/[a-zA-Z]+)?', function($args, $matches) {
    $page = new DiscountCreationController($matches);
    $page->handle();
});

$router->addRoute('/checkout(/[a-zA-Z]+)?', function($args, $matches) {
    $page = new CheckoutController($matches);
    $page->handle();
});

$router->addRoute('/order_confirmation', function() {
    $page = new OrderConfirmationController();
    $page->handle();
});

$router->addRoute('/login', function($args) {
    $page = new LoginController();
    $page->handle($args);
});

$router->run();

?>