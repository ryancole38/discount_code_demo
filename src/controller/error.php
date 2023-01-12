<?php

require_once('./view/error.php');
require_once('./controller/abc_page.php');

class ErrorController extends ABCPage {

    function __construct() {

    }

    function handle() {
        $view = (new ErrorView())->getView();
        $this->renderBasePage('Error', '', $view, null);
    }

}

?>