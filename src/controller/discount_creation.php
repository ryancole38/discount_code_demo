<?php

require_once('./controller/abc_page.php');

class DiscountCreationController extends ABCPage {

    function __construct() {
        $this->codeString = '';
        $this->startDate = '';
        $this->endDate = '';
        $this->discountMessage = '';
        $this->isPercentage = false;
        $this->discountAmount = 0.0;
        $this->timesRedeemable = 0;
        $this->isStackable = false;
        $this->minimumOrderAmount = 0.0;
    }

    public function handle() {
        if ($this->requestIsAsync()) {
            $this->handleAsync();
        }

        $this->renderView();
    }

    public function handleAsync() {

        exit();
    }

    public function renderView() {
        require_once('./view/discount_creation.php');
        $form = renderForm();
        $this->renderBasePage(
            'test', 
            'Create Discount Code', 
            $form, 
            '/discount_codes/admin'
        );
    }

}

?>