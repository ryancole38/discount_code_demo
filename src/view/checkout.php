<?php

require_once('./view/abc_page.php');

class CheckoutView {

    function __construct($cart, $transaction){
        $this->cart = $cart;
        $this->transaction = $transaction;
    }

    function renderCart() {
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
        ?>
    <div id='checkout-summary'>

        <?php
        echo $this->renderCart();
        ?>

        <t>Subtotal: <?php echo $this->transaction->getSubtotal(); ?></t></br>
        <t>Discount: <?php echo $this->transaction->getDiscount(); ?></t></br>
        <t>Total: <?php echo $this->transaction->getTotal(); ?></t></br>
        <label for="discount-code">Apply discount code: </label>
        <input id="discount-code" type="text"/>
        <button onclick="onDiscountCodeApply()">Submit</button> 
    </div>

        <?php
        return ob_get_clean();
    }
}

?>
