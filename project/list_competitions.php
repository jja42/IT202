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

$stmt = $db->prepare("SELECT count(*) as total from Competitions where paid_out = 0");
$stmt->execute([":id"=>get_user_id()]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$total = 0;
if($result){
    $total = (int)$result["total"];
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;

$stmt = $db->prepare("SELECT * FROM Competitions WHERE paid_out = 0 ORDER BY expires ASC LIMIT :offset, :count");
$stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
$stmt->bindValue(":count", $per_page, PDO::PARAM_INT);
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
							<a type="button" href="modify_competitions.php?id=<?php safer_echo($r['id']); ?>"><?php safer_echo($r["name"]); ?></a>
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
                            </div>
                            <div class="col">
                             Expires:
                                <?php safer_echo($r["expires"]); ?>
                            </div>
                        <p> </p>
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

