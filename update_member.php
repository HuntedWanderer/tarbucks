<?php
require '_base.php';


if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

$id = $_POST['id'] ?? null;
$user_id = $_POST['user_id'] ?? '';
$password = $_POST['password'] ?? '';
$email = !empty($_POST['email']) ? $_POST['email'] : null;
$$fav_person = !empty($_POST['fav_person']) ? $_POST['fav_person'] : null;
$role = $_POST['role'] ?? 'Member';

// Validate email format 
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}


$photo = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    
    $bucket = 'tarbucks-bucket'; // <--- Your Bucket
    $region = 'us-east-1';       // <--- Your Region

    $file_tmp = $_FILES['photo']['tmp_name'];
    $raw_name = basename($_FILES['photo']['name']);

    // SECURITY: Sanitize filename for exec command
    $clean_name = preg_replace('/[^a-zA-Z0-9._-]/', '', $raw_name);
    $s3_filename = time() . "_" . $clean_name;

    // Command: Upload to S3
    $cmd = "aws s3 cp \"$file_tmp\" \"s3://$bucket/images/$s3_filename\" --region $region";
    
    $output = [];
    $return_var = 0;
    exec($cmd, $output, $return_var);

    if ($return_var === 0) {
        $photo = $s3_filename; // Upload success, set variable for DB
    }
}

try {
    $stmt = $_db->prepare('UPDATE member SET 
        user_id = ?, 
        password = COALESCE(NULLIF(?, ""), password), 
        email = COALESCE(NULLIF(?, ""), email), 
        fav_person = COALESCE(NULLIF(?, ""), fav_person), 
        photo = COALESCE(?, photo), 
        role = ? 
        WHERE id = ?');
    
    $stmt->execute([$user_id, $password, $email, $fav_person, $photo, $role, $id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}