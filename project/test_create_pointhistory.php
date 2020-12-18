<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: home.php"));
}
?>
    <h3>Create Point Transaction</h3>
    <form method="POST">
        <label>Name</label>
        <input name="name" placeholder="Name"/>
        <label>Point Change</label>
        <input type="number" name="point_change"/>
        <label>Reason</label>
        <input name="reason" placeholder="Reason"/>
        <input type="submit" name="save" value="Create"/>
    </form>
<?php
if (isset($_POST["save"])) {
    //TODO add proper validation/checks
    $name = $_POST["name"];
    $point_change = $_POST["point_change"];
    $reason = $_POST["reason"];
    $user = get_user_id();
    $create_t = date('Y-m-d H:i:s');
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO PointsHistory (user_id, username, points_change,reason,created) VALUES(:user, :name, :point_change, :reason,:create_t)");
    $r = $stmt->execute([
        ":user" => $user,
        ":name" => $name,
        ":point_change" => $point_change,
        ":reason" => $reason,
        ":create_t" => $create_t
    ]);
    if ($r) {
        flash("Created successfully with id: " . $db->lastInsertId());
	$stmt = $db->prepare("SELECT points_change FROM PointsHistory WHERE user_id = :user");
					$r = $stmt->execute([
					":user" => get_user_id(),
					]);
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$points = 0;
					foreach ($results as $r):
					$points += $r["points_change"];
					endforeach;

					$stmt = $db->prepare("UPDATE Users set points=:points where id=:user");
					$r = $stmt->execute([
					":user" => get_user_id(),
					":points" => $points,
					]);
    }
    else {
        $e = $stmt->errorInfo();
        flash("Error creating: " . var_export($e, true));
    }
}
?>
<?php require(__DIR__ . "/partials/flash.php");
