<?php

interface YoDatabase {

    // open/client to database
    public function init();

    public function getUserByToken($token);

    public function getUserByFbId($uid);

    public function addUser($token, $uid);

    public function deleteUser($token);

    public function updateUserFbId($token, $uid);

}

class SqlUtils {
    
    const SQL_GET_LIST = "SELECT * FROM %s LIMIT %d, %d";

    static public function select($table, $offset = 0, $limit = 10) {
        return "SELECT * FROM $table LIMIT $offset, $limit";
    }

    static public function selectWhere($table, $where, $offset = 0, $limit = 10) {
        return "SELECT * FROM $table WHERE $where LIMIT $offset, $limit";
    }

    static public function insert($table, $data = array()) {
        if (count($data) == 0) {
            return;
        }
        $query_value = '';
        $query_fields = '';
        foreach($data as $field => $value){
            $query_value .= " '".$value."' ,";
            $query_fields .= " `".$field."` "." ,";
        }
        $query_value = substr($query_value,0,-1);
        $query_fields = substr($query_fields,0,-1);
        return "INSERT INTO `{$table}` (" . $query_fields . ") VALUES (" . $query_value . ")";
    }

    static public function update($table, $data = array(), $where) {
        $query_limit="";
        if(is_array($where)){
            foreach($where as $f=>$c){
                $query_limit .= " `".$f."` = "." '".$c."' AND";
            }
            $query_limit = substr($query_limit,0,-3);
        }else{
            $query_limit = $where;
        }
        $query_set = '';
        foreach($data as $field => $value){
            $query_set .= " `".$field."` = "." '".$value."' ,";
        }
        $query_set = substr($query_set,0,-1);
        return "UPDATE `{$table}` SET " . $query_set . " WHERE " . $query_limit;
    }

    static public function delete($table, $where) {
        return "DELETE FROM $table WHERE $where";
    }

}

class Sqlite3Db implements YoDatabase {

    const PATH_DATABASE = "yodb.sqlite3";
    const TABLE_USERS = "users";
    const SQL_CREATE_TABLE_BOOKS = <<<SQL_BOOKS
        CREATE TABLE IF NOT EXISTS users(
            token TEXT PRIMARY KEY,
            fb_uid TEXT
        );
SQL_BOOKS;

    var $handle = NULL;

    public function Sqlite3Db() {
        $this->init();
    }

    public function init() {
        $this->handle = new SQLite3(self::PATH_DATABASE);
        $this->handle->query(self::SQL_CREATE_TABLE_BOOKS);
    }

    public function getUserByToken($token) {
        $sql = SqlUtils::selectWhere(self::TABLE_USERS, "token = '$token'");
        $r = $this->handle->query($sql);
        $results = array();
        while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }

    public function getUserByFbId($uid) {
        $sql = SqlUtils::selectWhere(self::TABLE_USERS, "fb_uid = '$uid'");
        $r = $this->handle->query($sql);
        $results = array();
        while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
            $results[] = $row;
        }
        return $results;
    }

    public function addUser($token, $uid) {
        $user = array("token" => $token, "fb_uid" => $uid);
        $sql = SqlUtils::insert(self::TABLE_USERS, $user);
        @$this->handle->exec($sql); // maybe already exists
        return $this->handle->lastInsertRowID();
    }

    public function deleteUser($token) {
        $sql = SqlUtils::delete(self::TABLE_USERS, "token = '$token'");
        $this->handle->exec($sql);
        return $token;
    }

    public function updateUserFbId($token, $uid) {
        $user = array("fb_uid" => $uid);
        $sql = SqlUtils::update(self::TABLE_USERS, $user, "token = '$token'");
        $this->handle->exec($sql);
        return $token;
    }

    public function printError($msg) {
        $errorCode = $this->handle->lastErrorCode();
        if ($errorCode != 0) {
            echo $msg . ": " . $errorCode . " " . $this->handle->lastErrorMsg() . "\n";
        }
    }

}

class Sqlite3DbTest {

    var $handle = NULL;

    function Sqlite3DbTest() {
        $this->handle = new Sqlite3Db();
        $this->testInit();
        $this->testAddUser();
        $this->testGetUserByToken();
        $this->testUpdateUserFbId();
        $this->testGetUserByFbId();
    }

    function testInit() {
        $this->handle->printError(__FUNCTION__);
    }

    function testAddUser() {
        $this->handle->addUser("token1", "uid1");
        $this->handle->printError(__FUNCTION__);
    }

    function testGetUserByFbId() {
        $r = $this->handle->getUserByFbId("uid2");
        $this->handle->printError(__FUNCTION__);
    }

    function testGetUserByToken() {
        $r = $this->handle->getUserByToken("token1");
        $this->handle->printError(__FUNCTION__);
    }

    function testUpdateUserFbId() {
         $this->handle->updateUserFbId("token1", "uid2");
         $this->handle->printError(__FUNCTION__);
    }

    function testDeleteUser() {
        $this->handle->deleteUser("token1");
    }

}

//$test = new Sqlite3DbTest();

?>