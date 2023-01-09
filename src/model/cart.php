<?php

require_once('./model/merch_item.php');

/*
This is a stub class. It is not meant to represent a real
user's cart, since it's out of the scope of this project.
*/
class Cart {

    public $userId;
    public $items;

    function __construct() {
        $this->items = [
            new MerchItem('WORRY.', 19.99, 1, 1),
            new MerchItem('PUPTHEBAND Inc. T-shirt', 25, 2, 2),
            new MerchItem('PUP - Self-titled', 19.99, 1, 2),
            new MerchItem('Morbid Stuff', 19.99, 1, 2),
            new MerchItem('Pride', 35, 2, 1) 
        ];
    }

    public static function getCartByUserId($conn, $userId) {

        $cart = new Cart();
        $cart->userId = $userId;

        return $cart;

    }

}

?>