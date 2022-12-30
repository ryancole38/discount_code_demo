<?php
require_once('./router.php');
require_once('./module/db.php');
require_once('./controller/discount_codes.php');
require_once('./controller/code_detail.php');
require_once('./controller/checkout.php');
require_once('./controller/login.php');

$router = new Router();

// The function given for a route will be given two arguments:
// $parsed_params: The URL parameters provided parsed into a key:value array
// $matches: The regex matches for the provided route, that start at index 1.
//      Index 0 is the string that matched the pattern, which is the whole route. 

$router->addRoute('/discount_codes/admin', function() {
    $page = new DiscountCodesController();
    $page->renderView();
});

$router->addRoute('/discount_codes/code', function($args) {
    $page = new CodeDetailController($args);
    $page->renderView();
});

$router->addRoute('/checkout', function() {
    $page = new CheckoutController();
    $page->handle();
});

$router->addRoute('/login', function() {
    $page = new LoginController();
    $page->renderView();
});

$router->run();

?>