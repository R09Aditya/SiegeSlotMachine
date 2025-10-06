<?php
session_start();
include 'db.php';


$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (isset($_POST['action']) && $_POST['action'] == "signup") {

        $name = $conn->real_escape_string($_POST['name']);
        $email = $conn->real_escape_string($_POST['email']);
        $mobile = $conn->real_escape_string($_POST['mobile']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $check = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            $message = "<span style='color:red'>Email already registered!</span>";
        } else {
            $sql = "INSERT INTO users (name, email, mobile, password, coins) 
                    VALUES ('$name', '$email', '$mobile', '$password', 10)";
            if ($conn->query($sql) === TRUE) {
                $message = "<span style='color:green'>Signup successful! You can now login.</span>";
            } else {
                $message = "<span style='color:red'>Error: " . $conn->error . "</span>";
            }
        }

    } elseif (isset($_POST['action']) && $_POST['action'] == "login") {

        $email = $conn->real_escape_string($_POST['email']);
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['coins'] = $user['coins'];

                header("Location: slot.php");
                exit();
            } else {
                $message = "<span style='color:red'>Please check your email and password again.</span>";
            }
        } else {
            $message = "<span style='color:red'>Please check your email and password again.</span>";
        }
    }
    if (password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['coins'] = $user['coins'];


    setcookie("siege_user", $user['id'], time() + 86400, "/");

    header("Location: slot.php");
    exit();
}


}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Siege Slot Login/Signup</title>
<style>
body {
    font-family: 'Rajdhani', sans-serif;
    background: #2c3e50;
    color: #f1c40f;
    text-align: center;
    padding: 50px;
}
.container {
    background: #34495e;
    padding: 20px;
    border-radius: 10px;
    display: inline-block;
    min-width: 300px;
}
input {
    padding: 10px;
    margin: 5px 0;
    width: 90%;
    border-radius: 5px;
    border: none;
}
button {
    padding: 10px 20px;
    margin-top: 10px;
    background: #f39c12;
    border: none;
    border-radius: 5px;
    color: white;
    cursor: pointer;
}
button:hover {
    background: #e67e22;
}
a {
    color: #f1c40f;
    cursor: pointer;
}
#formToggle {
    margin-bottom: 15px;
}
</style>
<script>
function showForm(form) {
    if(form === 'login') {
        document.getElementById('loginForm').style.display = 'block';
        document.getElementById('signupForm').style.display = 'none';
    } else {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('signupForm').style.display = 'block';
    }
}
</script>
</head>
<body>
<div class="container">
    <h2>Siege Slot Machine</h2>
    <div id="formToggle">
        <a onclick="showForm('login')">Login</a> | 
        <a onclick="showForm('signup')">Signup</a>
    </div>


    <div><?php echo $message; ?></div>

    <form id="loginForm" method="POST" style="display:block;">
        <input type="hidden" name="action" value="login">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>


    <form id="signupForm" method="POST" style="display:none;">
        <input type="hidden" name="action" value="signup">
        <input type="text" name="name" placeholder="Full Name" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="number" name="mobile" placeholder="Mobile" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Signup</button>
    </form>
</div>
</body>
</html>
