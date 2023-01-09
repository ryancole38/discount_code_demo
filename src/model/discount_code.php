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
            AND isDeleted = 0;
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
            $this->minimumOrderAmount = 0;
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

        // Get discount by ARTISTID and $codeString
        public static function getCodeByArtistAndCode($conn, $artistId, $codeString) {
            $statement = $conn->prepare(DiscountCode::$selectByArtistIdAndCodeStringQuery);

            $statement->bindValue(':artistId', $artistId);
            $statement->bindValue(':codeString', $codeString);

            $result = $statement->execute();
            $row = $result->fetchArray(SQLITE3_ASSOC);
            if (!$row) {
                return false;
            }

            return DiscountCode::constructFromRow($row);
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

            // Code cannot already exist for this artist.
            if (!$this->isUniqueToArtist($conn)) {
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
                echo 'invalid';
                return false;
            }

            // Can't update a record to duplicate another code
            if (!$this->isUniqueToArtist($conn)) {
                echo 'not unique';
                return false;
            } 

            // TODO : update this to use versioning to set the 'updated by parameter'
            // Create the update prepared query
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
                $code->id = intval($row['id']);
                $code->artistId = intval($row['artistId']);
                $code->merchTypeId = intval($row['merchTypeId']);
                $code->dateCreated = 0; // TODO: change to now
                $code->codeString = $row['codeString'];
                $code->discountMessage = $row['discountMessage'];
                $code->startDate = $row['startDate'];
                $code->endDate = $row['endDate'];
                $code->discountType = intval($row['discountType']);
                $code->discountAmount = floatval($row['discountAmount']);
                $code->timesRedeemable = intval($row['timesRedeemable']);
                $code->minimumOrderAmount = floatval($row['minimumOrderAmount']);
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
            $code->startDate = DB::dateYmdToMdy($row['startDate']);
            $code->endDate = DB::dateYmdToMdy($row['endDate']);
            $code->discountType = $row['discountType'];
            $code->discountAmount = $row['discountAmount'];
            $code->timesRedeemable = $row['timesRedeemable'];
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
            $statement->bindValue(':startDate', DB::dateMdyToYmd($this->startDate), SQLITE3_TEXT);
            $statement->bindValue(':endDate', DB::dateMdyToYmd($this->endDate), SQLITE3_TEXT);
            $statement->bindValue(':discountType', $this->discountType, SQLITE3_INTEGER);
            $statement->bindValue(':discountAmount', $this->discountAmount, SQLITE3_FLOAT);
            $statement->bindValue(':timesRedeemable', $this->timesRedeemable, SQLITE3_INTEGER);
            $statement->bindValue(':minimumOrderAmount', $this->minimumOrderAmount, SQLITE3_FLOAT);

        }

        public function isUniqueToArtist($conn) {
            // An artist ID of 0 is a global code. Global codes still may not be duplicates.
            $existingCode = DiscountCode::getCodeByArtistAndCode($conn, $this->artistId, $this->codeString);
            
            // No code exists, return early.
            if (!$existingCode) {
                return true;
            }

            // If the code has the same ID, then we're updating the record and this is ok to update.
            if ($this->id > 0 && $existingCode->id === $this->id) {
                return true;
            }

            return false;

        }

        public function isActive() {
            if ($this->isDeleted) {
                return false;
            }

            // This will validate that the dates are valid
            if (!$this->isValid()) {
                return false;
            }

            // m - month with leading zeros
            // j - date with leading zeros
            // Y - Year 
            $startDateObj = date_create_from_format("m/j/Y", $this->startDate);
            $endDateObj = date_create_from_format("m/j/Y", $this->endDate);
            $currentTime = date_create();

            // check that start < now < end
            if ($currentTime < $startDateObj || $currentTime > $endDateObj) {
                return false;
            }

            return true;
        }

        // Check that the DiscountCode is in a valid state and can be
        // Committed to the database.
        public function isValid() {
            $valid = (
                $this->validateDate($this->startDate) &&
                $this->validateDate($this->endDate) &&
                $this->validateDiscountTypeAndAmount() &&
                $this->validateCodeString() && 
                ($this->minimumOrderAmount >= 0)
            );

            return $valid;
        }

        // Validates that $dateString is of the format MM/DD/YYYY and that
        // it is a valid date (e.g. no 02/31/2000)
        private function validateDate($dateString) {
            $dateExpr = "#^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$#";
            // If it doesn't match, return false
            if (!preg_match($dateExpr, $dateString, $matches)) {
                return false;
            }

            $month = $matches[1];
            $date = $matches[2];
            $year = $matches[3];

            return checkdate($month, $date, $year);
        }

        private function validateDiscountTypeAndAmount() {
            // Cannot have a negative discount amount
            if ($this->discountAmount < 0) {
                return false;
            }

            // Using a switch statement in non-C languages is kinda
            // cringe but (at least to my admittedly-C-familiar eyes)
            // cleaner than having a massive if-else block.
            // Validate discount type
            $valid = false;
            switch ($this->discountType) {
                case DiscountCode::FLAT_DISCOUNT:
                case DiscountCode::PERCENTAGE_DISCOUNT:
                    $valid = true; 
                    break;
                case DiscountCode::BOGO_DISCOUNT:
                    // Buy one, get one discounts cannot be more
                    // than 100% off
                    if ($this->discountAmount <= 100) {
                        $valid = true;
                    }
                    break;
                default:
                    // discount type was out of bounds
                    break;
            }

            return $valid;
        }

        // Validate that the code string is at least one character
        // long and contains a-z A-Z 0-9
        private function validateCodeString() {
            $codeExpr = '#^[a-zA-Z0-9]+$#';
            if (preg_match($codeExpr, $this->codeString)) {
                return true;
            }
            return false;
        }

    }

?>