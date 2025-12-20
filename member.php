<?php 
// 1. Include Standard Files
require 'database.php'; 
include '_base.php';

// 2. Security Check
auth('Member'); 

if (!is_logged_in()) {
    temp('info', 'Please login first');
    redirect('head.php');
}

// 3. Refresh User Session (Safe)
$user_id = $_SESSION['user']['user_id'];
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

    <style>
.category-nav {
    margin: 20px 0;
    
    
    display: flex;
    overflow-x: auto; 
    white-space: nowrap;
    padding: 10px 15px;
    gap: 10px;
    
    
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none; 
}

.category-nav::-webkit-scrollbar {
    display: none;
}

.category-nav a {
    text-decoration: none;
    color: #388e3c;
    font-weight: bold;
    border: 2px solid #388e3c;
    padding: 8px 20px;
    border-radius: 50px; 
    transition: all 0.3s ease;
    flex-shrink: 0; 
}

.category-nav a:hover,
.category-nav a.active {
    background-color: #388e3c;
    color: white;
}


.product-list {
    display: grid;
    
    grid-template-columns: repeat(auto-fit, minmax(220px, 300px));
    gap: 20px;
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
    justify-content: center; 
}

.product {
    border: 1px solid #ccc;
    padding: 15px;
    border-radius: 8px;
    background: white;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.product img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 6px;
    margin-bottom: 15px;
}

        /* ============================
           2. MENU SPECIFIC STYLES (SEARCH BAR)
           ============================ */
        .search-container {
            max-width: 600px;
            margin: 20px auto 10px auto; 
            padding: 0 15px;
            text-align: center;
        }

        .search-container input[type="text"] {
            width: 100%;
            padding: 10px;
            border-radius: 20px;
            border: 1px solid #ccc;
            outline: none;
        }
    </style>
</head>

<body>
<?php include 'heade.php'; ?>

    <div class="search-container">
       <?php if(file_exists('basic_searching.php')) include 'basic_searching.php'; ?>
    </div>

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