<?php
require_once('./view/discount_creation.php');
require_once('./view/abc_page.php');
class CodeDetailView extends DiscountCreationView {

    function __construct($discountCode, $artistId, $merchTypes){
        parent::__construct($artistId, $merchTypes);
        $this->discountCode = $discountCode;
        $this->discountId = $discountCode->id;
    }

    function getView() {
        $form = parent::getView();
        $backButton = ABCPageView::renderLink('<< Back', '/discount_codes/admin');
        return $form . '</br>' . $backButton;
    }
}
?>