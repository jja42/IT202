<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
}
else{
$email = "Guest.";
}
?>
    <p>Welcome, <?php echo $email; ?></p>
<?php
$weekago = date('Y-m-d H:i:s', time() + (60 * 60 * 24 * -7) );
//utilizing similar methods for month and approximating weeks
$monthago = date('Y-m-d H:i:s', time() + (60 * 60 * 24 * -7 * 4.35));
$now = date('Y-m-d H:i:s');
$topscores = "";
$results = [];
$db = getDB();
if (isset($_POST["lifetime"])) {
<<<<<<< HEAD
    $stmt = $db->prepare("SELECT username, score, user_id, created FROM Scores ORDER BY score DESC LIMIT 10");
=======
    $stmt = $db->prepare("SELECT username, score, created FROM Scores ORDER BY score DESC LIMIT 10");
>>>>>>> 706f88c47110f0ef9e79561d97b3a6232d1dde64
    $r = $stmt->execute();
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }
    $topscores = "Top All-Time Scores";
}

if (isset($_POST["weekly"])) {
<<<<<<< HEAD
    $stmt = $db->prepare("SELECT username, score, user_id, created FROM Scores WHERE created BETWEEN :weekago AND :now ORDER BY score DESC LIMIT 10");
=======
    $stmt = $db->prepare("SELECT username, score, created FROM Scores WHERE created BETWEEN :weekago AND :now ORDER BY score DESC LIMIT 10");
>>>>>>> 706f88c47110f0ef9e79561d97b3a6232d1dde64
    $r = $stmt->execute([
	":weekago" => $weekago,
        ":now" => $now
]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }
    $topscores = "Top Weekly Scores";
}

if (isset($_POST["monthly"])) {
<<<<<<< HEAD
     $stmt = $db->prepare("SELECT username, score, user_id, created FROM Scores WHERE created BETWEEN :monthago AND :now ORDER BY score DESC LIMIT 10");
=======
     $stmt = $db->prepare("SELECT username, score, created FROM Scores WHERE created BETWEEN :monthago AND :now ORDER BY score DESC LIMIT 10");
>>>>>>> 706f88c47110f0ef9e79561d97b3a6232d1dde64
    $r = $stmt->execute([
        ":monthago" => $monthago,
        ":now" => $now
]);
    if($r){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo(), true));
    }
    $topscores = "Top Monthly Scores";
}

if(!isset($_POST["lifetime"]) && !isset($_POST["monthly"]) && !isset($_POST["weekly"])){
	flash("Please Select a Leaderboard Category");
}
?>
<form method="POST">
<div>
<a type="button" href="game.php">Play</a>
</div>
<input type="submit" name="weekly" value="Weekly"/>
<input type="submit" name="monthly" value="Monthly"/>
<input type="submit" name="lifetime" value="Lifetime"/>
</form>

<div> <?php safer_echo($topscores);?> <p></p> </div>
<?php if (isset($results) && !empty($results)): ?>
<div class = "results">
<?php foreach ($results as $r): ?>
<div class="list-group-item">
        <div>
                        <?php safer_echo("User: "); ?>
<<<<<<< HEAD
			<div>
						<a type="button" href="profile.php?id=<?php safer_echo($r["user_id"]); ?>"><?php safer_echo($r["username"]); ?></a>
	</div>
=======
			<?php safer_echo($r["username"]); ?>
>>>>>>> 706f88c47110f0ef9e79561d97b3a6232d1dde64
	</div>
	<div>
                        <?php safer_echo("Score: "); ?>
                        <?php safer_echo($r["score"]); ?>
	</div>
	<div>
			<?php safer_echo("Time Achieved: "); ?>
			<?php safer_echo($r["created"]); ?>
                <p></p>
        </div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p>No Results To Display</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
