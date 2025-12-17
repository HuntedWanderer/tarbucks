<?php
// orderhistory.php

include '_base.php';
include 'heade.php';

// 验证登录
if (!is_logged_in()) {
    redirect('login.php');
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
$stmt->execute([$user->user_id, $user->user_id]);
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
        font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f0f4f3;
      
    }
    
  
    .cart-button {
        margin-left: auto;
        background-color: lightgreen;
        color: #388e3c;
        padding: 16px 28px;
        border-radius: 15px;
        font-size: 36px;
        font-weight: bold;
        text-decoration: none;
        transition: background-color 0.3s, transform 0.2s;
    }
    .cart-button:hover {
        background-color: #2e7d32;
        transform: translateY(-2px);
    }


    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        overflow-x: auto;
    }
    th, td {
        padding: 16px 20px;
        text-align: center;
        font-size: 15px;
        border-bottom: 1px solid #e0e0e0;
        transition: background-color 0.3s ease;
    }
    th {
        background-color: #388e3c;
        color: #ffffff;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    tbody tr:hover {
        background-color: #e8f5e9;
    }
    .amount-column {
        color: #2e7d32;
        font-weight: bold;
        font-size: 16px;
    }
    .btn {
        background-color: #388e3c;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: background-color 0.3s, transform 0.2s;
        display: inline-block;
        margin: 2px;
    }
    .btn:hover {
        background-color: #2e7d32;
        transform: translateY(-2px);
    }
    .delete-btn {
        background-color: #e53935;
    }
    .delete-btn:hover {
        background-color: #c62828;
    }
    .no-orders {
        text-align: center;
        padding: 60px 20px;
        color: #888;
        font-size: 20px;
        font-weight: 500;
    }
    </style>
</head>
<body>


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