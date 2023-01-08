<?php
class DB extends SQLite3{

    public static $db_filename = "./database.db";

    function __construct($db_filename='../database.db'){
        // Create the DB file if it doesn't exist
        $this->open(
            $db_filename,
            $flags=SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE
        );
    }

    function __destruct(){
        $this->close();
    }

    function create(){
    }

    // type conversions : I use ternary operators here because I think that it makes
    // what is returned more explicit without expanding into full if statements.

    // if true, return 1. otherwise 0
    public static function boolToInt(bool $value) : int {
        return $value === true ? 1 : 0;
    }

    // if 1, return true. otherwise false.
    public static function intToBool(int $value) : bool {
        return $value === 1 ? true : false;
    }

    // Convert MM/DD/YYYY to YYYY-MM-DD
    public static function dateMdyToYmd(string $dateString) : string {
        $dateExpr = "#^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$#";
        if (!preg_match($dateExpr, $dateString, $matches)) {
            return '1970-01-01'; // Sloppy, but return Jan 1, 1970 if no match
        }
        $year = $matches[3];
        $month = $matches[1];
        $date = $matches[2];
        return sprintf('%s-%s-%s', $year, $month, $date);
    }

    public static function dateYmdToMdy(string $dateString) : string {
        $dateExpr = "#^([0-9]{4})-([0-9]{2})-([0-9]{2})$#";
        if (!preg_match($dateExpr, $dateString, $matches)) {
            return '01/01/1970';
        }
        $year = $matches[1];
        $month = $matches[2];
        $date = $matches[3];
        return sprintf('%s/%s/%s', $month, $date, $year);
    } 

    public static function parseBool(string $string) : bool {
        if (strtolower($string) === 'true') {
            return true;
        }
        if (strtolower($string) === 'false') {
            return false;
        }

        throw new Exception('Unable to parse as boolean: '.$string);
    }
}

?>