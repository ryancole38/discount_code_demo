<?php
function renderCodesAsTable($codes) {
    echo <<<EOF
    <table>
    <th>
        <td>Discount Code</td>
        <td>Promo Message</td>
        <td>Discount Amount</td>
        <td>Active</td>
    </th>
    EOF;
    foreach ($codes as $code) {
        printf('<tr><td><a href=\'%s\'>%s</a></td><td>%s</td><td>%s</td><td>%s</td></tr>',
            getDiscountCodeLink($code),
            $code->codeString,
            $code->discountMessage,
            $code->discountAmount,
            $code->isActive() ? 'Yes' : 'No'
        );
    }
    echo '</table>';
}

function getDiscountCodeLink($code) {
    return sprintf('http://%s/discount_codes/code?code=%s',
        $_SERVER['HTTP_HOST'],
        $code->codeString
    );
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo $this->artist->username?>'s Discount Codes</title>
    </head>
    <body>
    <?php
    renderCodesAsTable($this->discountCodes);
    echo var_dump($this->artist);
    ?>
    </body>
</html>