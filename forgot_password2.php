<?php
require '_base.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = trim($_POST["user_id"]);
    $email = trim($_POST["email"]);
    $fav_person = trim($_POST["fav_person"]);
    
    $stmt = $_db->prepare("SELECT * FROM member WHERE user_id = ? AND email = ? AND fav_person = ?");
    $stmt->execute([$user_id, $email, $fav_person]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['reset_user'] = $user_id;
        header("Location: new_password2.php");
        exit();
    } else {
        $_SESSION['error'] = "Information incorrect";

    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Recovery - Tarbuck Coffee</title>
    <link rel="stylesheet" href="./zz.css">
    <style>
        .forget-container {
            width: 300px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f8f8f8;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .forget-container input {
            width: 250px;
            padding: 10px;
            margin-top: 10px;
            border: 3px solid #ccc;
            border-radius: 10px;
        }
        .forget-container button {
            background-color: lightgreen;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            padding: 10px;
            width: 100%;
            border-radius: 10px;
        }
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<header class="header">
    <img class="logo" src="logo.webp" alt="Tarbuck Coffee Logo">
    <t2 class id ="title"><strong>Tarbuck Coffee</strong></t2>
</header>
<div class="forget-container">
    <h2>Forget Password?</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="user_id" placeholder="Enter your username" required><br>
        <input type="email" name="email" placeholder="Enter your registered email" required><br>
        <input type="text" name="fav_person" placeholder="Enter your favorite person" required><br>
        <button type="submit">Confirm</button>
    </form>
    
    <p>Return to the profile? <a href="profile.php">Profile</a></p>
</div>


</body>
</html>