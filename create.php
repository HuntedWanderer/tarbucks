<?php
// 1. Include Database & Auth
// Use _base.php if you have it, otherwise ensure session is started for Admin check
include '_base.php'; 

// 2. Security: Ensure only Admin can access
if (!is_logged_in() || $_SESSION['user']->role !== 'Admin') {
    die("Access Denied. Admins only.");
}

$err_message = [];
$name = $price = $description = $type = '';
$photo = '';
$is_edit = false;

// 3. CHECK MODE: Are we Editing or Creating?
if (isset($_GET['id'])) {
    $is_edit = true;
    $id = $_GET['id'];
    
    // Fetch existing data
    $stmt = $_db->prepare("SELECT * FROM product WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $name = $product['name'];
        $price = $product['price'];
        $description = $product['description'];
        $type = $product['type'];
        $photo = $product['photo']; // Keep existing photo filename
    } else {
        die("Product not found.");
    }
}

// 4. HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = trim($_POST['type'] ?? '');
    
    // Validate form fields
    if (empty($name)) {
        $err_message[] = "Product name is required.";
    } elseif (!preg_match('/^[A-Za-z0-9 ]+$/', $name)) {
        $err_message[] = "Product name can only contain letters, numbers, and spaces.";
    }

    if (empty($price) || $price <= 0) {
        $err_message[] = "Price must be a valid positive number!";
    } elseif ($price > 999) {
        $err_message[] = "Price should not over RM 999.";
    }

    if (empty($description)) {
        $err_message[] = "Description cannot be empty.";
    }

    if (empty($type)) {
        $err_message[] = "Type cannot be empty.";
    }
            
    // 5. PHOTO HANDLING
    // If it's a NEW product, photo is required. 
    // If EDITING, it's optional (only if they want to change it).
    if (!empty($_FILES['photo']['name'])) {
        $file = $_FILES['photo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($file['type'], $allowed_types)) {
            $err_message[] = "Only JPG, PNG, GIF, and WEBP images are allowed.";
        } elseif ($file['size'] > 1 * 1024 * 1024) {
            $err_message[] = "Maximum file size is 1MB.";
        } else {
            // S3 Upload Logic
            $bucket = 'tarbucks-bucket'; // <--- CHECK THIS
            $clean_filename = basename($file['name']);
            $s3_filename = time() . "_" . $clean_filename;
            $temp_file_path = $file['tmp_name'];
            
            $cmd = "aws s3 cp \"$temp_file_path\" \"s3://$bucket/images/$s3_filename\" --region us-east-1";
            exec($cmd, $output, $return_var);

            if ($return_var === 0) {
                $photo = $s3_filename; // Update variable with NEW photo name
            } else {
                $err_message[] = "Failed to upload image to S3.";
            }
        }
    } elseif (!$is_edit) {
        // If Creating and no photo uploaded -> Error
        $err_message[] = "Photo is required for new products.";
    }
    // If Editing and no photo uploaded -> Do nothing (keep $photo as is)

    // 6. DATABASE UPDATE / INSERT
    if (empty($err_message)) {
        try {
            if ($is_edit) {
                // UPDATE
                $stmt = $_db->prepare("
                    UPDATE product 
                    SET name = :name, price = :price, description = :description, photo = :photo, type = :type 
                    WHERE id = :id
                ");
                $stmt->bindParam(':id', $id);
            } else {
                // INSERT
                $stmt = $_db->prepare("
                    INSERT INTO product (name, price, description, photo, type) 
                    VALUES (:name, :price, :description, :photo, :type)
                ");
            }

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':price', $price);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':photo', $photo);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
    
            $target_id = $is_edit ? $id : $_db->lastInsertId();
            $action_text = $is_edit ? "updated" : "added";
    
            echo "<script>
                    alert(' Product $action_text successfully!');
                    window.location.href = 'product_detail.php?id=$target_id';
                  </script>";
            exit();

        } catch (PDOException $e) {
            echo "<script>alert(' Database Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    } else {
        // Show errors
        $all_errors = implode("\\n", $err_message);
        echo "<script>alert('$all_errors');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Edit Product' : 'Create Product' ?></title>
    <link rel="icon" href="/images/logo.webp">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <header class="CreateHeader">
        <h1><?= $is_edit ? 'Edit Product' : 'Create New Product' ?></h1>
        <a href="try.php" class="HomeButton">HOME</a>
    </header>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('photoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const img = document.getElementById('previewImage');

            if (file && file.type.startsWith('image/')) {
                img.src = URL.createObjectURL(file);
            }
        });
    });
    </script>

    <style>
    label.upload img {
        display: block; border: 1px solid #333;
        width: 200px; height: 200px; object-fit: cover; cursor: pointer;
    }
    </style>

    <div class="formContainer">
        <div class="form">
            <form method="POST" enctype="multipart/form-data">
                
                <label for="photo">Photo (<?= $is_edit ? 'Click to change' : 'Required' ?>):</label>
                <label class="upload" tabindex="0">
                    <input type="file" name="photo" id="photoInput" accept="image/*" style="display:none;">
                    <img src="<?= ($is_edit && $photo) ? "view.php?image=$photo" : "/images/blank_photo.jpg" ?>" 
                         alt="Preview" id="previewImage">
                </label>

                <label for="product_name">Product Name:</label><br>
                <input type="text" id="product_name" name="name" 
                       value="<?= htmlspecialchars($name) ?>" required><br><br>

                <label for="description">Description:</label><br>
                <textarea id="description" name="description" style="width: 410px; height: 100px; resize: none;" required><?= htmlspecialchars($description) ?></textarea><br><br>

                <label for="price">Price:</label><br>
                <input type="number" id="price" name="price" step="0.01" 
                       value="<?= htmlspecialchars($price) ?>" required><br><br>

                <label for="type">Type:</label><br>
                <select id="type" name="type" required><br><br>
                    <option disabled <?= empty($type) ? 'selected' : '' ?>>--Select Type--</option>
                    <?php 
                    $options = ['Latte', 'Americano', 'Mocha', 'Cappuccino', 'Caramel', 'Chocolate', 'Coffee', 'Espresso', 'Tea', 'Other'];
                    foreach ($options as $opt) {
                        $selected = ($type === $opt) ? 'selected' : '';
                        echo "<option value='$opt' $selected>$opt</option>";
                    }
                    ?>
                </select>
                
                <button type="submit"><?= $is_edit ? 'Update Product' : 'Add Product' ?></button>
            </form>
        </div>
    </div>
</body>
</html>