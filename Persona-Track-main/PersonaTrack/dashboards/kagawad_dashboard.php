<?php
$committee = $_SESSION['committee'] ?? "General Committee";
?>

<!DOCTYPE html>
<html>
<head>
<title>Kagawad Dashboard</title>
<link rel="stylesheet" href="../styles/dashboard.css">
</head>

<body>
<?php include "../includes/topbar.php"; ?>

<h1>Kagawad Dashboard</h1>
<h3>Committee: <?php echo htmlspecialchars($committee); ?></h3>

<div class="cards">
    <div class="card"><h2>12</h2><p>Pending Requests</p></div>
    <div class="card"><h2>5</h2><p>Community Complaints</p></div>
    <div class="card"><h2>3</h2><p>Active Projects</p></div>
</div>

<div class="module-links">
    <a class="module-btn" href="../tickets/index.php">Assigned Cases</a>
    <a class="module-btn" href="../projects/index.php">Project Monitoring</a>
</div>

</body>
</html>
