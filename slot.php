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

if (isset($_POST['action']) && $_POST['action'] == 'spin') {
    $user_id = $_SESSION['user_id'];
    $multiplier = intval($_POST['multiplier']);
    if($multiplier < 1) $multiplier = 1;

    $result = $conn->query("SELECT coins FROM users WHERE id=$user_id");
    $row = $result->fetch_assoc();
    $coins = $row['coins'];

    $spinCost = 1 * $multiplier;
    if ($coins < $spinCost) {
        echo json_encode(["error" => "Not enough coins for this multiplier"]);
        exit;
    }

    $coins -= $spinCost;

    $slot1 = rand(1,4);
    $slot2 = rand(1,4);
    $slot3 = rand(1,4);

    $win = 0;
    if ($slot1 == $slot2 && $slot2 == $slot3) $win = 5;
    elseif ($slot1 == $slot2 || $slot2 == $slot3 || $slot1 == $slot3) $win = 2;

    $win *= $multiplier;
    $coins += $win;

    $conn->query("UPDATE users SET coins=$coins WHERE id=$user_id");
    $_SESSION['coins'] = $coins;

    echo json_encode([
        "slots" => [$slot1, $slot2, $slot3],
        "win" => $win,
        "coins" => $coins
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="slot.php">
<title>Siege Slot Machine</title>

</head>
<body>
<div class="header">
    <div class="menu-icon" onclick="openNav()">☰</div>
    <div class="profile-name"><?php echo $_SESSION['name']; ?> | Coins: <span id="coinCount"><?php echo $_SESSION['coins']; ?></span></div>
</div>

<div id="mySidebar" class="sidebar">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
    <a href="leaderboard.php">Leaderboard</a>
    <a href="javascript:void(0)" onclick="claimReward()">Claim Daily Reward</a>
    <a href="javascript:void(0)" onclick="joinLuckyDraw()">Join Lucky Draw (50 coins)</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
    <h1>Siege Slot Machine</h1>
    <label for="multiplier">Multiplier:</label>
    <select id="multiplier">
        <option value="1">1x</option>
        <option value="2">2x</option>
        <option value="3">3x</option>
    </select>
   <div class="slot-machine">
    <img src="symbols/slot.png" class="machine-frame" alt="Slot Machine Frame">
        <div class="reel" id="reel1"><img src="symbols/1.png" alt="slot1"></div>
        <div class="reel" id="reel2"><img src="symbols/1.png" alt="slot2"></div>
        <div class="reel" id="reel3"><img src="symbols/1.png" alt="slot3"></div>
    </div>
    <div class="lever-container">
        <div class="lever" id="lever">
            <div class="lever-ball"></div>
        </div>
    </div>
    <div id="message"></div>
</div>

<script>
function openNav() { document.getElementById("mySidebar").style.width = "250px"; }
function closeNav() { document.getElementById("mySidebar").style.width = "0"; }

const reels = [
    document.getElementById('reel1').querySelector('img'),
    document.getElementById('reel2').querySelector('img'),
    document.getElementById('reel3').querySelector('img')
];
const coinElem = document.getElementById('coinCount');
const msgElem = document.getElementById('message');
let spinning = false;

document.getElementById('lever').addEventListener('click', () => {
    if(spinning) return;
    const lever = document.getElementById('lever');
    lever.style.transform = 'rotateZ(60deg)';
    setTimeout(()=>{ lever.style.transform = 'rotateZ(0deg)'; }, 400);
    let coins = parseInt(coinElem.innerText);
    let multiplier = parseInt(document.getElementById('multiplier').value);
    if(coins < multiplier){
        msgElem.style.color = 'red';
        msgElem.innerText = "Not enough coins for this multiplier!";
        return;
    }
    spinning = true;
    msgElem.innerText = "";
    fetch('slot.php', {
        method: 'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: 'action=spin&multiplier='+multiplier
    })
    .then(res => res.json())
    .then(data => {
        if(data.error){
            msgElem.style.color = 'red';
            msgElem.innerText = data.error;
            spinning = false;
            return;
        }
        let spins = 20;
        let i = 0;
        let interval = setInterval(() => {
            for(let j=0;j<3;j++){
                reels[j].style.transform = `rotateX(${Math.random()*1080}deg)`;
                reels[j].src = `symbols/${Math.floor(Math.random()*4 +1)}.png`;
            }
            i++;
            if(i>=spins){
                clearInterval(interval);
                for(let j=0;j<3;j++){ 
                    reels[j].src = `symbols/${data.slots[j]}.png`;
                    reels[j].style.transform = 'rotateX(0deg)';
                }
                coinElem.innerText = data.coins;
                if(data.win>0){
                    msgElem.style.color = 'lightgreen';
                    msgElem.innerText = `You won ${data.win} coin(s)! 🎉`;
                }else{
                    msgElem.style.color = 'red';
                    msgElem.innerText = `No match. Try again!`;
                }
                spinning = false;
            }
        },90);
    });
});

function claimReward(){
    fetch('daily_reward.php')
    .then(res=>res.json())
    .then(data=>{
        if(data.error) alert(data.error);
        else {
            alert(`You got ${data.reward} coins! Streak: ${data.streak}`);
            document.getElementById('coinCount').innerText = data.coins;
        }
    });
}

function joinLuckyDraw(){
    fetch('join_luckydraw.php')
    .then(res=>res.json())
    .then(data=>{
        if(data.error) alert(data.error);
        else {
            alert("You joined the lucky draw! Winner will be announced Sunday.");
            document.getElementById('coinCount').innerText = data.coins;
        }
    });
}
</script>
</body>
</html>
