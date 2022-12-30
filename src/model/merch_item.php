<?php

class MerchItem {

    public $id;
    public $name;
    public $price; // In an actual system, I would use a "money" type rather than a float
    public $merchTypeId;

    private static $createTableQuery = <<<EOF
    CREATE TABLE MerchItem(
        id              INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        name            TEXT NOT NULL,
        price           INTEGER NOT NULL,
        merchTypeId     INTEGER NOT NULL,
        artistId        INTEGER
    );
    EOF;

    private static $insertIntoQuery = <<<EOF
    INSERT INTO MerchItem
        (name, price, merchTypeId, artistId)
        VALUES
        (:name, :price, :merchTypeId, :artistId);
    EOF;

    private static $getAllMerchItemQuery = <<<EOF
    SELECT * FROM MerchItem;
    EOF;

    private static $getMerchItemById = <<<EOF
    EOF;

    function __construct($name='', $price=0, $merchTypeId=0, $artistId=0) {
        $this->id = 0;
        $this->name = $name;
        $this->price = $price;
        $this->merchTypeId = $merchTypeId;
        $this->artistId = $artistId;
    }

    public function commit($conn) {
        $statement = $conn->prepare(MerchItem::$insertIntoQuery);
        
        $statement->bindValue(':name', $this->name);
        $statement->bindValue(':price', $this->price);
        $statement->bindValue(':merchTypeId', $this->merchTypeId);
        $statement->bindValue(':artistId', $this->artistId);

        $result = $statement->execute();

        return $result;
    }

    public function createTable($conn) {
        $conn->query('DROP TABLE IF EXISTS MerchItem;');

        $result = $conn->query(MerchItem::$createTableQuery);

        return $result;
    }

}

?>