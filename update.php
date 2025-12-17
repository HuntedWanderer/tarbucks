<?php
include 'database.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Product ID is missing.");
}

$stm = $_db->prepare('SELECT * FROM product WHERE id = ?');
$stm->execute([$id]);
$product = $stm->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found in the database.");
}

$product_page = "product/product_{$id}.php";

$err_message = '';

// Handle form submission (by using post method)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? $product['name']; 
    $price = $_POST['price'] ?? $product['price']; 
    $description = $_POST['description'] ?? $product['description']; 
    $photo = $product['photo'];
    $type = $_POST['type'] ?? $product['type'];

    // Handle photo upload
    if (!empty($_FILES['photo']['name'])) {
        $file = $_FILES['photo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowed_types)) {
            $err_message = "Only JPG, PNG, GIF, and WEBP images are allowed.";
        } elseif ($file['size'] > 1 * 1024 * 1024) {  
            $err_message = "Maximum file size is 1MB.";
        } else {
            // Ensure /images directory exists
            if (!is_dir("images")) {
                mkdir("images", 0777, true);
            }

            // Use the original file name (from the uploaded file)
            $photo = "images/" . basename($file['name']);
            
            // Move the uploaded file to the target directory with the original name
            if (!move_uploaded_file($file['tmp_name'], $photo)) {
                $err_message = "Failed to upload image.";
            }
        }
    }

    // Validations
    if (empty($name)) {
        $err_message = "Name cannot be empty.";
    }

    if (empty($price) || $price <= 0) {
        $err_message = "Invalid price.";
    } else if ($price > 999) {
        $err_message = "Price cannot over RM 999.";
    }

    if (empty($description)) {
        $err_message = "Description cannot be empty.";
    }

    // If no errors, then update the product in the database
    if (empty($err_message)) {
        try {
            $stm = $_db->prepare('UPDATE product SET name = ?, price = ?, description = ?, photo = ?, type = ? WHERE id = ?');
            $stm->execute([$name, $price, $description, $photo, $type, $id]);
    
            // Reload the updated product data
            $stm = $_db->prepare('SELECT * FROM product WHERE id = ?');
            $stm->execute([$id]);
            $product = $stm->fetch(PDO::FETCH_ASSOC);
    
            // ✅ Success message as popup
            echo "<script>alert('✅ Product updated successfully!');</script>";
    
        } catch (PDOException $e) {
            // ❌ Error message as popup
            echo "<script>alert('❌ Error: " . addslashes($e->getMessage()) . "');</script>";
        }
    } else {
        echo "<script>alert('❌ $err_message');</script>";
    }    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="icon" href="/images/logo.webp">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="UpHeaderClass">
    <h1>Update Product: <span id="product-name"></span></h1>
    <div class="HBButton">
    <a href="try.php" class="HomeButton">HOME</a>
    <a href="<?= htmlspecialchars($product_page) ?>" class="BackButton">BACK</a>
</div>
</header>

<script>
    document.getElementById("product-name").textContent = "<?= htmlspecialchars($product['name']) ?>";
</script>

<div class="formContainer">
<div class="form">
    <form method="POST" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>">
        <br><br>
        
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" value="<?= htmlspecialchars($product['price']) ?>">
        <br><br>

        <label for="description">Description:</label>
        <textarea id="description" name="description" style="width: 410px; height: 100px; resize: none;"><?= htmlspecialchars($product['description']) ?></textarea>
        <br><br>

        <label for="type">Type: <span style="color:red;"><?= htmlspecialchars($product['type']) ?></span></label><br>
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
        <br><br>

        <label for="photo">Photo:</label>
        <label class="upload" tabindex="0">
            <input type="file" name="photo" id="photoInput" accept="image/*" style="display:none;">
            <img src="<?= htmlspecialchars($product['photo']) ?>" alt="No Image" id="previewImage" style="width: 200px; height: 200px;">
        </label>

        <button type="submit">Update Product</button>
    </form>
</div>
</div>

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

</body>
</html>
