<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: home.php"));
}
?>
<?php
if(isset($_GET["id"])){
$id = $_GET["id"];
}
else{
flash("Competition ID Must Be Provided");
die(header("Location: list_competitions.php"));
}
$db = getDB();
$stmt = $db->prepare("SELECT paid_out from Competitions where id = :id LIMIT 1");
$stmt->execute([":id"=>$id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if($result["paid_out"] == 1){
flash("You cannot modify a competition that has already been paid out");
die(header("Location: list_competitions.php"));
}

if (isset($_POST["save"])) {
    $reward = (int)$_POST["reward"];
        $expires = new DateTime();
        $days = (int)$_POST["duration"];
        $expires->add(new DateInterval("P" . $days . "D"));
        $expires = $expires->format("Y-m-d H:i:s");
        $query = "UPDATE Competitions set duration =:duration, expires=:expires, min_score=:min_score, first_place_per=:fp, second_place_per=:sp, third_place_per=:tp, fee=:fee, reward=:reward where id=:id";
        $stmt = $db->prepare($query);
        $params = [
            ":duration" => $days,
            ":expires" => $expires,
            ":min_score" => $_POST["min_score"],
            ":fee" => $_POST["fee"],
            ":reward" => $_POST["reward"],
	    ":id" => $id
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
        if ($r) {
            flash("Successfully modified competition", "success");
        }
        else {
            flash("There was a problem modifying competition: " . var_export($stmt->errorInfo(), true), "danger");
        }
}
?>
    <div class="container-fluid">
        <h3>Modify Competition</h3>
        <form method="POST">
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
            <input type="submit" name="save" class="btn btn-success" value="Modify"/>
        </form>
    </div>
<?php require(__DIR__ . "/partials/flash.php");
