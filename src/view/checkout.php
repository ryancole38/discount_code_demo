<?php

require_once('./view/abc_page.php');

class CheckoutView {

    function __construct($cart, $transaction){
        $this->cart = $cart;
        $this->transaction = $transaction;
    }

    function renderCart() {
        ob_start();
        ?>
    <div class='items'>
        <?php
        foreach ($this->cart->items as $item) {
            echo $this->renderItem($item);
        }
        ?>
    </div>
        <?php
        return ob_get_clean();
    }

    function renderItem($item) {
        return sprintf(
            '
           <div class="item">
            <div class="details">
             <h3 class="name">%s</h3>
             <p class="artist">%s</p>
            </div>
            <div class="price">
             <p class="price">$%.2f</p>
            </div>
           </div>
           <hr>
            ',
            $item->name,
            $item->artistId,
            $item->price
        );
    }

    function getView() {
        ob_start();
        ?>
    <div class='checkout'>
        <div class='info'>
            <p>Insert Content Here</p>
        </div>
        <div class='summary'>
            <?php
            echo $this->renderCart();
            ?>
            <div class='totals'>
                <input id="discount-code" type="text" placeholder="Discount Code"/>
                <button onclick="onDiscountCodeApply()">Apply</button> </br>
                <hr>
                <t>Subtotal: <?php echo $this->transaction->getSubtotal(); ?></t></br>
                <t>Discount: <?php echo $this->transaction->getDiscount(); ?></t></br>
                <t>Total: <?php echo $this->transaction->getTotal(); ?></t></br>
            </div>
        </div>
    </div>

        <?php
        return ob_get_clean();
    }
}

?>
