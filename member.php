<?php 
// 1. Include Standard Files
require 'database.php'; 
include '_base.php';
include 'heade.php'; 

// 2. Security Check
auth('Member'); 

if (!is_logged_in()) {
    temp('info', 'Please login first');
    redirect('head.php');
}

// 3. Refresh User Session (Safe)
$user_id = $_SESSION['user']->user_id;
$stmt = $_db->prepare("SELECT * FROM member WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$_SESSION['user'] = $user;

// --- GHOST CODE DELETED ---
// I removed the 50+ lines of "Profile Update" logic. 
// It does not belong on a Menu page!

// 4. Cart Count Logic
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += is_array($qty) ? $qty['qty'] : $qty;
    }
}

// 5. Product Query (Fixed $pdo -> $_db)
$type = $_GET['type'] ?? 'All';

if ($type === 'All') {
    $stmt = $_db->query("SELECT * FROM product");
} else {
    $stmt = $_db->prepare("SELECT * FROM product WHERE type = ?");
    $stmt->execute([$type]);
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tarbuck - Menu</title>
    <link rel="stylesheet" href="zz.css">
    <link rel="stylesheet" href="css/ss.css">
    <link rel="stylesheet" href="/css/style.css">

    <style>
        /* Kept your custom styles that don't conflict */
        .headerMember {
            background-color: lightgreen;
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ccc;
            position: relative;
            z-index: 1;
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
            margin: 10px;
            width: 220px;
            text-align: center;
            border-radius: 8px;
            background: white;
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

<div class="category-nav">
   <?php
   // Keep searching logic if valid
   if(file_exists('basic_searching.php')) include 'basic_searching.php';
   
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
            
            <img src="view.php?f=<?= htmlspecialchars($row['photo']) ?>" 
                 alt="<?= htmlspecialchars($row['name']) ?>">
            
            <h2><?= htmlspecialchars($row['name']) ?></h2>
            <p>RM <?= $row['price'] ?></p>
            
            <a href="product_detail.php?id=<?= $row['id'] ?>">View Details</a>
        
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>