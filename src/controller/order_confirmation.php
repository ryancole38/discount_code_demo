<?php
require_once('./controller/abc_page.php');
require_once('./view/order_confirmation.php');

class OrderConfirmationController extends ABCPage {

    function __construct() {

    }

    function handle() {
        $view = (new OrderConfirmationView())->getView();
        $this->renderBasePage('Order Confirmation', '', $view, null);
    }

}

?>