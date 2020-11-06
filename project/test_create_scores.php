<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: home.php"));
}
?>

<form method="POST">
	<label>Name</label>
	<input name="name" placeholder="Name"/>
	<label>Score</label>
	<input type="number" min="1" name="score"/>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$name = $_POST["name"];
	$score = $_POST["score"];
	$create_t = date('Y-m-d H:i:s');//calc
	$db = getDB();
	$result = [];
	$stmt = $db->prepare("SELECT id FROM Users where username = :name");
	$result = $stmt->execute([
	":name"=>$name]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	if($result){
		$user = $result["id"];
		//flash("ID :" . var_export($user,true));
		$stmt = $db->prepare("INSERT INTO Scores (user_id,score,created) VALUES(:user, :score, :create_t)");
		$r = $stmt->execute([
			":user"=>$user,
			":score"=>$score,
			":create_t"=>$create_t
		]);
		if($r){
			flash("Created successfully with id: " . $db->lastInsertId());
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error creating: " . var_export($e, true));
		}
	}
	else{
		flash("Invalid Username");
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");
