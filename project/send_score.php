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
?>
