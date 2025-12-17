<?php
// view.php
$bucket = 'tarbucks-bucket'; // <--- UPDATE THIS
$region = 'us-east-1'; 

// 1. Get the filename securely
$filename = basename($_GET['image']); 

// 2. Define the path (Notice we add 'images/' here because your S3 has that folder)
$s3Path = "s3://{$bucket}/images/{$filename}";

// 3. Generate a Presigned URL using AWS CLI
$cmd = "aws s3 presign \"{$s3Path}\" --region {$region} --expires-in 300";
$url = shell_exec($cmd);

// 4. Redirect
if (filter_var(trim($url), FILTER_VALIDATE_URL)) {
    header("Location: " . trim($url));
    exit;
} else {
    header("HTTP/1.0 404 Not Found");
    echo "Image not found";
}
?>