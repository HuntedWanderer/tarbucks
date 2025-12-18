<?php
require '_base.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

try {
    $stmt = $_db->prepare('SELECT photo FROM member WHERE id = ?');
    $stmt->execute([$id]);
    $member = $stmt->fetch();
    
    // Delete the member
    $stmt = $_db->prepare('DELETE FROM member WHERE id = ?');
    $stmt->execute([$id]);
    
    // --- START S3 DELETION ---
    if ($member && !empty($member['photo'])) {
        $bucket = 'tarbucks-bucket'; // <--- UPDATE THIS
        
        // Construct the path (assuming your DB holds "photo.jpg" and S3 has "images/photo.jpg")
        $s3Path = "s3://{$bucket}/images/{$member['photo']}";
        
        // Run the AWS CLI remove command
        // 2>&1 redirects errors to output so we can debug if needed, but usually not required for delete
        $cmd = "aws s3 rm \"{$s3Path}\" --region us-east-1";
        exec($cmd);
    }
    // --- END S3 DELETION ---
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}