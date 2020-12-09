<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in() {
    return isset($_SESSION["user"]);
}

function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

function poor_mans_cronjob(){
	//change 0 here if you want to provide an initial delay
	//otherwise it's instant
	$next = isset($_SESSION["nextTime"])?$_SESSION["nextTime"]:0;
	if(time() >= $next){
		//rest of it is just reset and tracking logic
		$runs = 1;
		if(isset($_SESSION["runs"])){
			$runs = (int)$_SESSION["runs"];
		}
		echo "weeee! we ran $runs times.";
		$delay = 30;
		if(isset($_SESSION["delay"])){
			$delay = (int)$_SESSION['delay'];
		}
		$_SESSION["nextTime"] = time() + $delay;
		$_SESSION["runs"] += 1;
	}
}
poor_mans_cronjob();

//end flash
?>
