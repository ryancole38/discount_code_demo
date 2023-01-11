<?php

require_once('./view/abc_page.php');
// I'm not sure if these are required to specify a type,
// but I'm going to require them anyway.
require_once('./model/transaction.php');
require_once('./model/cart.php');

class CheckoutView {

    public Cart $cart;
    public $transaction;
    // Error message to display if something went wrong during processing.
    public $error;
    // String representation of the name of the artist, e.g. "PUP"
    public $artistName;
    // String representation of the discount code, e.g. "DISCOUNT20"
    public $discountCode;
    // Array of artist names that can have this discount applied
    public Array $possibleArtists;

    function __construct(Cart $cart, $transaction){
        $this->cart = $cart;
        $this->transaction = $transaction;
        $this->possibleArtists = [];
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

    function renderSubmitOrderButton() {
        return '<button onclick="onSubmitOrder()">Submit Order</button>';
    }

    function renderDiscountCodeInput() {
        $discountInput = '<input id="discount-code" type="text" placeholder="Discount Code"/>';
        $applyButton = '<button onclick="onDiscountCodeApply()">Apply</button></br>';

        if (empty($this->discountCode)) {
            return $discountInput . $applyButton;
        }

        $appliedDiscountCodeComponent = $this->renderAppliedDiscountCodeComponent($this->discountCode, $this->artistName);

        if (empty($this->artistName)) {

            return $appliedDiscountCodeComponent . 
                $this->renderDiscountArtistSelector() . $applyButton;
        }

        return $appliedDiscountCodeComponent;
    }

    function renderAppliedDiscountCodeComponent($discountCode, $artist) {
        $component = '<p style="display: inline" id="applied-discount-code">' . $discountCode . '</p>';
        if (!empty($artist)) {
            $component = $component . '<p id="applied-artist-name">' . $artist . '</p>';
        }
        $component = $component . '<button onclick="onDiscountCodeRemove()">Remove</button>';
        return $component;
    }

    function renderDiscountArtistSelector() {
        $selector = "<select id='discountArtistName'>";
        foreach($this->possibleArtists as $artist) {
            $selector = $selector . sprintf(
                "<option value='%s'>%s</option>",
                $artist,
                $artist
            );
        }
        $selector = $selector . "</select>";
        return $selector;
    }

    function getView() {
        ob_start();
        ?>
    <div class='checkout'>
        <div class='info'>
            <p>Insert Content Here</p>
            <?php echo $this->renderSubmitOrderButton(); ?>
        </div>
        <div class='summary'>
            <?php
            echo $this->renderCart();
            ?>
            <div class='totals'>
                <?php echo $this->renderDiscountCodeInput();?>
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
