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
        
        $transaction->subTotal = $this->getTotalForItems($this->cart->items); 

        $transaction->discountAmount = $this->calculateDiscount();

        $transaction->calculateTotal();

        return $transaction;
    }

    private function calculateDiscount() {
        // If there is no discount code, return a discount of 0.
        // If the minimumOrderAmount is not satisfied based on 
        // artistId and merchType, return 0.
        if (empty($this->discount) || !$this->isEligibleForDiscount()) {
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
        // isEligibleForDiscount() checks the minimumOrderAmount,
        // so we can just return the discountAmount (assumed in dollars)
        return $this->discount->discountAmount;
    }

    private function calculatePercentageDiscount() {
        $eligibleItems = $this->getItemsEligibleForDiscount(
            $this->discount->artistId,
            $this->discount->merchTypeId
        );

        $eligibleTotal = $this->getTotalForItems($eligibleItems);

        // discountAmount is a percentage 0 - 100, so we divide by
        // 100 so that we can multiply by the eligible total to get
        // the discount amount.
        return $eligibleTotal * ($this->discount->discountAmount / 100);
    }

    private function calculateBogoDiscount() {
        $eligibleItems = $this->getItemsEligibleForDiscount(
            $this->discount->artistId,
            $this->discount->merchTypeId
        );

        // To "buy one, get one" you need to have two items
        if(count($eligibleItems) < 2) {
            return 0;
        }
        
        // Sort the items by price
        $this->sortItemsByPrice($eligibleItems);
        // "Reindex" the array since PHP arrays are weird and use keys
        $eligibleItems = array_values($eligibleItems);

        // Since BOGO usually works by "equal or lesser value",
        // the highest priced item will be [0], and the next item
        // of lesser value will be [1], which will be what the discount
        // is applied to.
        $discountedItem = $eligibleItems[1];

        // Convert percentage amount to float amount discount, and apply.
        return $discountedItem->price * ($this->discount->discountAmount / 100);
    }

    // Determine if the cart is eligible for the specified discount
    // This is done by getting the subtotal for the artist specified
    // in the discount code and checking if it meets the minimumOrderAmount.
    // If no artist is specified, it's a global discount and the subtotal
    // for the cart is returned.
    private function isEligibleForDiscount() {
        $artist = $this->discount->artistId;
        $merchTypeId = $this->discount->merchTypeId;

        // If artist ID is 0, this will return all items in the cart
        $eligibleItems = $this->getItemsForArtist($artist);

        $total = $this->getTotalForItems($eligibleItems);

        // If the total is greater than the minimumOrder, the cart is
        // eligible.
        return ($total > $this->discount->minimumOrderAmount);
    }

    // Get all items in the cart with the specified artistId.
    private function getItemsForArtist($artistId) {
        // Specifying merchTypeId returns all merch types
        return $this->getItemsEligibleForDiscount($artistId, 0);
    }

    // Get items that belong to the artist who created the discount,
    // unless the discount doesn't have an artist which means it is a
    // "global" discount code. Same with merchTypeId.
    private function getItemsEligibleForDiscount($artistId, $merchTypeId) {
        // Some commentary: "Why does this take $artistId and $merchTypeId
        // when you could just use $this->discount->artistId etc etc?"
        // I generally prefer to write my functions more "pure" where
        // they don't depend as highly on the state of the object. This
        // makes them more testable and also reduces temporal coupling
        // since the output should be deterministic independent of the 
        // state of the object. This point is nullified by referencing
        // $this->cart below.
        $eligibleItems = $this->cart->items;

        // Skip filtering on artist id if global discount code.
        if ($artistId !== 0) {
            $eligibleItems = array_filter($eligibleItems, 
            function ($item) use ($artistId){
                return ($item->artistId === $artistId);
            });
        }
        // Skip filtering on merch type if all merch is allowed.
        if ($merchTypeId !== 0) {
            $eligibleItems = array_filter($eligibleItems,
            function ($item) use ($merchTypeId){
                return ($item->merchTypeId === $merchTypeId);
            });
        }

        return $eligibleItems;
    }

    private function getTotalForItems($items) {
        $total = 0;
        foreach($items as $item) {
            $total += $item->price;
        }

        return $total;
    }

    // Warning!! Sorts in place!!
    private function sortItemsByPrice($items) {
        usort($items, function($a, $b) {
            return $a->price - $b->price;
        });
    }
}

?>