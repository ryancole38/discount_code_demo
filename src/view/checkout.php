<?php

function renderCartAsTable($cart) {
    echo <<<EOF
    <table>
        <th>
            <td>Item</td>
            <td>Artist</td>
            <td>Price</td>
        </th>
    EOF;
    foreach ($cart->items as $item) {
        printf(
            '<tr><td>%s</td><td>%s</td><td>$%.2f</td></tr>',
            $item->name,
            $item->artistId,
            $item->price
        );
    }
    echo '</table>';
}

?>

<!DOCTYPE html>
<html lang="en">
    <head><head>
    <body>
        <?php renderCartAsTable($this->cart);?>
        Apply discount code: <input id="discount-code" type="text"/>
        <button onclick="onDiscountCodeApply()">Submit</button> 
    </body>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="static/js/checkout.js"></script>
</html>