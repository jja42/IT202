<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
else{
flash("Competition ID Must Be Provided");
die(header("Location: home.php"));
}
$i = 0;
$score_arr = array();
$user_arr = array();
$id_arr = array();
$name_arr = array();
$scoreboard_arr = array();
$db = getDB();

$stmt = $db->prepare("SELECT * FROM Competitions where id = :id LIMIT 1");
$r = $stmt->execute([":id" => $id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $db->prepare("SELECT user_id FROM CompetitionParticipants where competition_id = :id");
$r = $stmt->execute([":id" => $id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as $user){
$stmt = $db->prepare("SELECT score FROM Scores where created > :start AND created < :end AND user_id = :id");
                $r = $stmt->execute([
                        ":start"=>$result[0]["created"],
                        ":end"=>$result[0]["expires"],
                        ":id"=>$user["user_id"]
        ]);
 $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
 foreach ($scores as $score){
        //save the scores and users
        $score_arr[$i] = $score["score"];
        $user_arr[$i] = $user["user_id"];
        $i++;
}
}
$max = 10;
if(count($score_arr)<$max){
$max = count($score_arr);
}
for($i = 0; $i<$max; $i++){
$scoreboard_arr[$i] = max($score_arr);
$id_arr[$i] = $user_arr[array_search(max($score_arr),$score_arr)];
unset($score_arr[array_search(max($score_arr),$score_arr)]);
$stmt = $db->prepare("SELECT username FROM Users where id = :id LIMIT 1");
$r = $stmt->execute([":id" => $id_arr[$i]]);
$name = $stmt->fetchAll(PDO::FETCH_ASSOC);
$name_arr[$i] = $name[0]["username"];
}  
?>
<div class="container-fluid">
        <h3>Competition Scoreboard</h3>
        <div class="list-group">
            <?php if (!empty($scoreboard_arr)): ?>
                <?php for($i = 0; $i<$max; $i++): ?>
                    <div class="list-group-item">
                        <div class="row">
                            <div class="col">
				#<?php safer_echo($i+1);?>
								Username: 
								<a type="button" href="profile.php?id=<?php safer_echo($id_arr[i]); ?>"><?php safer_echo($name_arr[$i]); ?></a>
                            </div>
				<div class="col">
                            Score: 
                                <?php safer_echo($scoreboard_arr[$i]); ?>
                           </div>
				<p> </p>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            <?php else: ?>
                <div class="list-group-item">
                    No scores available right now
                </div>
            <?php endif; ?>
        </div>
    </div>
    </div>
