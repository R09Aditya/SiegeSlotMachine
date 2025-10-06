<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$week_start = date('Y-m-d', strtotime('monday this week'));


$check = $conn->query("SELECT * FROM lucky_draw WHERE user_id=$user_id AND week_start='$week_start'");
if ($check->num_rows > 0) {
    echo json_encode(["error" => "You already joined this week's lucky draw!"]);
    exit;
}


$res = $conn->query("SELECT coins FROM users WHERE id=$user_id");
$user = $res->fetch_assoc();
if ($user['coins'] < 50) {
    echo json_encode(["error" => "Not enough coins"]);
    exit;
}

$new_coins = $user['coins'] - 50;
$conn->query("UPDATE users SET coins=$new_coins WHERE id=$user_id");


$conn->query("INSERT INTO lucky_draw (user_id, coins_entered, week_start) VALUES ($user_id, 50, '$week_start')");
$_SESSION['coins'] = $new_coins;

echo json_encode(["success" => true, "coins" => $new_coins]);
?>
