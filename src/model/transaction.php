<?php

class Transaction {

    public $id;
    public $dateCreated;
    public $userId;
    public $total;
    public $subTotal;
    public $appliedDiscountId;
    public $discountAmount;

    function __construct() {
        $this->id = 0;
        $this->dateCreated = '';
        $this->userId = 0;
        $this->total = 0;
        $this->subTotal = 0;
        $this->appliedDiscountId = 0;
        $this->discountAmount = 0;
    }

    // Calculate the transaction total from the subtotal and discount
    // amount. Here is where taxes would also be applied.
    public function calculateTotal() {
        if ($this->total !== 0) {
            throw new Exception('Attempting to calculate total on populated transaction');
        }

        $this->total = $this->subTotal - $this->discountAmount;
    }

    public function getSubtotal() : string {
        return $this->getFormattedAsDollars($this->subTotal);
    }

    public function getTotal() : string {
        return $this->getFormattedAsDollars($this->total);
    }

    public function getDiscount() : string {
        return $this->getFormattedAsDollars($this->discountAmount);
    }

    private function getFormattedAsDollars($value) {
        return sprintf('$%.2f', $value);
    }

}

?>