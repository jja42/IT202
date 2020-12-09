<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
$score = (int) $_POST["ScoreKey"];
$user = get_user_id();
$create_t = date('Y-m-d H:i:s');
$db = getDB();
$stmt  = $db->prepare("SELECT * from Scores where user_id = :q");
$r = $stmt->execute([":q"=>$user]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$name = $result["username"];
$stmt = $db->prepare("INSERT INTO Scores (user_id, username, score,created) VALUES(:user, :name, :score, :create_t)");
$r = $stmt->execute([
        ":user" => $user,
        ":name" => $name,
        ":score" => $score,
        ":create_t" => $create_t
    ]);
    
$point_change = $score/10;
$reason = "Scored in-game";
$stmt = $db->prepare("INSERT INTO PointsHistory (user_id, username, points_change,reason,created) VALUES(:user, :name, :point_change, :reason,:create_t)");
    $r = $stmt->execute([
        ":user" => $user,
        ":name" => $name,
        ":point_change" => $point_change,
        ":reason" => $reason,
        ":create_t" => $create_t
    ]);

$stmt = $db->prepare("SELECT `points_change` FROM `PointsHistory` WHERE user_id = :user");
    $r = $stmt->execute([
        ":user" => $user,
    ]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
var_export($results,false);
$points = 0;
foreach ($results as $r):
$points += $r["points_change"];
endforeach;

$stmt = $db->prepare("UPDATE Users set points=:points where id=:user");
$r = $stmt->execute([
        ":user" => $user,
        ":points" => $points,
    ]);
?>
