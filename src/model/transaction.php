<?php

class Transaction {

    public $id;
    public $dateCreated;
    public $userId;
    public $total;
    public $subTotal;
    public $appliedDiscountId;
    public $discountAmount;

    // Note: The name of the table is "OrderTransaction" not "Transaction"
    // because it seems like that's a reserved keyword in SQLite
    private static $createTableQuery = <<<EOF
    CREATE TABLE OrderTransaction(
        id                  INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        dateCreated         TEXT DEFAULT CURRENT_TIMESTAMP, 
        userId              INTEGER NOT NULL,
        total               REAL,
        subTotal            REAL,
        appliedDiscountId   INTEGER,
        discountAmount      REAL
    );
    EOF;

    private static $insertIntoQuery = <<<EOF
    INSERT INTO OrderTransaction(
        userId,
        total,
        subTotal,
        appliedDiscountId,
        discountAmount
    ) VALUES (
        :userId,
        :total,
        :subTotal,
        :appliedDiscountId,
        :discountAmount
    );
    EOF;

    private static $countDiscountUsagesQuery = <<<EOF
    SELECT COUNT(*) as count
    FROM OrderTransaction
    WHERE 
        appliedDiscountId = :discountId;
    EOF;

    function __construct() {
        $this->id = 0;
        $this->dateCreated = '';
        $this->userId = 0;
        $this->total = 0;
        $this->subTotal = 0;
        $this->appliedDiscountId = 0;
        $this->discountAmount = 0;
    }

    public static function createTable($conn) {
        $conn->query('DROP TABLE IF EXISTS OrderTransaction;');
        $conn->query(Transaction::$createTableQuery);
    }

    public function commit($conn) {
        $statement = $conn->prepare(Transaction::$insertIntoQuery);

        $this->bindInstanceToPreparedStatement($statement);
        $result = $statement->execute();
        // TODO: check results
    }

    public static function getTotalUsagesOfDiscount($conn, $discountId) {
        $statement = $conn->prepare(Transaction::$countDiscountUsagesQuery);
        $statement->bindValue(':discountId', $discountId);

        $result = $statement->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        // If error, return -1
        if (!$row) {
            return -1;
        }

        return $row['count'];
    }

    private function bindInstanceToPreparedStatement($statement) {
        $statement->bindValue(':userId', $this->userId);
        $statement->bindValue(':total', $this->total);
        $statement->bindValue(':subTotal', $this->subTotal);
        $statement->bindValue(':appliedDiscountId', $this->appliedDiscountId);
        $statement->bindValue(':discountAmount', $this->discountAmount);
    }

    // Calculate the transaction total from the subtotal and discount
    // amount. Here is where taxes would also be applied.
    public function calculateTotal() {
        if ($this->total !== 0) {
            throw new Exception('Attempting to calculate total on populated transaction');
        }

        $this->total = $this->subTotal - $this->discountAmount;
    }

    public function getSubtotal() : string {
        return $this->getFormattedAsDollars($this->subTotal);
    }

    public function getTotal() : string {
        return $this->getFormattedAsDollars($this->total);
    }

    public function getDiscount() : string {
        return $this->getFormattedAsDollars($this->discountAmount);
    }

    private function getFormattedAsDollars($value) {
        return sprintf('$%.2f', $value);
    }

}

?>