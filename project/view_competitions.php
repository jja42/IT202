<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>
<?php
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
if (isset($_POST["join"])) {
    $stmt = $db->prepare("SELECT points FROM Users where id = :id");
	$r = $stmt->execute([":id" => get_user_id()]);
	$points = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = $points["points"];
    //prevent user from joining expired or paid out comps
    $stmt = $db->prepare("select fee from Competitions where id = :id && expires > current_timestamp && paid_out = 0");
    $r = $stmt->execute([":id" => $_POST["cid"]]);
    if ($r) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $fee = (int)$result["fee"];
            if ($balance >= $fee) {
                $stmt = $db->prepare("INSERT INTO CompetitionParticipants (competition_id, user_id) VALUES(:cid, :uid)");
                $r = $stmt->execute([":cid" => $_POST["cid"], ":uid" => get_user_id()]);
                if ($r) {
					$stmt = $db->prepare("SELECT participants, reward FROM Competitions where id = :cid");
					$r = $stmt->execute([":cid" => $_POST["cid"]]);
					$result = $stmt->fetch(PDO::FETCH_ASSOC);
					$reward = $result["reward"];
					if($fee == 0){
						$reward += 1;}
					else{
						$reward += ceil($fee*.5);
					}
					$stmt = $db->prepare("UPDATE Competitions set participants =:participants, reward =:reward where id = :cid");
					$r = $stmt->execute([":participants" => $result[participants]+1, ":reward" => $reward, ":cid" => $_POST["cid"]]);

					$point_change = $fee * -1;
					$reason = "Competition Fee";
					$create_t = date('Y-m-d H:i:s');

					 $stmt = $db->prepare("INSERT INTO PointsHistory (user_id, username, points_change,reason,created) VALUES(:user, :name, :point_change, :reason,:create_t)");
					$r = $stmt->execute([
					":user" => get_user_id(),
					":name" => get_username(),
					":point_change" => $point_change,
					":reason" => $reason,
					":create_t" => $create_t
					]);

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

                    flash("Successfully join competition", "success");
                    die(header("Location: #"));
                }
                else {
                    flash("There was a problem joining the competition: " . var_export($stmt->errorInfo(), true), "danger");
                }
            }
            else {
                flash("You can't afford to join this competition, try again later", "warning");
            }
        }
        else {
            flash("Competition is unavailable", "warning");
        }
    }
    else {
        flash("Competition is unavailable", "warning");
    }
}

$stmt = $db->prepare("SELECT count(*) as total from Competitions where expires > current_timestamp AND paid_out = 0");
$stmt->execute([":id"=>get_user_id()]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($result){
    $total = (int)$result["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;
$stmt = $db->prepare("SELECT c.*, c_p.user_id as reg FROM Competitions c LEFT JOIN (SELECT * FROM CompetitionParticipants where user_id = :id) as c_p on c.id = c_p.competition_id WHERE c.expires > current_timestamp AND paid_out = 0 ORDER BY expires ASC LIMIT :offset, :count");
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
$stmt->bindValue(":id", get_user_id());
$r = $stmt->execute();
if ($r) {
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else {
    flash("There was a problem looking up competitions: " . var_export($stmt->errorInfo(), true), "danger");
}
?>
    <div class="container-fluid">
        <h3>Competitions</h3>
        <div class="list-group">
            <?php if (isset($results) && count($results)): ?>
                <?php foreach ($results as $r): ?>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col">
								Name: 
                                <a type="button" href="competition_scoreboard.php?id=<?php safer_echo($r["id"]); ?>"><?php safer_echo($r["name"]); ?></a>
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
                            <div class="col">
                                <?php if ($r["reg"] != get_user_id()): ?>
                                    <form method="POST">
                                        <input type="hidden" name="cid" value="<?php safer_echo($r["id"]); ?>"/>
                                        <input type="submit" name="join" class="btn btn-primary"
                                               value="Join (Cost: <?php safer_echo($r["fee"]); ?>)"/>
                                    </form>
                                <?php else: ?>
                                    Already Registered
				<p> </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="list-group-item">
                    No competitions available right now
                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
        <nav aria-label="Competitions">
            <ul class="pagination justify-content-center">
				<?php if (($page-1) > 0): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
                </li>
                <?php endif; ?>
                <?php if (($page+1) <= $total_pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page+1;?>">Next</a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

<?php require(__DIR__ . "/partials/flash.php");
