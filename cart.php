<?php

include '_base.php';

// Refresh User Session (Safe)
$user_id = $_SESSION['user']['user_id'];
$stmt = $_db->prepare("SELECT * FROM member WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$_SESSION['user'] = $user;

// --- GHOST CODE DELETED ---
// I removed the 50+ lines of "Profile Update" logic. 
// It does not belong on a Menu page!

// Cart Count Logic
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += is_array($qty) ? $qty['qty'] : $qty;
    }
}

//  商品删除
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    unset($_SESSION['cart'][$remove_id]);
    header("Location: cart.php");
    exit();
}

//  数量 + 冰 + 糖 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    foreach ($_POST['quantities'] as $product_id => $qty) {
        $product_id = intval($product_id);
        $qty = intval($qty);
        $ice = $_POST['ice'][$product_id] ?? 'regular';
        $sugar = $_POST['sugar'][$product_id] ?? 'full';

        if ($qty <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = [
                'qty' => $qty,
                'ice' => $ice,
                'sugar' => $sugar
            ];
        }
    }
    header("Location: cart.php");
    exit();
}

// 获取购物车内容
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT * FROM product WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        $cart_data = $_SESSION['cart'][$product['id']];
        $qty = is_array($cart_data) ? $cart_data['qty'] : $cart_data;
        $subtotal = $qty * $product['price'];
        $total += $subtotal;

        $product['quantity'] = $qty;
        $product['subtotal'] = $subtotal;
        $cart_items[] = $product;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Cart - Tarbuck</title>
    <link rel="stylesheet" href="css/ss.css">
 
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding-bottom: 60px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-top: 30px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Navigation Link */
        .continue-btn {
            display: inline-block;
            margin-bottom: 20px;
            color: #388e3c;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
        }
        .continue-btn:hover {
            text-decoration: underline;
        }

        /* Table Styling */
        .table-wrapper {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow-x: auto; /* Enables scroll on mobile */
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px; /* Forces layout to stay wide enough */
        }

        th {
            background-color: #388e3c;
            color: white;
            padding: 15px;
            text-align: left;
        }

        td {
            padding: 15px;
            vertical-align: top;
            border-bottom: 1px solid #eee;
            color: #555;
        }

        /* Product Image */
        td img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        /* Form Inputs */
        .option-group {
            margin-bottom: 8px;
        }
        
        .option-group label {
            font-size: 12px;
            color: #888;
            display: block;
            margin-bottom: 2px;
        }

        input[type="number"], select {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            width: 100px;
            font-size: 14px;
        }

        /* Prices */
        .price {
            font-weight: bold;
            color: #333;
        }
        .subtotal {
            font-weight: bold;
            color: #388e3c;
            font-size: 16px;
        }

        /* Actions */
        .remove-link {
            color: #d32f2f;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
        .remove-link:hover {
            text-decoration: underline;
        }

        /* Summary & Buttons */
        .cart-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .total-display {
            font-size: 22px;
            font-weight: bold;
            color: #333;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.2s;
        }

        .btn-update {
            background-color: #757575;
            color: white;
        }
        .btn-update:hover {
            background-color: #616161;
        }

        .btn-checkout {
            background-color: #388e3c;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        .btn-checkout:hover {
            background-color: #2e7d32;
        }

        /* Mobile specific adjustments */
        @media (max-width: 768px) {
            .cart-footer {
                flex-direction: column;
                text-align: center;
            }
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<?php include 'heade.php'; ?>
<div class="container">
    <h1>Your Shopping Cart 🛒</h1>
    
    <a href="mainpage_menu.php" class="continue-btn">← Continue Shopping</a>

    <?php if (empty($cart_items)): ?>
        <div style="text-align: center; padding: 50px; background: white; border-radius: 10px;">
            <p style="font-size: 18px; color: #777;">Your cart is currently empty.</p>
            <a href="mainpage_menu.php" class="btn btn-checkout" style="margin-top: 10px;">Browse Menu</a>
        </div>
    <?php else: ?>
        
        <form method="post">
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 100px;">Photo</th>
                            <th>Product Details</th>
                            <th>Configuration</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <?php
                            $item_data = $_SESSION['cart'][$item['id']];
                            $qty = $item_data['qty'] ?? $item_data;
                            $ice = $item_data['ice'] ?? 'regular';
                            $sugar = $item_data['sugar'] ?? 'full';
                            ?>
                            <tr>
                                <td>
                                    <img src="view.php?image=<?= htmlspecialchars($item['photo']) ?>" alt="Product">
                                </td>

                                <td>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                </td>

                                <td>
                                    <div class="option-group">
                                        <label>Quantity</label>
                                        <input type="number" name="quantities[<?= $item['id'] ?>]" value="<?= $qty ?>" min="0">
                                    </div>

                                    <div class="option-group">
                                        <label>Ice Level</label>
                                        <select name="ice[<?= $item['id'] ?>]">
                                            <option value="regular" <?= $ice == 'regular' ? 'selected' : '' ?>>Regular</option>
                                            <option value="less" <?= $ice == 'less' ? 'selected' : '' ?>>Less Ice</option>
                                            <option value="no" <?= $ice == 'no' ? 'selected' : '' ?>>No Ice</option>
                                        </select>
                                    </div>

                                    <div class="option-group">
                                        <label>Sugar Level</label>
                                        <select name="sugar[<?= $item['id'] ?>]">
                                            <option value="full" <?= $sugar == 'full' ? 'selected' : '' ?>>Full Sugar</option>
                                            <option value="half" <?= $sugar == 'half' ? 'selected' : '' ?>>Half Sugar</option>
                                            <option value="no" <?= $sugar == 'no' ? 'selected' : '' ?>>No Sugar</option>
                                        </select>
                                    </div>
                                </td>

                                <td class="price">RM <?= number_format($item['price'], 2) ?></td>
                                
                                <td class="subtotal">RM <?= number_format($item['subtotal'], 2) ?></td>
                                
                                <td>
                                    <a href="cart.php?remove=<?= $item['id'] ?>" 
                                       onclick="return confirm('Remove this item from cart?')" 
                                       class="remove-link">
                                       🗑 Remove
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-footer">
                <div class="total-display">
                    Total: <span style="color: #388e3c;">RM <?= number_format($total, 2) ?></span>
                </div>
                
                <div style="display: flex; gap: 10px; width: 100%; max-width: 400px;">
                    <button type="submit" name="update" class="btn btn-update" style="flex: 1;">Update Cart</button>
                    <a href="checkout.php" class="btn btn-checkout" style="flex: 1; text-align: center;">Checkout</a>
                </div>
            </div>
        </form>

    <?php endif; ?>
</div>
</body>
</html>