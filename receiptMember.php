<?php
session_start();
include '_base.php';

// 检查订单ID参数
if (!isset($_GET['order_id'])) {
    die("Error: No order ID provided.");
}
$order_id = $_GET['order_id'];

// 获取订单基本信息（包含用户ID）
$stmt = $pdo->prepare("
    SELECT o.* 
    FROM orders o
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Error: Order not found.");
}

// 获取订单商品
$stmt = $pdo->prepare("
    SELECT oi.*, p.name AS product_name 
    FROM order_item oi 
    JOIN product p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 计算总金额
$grand_total = 0;
foreach ($items as $item) {
    $grand_total += $item['quantity'] * $item['price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Receipt</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e8f5e9, #f1f8e9);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .receipt {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            max-width: 800px;
            width: 100%;
            animation: fadeIn 1s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1 {
            color: #2e7030;
            text-align: center;
            font-size: 40px;
            margin-bottom: 30px;
        }

        .order-info {
            margin-bottom: 20px;
            font-size: 18px;
            color: #555;
        }

        .order-info p {
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fafafa;
            border-radius: 10px;
            overflow: hidden;
        }

        table th, table td {
            padding: 15px;
            text-align: center;
        }

        table th {
            background-color: #388e3c;
            color: white;
            font-size: 18px;
            letter-spacing: 0.5px;
        }

        table td {
            font-size: 16px;
            color: #444;
            border-bottom: 1px solid #eee;
        }

        .total {
            text-align: right;
            font-size: 24px;
            font-weight: bold;
            color: #2e7030;
            margin-top: 20px;
        }

        .buttons {
            text-align: center;
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: #388e3c;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 18px;
            margin: 10px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn:hover {
            background-color: #2e7030;
            transform: translateY(-2px);
        }

        .print-btn {
            background-color: #4caf50;
        }

        .print-btn:hover {
            background-color: #3d8b40;
        }

        @media (max-width: 600px) {
            .receipt {
                padding: 20px;
            }

            table th, table td {
                font-size: 14px;
                padding: 10px;
            }

            .total {
                font-size: 20px;
            }

            h1 {
                font-size: 32px;
            }

        }
    </style>
</head>
<body>

<div class="receipt">
    <h1><i class="fas fa-receipt"></i> Order Receipt</h1>
    <div class="order-info">
        <p><strong><i class="fas fa-file-invoice"></i> Order ID:</strong> #<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></p>
        <?php if (!empty($order['member_user_id'])): ?>
            <p><strong><i class="fas fa-user"></i> User ID:</strong> <?= htmlspecialchars($order['member_user_id']) ?></p>
        <?php else: ?>
            <p><strong><i class="fas fa-user"></i> User:</strong> Guest</p>
        <?php endif; ?>
        <p><strong><i class="fas fa-credit-card"></i> Payment Method:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
        <p><strong><i class="fas fa-calendar-alt"></i> Order Date:</strong> <?= htmlspecialchars($order['created_at']) ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>🍹 Product</th>
                <th>🔢 Quantity</th>
                <th>💵 Price (RM)</th>
                <th>🧮 Subtotal (RM)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['price'], 2) ?></td>
                    <td><?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="total">
        Total: RM<?= number_format($grand_total, 2) ?>
    </div>

    <div class="buttons">
        <a href="member.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Member Area</a>
        <a href="#" onclick="window.print()" class="btn print-btn"><i class="fas fa-print"></i> Print Receipt</a>
    </div>
</div>

</body>
</html>