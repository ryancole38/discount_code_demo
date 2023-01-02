<?php

require_once('./controller/abc_page.php');
require_once('./module/db.php');
require_once('./model/user.php');
require_once('./model/discount_code.php');
require_once('./model/merch_type.php');

class CodeDetailController extends ABCPage {

    public $artistId;
    public $discountCode;
    public $merchTypes;

    function __construct($query_params) {

        $conn = new DB();
        $this->requireArtistLogin($conn);

        if (!isset($query_params['code'])) {
            echo 'No code provided';
            exit();
        }
        $code = $query_params['code'];
        $this->artistId = $this->getLoggedInUserId();
        
        $discountCode = DiscountCode::getCodeByArtistAndCode($conn, $this->artistId, $code);
        if (!$discountCode) {
            echo 'No code found??';
            exit();
        }

        $this->merchTypes = MerchType::getAllMerchTypes($conn);

        $this->discountCode = $discountCode;

    }

    public function renderView() {
        require_once('./view/code_detail.php');
        $view = new CodeDetailView(
            $this->discountCode, 
            $this->artistId, 
            $this->merchTypes
        );
        $content = $view->getView();
        $this->renderBasePage('Code Details', 'Code Details', $content, '/discount_codes/admin');
    }

}

?>