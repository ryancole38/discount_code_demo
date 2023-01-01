<?php

function renderForm() {
ob_start();
?>
    <div>
        <t>Code: </t><input type="text"/></br>
        <t>Start Date: </t><input type="text"/></br>
        <t>End Date: </t><input type="text"/><br>
        <t>Promotional Message: </t><input type="text"/></br>
        <t>Discount Amount: </t><input type="text"/></br>
        <t>Times Redeemable: </t><input type="text"/></br>
        <t>Is Stackable: </t><input type="checkbox"/></br>
        <t>Minimum Order Amount: </t><input type="text"/><br>
    </div>
<?php

return ob_get_clean();
}

?>