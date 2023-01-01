<?php

require_once('./controller/abc_page.php');
require_once('./module/db.php');
require_once('./model/user.php');
require_once('./model/discount_code.php');

class CodeDetailController extends ABCPage {

    function __construct($query_params) {
        $this->requireLogin();

        if (!isset($query_params['code'])) {
            echo 'No code provided';
            exit();
        }
        $code = $query_params['code'];
        $artistId = $this->getLoggedInUserId();

        $conn = new DB();
        
        $artist = User::getById($conn, $artistId);
        if(!$artist || !$artist->isArtist) {
            echo 'Not artist!!';
            exit();
        }

        $discount_code = DiscountCode::getCodeByArtistAndCode($conn, $artistId, $code);
        if (!$discount_code) {
            echo 'No code found??';
            exit();
        }

        $this->discount_code = $discount_code;

    }

    public function renderView() {
        require_once('./view/code_detail.php');
        $view = new CodeDetailView($this->discount_code);
        $content = $view->getView();
        $this->renderBasePage('Code Details', 'Code Details', $content, '/discount_codes/admin');
    }

}

?>