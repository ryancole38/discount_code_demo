<?php

class ArtistDiscount {

    public $artistName;
    public $artistId;
    public $discountCode;
    public $discountId;

    private static $getAllByCodeQuery = <<<EOF
    SELECT  
        USER.ID as artistId, 
        USER.USERNAME as artistName, 
        DiscountCode.id as discountId,
        DiscountCode.codeString as discountCode
    FROM 
        USER INNER JOIN DiscountCode
    ON 
        USER.ID = DiscountCode.artistId
    WHERE
        ISARTIST = 1 AND
        isDeleted = 0 AND
        codeString = :discountCode;
    EOF;

    function __construct() {
        $artistName = '';
        $artistId = 0;
        $discountCode = '';
        $discountId = 0;
    }

    public static function getAllByCode($conn, $code) {
        $statement = $conn->prepare(ArtistDiscount::$getAllByCodeQuery);
        $statement->bindValue(':discountCode', $code);

        $results = $statement->query();
        $artistDiscounts = [];
        while($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $artistDiscounts[] = ArtistDiscount::constructFromRow($row);
        }
        return $artistDiscounts;
    }

    private static function getByRow($row) {
        $temp = new ArtistDiscount();
        $temp->artistId = $row['artistId'];
        $temp->artistName = $row['artistName'];
        $temp->discountId = $row['discountId'];
        $temp->discountCode = $row['discountCode'];

        return $temp;
    }

}

?>