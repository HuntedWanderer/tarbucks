<?php
include '_base.php';

$photo_preview = '/images/photo.jpg'; 
$form_values = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = req("user_id");
    $password = req("password");
    $confirm_password = req("confirm_password");
    $email = req("email");
    $fav_person = req("fav_person");
    $f = get_file('photo');
    $role = 'Member';
    
    $form_values = [
        'user_id' => $user_id,
        'email' => $email,
        'fav_person' => $fav_person
    ];
    
    if (empty($user_id)) {
        $_SESSION['error'] = "Username cannot be empty!";
    } elseif (strlen($user_id) < 5) {
        $_SESSION['error'] = "Username must be at least 5 characters!";
    }
    elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $_SESSION['error'] = "Password must contain at least 8 characters with uppercase, lowercase and numbers!";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match!";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format, e.g.: username@example.com";
    }
    elseif (!$f) {
        $_SESSION['error'] = 'Please upload a photo';
    } elseif (!str_starts_with($f->type, 'image/')) {
        $_SESSION['error'] = 'Only image files (JPG/PNG etc.) are allowed';
    } elseif ($f->size > 1 * 1024 * 1024) {
        $_SESSION['error'] = 'Image size cannot exceed 1MB';
    }
    else {
        $check = $_db->prepare("SELECT user_id FROM member WHERE user_id = ? OR email = ?");
        $check->execute([$user_id, $email]);
        
        if ($check->rowCount() > 0) {
            $_SESSION['error'] = "Username or email already in use!";
        } else {
            // Hash the password before storing
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // --- START S3 UPLOAD ---
$bucket = 'tarbucks-bucket'; // <--- UPDATE THIS
$region = 'us-east-1';

// 1. Get the uploaded file path from your helper function object ($f)
// Note: I am assuming $f is an object or array returned by your get_file() function.
// If get_file() returns an object with tmp_name, use $f->tmp_name
// If get_file() returns $_FILES['photo'], use $f['tmp_name']

// Standard PHP $_FILES approach (safest bet to replace your helper for the upload part):
$file_tmp = $_FILES['photo']['tmp_name'];
$file_name = basename($_FILES['photo']['name']);
$s3_filename = time() . "_" . $file_name; // Unique name

// 2. Upload to S3
$cmd = "aws s3 cp \"$file_tmp\" \"s3://$bucket/images/$s3_filename\" --region $region";
exec($cmd, $output, $return_var);

if ($return_var === 0) {
    $photo = $s3_filename; // Success
} else {
    $_SESSION['error'] = "Photo upload failed";
    // Redirect or stop execution if strictly required
}
// --- END S3 UPLOAD ---
            
            $stm = $_db->prepare('
                INSERT INTO member (user_id, password, email, fav_person, photo, role) 
                VALUES (?, ?, ?, ?, ?, ?) ');
            
            if ($stm->execute([$user_id, $hashed_password, $email, $fav_person, $photo, $role])) {
                $_SESSION['success'] = "Registration successful! You can now login.";
                $form_values = [];
            } else {
                $_SESSION['error'] = "Registration failed, database error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Tarbuck Coffee</title>
    <link rel="stylesheet" href="./zz.css">
    <script src="a.js"></script>
   

    <style>
      
        input[type="password"]::-ms-reveal,
        input[type="password"]::-webkit-credentials-auto-fill-button {
            display: none;
        }
        
        
    
        .upload {
            display: inline-block;
            cursor: pointer;
            border: 1px solid #ccc;
            padding: 5px;
            border-radius: 4px;
            background: #f9f9f9;
        }
    
    </style>
    <script>
       
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }

    </script>
</head>
<body>
<header class="header">
   
</header>
<?php include 'header.php'?>


<div class="register-container">
    <h2 style="text-align: center; margin-bottom: 20px;">Create New Account</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <input type="text" id="user_id" name="user_id" placeholder="Username (min 5 characters)" 
                   value="<?= htmlspecialchars($form_values['user_id'] ?? '') ?>" required>
        </div>
        
        <div class="form-group input-container">
            <input type="password" id="password" name="password" 
                   placeholder="Password " required>
            <span class="eye-icon" onclick="togglePassword('password')">👁</span>
        </div>
        
        <div class="form-group input-container">
            <input type="password" id="confirm_password" name="confirm_password" 
                   placeholder="Confirm Password" required>
            <span class="eye-icon" onclick="togglePassword('confirm_password')">👁</span>
        </div>
        
        <div class="form-group">
            <input type="email" id="email" name="email" placeholder="Your Email" 
                   value="<?= htmlspecialchars($form_values['email'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <input type="text" id="fav_person" name="fav_person" placeholder="Your Favorite Character" 
                   value="<?= htmlspecialchars($form_values['fav_person'] ?? '') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="photo">Profile Photo</label>
            <label class="upload" tabindex="0">
                <input type="file" id="photo" name="photo" accept="image/*" style="display: none;" required>
                <img id="photo-preview" src="/images/photo.jpg" alt="picture">
            </label>
            <p>Max 1MB (JPG/PNG)</p>
        </div>

        <button type="submit" class="submit-btn">Register</button>
    </form>
    
    <p style="text-align: center; margin-top: 15px;">Already have an account? <a href="head.php">Login here</a></p>
</div>
<script>
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const preview = document.getElementById('photo-preview');
    preview.src = URL.createObjectURL(file);

    preview.onload = function() {
        URL.revokeObjectURL(preview.src);
    };
});
</script>


</body>
</html>