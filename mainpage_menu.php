<?php 
// 1. Use the Master Connection File
require 'database.php'; 
include '_base.php';

// Check if user is logged in
$user_id = $_SESSION['user']->user_id;

// Refresh User Data
// Note: We use $_db (the correct variable from database.php)
$stmt = $_db->prepare("SELECT * FROM member WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$_SESSION['user'] = $user;

// --- GHOST CODE REMOVED ---
// I removed the "Profile Update" logic (email, photo upload) from here.
// It does not belong on a Product Listing page.

// Cart Logic
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += is_array($qty) ? $qty['qty'] : $qty;
    }
}

// Product Query
$type = $_GET['type'] ?? 'All';

if ($type === 'All') {
    // 2. Fix: Use $_db instead of $pdo
    $stmt = $_db->query("SELECT * FROM product");
} else {
    // 2. Fix: Use $_db instead of $pdo
    $stmt = $_db->prepare("SELECT * FROM product WHERE type = ?");
    $stmt->execute([$type]);
}
// 3. Fix: Specify Fetch Mode
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tarbuck - Home</title>
    <link rel="stylesheet" href="css/ss.css">
    <link rel="stylesheet" href="zz.css">
    <style>
        .cart-link {
            font-size: 20px;
            position: absolute;
            right: 120px;    
            top: 25px;
            text-decoration: none;
            color: white;
        }

        .category-nav {
            text-align: center;
            margin: 20px 0;
        }

        .category-nav a {
            text-decoration: none;
            margin: 0 10px;
            color: #388e3c;
            font-weight: bold;
            border: 2px solid #388e3c;
            padding: 5px 10px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .category-nav a:hover,
        .category-nav a.active {
            background-color: #388e3c;
            color: white;
        }

        .product-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .product {
            border: 1px solid #ccc;
            padding: 15px;
            width: 220px;
            text-align: center;
            border-radius: 8px;
            margin: 10px; /* Added spacing */
        }

        .product img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<div id="info"><?= temp('info') ?></div>

    <header class="headerMember">
      <?php include 'header.php' ?>
    </header>

    <div class="category-nav">
        <?php
        $categories = ['All', 'Latte', 'Americano', 'Mocha', 'Cappuccino', 'Caramel', 'Chocolate', 'Coffee', 'Espresso', 'Tea', 'Other'];
        foreach ($categories as $cat) {
            $active = ($type === $cat) ? 'active' : '';
            echo "<a href='?type=$cat' class='$active'>$cat</a>";
        }
        ?>
    </div>

    <div class="product-list">
        <?php foreach ($products as $row): ?>
            <div class="product">
                
                <img src="view.php?image=<?= htmlspecialchars($row['photo']) ?>" 
                     alt="<?= htmlspecialchars($row['name']) ?>">
                
                <h2><?= htmlspecialchars($row['name']) ?></h2>
                <p>RM <?= $row['price'] ?></p>
                
                <a href="product_detail.php?id=<?= $row['id'] ?>">View Details</a>
            
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>

