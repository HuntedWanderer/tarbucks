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
            background-color: #f4f4f4;
            margin:0px 10px 20px 10px;
            
        }

   

        table {
            width: 98%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        th {
            background-color: #388e3c;
            color: white;
            padding: 12px;
            text-align:center;
        }

        td {
            padding: 12px;
            vertical-align: middle;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        img {
            border-radius: 6px;
        }

        input[type="number"],
        select {
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 5px;
            width: 100%;
        }

        #link {
            color: #388e3c;
            text-decoration: none;
            font-weight: bold;
        }

        #link:hover {
            text-decoration: underline;
        }

        button, input[type="submit"] {
            background-color: #388e3c;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover, input[type="submit"]:hover {
            background-color: #2e7030;
        }

        .cart-summary {
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<?php include 'heade.php'; ?>
<h1>Your Shopping Cart 🛒</h1>
<a href="member.php" id="link">← Continue Shopping</a><br><br><br>

<?php if (empty($cart_items)): ?>
    <p>Your cart is empty.</p>
<?php else: ?>
    <form method="post">
        <table>
            <tr>
                <th>Product</th>
                <th>Photo</th>
                <th>Qty / Ice / Sugar</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>

            <?php foreach ($cart_items as $item): ?>
                <?php
                $item_data = $_SESSION['cart'][$item['id']];
                $qty = $item_data['qty'] ?? $item_data;
                $ice = $item_data['ice'] ?? 'regular';
                $sugar = $item_data['sugar'] ?? 'full';
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><img src="view.php?image=<?php echo $item['photo']; ?>" width="80"></td>
                    <td>
                        <label>Quantity:</label>
                        <input type="number" name="quantities[<?= $item['id'] ?>]" value="<?= $qty ?>" min="0"><br>

                        <label>Ice:</label>
                        <select name="ice[<?= $item['id'] ?>]">
                            <option value="regular" <?= $ice == 'regular' ? 'selected' : '' ?>>Regular</option>
                            <option value="less" <?= $ice == 'less' ? 'selected' : '' ?>>Less</option>
                            <option value="no" <?= $ice == 'no' ? 'selected' : '' ?>>No Ice</option>
                        </select><br>

                        <label>Sugar:</label>
                        <select name="sugar[<?= $item['id'] ?>]">
                            <option value="full" <?= $sugar == 'full' ? 'selected' : '' ?>>Full</option>
                            <option value="half" <?= $sugar == 'half' ? 'selected' : '' ?>>Half</option>
                            <option value="no" <?= $sugar == 'no' ? 'selected' : '' ?>>No Sugar</option>
                        </select>
                    </td>
                    <td>RM <?= number_format($item['price'], 2) ?></td>
                    <td>RM <?= number_format($item['subtotal'], 2) ?></td>
                    <td>
                        <a href="cart.php?remove=<?= $item['id'] ?>" onclick="return confirm('Remove item?')" id="link">🗑 Remove</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <p class="cart-summary">Total: RM <?= number_format($total, 2) ?></p>

        <input type="submit" name="update" value="Update Cart">
    </form>

    <form action="checkout.php" method="get" style="margin-top: 20px;">
        <button type="submit">Proceed to Checkout</button>
    </form>
<?php endif; ?>
<footer>
    <?php

?></footer>
</body>
</html>