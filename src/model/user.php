<?php

class User {

    public $id;
    public $username;
    public $isArtist;

    private static $createTableQuery = <<<EOF
    DROP TABLE IF EXISTS USER;
    CREATE TABLE USER(
        ID              INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
        USERNAME        TEXT NOT NULL,
        ISARTIST        INTEGER NOT NULL
    );
    EOF;

    private static $insertIntoQuery = <<<EOF
    INSERT INTO USER
        (USERNAME, ISARTIST)
        VALUES
        (:username, :isArtist)
    ;
    EOF;

    // IMPORTANT: When using a prepared statement with string values,
    // you MUST omit quotes from the value that will be substituted.
    // so below, `USERNAME = :username` must not surround `:username`
    // in quotes, even though the string itself must be, e.g.
    // `USERNAME = 'my_user'`
    private static $selectByUsernameQuery = <<<EOF
    SELECT * FROM USER
    WHERE
    USERNAME = :username
    ;
    EOF;

    private static $selectByIdQuery = <<<EOF
    SELECT * FROM USER
    WHERE
    ID = :id;
    EOF;

    function __construct() {
        $this->id = -1;
        $this->username = '';
        $this->isArtist = false;
    }

    public function commit($conn) : bool {
        
        // TODO: check that username is set
        // TODO: check that isArtist is set
        $statement = $conn->prepare(User::$insertIntoQuery);
        if (!$statement) {
            echo 'Error: failed to get prepared statement\n';
            return false;
        }

        $statement->bindValue(':username', $this->username, SQLITE3_TEXT);
        $statement->bindValue(':isArtist', DB::boolToInt($this->isArtist), SQLITE3_INTEGER);

        $result = $statement->execute();

        // TODO: check result is success
        return true;

    }

    public function update($conn) : bool {

        return false;

    }

    public static function getAll($conn) {

    }

    public static function getById($conn, $id) {
        $statement = $conn->prepare(User::$selectByIdQuery);
        $statement->bindValue(':id', $id, SQLITE3_INTEGER);

        $result = $statement->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);

        return User::constructFromRow($row);
    }

    public static function getByUsername($conn, $username) {

        $statement = $conn->prepare(User::$selectByUsernameQuery);
        if (!$statement) {
            return null;
        }

        $bind_success = $statement->bindValue(':username', $username, SQLITE3_TEXT);
        if (!$bind_success) {
            echo 'ERROR: Failed to bind value username??';
            return null;
        }

        $result = $statement->execute();

        if (!$result) {
            echo 'ERROR: Failed to execute select query';
            return null;
        }

        $row = $result->fetchArray(SQLITE3_ASSOC);

        if (!$row) {
            echo 'ERROR: Failed to retrieve user';
            return null;
        }

        echo var_dump($row);

        // we only fetch the first. if more than one exist, that's not my problem.
        return User::constructFromRow($row); 

    }

    // Create a table in the database for this model
    public static function createTable($conn) {
        $conn->query(User::$createTableQuery);
        // TODO: Check that query executed
    }

    public static function constructFromRow($row) {
        $user = new User();
        $user->id = $row['ID'];
        $user->username = $row['USERNAME'];
        $user->isArtist = DB::intToBool($row['ISARTIST']);

        return $user;
    }

}

?>