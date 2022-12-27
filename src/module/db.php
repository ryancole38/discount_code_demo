<?php
class DB extends SQLite3{

    public static $db_filename = "./database.db";

    function __construct(){
        // Create the DB file if it doesn't exist
        $this->open(
            DB::$db_filename,
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
}

?>