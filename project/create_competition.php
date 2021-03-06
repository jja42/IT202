<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
?>
<?php
if (isset($_POST["name"])) {

    $cost = (int)$_POST["reward"];
    if ($cost <= 0) {
        $cost = 0;
    }
    $cost++;
    $reward = (int)$_POST["reward"];
    $db = getDB();
    $stmt = $db->prepare("SELECT points FROM Users where id = :id");
	$r = $stmt->execute([":id" => get_user_id()]);
	$points = $stmt->fetch(PDO::FETCH_ASSOC);
    $balance = $points["points"];
    if ($cost > $balance) {
        flash("You can't afford to create this competition", "warning");
    }
    else {
        $db = getDB();
        $expires = new DateTime();
        $days = (int)$_POST["duration"];
        $expires->add(new DateInterval("P" . $days . "D"));
        $expires = $expires->format("Y-m-d H:i:s");
        $query = "INSERT INTO Competitions (name, duration, expires, cost, min_score, first_place_per, second_place_per, third_place_per, fee, creator_id, reward) VALUES(:name, :duration, :expires, :cost, :min_score, :fp, :sp, :tp, :fee, :uid, :reward)";
        $stmt = $db->prepare($query);
        $params = [
            ":name" => $_POST["name"],
            ":duration" => $days,
            ":expires" => $expires,
            ":cost" => $cost,
            ":min_score" => $_POST["min_score"],
            ":uid" => get_user_id(),
            ":fee" => $_POST["fee"],
            ":reward" => $_POST["reward"]
        ];
        switch ((int)$_POST["split"]) {
            /* case 0:
                 break;  using default for this*/
            case 1:
                $params[":fp"] = .8;
                $params[":sp"] = .2;
                $params[":tp"] = 0;
                break;
            case 2:
                $params[":fp"] = .7;
                $params[":sp"] = .3;
                $params[":tp"] = 0;
                break;
            case 3:
                $params[":fp"] = .7;
                $params[":sp"] = .2;
                $params[":tp"] = .1;
                break;
            case 4:
                $params[":fp"] = .6;
                $params[":sp"] = .3;
                $params[":tp"] = .1;
                break;
            default:
                $params[":fp"] = 1;
                $params[":sp"] = 0;
                $params[":tp"] = 0;
                break;
        }
        $r = $stmt->execute($params);
        $point_change = $cost * -1;
        $reason = "Competition Creation";
        $create_t = date('Y-m-d H:i:s');
        if ($r) {
            flash("Successfully created competition", "success");
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
        }
        else {
            flash("There was a problem creating a competition: " . var_export($stmt->errorInfo(), true), "danger");
        }
    }
}
?>
    <div class="container-fluid">
        <h3>Create Competition</h3>
        <form method="POST">
            <div class="form-group">
                <label for="name">Name</label>
                <input id="name" name="name" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="d">Duration (in days)</label>
                <input id="d" name="duration" type="number" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="s">Minimum Required Score</label>
                <input id="s" name="min_score" type="number" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="r">Reward Split (First, Second, Third)</label>
                <select id="r" name="split" type="number" class="form-control">
                    <option value="0">100%</option>
                    <option value="1">80%/20%</option>
                    <option value="2">70%/30%</option>
                    <option value="3">70%/20%/10%</option>
                    <option value="4">60%/30%/10%</option>
                </select>
            </div>
            <div class="form-group">
                <label for="rw">Reward/Payout</label>
                <input id="rw" name="reward" type="number" class="form-control"/>
            </div>
            <div class="form-group">
                <label for="f">Entry Fee</label>
                <input id="f" name="fee" type="number" class="form-control"/>
            </div>
            <input type="submit" class="btn btn-success" value="Create"/>
        </form>
    </div>
<?php require(__DIR__ . "/partials/flash.php");
