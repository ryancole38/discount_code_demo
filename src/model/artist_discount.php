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

    private static $getByArtistNameAndCodeStringQuery = <<<EOF
    SELECT * FROM (
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
            isDeleted = 0
        )
    WHERE
        discountCode = :discountCode AND
        artistName = :artistName;
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

        $results = $statement->execute();
        $artistDiscounts = [];
        while($row = $results->fetchArray(SQLITE3_ASSOC)) {
            $artistDiscounts[] = ArtistDiscount::constructFromRow($row);
        }
        return $artistDiscounts;
    }

    public static function getByArtistNameAndCodeString($conn, $artistName, $codeString) {
        $statement = $conn->prepare(ArtistDiscount::$getByArtistNameAndCodeStringQuery);

        $statement->bindValue(':artistName', $artistName);
        $statement->bindValue(':discountCode', $codeString);

        $result = $statement->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if (!$row) {
            return null;
        }
        return ArtistDiscount::constructFromRow($row); 
    }

    private static function constructFromRow($row) {
        $temp = new ArtistDiscount();
        $temp->artistId = $row['artistId'];
        $temp->artistName = $row['artistName'];
        $temp->discountId = $row['discountId'];
        $temp->discountCode = $row['discountCode'];

        return $temp;
    }

}

?>