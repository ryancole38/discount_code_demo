<?php
require_once('./view/abc_page.php');

function getView($codes) {
    ob_start();
    ?>
    <?php 
    
        echo renderCodesAsTable($codes);
        echo ABCPageView::renderLink(
            'Create Discount Code', 
            '/discount_codes/create'
        );
    ?>

    <?php
    return ob_get_clean();
}

function renderCodesAsTable($codes) {
    ob_start();
    ?>
    <table id='discount-code-table'>
    <th>
        <td>Discount Code</td>
        <td>Promo Message</td>
        <td>Discount Amount</td>
        <td>Active</td>
        <td></td>
    </th>
    <?php
    foreach ($codes as $code) {
        printf(
            '<tr id=\'%s\'>
                <td><a href=\'%s\'>%s</a></td>
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