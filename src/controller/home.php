<?php

require_once('./view/home.php');
require_once('./controller/abc_page.php');

class HomeController extends ABCPage {

    function __construct() {

    }

    function handle() {
        $view = (new HomeView())->getView();
        $this->renderBasePage('Home', '', $view, null);
    }

}

?>