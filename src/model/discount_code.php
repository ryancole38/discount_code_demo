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
        /* The date that this record was added to the database. */
        public $dateCreated;
        /* Indicates that this entry has been deleted and should not be displayed to the user. */
        public $isDeleted;
        
        /* --------- Model attributes -------- */
        public $codeString;
        public $discountMessage;
        public $startDate;
        public $endDate;
        public $discountType;
        public $discountAmount;
        public $timesRedeemable;
        public $userCanReuse;
        public $isStackable;
        public $minimumOrderAmount;

        private static $createTableQuery = <<<EOF
        CREATE TABLE DiscountCode(
            id                  INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            artistId            INTEGER NOT NULL,
            merchTypeId         INTEGER NOT NULL,
            updatedById         INTEGER,
            dateCreated         TEXT NOT NULL,
            isDeleted           INTEGER,
            codeString          TEXT NOT NULL,
            discountMessage     TEXT,
            startDate           TEXT NOT NULL,
            endDate             TEXT,
            discountType        INTEGER,
            discountAmount      REAL NOT NULL,
            timesRedeemable     INTEGER,
            isStackable         INTEGER,
            minimumOrderAmount  REAL 
        );
        EOF;

        private static $insertIntoQuery = <<<EOF
        INSERT INTO DiscountCode(
            artistId,
            merchTypeId,
            updatedById,
            dateCreated,
            isDeleted,
            codeString,
            discountMessage,
            startDate,
            endDate,
            discountType,
            discountAmount,
            timesRedeemable,
            isStackable,
            minimumOrderAmount
        )
        VALUES (
            :artistId,
            :merchTypeId,
            :updatedById,
            :dateCreated,
            :isDeleted,
            :codeString,
            :discountMessage,
            :startDate,
            :endDate,
            :discountType,
            :discountAmount,
            :timesRedeemable,
            :isStackable,
            :minimumOrderAmount
        );
        EOF;

        private static $selectByIdQuery = <<<EOF
        SELECT * FROM DiscountCode
        WHERE id = :id;
        EOF;

        private static $selectByArtistIdQuery = <<<EOF
        SELECT * FROM DiscountCode
        WHERE artistId = :artistId AND isDeleted = 0;
        EOF;

        private static $selectByArtistIdAndCodeStringQuery = <<<EOF
        SELECT * FROM DiscountCode
        WHERE 
            artistId = :artistId 
            AND codeString = :codeString;
        EOF;

        private static $selectByCodeStringQuery = <<<EOF
            SELECT * FROM DiscountCode
            WHERE
                codeString = :codeString;
        EOF;

        private static $updateQuery = <<<EOF
        UPDATE DiscountCode
        SET
            merchTypeId = :merchTypeId,
            isDeleted = :isDeleted,
            codeString = :codeString,
            discountMessage = :discountMessage,
            startDate = :startDate,
            endDate = :endDate,
            discountType = :discountType,
            discountAmount = :discountAmount,
            timesRedeemable = :timesRedeemable,
            isStackable = :isStackable,
            minimumOrderAmount = :minimumOrderAmount
        WHERE id = :id;
        EOF;

        private static $deleteQuery = <<<EOF
        UPDATE DiscountCode
        SET
            isDeleted = 1
        WHERE id = :id;
        EOF;

        const FLAT_DISCOUNT = 0;
        const PERCENTAGE_DISCOUNT = 1;
        const BOGO_DISCOUNT = 2;

        function __construct() {
            $this->id = 0;
            $this->artistId = 0;
            $this->merchTypeId = 0;
            $this->updatedById = 0;
            $this->dateCreated = '';
            $this->isDeleted = false;
            $this->codeString = '';
            $this->discountMessage = '';
            $this->startDate = '';
            $this->endDate = '';
            $this->discountType = self::FLAT_DISCOUNT;
            $this->discountAmount = 0;
            $this->timesRedeemable = 0;
            $this->isStackable = false;
            $this->minimumOrderAmount = 0;
        }

        public function isActive() {
            return true;
        }

        public static function getAllByArtistId($conn, $artistId) {
            $statement = $conn->prepare(DiscountCode::$selectByArtistIdQuery);
            
            $statement->bindValue(':artistId', $artistId);

            $result = $statement->execute();
            // TODO: figure out how to check if there's an error that's not
            // the same as there being no results
            $codes = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $codes[] = DiscountCode::constructFromRow($row);
            }

            return $codes;
        }

        public static function getById($conn, $discountId) {
            $statement = $conn->prepare(DiscountCode::$selectByIdQuery);

            $statement->bindValue(':id', $discountId);

            $result = $statement->execute();

            $row = $result->fetchArray(SQLITE3_ASSOC);

            return DiscountCode::constructFromRow($row);
        }

        public static function getAllByCodeString($conn, $codeString) {
            $statement = $conn->prepare(DiscountCode::$selectByCodeStringQuery);
            $statement->bindValue(':codeString', $codeString);

            $result = $statement->execute();

            return DiscountCode::getArrayFromResult($result);
        }

        public static function getCodeByArtistAndCode($conn, $artistId, $codeString) {
            $statement = $conn->prepare(DiscountCode::$selectByArtistIdAndCodeStringQuery);

            $statement->bindValue(':artistId', $artistId);
            $statement->bindValue(':codeString', $codeString);

            $result = $statement->execute();
            // TODO: check for error

            return DiscountCode::constructFromRow($result->fetchArray(SQLITE3_ASSOC));
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
                return $this->update($conn); 
            }

            // Can't commit: this has invalid values.
            if (!$this->isValid()) {
                return false;
            }

            $statement = $conn->prepare(DiscountCode::$insertIntoQuery);

            $this->bindInstanceToPreparedStatement($statement);

            $result = $statement->execute();

            if (!$result) {
                echo $conn->lastErrorMsg();
            }

            return $result;
        }

        public function update($conn) {
            
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

        public function delete($conn) {
            // Return if id is 0 since that means that no record has been
            // created.
            if ($this->id === 0) {
                return;
            }

            $statement = $conn->prepare(DiscountCode::$deleteQuery);
            $statement->bindValue(':id', $this->id, SQLITE3_INTEGER);

            $result = $statement->execute();

            return $result;
        }

        public static function constructFromPublicJsonValues($row) {

            $code = new DiscountCode();

            try {
                $code->id = strval($row['id']);
                $code->artistId = strval($row['artistId']);
                $code->merchTypeId = strval($row['merchTypeId']);
                $code->dateCreated = 0; // TODO: change to now
                $code->codeString = $row['codeString'];
                $code->discountMessage = $row['discountMessage'];
                $code->startDate = $row['startDate'];
                $code->endDate = $row['endDate'];
                $code->discountType = $row['discountType'];
                $code->discountAmount = strval($row['discountAmount']);
                $code->timesRedeemable = strval($row['timesRedeemable']);
                $code->isStackable = DB::parseBool($row['isStackable']);
                $code->minimumOrderAmount = strval($row['minimumOrderAmount']);
            } catch(Exception $error){
                // TODO: log the error or something
                $code = null;
            }

            return $code;

        }

        private static function getArrayFromResult($result) {
            $arr = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $arr[] = DiscountCode::constructFromRow($row);
            }

            return $arr;
        }

        private static function constructFromRow($row) {
            $code = new DiscountCode();

            $code->id = $row['id'];
            $code->artistId = $row['artistId'];
            $code->merchTypeId = $row['merchTypeId'];
            $code->updatedById = $row['updatedById'];
            $code->dateCreated = $row['dateCreated'];
            $code->isDeleted = $row['isDeleted'];
            $code->codeString = $row['codeString'];
            $code->discountMessage = $row['discountMessage'];
            $code->startDate = $row['startDate'];
            $code->endDate = $row['endDate'];
            $code->discountType = $row['discountType'];
            $code->discountAmount = $row['discountAmount'];
            $code->timesRedeemable = $row['timesRedeemable'];
            $code->isStackable = $row['isStackable'];
            $code->minimumOrderAmount = $row['minimumOrderAmount'];

            return $code;
        }

        private function bindInstanceToPreparedStatement($statement) {
            $statement->bindValue(':artistId', $this->artistId, SQLITE3_INTEGER);
            $statement->bindValue(':merchTypeId', $this->merchTypeId, SQLITE3_INTEGER);
            $statement->bindValue(':updatedById', $this->updatedById, SQLITE3_INTEGER);
            $statement->bindValue(':dateCreated', $this->dateCreated, SQLITE3_TEXT);
            $statement->bindValue(':isDeleted', DB::boolToInt($this->isDeleted), SQLITE3_INTEGER);
            $statement->bindValue(':codeString', $this->codeString, SQLITE3_TEXT);
            $statement->bindValue(':discountMessage', $this->discountMessage, SQLITE3_TEXT);
            $statement->bindValue(':startDate', $this->startDate, SQLITE3_TEXT);
            $statement->bindValue(':endDate', $this->endDate, SQLITE3_TEXT);
            $statement->bindValue(':discountType', $this->discountType, SQLITE3_INTEGER);
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