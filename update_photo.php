
<?php
include '_base.php';

if (is_logged_in()) {
    $stm = $_db->prepare("SELECT * FROM member WHERE id = ?");
    $stm->execute([$_SESSION['user']->id]);
    $_SESSION['user'] = $stm->fetch();
    $user = $_SESSION['user'];
}

if (!is_logged_in()) {
    temp('info', 'Please login first');
    redirect('head.php');
}



if (is_post()) {
    $new_user_id = post('user_id', $user->user_id);
    $new_email = post('email', $user->email);
    $new_fav = post('fav_person', $user->fav_person);
    $new_photo = get_file('photo');

    $_err = [];

    if ($new_user_id != $user->user_id && !is_unique($new_user_id, 'member', 'user_id')) {
        $_err['user_id'] = 'User ID already exists';
    }

    if (!is_email($new_email)) {
        $_err['email'] = 'Invalid email format';
    }

    $photo_update = '';
    if ($new_photo && $new_photo->error == UPLOAD_ERR_OK) {
        try {
            if ($user->photo && $user->photo != 'uploads/default.jpg') {
                @unlink($user->photo);
            }
            $photo_path = save_photo($new_photo, 'uploads');
            $photo_update = ", photo = :photo";
        } catch (Exception $e) {
            $_err['photo'] = 'Photo upload failed: '.$e->getMessage();
        }
    }

    if (empty($_err)) {
        try {
            $sql = "UPDATE member SET 
                    user_id = :user_id,
                    email = :email,
                    fav_person = :fav_person
                    $photo_update
                    WHERE id = :id";

            $stm = $_db->prepare($sql);
            $params = [
                ':user_id' => $new_user_id,
                ':email' => $new_email,
                ':fav_person' => $new_fav,
                ':id' => $user->id
            ];
            
            if (!empty($photo_update)) {
                $params[':photo'] = $photo_path;
            }

            $stm->execute($params);

            $stm = $_db->prepare("SELECT * FROM member WHERE id = ?");
            $stm->execute([$user->id]);
            $_SESSION['user'] = $stm->fetch();

            temp('info', 'Profile updated successfully');
            redirect('login.php');

        } catch (PDOException $e) {
            temp('info', 'Update error: '.$e->getMessage());
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
    <link rel="stylesheet" href="./zz.css">
    <style>
        
        .profile-header {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .profile-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .profile-form {
            max-width: 500px;
            margin: 30px auto;
            padding: 20px;
            background: #f8f8f8;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .photo-upload {
            margin: 15px 0;
            text-align: center;
        }
        .photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 2px solid white;

        }
    </style>
</head>
<body>
    <div id="info"><?= temp('info') ?></div>
    
    <header class="header">
        <img class="logo" src="logo.webp" alt="Logo">
        <span class="main"><strong>Tarbuck Coffee</strong></span>
        
        <div class="profile-header">
            <img src="<?= $photo_path ?>?v=<?= time() ?>" 
                 class="profile-photo" 
                 alt="Profile"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='inline-block'">
            <span style="display:none;background:#eee;padding:5px">No Photo</span>
            <span><?= encode($user->user_id) ?></span>
        </div>
    </header>

    <div class="main-content">
        <form method="POST" enctype="multipart/form-data" class="profile-form">
            <h2>Edit Profile</h2>

            <div class="form-group">
                <label>Profile Photo</label>
                <div class="photo-upload">
                    <img src="<?= $photo_path ?>?v=<?= time() ?>" 
                         class="photo-preview"
                         id="photoPreview"
                         onerror="this.src='uploads/default.jpg'">
                    <label class="upload">
                        <input type="file" name="photo" id="photo" 
                               accept="image/*" 
                               onchange="previewImage(event)">
                        Choose New Photo
                    </label>
                    <?php if (isset($_err['photo'])): ?>
                        <div class="error"><?= $_err['photo'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

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

            <button type="submit" class="submit-btn">Save Changes</button>
        </form>
    </div>

    <script>
        // 照片预览功能
        function previewImage(event) {
            const reader = new FileReader();
            const preview = document.getElementById('photoPreview');
            const headerPhoto = document.querySelector('.profile-photo');
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                headerPhoto.src = e.target.result;
            }
            
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>
</body>
</html>
[file content end]
<label>Profile Photo</label>
                <div class="photo-upload">
                    <img src="<?= $photo_path ?>" class="photo-preview" id="photoPreview">
                    <label class="upload">
                        <input type="file" name="photo" id="photo" accept="image/*">
                        Choose Photo
                    </label>