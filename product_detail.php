<?php
include '_base.php'; // 1. Use the Master Base File
require_once 'database.php'; // 2. Use the Master Database Connection
// Get Product ID
if (!isset($_GET['id'])) {
    die("No product selected.");
}
$id = intval($_GET['id']);

// 2. Use $_db (Standard Connection)
$stmt = $_db->prepare("SELECT * FROM product WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found.");
}

// Calculate Display Stock (Real Stock - Cart Qty)
$cartQty = $_SESSION['cart'][$id]['qty'] ?? 0;
$displayStock = $product['stock'] - $cartQty;

// Handle Add to Cart
if (is_post()) { // 3. Use is_post() helper
    
    // Security: Ensure user is logged in before adding
    if (!is_logged_in()) {
        redirect('head.php');
        exit;
    }

    $qty = intval(post('quantity') ?? 1);
    $sugar = post('sugar') ?? 'full';
    $ice = post('ice') ?? 'regular';

    // Validate Stock
    if ($product['stock'] <= 0) {
        // 4. Fix Redirect URL (Removed "product_detail(3).php")
        redirect("product_detail.php?id=$id&error=out_of_stock");
    }

    // Validate Total Request
    $totalRequired = $cartQty + $qty;
    if ($totalRequired > $product['stock']) {
        $remaining = $product['stock'] - $cartQty;
        $errorType = $remaining > 0 ? "insufficient_stock&remaining=$remaining" : "stock_limit";
        redirect("product_detail.php?id=$id&error=$errorType");
    }

    // Update Cart
    $_SESSION['cart'][$id] = [
        'qty' => $totalRequired,
        'sugar' => $sugar,
        'ice' => $ice
    ];

    redirect("product_detail.php?id=$id&added=1");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> - Tarbuck</title>
    <link rel="stylesheet" href="css/ss.css">
    <link rel="stylesheet" href="/css/style.css"> 
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .stock-info { 
            color: #666; 
            margin: 10px 0; 
            font-size: 1.1em;
        }
        .stock-number { 
            font-weight: bold; 
            color: <?= $displayStock > 0 ? 'green' : 'red' ?>; 
        }
        .linkButton { margin: 20px; }
        #link {
            display: inline-block;
            background-color: #388e3c;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        #link:hover { background-color: #2e7031; }
        .product-detail {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .product-image {
            width: 300px;
            max-width: 100%;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .price {
            font-size: 1.6em;
            color: #388e3c;
            margin: 10px 0 20px;
        }
        .description {
            font-size: 1em;
            color: #555;
            line-height: 1.6;
            margin-bottom: 30px;
            text-align: left;
        }
        .order-form {
            margin-top: 20px;
            text-align: left;
        }
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #444;
        }
        input[type="number"], select {
            width: 100%;
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
        }
        #cartButt {
            width: 100%;
            padding: 12px;
            background-color: #388e3c;
            color: #fff;
            font-size: 1.1em;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #cartButt:hover { background-color: #2e7031; }
        .alert.error {
            max-width: 800px;
            margin: 20px auto;
            background: #f44336;
            color: #fff;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .out-of-stock {
            margin-top: 20px;
            font-size: 1.2em;
            color: red;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="linkButton">
    <br><a href="mainpage_menu.php" id="link">← Back to Product List</a>
</div>

<?php if (isset($_GET['added'])): ?>
    <script>alert("Item added to cart successfully!");</script>
<?php elseif (isset($_GET['error'])): ?>
    <div class="alert error">
        <?php switch ($_GET['error']) {
            case 'out_of_stock': echo '❌ This product is out of stock!'; break;
            case 'insufficient_stock': 
                $remaining = $_GET['remaining'] ?? 0;
                echo "❌ You can only add $remaining more"; 
                break;
            case 'stock_limit': echo '❌ Already reached stock limit!'; break;
        } ?>
    </div>
<?php endif; ?>

<div class="product-detail">
    <img src="view.php?image=<?= htmlspecialchars($product['photo']) ?>" 
         alt="<?= htmlspecialchars($product['name']) ?>" 
         class="product-image">

    <h1><?= htmlspecialchars($product['name']) ?></h1>
    
    <div class="stock-info">
        Available Stock: 
        <span class="stock-number"><?= max(0, $displayStock) ?></span>
    </div>
    
    <p class="price">RM <?= number_format($product['price'], 2) ?></p>
    <p class="description"><?= nl2br(htmlspecialchars($product['description'])) ?></p>

    <?php if ($displayStock > 0): ?>
    <form method="post" class="order-form">
        <div class="form-group">
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $displayStock ?>" required>
        </div>

        <div class="form-group">
            <label for="sugar">Sugar Level:</label>
            <select name="sugar" id="sugar">
                <option value="full">Full</option>
                <option value="half">Half</option>
                <option value="less">Less</option>
                <option value="no">No Sugar</option>
            </select>
        </div>

        <div class="form-group">
            <label for="ice">Ice Level:</label>
            <select name="ice" id="ice">
                <option value="regular">Regular</option>
                <option value="less">Less</option>
                <option value="no">No Ice</option>
            </select>
        </div>

        <?php if (is_logged_in()): ?>
            <button type="submit" id="cartButt">Add to Cart</button>
        <?php else: ?>
            <button type="button" id="cartButt" onclick="window.location.href='head.php';">
                Login To Purchase
            </button>
        <?php endif; ?>
        
    </form>
    <?php else: ?>
        <div class="out-of-stock">This product is currently unavailable</div>
    <?php endif; ?>

</div>

</body>
</html>