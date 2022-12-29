<?php
require_once('./src/module/db.php');
require_once('./src/model/user.php');
require_once('./src/model/discount_code.php');

$conn = new DB('./database.db');

User::createTable($conn);
DiscountCode::createTable($conn);

$user = new User();
$user->username = 'jeffrosenstock';
$user->isArtist = true;
$user->commit($conn);

$user->username = 'PUP';
$user->isArtist = true;
$user->commit($conn);

$retrievedUser = User::getByUsername($conn, 'jeffrosenstock');
echo var_dump($retrievedUser);


$code = new DiscountCode();
$code->artistId = 1;
$code->codeString = 'SKADREAM20';
$code->isPercentage = true;
$code->discountAmount = 20.0;
$code->timesRedeemable = 0;
$code->userCanReuse = false;
$code->startDate = 'today';
$code->endDate = 'tomorrow';

$code->commit($conn);

$code = new DiscountCode();
$code->artistId = 1;
$code->codeString = 'WORRY';
$code->isPercentage = true;
$code->discountAmount = 20.0;
$code->timesRedeemable = 0;
$code->userCanReuse = false;
$code->startDate = 'today';
$code->endDate = 'tomorrow';

$code->commit($conn);


?>