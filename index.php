<?php
ini_set('display_errors', 'On');
include("m_database.php");
include("m_http.php");

if(isset($argv[1]))
{
    for($i=1;$i<$argc;$i++)
    {
        $av = explode("=", $argv[$i]);
        $_GET[$av[0]] = $av[1];
    }
}

if(isset($_GET["action"])) {
    $action = $_GET["action"];
}

$return = array("status" => "0", "msg" => "unknown action");
switch($action) {
    case "login": {
        $db = new Sqlite3Db();
        $token = $_REQUEST["token"];
        $uid = $_REQUEST["uid"];
        if ($token == "" || $uid == "") {
            $return = array("status" => "0", "msg" => "token or uid is null");
            break;
        }
        $users = $db->getUserByToken($token);
        if (count($users) == 0) {
            $db->addUser($token, $uid);
        } else {
            // already exists
            if ($users[0]["fb_uid"] != $uid) {
                // update fb id
                $db->updateUserFbId($token, $uid);
            }
        }
        $return = array("status" => "1", "msg" => "Success");
    break;
    }

    case "send": {
        $db = new Sqlite3Db();
        $http = new Http();
        $token = $_REQUEST["token"];
        $uid = $_REQUEST["uid"];
        $msg = $_REQUEST["msg"];
        if ($token == "" || $uid == "" || $msg == "") {
            $return = array("status" => "0", "msg" => "token, uid or msg is null");
            break;
        }
        $users = $db->getUserByToken($token);
        if (count($users) == 0) {
            $return = array("status" => "0", "msg" => "send from user not exists");
            break;
        }
        $users = $db->getUserByFbId($uid);
        if (count($users) == 0) {
            $return = array("status" => "0", "msg" => "send to user not exists");
            break;
        }
        $result = array();
        foreach ($users as &$user) {
            $toToken = $user["token"];
            if ($toToken == "") {
                // do some thing
            } elseif ($toToken == $token) {
                $result[] = "To: $toToken, myself";
            } else {
                $r = $http->send($toToken, $msg);
                $result[] = $r;
            }
        }
        $return = array("status" => "0", "msg" => $result);
    break;
    }
        
    default: {
    break;
    }
}

echo json_encode($return);
?>