<?php
require_once('./src/module/db.php');
require_once('./src/model/user.php');
require_once('./src/model/discount_code.php');
require_once('./src/model/merch_type.php');
require_once('./src/model/transaction.php');

$conn = new DB('./database.db');

User::createTable($conn);
DiscountCode::createTable($conn);
MerchType::createTable($conn);
Transaction::createTable($conn);

$user = new User();
$user->username = 'jeffrosenstock';
$user->isArtist = true;
$user->commit($conn);

$user->username = 'PUP';
$user->isArtist = true;
$user->commit($conn);

$user->username = 'leah';
$user->isArtist = false;
$user->commit($conn);

$retrievedUser = User::getByUsername($conn, 'jeffrosenstock');
echo var_dump($retrievedUser);

$allUsers = User::getAll($conn);
echo var_dump($allUsers);

$code = new DiscountCode();
$code->artistId = 1;
$code->codeString = 'SKADREAM20';
$code->discountType = DiscountCode::PERCENTAGE_DISCOUNT;
$code->discountAmount = 20.0;
$code->timesRedeemable = 1;
$code->startDate = '12/31/2021';
$code->endDate = '04/02/2023';

$code->commit($conn);

$code->artistId = 1;
$code->codeString = 'WORRY';
$code->discountType = DiscountCode::FLAT_DISCOUNT;
$code->discountAmount = 20.0;
$code->timesRedeemable = 5;
$code->startDate = '12/31/2021';
$code->endDate = '04/02/2023';
$code->minimumOrderAmount = 25;

$code->commit($conn);

$code->artistId = 2;
$code->codeString = 'DVP';
$code->discountType = DiscountCode::BOGO_DISCOUNT;
$code->discountAmount = 50.0;
$code->timesRedeemable = 0;
$code->startDate = '12/31/2021';
$code->endDate = '04/02/2023';
$code->minimumOrderAmount = 0;
$code->merchTypeId = 1; // Vinyl

$code->commit($conn);

$code->codeString = 'SKADREAM20';
$code->commit($conn);

$type = new MerchType();
$type->merchTypeString = 'Vinyl';
$type->commit($conn);
$type->merchTypeString = 'Clothing';
$type->commit($conn);
$type->merchTypeString = 'Poster';
$type->commit($conn);

$merch = MerchType::getAllMerchTypes($conn);
echo var_dump($merch);

$transaction = new Transaction();
$transaction->appliedDiscountId = 1; // SKADREAM20
$transaction->userId = 3;            // leah

$transaction->commit($conn);

$transaction->appliedDiscountId = 2; // WORRY
// Use 4 times so that it can be used another time and deactivate
$transaction->commit($conn);
$transaction->commit($conn);
$transaction->commit($conn);
$transaction->commit($conn);

$usages = Transaction::getTotalUsagesOfDiscount($conn, 2);
echo 'Discount 2 used ' . strval($usages) . " times\n"

?>