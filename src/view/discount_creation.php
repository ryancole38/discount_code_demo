<?php
require_once('./model/discount_code.php');

class DiscountCreationView {

    function __construct($artistId, $merchTypes) {
        // TODO: require that the logged in user is this artist.
        // That should be done in the controller, though.
        $this->discountCode = new DiscountCode();
        $this->artistId = $artistId;
        $this->merchTypes = $merchTypes;
    }

    function getView() {
        return $this->renderForm();
    }

    function renderForm() {
        ob_start();
        ?>

        <div id='discount-code-value-form'>
            <input type="number" id='id' value="<?php echo $this->discountCode->id;?>" disabled hidden/>
            <input type="number" id='artistId' value="<?php echo $this->artistId;?>" disabled hidden>
            <t>Code: </t>
            <input type="text" id='codeString' value="<?php 
                echo $this->discountCode->codeString;
            ?>"/></br>
            <t>Start Date: </t>
            <input type="text" id='startDate' value="<?php
                echo $this->discountCode->startDate;
            ?>"/></br>
            <t>End Date: </t>
            <input type="text" id='endDate' value="<?php 
                echo $this->discountCode->endDate;
            ?>"/><br>
            <t>Promotional Message: </t>
            <input type="text" id='discountMessage' value="<?php 
                echo $this->discountCode->discountMessage;
            ?>"/></br>
            <t>Discount Type: </t>
            <select id='discountType'>
                <option value='0'<?php if($this->discountCode->discountType === 0) echo ' selected';?>>Flat Discount</option>
                <option value='1'<?php if($this->discountCode->discountType === 1) echo ' selected';?>>Percentage</option>
                <option value='2'<?php if($this->discountCode->discountType === 2) echo ' selected';?>>Buy One, Get One</option>
            </select></br>
            <t>Merch Type</t>
            <select id='merchTypeId'>
                <option value='0'>All Types</option>
                <?php 
                foreach($this->merchTypes as $merchType) {
                    printf(
                        "<option value='%d'%s>%s</option>",
                        $merchType->id,
                        ($this->discountCode->merchTypeId === $merchType->id) ? ' selected' : '',
                        $merchType->merchTypeString
                    );
                }
                ?>
            </select></br>
            <t>Discount Amount: </t>
            <input type="text" id='discountAmount' value="<?php 
                echo $this->discountCode->discountAmount;
            ?>"/></br>
            <t>Times Redeemable: </t>
            <input type="text" id='timesRedeemable' value="<?php 
                echo $this->discountCode->timesRedeemable;
            ?>"/></br>
            <t>Minimum Order Amount: </t>
            <input type="text" id='minimumOrderAmount' value="<?php 
                echo $this->discountCode->minimumOrderAmount;
            ?>"/><br>
        </div>
        <button onclick="onSubmitDiscountCodeChanges()">Submit</button>

        <?php

        return ob_get_clean();
    }
}



?>