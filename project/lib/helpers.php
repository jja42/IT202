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

function calc_competitions(){
                $i = 0;
                $score_arr = array();
                $user_arr = array();
                $name_arr = array();
        $next = isset($_SESSION["nextTime"])?$_SESSION["nextTime"]:0;
        if(time() >= $next){
                $delay = 30;
                if(isset($_SESSION["delay"])){
                        $delay = (int)$_SESSION['delay'];
                }
                $_SESSION["nextTime"] = time() + $delay;
        //get all competitions
        //filter by expired and not paid_out and filter out low participants
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM Competitions where expires < current_timestamp AND paid_out = 0 AND participants > 2");
                $r = $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($results as $result){        
        //determine winners
        //reference scores between competition start and end
        //get users in competition
        if($results){
                $stmt = $db->prepare("SELECT user_id FROM CompetitionParticipants where competition_id = :cid");
                $r = $stmt->execute([
                        ":cid"=>$result["id"]
        ]);
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($users as $user){
				//get user scores
                $stmt = $db->prepare("SELECT score FROM Scores where created > :start AND created < :end AND user_id = :id");
                $r = $stmt->execute([
                        ":start"=>$result["created"],
                        ":end"=>$result["expires"],
                        ":id"=>$user["user_id"]
        ]);
        //sum them up
        $sum = 0;
        $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($scores as $score){
        $sum += $score["score"];
        }
        //save the scores and users
        $score_arr[$i] = $sum;
        $user_arr[$i] = $user["user_id"];
        $i++;
                }
        //calc the top scores and users
        $first_place = array_search(max($score_arr),$score_arr);
        unset($score_arr[$first_place]);
        $second_place = array_search(max($score_arr),$score_arr);
        unset($score_arr[$second_place]);
        $third_place = array_search(max($score_arr),$score_arr);
        
        //calculate payouts
        $first_payout = ceil($result["reward"]*$result["first_place_per"]);
        $second_payout = ceil($result["reward"]*$result["second_place_per"]);
        $third_payout = ceil($result["reward"]*$result["third_place_per"]);
        
        //distribute payouts
		$stmt = $db->prepare("SELECT username FROM Users where id = :id");
		$r = $stmt->execute([
        ":id" => $user_arr[$first_place],
		]);  
		$name = $stmt->fetch(PDO::FETCH_ASSOC);
		$name_arr[$first_place] = $name["username"];
		
		$stmt = $db->prepare("SELECT username FROM Users where id = :id");
		$r = $stmt->execute([
        ":id" => $user_arr[$second_place],
		]);  
		$name = $stmt->fetch(PDO::FETCH_ASSOC);
		$name_arr[$second_place] = $name["username"];
		
		$stmt = $db->prepare("SELECT username FROM Users where id = :id");
		$r = $stmt->execute([
        ":id" => $user_arr[$third_place],
		]);  
		$name = $stmt->fetch(PDO::FETCH_ASSOC);
		$name_arr[$third_place] = $name["username"];
		
		$create_t = date('Y-m-d H:i:s');
		
        $reason = "Competition Win 1st Place";
        $stmt = $db->prepare("INSERT INTO PointsHistory (user_id, username, points_change,reason,created) VALUES(:user, :name, :point_change, :reason,:create_t)");
		$r = $stmt->execute([
        ":user" => $user_arr[$first_place],
        ":name" => $name_arr[$first_place],
        ":point_change" => $first_payout,
        ":reason" => $reason,
        ":create_t" => $create_t
		]);
		
		$reason = "Competition Win 2nd Place";
        $stmt = $db->prepare("INSERT INTO PointsHistory (user_id, username, points_change,reason,created) VALUES(:user, :name, :point_change, :reason,:create_t)");
		$r = $stmt->execute([
        ":user" => $user_arr[$second_place],
        ":name" => $name_arr[$second_place],
        ":point_change" => $second_payout,
        ":reason" => $reason,
        ":create_t" => $create_t
		]);
		
		$reason = "Competition Win 3rd Place";
        $stmt = $db->prepare("INSERT INTO PointsHistory (user_id, username, points_change,reason,created) VALUES(:user, :name, :point_change, :reason,:create_t)");
		$r = $stmt->execute([
        ":user" => $user_arr[$third_place],
        ":name" => $name_arr[$third_place],
        ":point_change" => $third_payout,
        ":reason" => $reason,
        ":create_t" => $create_t
		]);   
        //mark paid_out
         $stmt = $db->prepare("UPDATE Competitions set paid_out = :paid_out where id = :id");
                $r = $stmt->execute([
                        ":paid_out"=> 1,
                        ":id"=>$result["id"]
        ]);

        }
	}
}
        
        
        }
calc_competitions();

