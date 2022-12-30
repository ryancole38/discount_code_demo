<?php

?>

<!DOCTYPE html>
<html lang="en">
    <head><head>
    <body>
        <?php echo var_dump($this->cart);?>
        <button onclick="onDiscountCodeApply()">Submit</button> 
    </body>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="static/js/checkout.js"></script>
</html>