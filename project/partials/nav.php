<link rel="stylesheet" href="static/css/styles.css">
<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<nav>
<ul class="nav">
    <li><a href="home.php">Home</a></li>
    <?php if (!is_logged_in()): ?>
        <li><a href="login.php">Login</a></li>
        <li><a href="register.php">Register</a></li>
    <?php endif; ?>
    <?php if (is_logged_in()): ?>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="logout.php">Logout</a></li>
	<li><a href="test_create_scores.php">Create Scores</a></li>
    	<li><a href="test_list_scores.php">View Scores</a></li>
        <li><a href="test_create_pointhistory.php">Create Point Transaction</a></li>
        <li><a href="test_list_pointhistory.php">View Point Transactions</a></li>
	<li><a href="leaderboard.php">Leaderboard</a></li>
	<li><a href="index.html">Play</a></li>
    <?php endif; ?>
</ul>
</nav>
