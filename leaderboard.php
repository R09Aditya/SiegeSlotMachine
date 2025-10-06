<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    if (isset($_COOKIE['siege_user'])) {
        $user_id = $_COOKIE['siege_user'];
        $result = $conn->query("SELECT * FROM users WHERE id='$user_id'");
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['coins'] = $user['coins'];
        } else {
            header("Location: login_signup.php");
            exit();
        }
    } else {
        header("Location: login_signup.php");
        exit();
    }
}


$leaderboard = $conn->query("SELECT name, coins FROM users ORDER BY coins DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leaderboard - Siege Slot Machine</title>
<style>
body {
    font-family: 'Rajdhani', sans-serif;
    background: #2c3e50;
    color: #f1c40f;
    text-align: center;
    margin: 0;
    padding: 0;
}

.header {
    display: flex;
    align-items: center;
    padding: 10px;
    background: #34495e;
    position: relative;
}

.menu-icon {
    font-size: 24px;
    cursor: pointer;
    margin-right: 10px;
    user-select: none;
}

.profile-name {
    flex-grow: 1;
    text-align: left;
    font-size: 18px;
    font-weight: bold;
}

.container {
    max-width: 500px;
    margin: 20px auto;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th,
td {
    border: 1px solid #f1c40f;
    padding: 10px;
    text-align: center;
}

th {
    background: #34495e;
}

a {
    color: #f1c40f;
    text-decoration: none;
    margin-top: 10px;
    display: inline-block;
}
.sidebar {
    height: 100%;
    width: 0;
    position: fixed;
    z-index: 10;
    top: 0;
    left: 0;
    background-color: #2c3e50;
    overflow-x: hidden;
    transition: 0.3s;
    padding-top: 60px;
}
.sidebar a {
    padding: 10px 20px;
    text-decoration: none;
    font-size: 18px;
    color: #f1c40f;
    display: block;
    transition: 0.2s;
}
.sidebar a:hover {
    background: #34495e;
}

.sidebar .closebtn {
    position: absolute;
    top: 10px;
    right: 15px;
    font-size: 30px;
}
</style>
</head>
<body>
<div class="header">
    <div class="menu-icon" onclick="openNav()">☰</div>
    <div class="profile-name"><?php echo $_SESSION['name']; ?> | Coins: <?php echo $_SESSION['coins']; ?></div>
</div>


<div id="mySidebar" class="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
    <a href="slot.php">Back to Game</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
    <h1>Leaderboard</h1>
    <table>
        <tr>
            <th>Rank</th>
            <th>Name</th>
            <th>Coins</th>
        </tr>
        <?php
        $rank = 1;
        while($row = $leaderboard->fetch_assoc()){
            echo "<tr>
                    <td>{$rank}</td>
                    <td>{$row['name']}</td>
                    <td>{$row['coins']}</td>
                  </tr>";
            $rank++;
        }
        ?>
    </table>
</div>

<script>
function openNav() { document.getElementById("mySidebar").style.width = "250px"; }
function closeNav() { document.getElementById("mySidebar").style.width = "0"; }
</script>
</body>
</html>
