<?php
require '_base.php'; // 1. Use Master Base File

// Security: Ensure user has passed the security question/email check
if (!isset($_SESSION['reset_user'])) {
    redirect("forget_password.php");
}

if (is_post()) {
    $new_password = post("new_password");
    $confirm_password = post("confirm_password");
    
    // Validation
    if (empty($new_password) || empty($confirm_password)) {
        temp('error', "Please enter and confirm your new password");
    } 
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/', $new_password)) {
        temp('error', "Password must be at least 8 characters with uppercase, lowercase and numbers");
    } 
    elseif ($new_password !== $confirm_password) {
        temp('error', "Passwords do not match");
    } 
    else {
        // Hash and Update
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // NOTE: We assume $_SESSION['reset_user'] holds the 'user_id' (string username).
        // If it holds the numeric 'id', change the WHERE clause to 'WHERE id = ?'
        $stmt = $_db->prepare("UPDATE member SET password = ? WHERE user_id = ?");
        
        if ($stmt->execute([$hashed_password, $_SESSION['reset_user']])) {
            
            // Cleanup and Redirect
            unset($_SESSION['reset_user']);
            temp('success', "Password reset successful! Please login.");
            redirect("head.php"); // Redirect to login page
            
        } else {
            temp('error', "System error. Please try again.");
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
    <link rel="stylesheet" href="/css/style.css"> <style>
        /* Specific styles for this page */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none;
        }
        .newpass-container {
            width: 300px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .input-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
            margin-bottom: 15px;
        }
        .newpass-container input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            box-sizing: border-box; /* Important for padding */
        }
        .eye-icon {
            position: absolute;
            right: 10px;
            cursor: pointer;
            color: #666;
            user-select: none;
        }
        .newpass-container button {
            background-color: #4caf50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .newpass-container button:hover {
            background-color: #45a049;
        }
        .header {
            text-align: center;
            padding: 20px;
            background-color: #fff;
            border-bottom: 1px solid #eee;
        }
        .logo {
            width: 80px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<header class="header">
    <img class="logo" src="logo.webp" alt="Tarbuck Coffee Logo">
    <h1 style="display:inline; margin-left:10px; font-size:24px; color:#333;">Tarbuck Coffee</h1>
</header>

<div class="newpass-container">
    <h2>Set New Password</h2>

    <div style="color:red; margin-bottom:15px;"><?= temp('error') ?></div>

    <form method="POST">
        <div class="input-container">
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
            <span class="eye-icon" onclick="togglePassword('new_password')">👁</span>
        </div>
        
        <div class="input-container">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
            <span class="eye-icon" onclick="togglePassword('confirm_password')">👁</span>
        </div>
        
        <button type="submit">Reset Password</button>
        
        <div style="margin-top:15px;">
            <a href="head.php" style="color:#666; text-decoration:none; font-size:0.9em;">Cancel</a>
        </div>
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