<?php

require_once('./model/transaction.php');
require_once('./model/discount_code.php');

class TransactionProcessor {

    public $cart;
    public $discount;

    function __construct($cart, $discount) {
        $this->cart = $cart;
        $this->discount = $discount;
    }

    public function process() : Transaction {
        $transaction = new Transaction();
        
        foreach($this->cart->items as $item) {
            $transaction->subTotal += $item->price;
        }

        $transaction->discountAmount = $this->calculateDiscount();

        $transaction->calculateTotal();

        return $transaction;
    }

    private function calculateDiscount() {
        // If there is no discount code, return a discount of 0
        if (empty($this->discount)) {
            return 0;
        }

        $discountType = $this->discount->discountType;
        $amount = 0;

        // If I had to maintain this, I would probably have discount code
        // be a base class with a different class for each discount type.
        // Then, I could just call $discount->calculateDiscount();
        switch ($discountType) {
            case DiscountCode::FLAT_DISCOUNT:
                $amount = $this->calculateFlatDiscount();
                break;
            case DiscountCode::PERCENTAGE_DISCOUNT:
                $amount = $this->calculatePercentageDiscount();
                break;
            case DiscountCode::BOGO_DISCOUNT:
                $amount = $this->calculateBogoDiscount();
                break;
        }

        return $amount;
    }

    private function calculateFlatDiscount() {
        return 0;
    }

    private function calculatePercentageDiscount() {
        return 0;
    }

    private function calculateBogoDiscount() {
        return 0;
    }

}

?>