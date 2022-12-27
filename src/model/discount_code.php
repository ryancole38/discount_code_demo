<?php

    class DiscountCode {

        /* -------- Database housekeeping -------- */
        /* Primary key for this discount code */
        public $id;
        /* Foreign key for the artist for which this discount code pertains */
        public $artistId;
        /* The foreign key for the type of merch for which this discount code pertains.
           No key indicates that this discount can be used for any type of merch. */
        public $merchTypeId;
        // TODO: allow discount codes to be versioned.
        /* UNUSED: The primary key of the entry in this table by which this entry is updated.
           No value indicates that this has not been updated. */
        public $updatedBy;
        /* Indicates that this entry has been deleted and should not be displayed to the user. */
        public $isDeleted;
        
        /* --------- Model attributes -------- */
        public $codeString;
        public $discountMessage;
        public $startDate;
        public $endDate;
        public $isPercentage;
        public $discountAmount;
        public $timesRedeemable;
        public $userCanReuse;
        public $isStackable;
        public $minimumOrderAmount;

        private static $createTableQuery = <<<EOF
        CREATE TABLE DiscountCode(
            Id                  INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            artistId            INTEGER NOT NULL,
            merchTypeId         INTEGER NOT NULL,
            updatedById         INTEGER,
            isDeleted           INTEGER,
            codeString          TEXT NOT NULL,
            discountMessage     TEXT,
            startDate           TEXT NOT NULL,
            endDate             TEXT,
            isPercentage        INTEGER,
            discountAmount      REAL NOT NULL,
            timesRedeemable     INTEGER,
            userCanReuse        INTEGER,
            isStackable         INTEGER,
            minimumOrderAmount  REAL 
        );
        EOF;

        private static $insertIntoQuery = <<<EOF
        INSERT INTO DiscountCode(
            artistId,
            merchTypeId,
            updatedById,
            isDeleted,
            codeString,
            discountMessage,
            startDate,
            endDate,
            isPercentage,
            discountAmount,
            timesRedeemable,
            userCanReuse,
            isStackable,
            minimumOrderAmount
        )
        VALUES (
            :artistId,
            :merchTypeId,
            :updatedById,
            :isDeleted,
            :codeString,
            :discountMessage,
            :startDate,
            :endDate,
            :isPercentage,
            :discountAmount,
            :timesRedeemable,
            :userCanReuse,
            :isStackable,
            :minimumOrderAmount
        );
        EOF;

        private static $updateQuery = <<<EOF
        UPDATE DiscountCode
        WHERE Id = :id
        EOF;

        function __construct() {
            $this->id = 0;
            $this->merchTypeId = 0;
            $this->updatedById = 0;
            $this->isDeleted = false;
            $this->codeString = '';
            $this->discountMessage = '';
            $this->startDate = '';
            $this->endDate = '';
            $this->isPercentage = false;
            $this->discountAmount = 0;
            $this->timesRedeemable = 0;
            $this->userCanReuse = false; // TODO : is this necessary? timesRedeemable could just be 0
            $this->isStackable = false;
            $this->minimumOrderAmount = 0;
        }

        public static function createTable($conn) {
            // For some reason, if `DROP TABLE` is included as part of the table creation,
            // it will not create table. Or maybe it creates the table, and then drops it.
            $conn->query('DROP TABLE IF EXISTS DiscountCode;');
            $result = $conn->query(DiscountCode::$createTableQuery);

            if (!$result) {
                return false;
            }

            return true;
        }

        public function commit($conn) {
            // If this already has an ID, it should be updated instead.
            if ($this->id > 0) {
                return $this->update(); 
            }

            // Can't commit: this has invalid values.
            if (!$this->isValid()) {
                return false;
            }
           
            $statement = $conn->prepare(DiscountCode::$insertIntoQuery);

            $this->bindInstanceToPreparedStatement($statement);

            $result = $statement->execute();

            return $result;
        }

        public function update() {
            
            // We must have a valid ID in order to update the record.
            if (!($this->id > 0)) {
                return false;
            }

            // Can't update a record to be invalid.
            if (!$this->isValid()) {
                return false;
            }

            // TODO : update this to use versioning to set the 'updated by parameter'
            // Create the update preared query
            $statement = $conn->prepare(DiscountCode::$updateQuery);
            $statement->bindValue(':id', $this->id, SQLITE3_INTEGER);
            $this->bindInstanceToPreparedStatement($statement);

            $result = $statement->execute();

            return $result;
        }

        private function bindInstanceToPreparedStatement($statement) {

            $statement->bindValue(':artistId', $this->artistId, SQLITE3_INTEGER);
            $statement->bindValue(':merchTypeId', $this->merchTypeId, SQLITE3_INTEGER);
            $statement->bindValue(':updatedById', $this->updatedById, SQLITE3_INTEGER);
            $statement->bindValue(':isDeleted', DB::boolToInt($this->isDeleted), SQLITE3_INTEGER);
            $statement->bindValue(':codeString', $this->codeString, SQLITE3_TEXT);
            $statement->bindValue(':discountMessage', $this->discountMessage, SQLITE3_TEXT);
            $statement->bindValue(':startDate', $this->startDate, SQLITE3_TEXT);
            $statement->bindValue(':endDate', $this->endDate, SQLITE3_TEXT);
            $statement->bindValue(':isPercentage', DB::boolToInt($this->isPercentage), SQLITE3_INTEGER);
            $statement->bindValue(':discountAmount', $this->discountAmount, SQLITE3_FLOAT);
            $statement->bindValue(':timesRedeemable', $this->timesRedeemable, SQLITE3_INTEGER);
            $statement->bindValue(':isStackable', DB::boolToInt($this->isStackable), SQLITE3_INTEGER);
            $statement->bindValue(':minimumOrderAmount', $this->minimumOrderAmount, SQLITE3_FLOAT);

        }

        public function isValid() {
            // TODO: perform validation logic
            return true;
        }

    }

?>