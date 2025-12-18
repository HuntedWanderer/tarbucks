<?php
include '_base.php';

// if (!is_logged_in()) {
//     temp('info', 'Please login first');
//     redirect('head.php');
// }

$user_id = $_SESSION['user']->user_id;
$stmt = $_db->prepare("SELECT * FROM member WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$_SESSION['user'] = $user;

if (is_post()) {
    $email = post('email');
    $user_id = post('user_id');
    $fav = post('fav_person');
    $f = get_file('photo');

    $_err = [];

    if ($user_id != $user->user_id && !is_unique($user_id, 'member', 'user_id')) {
        $_err['user_id'] = 'User ID already exists';
    }
    elseif (strlen($user_id) < 5) {
        $_err['user_id'] = "Username must be at least 5 characters!";
    }

    if (!is_email($email)) {
        $_err['email'] = 'Invalid email format';
    }
    if ($email != $user->email && !is_unique($email, 'member', 'email')) {
        $_err['user_id'] = 'Email already exists';
    }

    $photo = $user->photo;
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Only image files are allowed';
        } elseif ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Image size cannot exceed 1MB';
        } else {
            // --- START S3 UPLOAD FIX ---
            $bucket = 'tarbucks-bucket'; // <--- UPDATE THIS!
            
            // 1. Delete OLD photo from S3 (optional, keeps bucket clean)
            if (!empty($user->photo)) {
                 $oldS3Path = "s3://{$bucket}/images/" . $user->photo;
                 exec("aws s3 rm \"{$oldS3Path}\" --region us-east-1 > /dev/null 2>&1");
            }

            // 2. Upload NEW photo to S3
            $file_tmp = $_FILES['photo']['tmp_name'];
            $file_name = basename($_FILES['photo']['name']);
            $new_s3_name = time() . "_" . $file_name; // Unique timestamped name
            
            // Run the upload command
            $cmd = "aws s3 cp \"$file_tmp\" \"s3://$bucket/images/$new_s3_name\" --region us-east-1";
            exec($cmd, $output, $return_var);

            if ($return_var === 0) {
                $photo = $new_s3_name; // Success! Save this name to DB
            } else {
                $_err['photo'] = "Failed to upload to S3";
            }
            // --- END S3 UPLOAD FIX ---
        }
    }

    if (empty($_err)) {
        try {
            $stm = $_db->prepare("
                UPDATE member 
                SET user_id = ?, email = ?, fav_person = ?, photo = ?
                WHERE id = ?
            ");
            $params = [$user_id, $email, $fav, $photo, $user->id];
            $stm->execute($params);

            $user->user_id = $user_id;
            $user->email = $email;
            $user->fav_person = $fav;
            $user->photo = $photo;
            $_SESSION['user'] = $user;

            temp('info', 'Profile updated successfully');
            redirect('profile.php');
            
        } catch (PDOException $e) {
            temp('info', 'Error updating profile: ' . $e->getMessage());
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TARBUCK</title>
    <link rel="icon" href="/images/logo.webp">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="zz.css">
    <script src="/js/script.js"></script>
    <style>
        .profile-header {
            position: absolute;
            top: 5px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .profile-form {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #f8f8f8;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .photo-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            margin: 10px 0;
        }

        .photo-upload-area {
            display: inline-block;
            border: 2px dashed #ccc;
            border-radius: 8px;
            padding: 10px;
            cursor: pointer;
            transition: border-color 0.3s;
        }
        .photo-upload-area:hover {
            border-color: #6f4e37;
        }
        #photo-input {
            display: none;
        }
        .upload-hint {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div id="info"><?= temp('info') ?></div>
    

    <div class="profile-header">
    <?php if ($user->photo): ?>
       
        <a href="profile.php">
            <img src="view.php?image=<?= encode($user->photo) ?>" 
                 alt="Profile Photo"
                 class="photo-preview">
        </a>
    <?php endif; ?>
</div>

<header class="header">
<img class="logo" src="/images/logo.webp" alt="Tarbuck Coffee Logo">
<div class="main-head-content">
    
    <a href="try.php" class="title-link">
        <h1 id="title">Tarbuck Coffee Admin</h1>
    </a>
    <a href="product_listing.php" class="main-nav">
        <span>PRODUCT</span>
    </a>
    <a href="create.php" id="create">
        <span>CREATE</span>
    </a>
    <a href="order_listing.php" id="order">
        <span>ORDER</span>
    </a>
    <a href="member_list.php" id="order">
        <span>MEMBER</span>
    </a>
    <a href="/" id="logout">
        <span>LOGOUT</span>
    </a>
</div>
    </header>
</body>
</html>