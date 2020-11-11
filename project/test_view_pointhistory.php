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
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT username,points_change,reason,created,user_id FROM PointsHistory where id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }
}
?>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <div>Transaction Date:</div>
				<?php safer_echo($result["created"]); ?>
				<p></p>
        </div>
        <div class="card-body">
            <div>
                <div>DETAILS</div>
                <div>UserName: <?php safer_echo($result["username"]); ?></div>
                <div>Reason: <?php safer_echo($result["reason"]); ?></div>
                <div>Point Change: <?php safer_echo($result["points_change"]); ?></div>
                <div>User ID: <?php safer_echo($result["user_id"]); ?></div>
            </div>
        </div>
    </div>
<?php else: ?>
    <p>Error looking up id...</p>
<?php endif; ?>
<?php require(__DIR__ . "/partials/flash.php");
