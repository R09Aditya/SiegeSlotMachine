<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');



$result = $conn->query("SELECT coins, last_claim, streak FROM users WHERE id=$user_id");
$user = $result->fetch_assoc();

$last_claim = $user['last_claim'];
$streak = $user['streak'];

if ($last_claim == $today) {
    echo json_encode(["error" => "You already claimed today's reward!"]);
    exit;
}

if ($last_claim == date('Y-m-d', strtotime('-1 day'))) {
    $streak += 1;
} else {
    $streak = 1;
}

$reward = 5 * $streak;
$new_coins = $user['coins'] + $reward;

$conn->query("UPDATE users SET coins=$new_coins, last_claim='$today', streak=$streak WHERE id=$user_id");
$_SESSION['coins'] = $new_coins;

echo json_encode(["success" => true, "reward" => $reward, "streak" => $streak, "coins" => $new_coins]);
?>
