<?php
    require_once('./controller/abc_page.php');
    require_once('./module/db.php');
    require_once('./model/discount_code.php');
    require_once('./model/user.php');

    class DiscountCodesController extends ABCPage {

        function __construct() {

            $this->requireLogin();

            $artistId = $this->getLoggedInUserId();

            $conn = new DB();
            $this->artist = User::getById($conn, $artistId); 
            // TODO: handle case where artist does not exist.
            $this->discountCodes = DiscountCode::getAllByArtistId($conn, $artistId);
        }

        function renderView() {
            require_once('./view/discount_codes.php');
            $view = getView($this->discountCodes);
            $this->renderBasePage(
                sprintf("%s's Discount Codes", $this->artist->username),
                'Codes',
                $view,
                null
            );
        }

        
    }

?>