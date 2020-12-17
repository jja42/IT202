<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//Note: we have this up here, so our update happens before our get/fetch
//that way we'll fetch the updated data and have it correctly reflect on the form below
//As an exercise swap these two and see how things change
if (!is_logged_in() && !isset($_GET["id"])) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
else{
$id = get_user_id();
}
$page = 1;
$per_page = 10;
if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}
$db = getDB();
if($id != get_user_id()){
$stmt = $db->prepare("SELECT username,private FROM Users where id = :id");
$stmt->execute([":id"=>$id]);
$r = $stmt->fetch(PDO::FETCH_ASSOC);
if((int)$r["private"] == 1){
flash("You've been redirected! " . $r["username"] . " has a private profile.");
die(header("Location: home.php"));
}
}
$results = [];
if (isset($_GET["score"])) {
$stmt = $db->prepare("SELECT count(*) as total from Scores where user_id = :id");
$stmt->execute([":id"=>$id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($result){
    $total = (int)$result["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;

$stmt = $db->prepare("SELECT score, created FROM Scores WHERE user_id = :id ORDER BY created DESC LIMIT :offset, :count");
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":id", $id);
$r = $stmt->execute();
 if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching your scores " . var_export($stmt->errorInfo(), true));
    }
}
if (isset($_GET["comp"])) {
$stmt = $db->prepare("SELECT count(*) as total from CompetitionParticipants where user_id = :id");
$stmt->execute([":id"=>$id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($result){
    $total = (int)$result["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;

$stmt = $db->prepare("SELECT * FROM Competitions JOIN CompetitionParticipants ON CompetitionParticipants.competition_id = Competitions.id WHERE CompetitionParticipants.user_id  = :id ORDER BY expires DESC LIMIT :offset, :count");
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":id", $id);
$r = $stmt->execute();
if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem looking up competitions: " . var_export($stmt->errorInfo(), true), "danger");
}
}

$stmt = $db->prepare("SELECT points, username FROM Users where id = :id");
$r = $stmt->execute([":id" => $id]);
$points = $stmt->fetch(PDO::FETCH_ASSOC);
//save data if we submitted the form
if (isset($_POST["saved"])) {
    if(isset($_POST["private"])){
	$private = 1;
}
else{
$private = 0;
}
    $isValid = true;
    //check if our email changed
    $newEmail = get_email();
    if (get_email() != $_POST["email"]) {
        //TODO we'll need to check if the email is available
        $email = $_POST["email"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where email = :email");
        $stmt->execute([":email" => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
        }
        if ($inUse > 0) {
            flash("Email already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newEmail = $email;
        }
    }
    $newUsername = get_username();
    if (get_username() != $_POST["username"]) {
        $username = $_POST["username"];
        $stmt = $db->prepare("SELECT COUNT(1) as InUse from Users where username = :username");
        $stmt->execute([":username" => $username]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $inUse = 1;//default it to a failure scenario
        if ($result && isset($result["InUse"])) {
            try {
                $inUse = intval($result["InUse"]);
            }
            catch (Exception $e) {

            }
 }
        if ($inUse > 0) {
            flash("Username already in use");
            //for now we can just stop the rest of the update
            $isValid = false;
        }
        else {
            $newUsername = $username;
        }
    }
    if ($isValid) {
        $stmt = $db->prepare("UPDATE Users set email = :email, username = :username, private = :private where id = :id");
        $r = $stmt->execute([":email" => $newEmail, ":username" => $newUsername, ":private" => $private, ":id" => $id]);
        if ($r) {
            flash("Updated profile");
        }
        else {
            flash("Error updating profile");
        }
        $stmt = $db->prepare("UPDATE Scores set username= :username where user_id = :id");
        $r = $stmt->execute([":username" => $newUsername, ":id" => $id]);
        $stmt = $db->prepare("UPDATE PointHistory set username= :username where user_id = :id");
        $r = $stmt->execute([":username" => $newUsername, ":id" => $id]);
        //password is optional, so check if it's even set
        //if so, then check if it's a valid reset request
        if (!empty($_POST["password"]) && !empty($_POST["confirm"])) {
            if(!empty($_POST["old_password"])){
				$stmt = $db->prepare("SELECT password from Users WHERE id = :id LIMIT 1");
				$params = array(":id" => get_user_id());
				$r = $stmt->execute($params);
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
				$password_hash_from_db = $result["password"];
                $password = $_POST["old_password"];
		if (password_verify($password, $password_hash_from_db)) {
            if ($_POST["password"] == $_POST["confirm"]) {
                $password = $_POST["password"];
                $hash = password_hash($password, PASSWORD_BCRYPT);
                //this one we'll do separate
                $stmt = $db->prepare("UPDATE Users set password = :password where id = :id");
                $r = $stmt->execute([":id" => $id, ":password" => $hash]);
                if ($r) {
                    flash("Password Reset Successful");
                }
                else {
                    flash("Error resetting password");
                }
			}
            else {
                    flash("New Password Fields Must Match");
                }
			}
		else {
			flash("Incorrect Current Password");
		}
	}
			else{
				flash("You must enter your current password to reset your password");
			}
        }
//fetch/select fresh data in case anything changed
 $stmt = $db->prepare("SELECT email, username from Users WHERE id = :id LIMIT 1");
        $stmt->execute([":id" => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $email = $result["email"];
            $username = $result["username"];
            //let's update our session too
            $_SESSION["user"]["email"] = $email;
            $_SESSION["user"]["username"] = $username;
        }
    }
    else {
        //else for $isValid, though don't need to put anything here since the specific failure will output the message
    }
}


?>
    <div>
	<div></div> Username: <?php safer_echo($points["username"]);?></div>
	<p> </p>
    <div>Points:</div>
    <?php if (isset($points) && !empty($points)): ?>
    <div><?php safer_echo($points["points"]);?></div>
    <?php else: ?>
	<p>No Results To Display</p>
<?php endif; ?>
 <p></p>
 <?php if (isset($_GET["score"])):?>
    <div> Recent Scores</div>
<p> </p>
     <?php if (isset($results) && !empty($results)): ?>
	<div class = "results">
<?php foreach ($results as $r): ?>
        <div class="list-group-item">
	<div>
	<?php safer_echo("Score: "); ?>
	<?php safer_echo($r["score"]); ?>
	</div>
	<div>
	<?php safer_echo("Date: "); ?>
	<?php safer_echo($r["created"]); ?>
	<p></p>
	</div>
</div>
<?php endforeach; ?>
</div>
	<?php else: ?>
	<p>No Results To Display</p>
<?php endif; ?>
<p></p>
    </div>
    
    <nav aria-label="Scores">
            <ul class="pagination justify-content-center">
				<?php if (($page-1) > 0): ?>
                <li class="page-item">
                    <a class="page-link" href="?score&page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
                </li>
                <?php endif; ?>
                <?php if (($page+1) <= $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?score&page=<?php echo $page+1;?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
<?php endif; ?>

<?php if (isset($_GET["comp"])):?>
    <div> Competitions </div>
    <p> </p>
     <?php if (isset($results) && !empty($results)): ?>
	<div class = "results">
<?php foreach ($results as $r): ?>
        <div class="list-group-item">
	<div class="row">
                            <div class="col">
								Name: 
                                <?php safer_echo($r["name"]); ?>
                            </div>
                            <div class="col">
								Participants: 
                                <?php safer_echo($r["participants"]); ?>
                            </div>
                            <div class="col">
								Required Score: 
                                <?php safer_echo($r["min_score"]); ?>
                            </div>
                            <div class="col">
								Reward: 
                                <?php safer_echo($r["reward"]); ?>
                                <!--TODO show payout-->
                            </div>
                            <div class="col">
								Expires: 
                                <?php safer_echo($r["expires"]); ?>
                            </div>
	<p></p>
	</div>
</div>
<?php endforeach; ?>
</div>
	<?php else: ?>
	<p>No Results To Display</p>
<?php endif; ?>
<p></p>
    </div>
    
    <nav aria-label="Competitions">
            <ul class="pagination justify-content-center">
				<?php if (($page-1) > 0): ?>
                <li class="page-item">
                    <a class="page-link" href="?comp&page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
                </li>
                <?php endif; ?>
                <?php if (($page+1) <= $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?comp&page=<?php echo $page+1;?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
<?php endif; ?>

        <form method="GET">
		<a type="button" href="profile.php?id=<?php safer_echo($id); ?>&score">Scores</a>
		<p> </p>
		<a type="button" href="profile.php?id=<?php safer_echo($id); ?>&comp">Competition History</a>
		<?php if($id == get_user_id()): ?>
		<p> </p>
		<a type="button" href="profile.php?id=<?php safer_echo($id); ?>&acc">Update Account Details</a>
		<?php endif; ?>
	</form>
	<form method="POST">
        <?php if (isset($_GET["acc"])): ?>
        <label for="private">Private</label>
	<input type="checkbox" name="private" value="Private">
	<label for="email">Email</label>
        <input type="email" name="email" value="<?php safer_echo(get_email()); ?>"/>
        <label for="username">Username</label>
        <input type="text" maxlength="60" name="username" value="<?php safer_echo(get_username()); ?>"/>
        <!-- DO NOT PRELOAD PASSWORD-->
        <label for="pw">Current Password</label>
        <input type="password" name="old_password" minlength="4"/>
        <label for="pw">New Password</label>
        <input type="password" name="password" minlength="4"/>
        <label for="cpw">Confirm New Password</label>
        <input type="password" name="confirm" minlength="4"/>
        <input type="submit" name="saved" value="Save Profile"/>
        <?php endif; ?>
    </form>
<?php require(__DIR__ . "/partials/flash.php");
