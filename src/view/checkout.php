<?php

require_once('./view/abc_page.php');

class CheckoutView {

    function __construct($cart){
        $this->cart = $cart;
    }

    function renderCartAsTable() {
        echo <<<EOF
        <table>
            <th>
                <td>Item</td>
                <td>Artist</td>
                <td>Price</td>
            </th>
        EOF;
        foreach ($this->cart->items as $item) {
            printf(
                '<tr><td>%s</td><td>%s</td><td>$%.2f</td></tr>',
                $item->name,
                $item->artistId,
                $item->price
            );
        }
        echo '</table>';
    }

    function getView() {
        ob_start();

        echo $this->renderCartAsTable();
        ?>

        Apply discount code: <input id="discount-code" type="text"/>
        <button onclick="onDiscountCodeApply()">Submit</button> 

        <?php
        return ob_get_clean();
    }
}

?>
