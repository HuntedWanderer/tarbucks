<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Product</title>
    <link rel="icon" href="/images/logo.webp">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="CreateHeader">
        <h1>Create New Product</h1>
        <a href="try.php" class="HomeButton">HOME</a>
    </header>

    <?php
    require 'database.php'; 
    $err_message = [];
    $name = $price = $description = '';
    $photo = '';
    $type = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = trim($_POST['name'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = trim($_POST['type'] ?? '');
        
        // Validate form fields
        if (empty($name)) {
            $error_messages[] = "Product name is required.";
        } elseif (!preg_match('/^[A-Za-z0-9 ]+$/', $name)) {
            $error_messages[] = "Product name can only contain letters, numbers, and spaces.";
        }

        if (empty($price) || $price <= 0) {
            $error_messages[] = "Price must be a valid positive number!";
        } elseif ($price > 999) {
            $error_messages[] = "Price should not over RM 999.";
        }

        if (empty($description)) {
            $error_messages[] = "Description cannot be empty.";
        }

        if (empty($type)) {
            $error_messages[] = "Type cannot be empty.";
        }
                
        if (!empty($_FILES['photo']['name'])) {
    $file = $_FILES['photo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    if (!in_array($file['type'], $allowed_types)) {
        $error_messages[] = "Only JPG, PNG, GIF, and WEBP images are allowed.";
    } elseif ($file['size'] > 1 * 1024 * 1024) {
        $error_messages[] = "Maximum file size is 1MB.";
    } else {
        // 1. Prepare for S3 Upload
        $bucket = 'tarbucks-bucket'; // <--- UPDATE THIS
        $region = 'us-east-1';
        
        // We only want the filename in the database (e.g., "coffee.jpg")
        // We DO NOT want "images/coffee.jpg" because S3 uses folders differently
        $clean_filename = basename($file['name']);
        
        // 2. Upload to S3 using AWS CLI
        // Note: We rename the file to avoid duplicates (e.g., timestamp_filename)
        $s3_filename = time() . "_" . $clean_filename;
        $temp_file_path = $file['tmp_name'];
        
        // Command: aws s3 cp /tmp/phpFile s3://bucket/images/filename
        $cmd = "aws s3 cp \"$temp_file_path\" \"s3://$bucket/images/$s3_filename\" --region $region";
        exec($cmd, $output, $return_var);

        if ($return_var === 0) {
            // Success! Save ONLY the filename to the variable for the DB
            $photo = $s3_filename; 
        } else {
            $error_messages[] = "Failed to upload image to S3.";
        }
    }
} else {
    $error_messages[] = "Photo is required.";
}
        // If there are any errors, show them in one alert and go back ONCE
        if (!empty($error_messages)) {
            $all_errors = implode("\\n", $error_messages); // separate multiple messages by newlines
            echo "<script>alert('$all_errors'); window.history.back();</script>";
            exit();
        }
    
        // If no errors, insert into the database
        if (empty($err_message)) {
            try {
                $stmt = $_db->prepare("INSERT INTO product (name, price, description, photo, type) VALUES (:name, :price, :description, :photo, :type)");
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':photo', $photo);
                $stmt->bindParam(':type', $type);
                $stmt->execute();
        
                // Get the inserted product ID
                $product_id = $_db->lastInsertId();
        
                echo "<p style='color:green;'>✅ Product added successfully! <a href='product_detail.php?id=$product_id'>View Product</a></p>";
            } catch (PDOException $e) {
                echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
            }
        } else {
            echo "<p style='color:red;'>$err_message</p>";
        }
    }
    ?>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const img = document.getElementById('previewImage');

            if (file && file.type.startsWith('image/')) {
                const objectURL = URL.createObjectURL(file);
                
                if (img.dataset.url) {
                    URL.revokeObjectURL(img.dataset.url);
                }

                img.src = objectURL;
                img.dataset.url = objectURL;
            } else {
                img.src = '/images/blank_photo.jpg';
            }
        });
    });
    </script>

    <style>
    label.upload img {
        display: block;
        border: 1px solid #333;
        width: 200px;
        height: 200px;
        object-fit: cover;
        cursor: pointer;
    }
    </style>

    <div class="formContainer">
        <div class="form">
            <form action="" method="POST" enctype="multipart/form-data">
                <label for="photo">Photo:</label>
                <label class="upload" tabindex="0">
                    <input type="file" name="photo" id="photoInput" accept="image/*" style="display:none;">
                    <img src="/images/blank_photo.jpg" alt="No Image" id="previewImage">
                </label>

                <label for="product_name">Product Name:</label><br>
                <input type="text" id="product_name" name="name" required><br><br>

                <label for="description">Description:</label><br>
                <textarea id="description" name="description" style="width: 410px; height: 100px; resize: none;" required></textarea><br><br>

                <label for="price">Price:</label><br>
                <input type="number" id="price" name="price" step="0.01" required><br><br>

                <label for="type">Type:</label><br>
                <select id="type" name="type" required><br><br>
                    <option disabled selected>--Select Type--</option>
                    <option>Latte</option>
                    <option>Americano</option>
                    <option>Mocha</option>
                    <option>Cappuccino</option>
                    <option>Caramel</option>
                    <option>Chocolate</option>
                    <option>Coffee</option>
                    <option>Espresso</option>
                    <option>Tea</option>
                    <option>Other</option>
                </select>
                <button type="submit">Add Product</button>
            </form>
        </div>
    </div>
    <footer>
    <?php

?></footer>
</body>
</html>
