<?php
class CodeDetailView {

    function __construct($code){
        $this->code = $code;
    }

    function getView() {
        return $this->code->codeString;
    }
}
?>