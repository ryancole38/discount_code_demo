<?php

class MerchType {

    public $id;
    public $merchTypeString;

    private static $createTableQuery = <<<EOF
    CREATE TABLE MerchType(
        id                     INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        merchTypeString        TEXT NOT NULL
    );
    EOF;

    private static $insertIntoQuery = <<<EOF
    INSERT INTO MerchType
        (merchTypeString)
        VALUES
        (:merchTypeString);
    EOF;

    private static $selectAllQuery = <<<EOF
    SELECT * FROM MerchType;
    EOF;

    private static $selectById = <<<EOF
    SELECT * FROM MerchType
        WHERE
        id = :id;
    EOF;

    function __construct() {
        $this->id = 0;
        $this->merchTypeString = '';
    }

    public function commit($conn) {
        $statement = $conn->prepare(MerchType::$insertIntoQuery);

        // TODO: check that statement executed
        $statement->bindValue(':merchTypeString', $this->merchTypeString);

        $statement->execute();
    }

    public static function createTable($conn) {
        $conn->query('DROP TABLE IF EXISTS MerchType;');
        
        $result = $conn->query(MerchType::$createTableQuery);

        return $result;
    }

    public static function getAllMerchTypes($conn) {
        $statement = $conn->prepare(MerchType::$selectAllQuery);
        $result = $statement->execute();

        $types = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $types[] = MerchType::constructFromRow($row);
        }

        return $types;
    }

    private static function constructFromRow($row) {
        $type = new MerchType();
        $type->id = $row['id'];
        $type->merchTypeString = $row['merchTypeString'];

        return $type;
    }

}

?>