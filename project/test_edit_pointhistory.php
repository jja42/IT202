<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: home.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if(isset($_GET["id"])){
	$id = $_GET["id"];
}
?>
<?php
//saving
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$name = $_POST["name"];
	$points_change = $_POST["points_change"];
	$reason = $_POST["reason"];
	$create_t = date('Y-m-d H:i:s');
	$user = get_user_id();
	$db = getDB();
	if(isset($id)){
		$stmt = $db->prepare("UPDATE PointsHistory set user_id=:user, points_change=:points_change, reason=:reason,created=:create_t, username=:name where id=:id");
        $r = $stmt->execute([
			":user"=>$user,
			":points_change"=>$points_change,
			":reason"=>$reason,
			":create_t"=>$create_t,
			":name"=>$name,
			":id"=>$id
	]);
		if($r){
			flash("Updated successfully with id: " . $id);
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error updating: " . var_export($e, true));
		}
		}
	else{
		flash("ID isn't set, we need an ID in order to update");
	}
}
?>
<?php
//fetching
$result = [];
if(isset($id)){
	$id = $_GET["id"];
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM PointsHistory where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
	<label>Name</label>
	<input name="name" placeholder="Name" value="<?php echo $result["username"];?>"/>
	<label>Point Change</label>
	<input type="number" name="points_change" value="<?php echo $result["points_change"];?>" />
	<label>Reason</label>
	<input name="reason" placeholder="Reason" value="<?php echo $result["reason"];?>"/>
	<input type="submit" name="save" value="Update"/>
</form>


<?php require(__DIR__ . "/partials/flash.php");
