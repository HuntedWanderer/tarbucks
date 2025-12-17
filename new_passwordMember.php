<?php
require '_base.php';

if (!isset($_SESSION['reset_user'])) {
    header("Location: forget_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    
    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "Please enter and confirm your new password";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/', $new_password)) {
        $_SESSION['error'] = "Password must be at least 8 characters with uppercase, lowercase and numbers";
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
    } else {

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
  
        $stmt = $_db->prepare("UPDATE member SET password = ? WHERE user_id = ?");
        if ($stmt->execute([$hashed_password, $_SESSION['reset_user']])) {
            $_SESSION['success'] = "Password reset successful! Please login with your new password";
            unset($_SESSION['reset_user']);
            header("Location: profileMember.php");
            exit();
        } else {
            $_SESSION['error'] = "Password update failed, please try again";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - Tarbuck Coffee</title>
    <link rel="stylesheet" href="./zz.css">
    <script src="a.js"></script>
    <style>
     
        /*这串代码是为了删掉web自带的显示密码*/
        input[type="password"]::-ms-reveal,
        input[type="password"]::-webkit-credentials-auto-fill-button {
    display: none;
}
        .newpass-container {
            width: 300px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f8f8f8;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .input-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 310px;
        }
        .newpass-container input {
            width: 250px;
            padding: 10px;
            margin-top: 10px;
            border: 3px solid #ccc;
            border-radius: 10px;
            padding-right: 40px; 
        }
        .eye-icon {
            position: absolute;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
        }
        .newpass-container button {
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
 
    </style>
</head>
<body>
<header class="header">
    <img class="logo" src="logo.webp" alt="Tarbuck Coffee Logo">
    <t2 class id ="title"><strong>Tarbuck Coffee</strong></t2>
</header>
<div class="newpass-container">
    <h2>Set New Password</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST">
        <div class="input-container">
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
            <span class="eye-icon" onclick="togglePassword('new_password')">👁</span>
        </div><br>
        <div class="input-container">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
            <span class="eye-icon" onclick="togglePassword('confirm_password')">👁</span>
        </div><br>
        <button type="submit">Reset Password</button>
    </form>
</div>

<script>
    function togglePassword(inputId) {
        let input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
        } else {
            input.type = "password";
        }
    }
</script>

</body>
</html>