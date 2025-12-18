<?php
require '_base.php';

if (!is_logged_in()) {
    temp('info', 'Please login first');
    redirect('head.php');
}

// Refresh User Session (Safe)
$user_id = $_SESSION['user']['user_id'];
$stmt = $_db->prepare("SELECT * FROM member WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$_SESSION['user'] = $user;

// --- GHOST CODE DELETED ---
// I removed the 50+ lines of "Profile Update" logic. 
// It does not belong on a Menu page!

// Cart Count Logic
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += is_array($qty) ? $qty['qty'] : $qty;
    }
}

$user_id = $_SESSION['user']['user_id'];
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

    if ($user_id != $user['user_id'] && !is_unique($user_id, 'member', 'user_id')) {
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

    $photo = $user['photo'];
    if ($f) {
        if (!str_starts_with($f->type, 'image/')) {
            $_err['photo'] = 'Only image files are allowed';
        } elseif ($f->size > 1 * 1024 * 1024) {
            $_err['photo'] = 'Image size cannot exceed 1MB';
        } else {
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

            $user['user_id'] = $user_id;
            $user['email'] = $email;
            $user['fav_person'] = $fav;
            $user['photo'] = $photo;
            $_SESSION['user'] = $user;

            temp('info', 'Profile updated successfully');
            redirect('profileMember.php'); 
            
        } catch (PDOException $e) {
            temp('info', 'Error updating profile: ' . $e->getMessage());
        }
    }
}

?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarbuck Coffee - Profile</title>
    <link rel="stylesheet" href="/css/ss.css">
  
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

        .form-group input {
            width: 500px;
            height: 25px;
            border-radius: 20px;
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
            border: 2px solid white;

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
        .main-head-content {
    gap: 30px;
    display: flex;
    align-items: center;
}
button.submitBtn{
    background-color: #6f4e37;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
}
    </style>
</head>
<body>
<?php include 'heade.php';?>
<div id="info"><?= temp('info') ?></div>




</header>
    <div class="main-content">
        <form method="POST" class="profile-form" enctype="multipart/form-data">
            <h2>Edit Profile</h2>

            <div class="form-group">
                <label>User ID</label>
                <input type="text" name="user_id" 
                       value="<?= encode($user->user_id) ?>" 
                       required>
                <?php err('user_id'); ?>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" 
                       value="<?= encode($user->email) ?>" 
                       required>
                <?php err('email'); ?>
            </div>

            <div class="form-group">
                <label>Favorite Person</label>
                <input type="text" name="fav_person" 
                       value="<?= encode($user->fav_person) ?>">
            </div>

            <div class="form-group">
                <label>Profile Photo</label>
                <div class="photo-upload-area" onclick="document.getElementById('photo-input').click()">
                    <?php if ($user->photo): ?>
                        <img src="view.php?image=<?= encode($user->photo) ?>" 
                             class="photo-preview"
                             id="preview-image">
                    <?php else: ?>
                        <div class="upload-hint">Click to upload photo</div>
                    <?php endif; ?>
                </div>
                <input type="file" name="photo" id="photo-input" accept="image/*">
                <?php err('photo'); ?>
            </div>

            <button type="submit" class="submit-btn">Save Changes</button>
            <div class="login-links" style="justify-content: center;top: 100px;">

        </div>
     
        </form>
       
    </div>

    <script>
        // 即时预览功能
        document.getElementById('photo-input').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('preview-image');
            const container = document.querySelector('.photo-upload-area');

            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (!preview) {
                        const img = document.createElement('img');
                        img.id = 'preview-image';
                        img.className = 'photo-preview';
                        img.src = e.target.result;
                        container.innerHTML = '';
                        container.appendChild(img);
                    } else {
                        preview.src = e.target.result;
                    }
                }
                
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>