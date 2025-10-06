<?php
include 'db.php';

$week_start = date('Y-m-d', strtotime('monday last week'));

$res = $conn->query("SELECT * FROM lucky_draw WHERE week_start='$week_start'");
if ($res->num_rows == 0) {
    die("No entries this week.");
}


$entries = [];
while($row = $res->fetch_assoc()) {
    $entries[] = $row;
}
$winner = $entries[array_rand($entries)];
$prize = $winner['coins_entered'] * 5;

$conn->query("UPDATE users SET coins = coins + $prize WHERE id=".$winner['user_id']);


$conn->query("DELETE FROM lucky_draw WHERE week_start='$week_start'");

echo "Winner: User ID ".$winner['user_id']." won $prize coins!";
?>
