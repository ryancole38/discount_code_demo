<?php
require_once('./view/abc_page.php');

function getView($codes) {
    ob_start();
    ?>
    <?php 
    
        echo renderCodesAsTable($codes);

    ?>
    </br>
    <a class="button-small" href="http://localhost:8000/discount_codes/create"> Create Discount Code </a>

    <?php
    return ob_get_clean();
}

function renderCodesAsTable($codes) {
    ob_start();
    ?>
    <table class='table' id='discount-code-table'>
    <tr>
        <th>Discount Code</th>
        <th>Promo Message</th>
        <th>Discount Amount</th>
        <th>Active</th>
        <th></th>
    </tr>
    <?php
    foreach ($codes as $code) {
        printf(
            '<tr id=\'%s\'>
                <td><a class="discount-name" href=\'%s\'>%s</a></td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td><button onclick="onDeleteDiscountCodeClicked(%s)">Delete</button>
            </tr>',
            $code->id,
            getDiscountCodeLink($code),
            $code->codeString,
            $code->discountMessage,
            $code->discountAmount,
            $code->isActive() ? 'Yes' : 'No',
            $code->id
        );
    }
    ?>
    </table>
    <?php
    return ob_get_clean();
}

function getDiscountCodeLink($code) {
    return sprintf('http://%s/discount_codes/code?code=%s',
        $_SERVER['HTTP_HOST'],
        $code->codeString
    );
}

?>