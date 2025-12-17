<?php
include 'database.php';  // Ensure this file contains your database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <style>
        /* General page styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        /* Delete Confirmation Section */
        .delete-confirmation {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            width: 50%;
            margin: 50px auto;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .delete-confirmation h3 {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
        }

        .delete-confirmation strong {
            color: #e74c3c;
        }

        /* Buttons */
        .btn-confirm, .btn-cancel {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            text-decoration: none;
            font-size: 1em;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-confirm {
            background-color: #e74c3c;
            color: white;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-confirm:hover {
            background-color: #c0392b;
        }

        .btn-cancel {
            background-color: #95a5a6;
            color: white;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-cancel:hover {
            background-color: #7f8c8d;
        }

        /* Form Layout */
        .confirmation-form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $productId = intval($_GET['id']);

        // Fetch product details
        $stmt = $_db->prepare('SELECT * FROM product WHERE id = :id');
        $stmt->bindParam(':id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            echo "<div class='delete-confirmation'>";
            echo "<h3>Are you sure you want to delete the product: <strong>" . htmlspecialchars($product['name']) . "</strong>?</h3>";
            echo "<form action='' method='POST' class='confirmation-form'>";
            echo "<input type='hidden' name='id' value='" . htmlspecialchars($product['id']) . "'>";
            echo "<button type='submit' class='btn-confirm'>Yes, delete it</button>";
            echo "</form>";
            echo "<a href='product_detail.php?id=" . htmlspecialchars($product['id']) . "' class='btn-cancel'>Cancel</a>";
            echo "</div>";
        } else {
            echo "Product not found!";
        }
    } else {
        echo "No product ID provided.";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $ids = explode(',', $_POST['id']);
        $ids = array_filter($ids, 'is_numeric'); // Ensure all are numbers
        
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
            try {
                // --- NEW STEP 1: Fetch photos from DB before deleting them ---
                // We need to know the filenames to delete them from S3
                $sql = "SELECT photo FROM product WHERE id IN ($placeholders)";
                $stmt = $_db->prepare($sql);
                $stmt->execute($ids);
                $products_to_delete = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // --- NEW STEP 2: Delete images from S3 ---
                $bucket = 'tarbucks-bucket';
                
                foreach ($products_to_delete as $prod) {
                    if (!empty($prod['photo'])) {
                        // Assuming database has "latte.jpg" and S3 has "images/latte.jpg"
                        $s3Path = "s3://{$bucket}/images/" . $prod['photo'];
                        
                        // Run AWS CLI command to remove file
                        // 2>&1 sends errors to null so script doesn't crash if file is missing
                        exec("aws s3 rm \"{$s3Path}\" --region us-east-1 > /dev/null 2>&1");
                    }
                }

                // --- STEP 3: Delete products from database (Same as before) ---
                $deleteQuery = "DELETE FROM product WHERE id IN ($placeholders)";
                $stmt = $_db->prepare($deleteQuery);
                $stmt->execute($ids);
    
                // Redirect after deletion
                $_SESSION['message'] = "Selected products deleted successfully.";
                header("Location: product_listing.php");
                exit();
    
            } catch (PDOException $e) {
                die("Error deleting products: " . $e->getMessage());
            }
        }
    } else {
        echo "No product IDs provided.";
    }
}
}

?>
