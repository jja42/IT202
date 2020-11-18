<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$score = (int) $_POST["ScoreKey"];
$reason = "Scored in-game";
$user = get_user_id();
$create_t = date('Y-m-d H:i:s');
$db = getDB();
$stmt  = $db->prepare("SELECT * from Users where id = :q");
$r = $stmt->execute([":q"=>$user]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$name = $result["username"];
$stmt = $db->prepare("INSERT INTO PointsHistory (user_id, username, points_change,reason,created) VALUES(:user, :name, :point_change, :reason,:create_t)");
$r = $stmt->execute([
        ":user" => $user,
        ":name" => $name,
        ":point_change" => $score,
        ":reason" => $reason,
        ":create_t" => $create_t
    ]);
?>
