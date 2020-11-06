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
	$score = $_POST["score"];
	$create_t = date('Y-m-d H:i:s');//calc
	$db = getDB();
	if(isset($id)){
		$result = [];
		$stmt = $db->prepare("SELECT id FROM Users where username = :name");
		$result = $stmt->execute([
     	  	":name"=>$name]);
		 $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if($result){
               $user = $result["id"];
               $stmt = $db->prepare("UPDATE Scores set user_id=:user, score=:score, created=:create_t where id=:id");
                $r = $stmt->execute([
                        ":user"=>$user,
                        ":score"=>$score,
                        ":create_t"=>$create_t,
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
	$stmt = $db->prepare("SELECT * FROM Scores where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt = $db->prepare("SELECT username FROM Users where id =:userid");
	$userid = $result["user_id"];
	$r = $stmt->execute([":userid"=>$userid]);
	$username = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
	<label>Name</label>
	<input name="name" placeholder="Name" value="<?php echo $username["username"];?>"/>
	<label>Score</label>
	<input type="number" min="1" name="score" value="<?php echo $result["score"];?>" />
	<input type="submit" name="save" value="Update"/>
</form>


<?php require(__DIR__ . "/partials/flash.php");
