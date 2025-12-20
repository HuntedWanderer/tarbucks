<?php
// orderhistory.php

include '_base.php';

// 验证登录
if (!is_logged_in()) {
    redirect('login.php');
}

// Refresh User Session (Safe)
$user_id = $_SESSION['user']['user_id'];
$stmt = $_db->prepare("SELECT * FROM member WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

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

// 获取当前用户信息
$user = $_SESSION['user'];

// 查询订单
$stmt = $_db->prepare("
    SELECT 
        o.id,
        o.users_name,
        o.payment_method,
        o.created_at,
        COUNT(oi.id) AS total_items,
        SUM(oi.quantity * oi.price) AS total_amount
    FROM orders o
    LEFT JOIN order_item oi ON o.id = oi.order_id
    WHERE 
        o.member_user_id = ? OR 
        (o.users_name = ? AND o.member_user_id IS NULL)
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$user['user_id'], $user['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History - Tarbuck</title>
    <link rel="stylesheet" href="css/ss.css">
    <style>
   body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding-bottom: 50px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin: 30px 0 20px 0;
            font-size: 28px;
        }

        /* Container for the table */
        .history-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
            overflow-x: auto; /* Allows table to scroll on mobile */
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden; /* Rounds the corners of the table */
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            min-width: 600px; /* Forces scroll if screen is too small */
        }

        th, td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #388e3c; /* Tarbuck Green */
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f9fff9; /* Very light green on hover */
        }

        /* Specific Column Styles */
        .amount-column {
            color: #2e7d32;
            font-weight: bold;
            font-family: monospace; /* Aligns numbers nicely */
            font-size: 16px;
        }

        /* Action Buttons */
        .btn {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            transition: all 0.2s;
            margin-right: 5px;
        }

        .btn-view {
            background-color: #388e3c;
            color: white;
            border: 1px solid #388e3c;
        }
        .btn-view:hover {
            background-color: #2e7d32;
        }

        .btn-delete {
            background-color: white;
            color: #d32f2f;
            border: 1px solid #ef9a9a;
        }
        .btn-delete:hover {
            background-color: #ffebee;
            border-color: #d32f2f;
        }

        /* Empty State */
        .no-orders {
            text-align: center;
            background: white;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            color: #777;
            font-size: 18px;
        }
    </style>
</head>
<body>
<?php include 'heade.php'; ?>

<h1>🧾 Your Order History</h1>

<div class="history-container">
    <?php if (empty($orders)): ?>
        <div class="no-orders">You haven't placed any orders yet.</div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Items</th>
                    <th>Total Amount</th>
                    <th>Payment</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#TAR<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td><?= $order['total_items'] ?></td>
                        <td class="amount-column">RM <?= number_format($order['total_amount'], 2) ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $order['payment_method'])) ?></td>
                        <td><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                        <td>
                            <a class="btn" href="receiptMember.php?order_id=<?= $order['id'] ?>">View Details</a>
                            <a class="btn delete-btn" href="deleteOrder.php?order_id=<?= $order['id'] ?>" onclick="return confirm('Are you sure you want to delete this order?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<footer>
    <?php

?></footer>
</body>
</html>