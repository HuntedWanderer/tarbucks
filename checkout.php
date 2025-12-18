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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];

    try {
        $pdo->beginTransaction();

        
        $stmt = $pdo->prepare("INSERT INTO orders 
            (member_user_id, payment_method, created_at) 
            VALUES (?, ?, NOW())");
        $stmt->execute([
            $_SESSION['user']['user_id'],
            $payment_method
        ]);
        $order_id = $pdo->lastInsertId();
        
        
        foreach ($_SESSION['cart'] as $product_id => $item) {
            
            if (!is_array($item)) {
                $item = [
                    'qty' => $item,
                    'ice' => 'regular',
                    'sugar' => 'full'
                ];
            }

            $qty = $item['qty'];
            $ice = $item['ice'];
            $sugar = $item['sugar'];

            
            $checkStmt = $pdo->prepare("SELECT stock FROM product WHERE id = ? FOR UPDATE");
            $checkStmt->execute([$product_id]);
            $current_stock = $checkStmt->fetchColumn();

            if ($current_stock < $qty) {
                throw new Exception("Product ID $product_id 库存不足");
            }

           
            $updateStmt = $pdo->prepare("UPDATE product SET stock = stock - ? WHERE id = ?");
            $updateStmt->execute([$qty, $product_id]);

        
            $priceStmt = $pdo->prepare("SELECT price FROM product WHERE id = ?");
            $priceStmt->execute([$product_id]);
            $price = $priceStmt->fetchColumn();

            $itemStmt = $pdo->prepare("INSERT INTO order_item 
                (order_id, product_id, quantity, price, ice, sugar) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $itemStmt->execute([$order_id, $product_id, $qty, $price, $ice, $sugar]);
        }

        $pdo->commit();
        unset($_SESSION['cart']);
        header("Location: receiptMember.php?order_id=" . $order_id);
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('Checkout Error: ' . $e->getMessage());
        header("Location: checkout.php?error=1");
    }
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Tarbuck Coffee</title>
    <link rel="stylesheet" href="css/ss.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e8f5e9, #f1f8e9);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1 {
       
            text-align: center;
           
            font-size: 36px;
        }

        form {
            background-color: white;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 100%;
            margin: 30px auto;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
            font-size: 18px;
            color: #444;
        }

        select {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
            background-color: #fafafa;
            transition: border-color 0.3s;
        }

        select:focus {
            border-color: #388e3c;
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #388e3c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2e7030;
        }

   

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<?php include 'heade.php';?>

<h1 class ="">Checkout</h1>
<form method="post">
    <label>Payment Method:</label>
    <select name="payment_method" required>
        <option value="touch_n_go">Touch 'n Go</option>
        <option value="debit">Debit Card</option>
        <option value="credit">Credit Card</option>
        <option value="cash">Cash</option>
    </select>

    <button type="submit">Place Order</button>
</form>
<footer>
    <?php

?></footer>
</body>
</html>