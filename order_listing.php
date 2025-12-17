<?php
require '_base.php'; // 1. Use Master Base File

// --- DELETE LOGIC ---
if (is_post() && isset($_POST['delete'])) {
    $order_id = post('id'); // Use helper function
    
    if ($order_id) {
        try {
            $_db->beginTransaction(); // Use $_db, not $pdo
            
            // 1. Delete the items inside the order first
            $stmt = $_db->prepare("DELETE FROM order_item WHERE order_id = ?");
            $stmt->execute([$order_id]);
            
            // 2. Delete the order itself
            $stmt = $_db->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            
            $_db->commit();
            temp('success', 'Order #' . $order_id . ' deleted successfully');
            
        } catch (Exception $e) {
            $_db->rollBack();
            temp('error', 'Delete failed: ' . $e->getMessage());
            error_log('Delete order failed: ' . $e->getMessage());
        }
    }
    redirect('order_listing.php');
}

// --- FETCH ORDERS ---
// Fetch all orders, newest first
$stmt = $_db->query("SELECT id, member_user_id, address, payment_method, created_at 
                     FROM orders 
                     ORDER BY created_at DESC");
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Listing - Admin | Tarbuck</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        /* Specific Page Styles */
        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: #f4f4f4;
            margin: 0;
        }
        h1 {
            text-align: center;
            color: #333;
            margin: 30px 0;
        }
        .table-wrapper {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden; /* Rounds corners */
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background-color: #4caf50;
            color: white;
            font-weight: 600;
        }
        tr:hover { background-color: #f9f9f9; }
        
        .action-group {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9em;
            cursor: pointer;
            border: none;
        }
        .view-btn { background: #3498db; color: white; }
        .view-btn:hover { background: #2980b9; }
        
        .delete-btn { background: #e74c3c; color: white; }
        .delete-btn:hover { background: #c0392b; }
        
        .no-orders {
            text-align: center;
            color: #999;
            padding: 40px;
        }
    </style>
</head>
<body>

    <?php include 'main_page.php'; ?>

    <div class="container">
        <h1>All Customer Orders</h1>

        <div style="text-align:center; margin-bottom:20px;">
            <span style="color:green"><?= temp('success') ?></span>
            <span style="color:red"><?= temp('error') ?></span>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) === 0): ?>
                        <tr>
                            <td colspan="5" class="no-orders">No orders found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#TAR<?= str_pad($order->id, 5, '0', STR_PAD_LEFT) ?></td>
                                
                                <td><?= htmlspecialchars($order->member_user_id ?? 'Guest') ?></td>
                                
                                <td><?= ucfirst($order->payment_method) ?></td>
                                
                                <td><?= date('Y-m-d H:i', strtotime($order->created_at)) ?></td>
                                
                                <td>
                                    <div class="action-group">
                                        <a class="btn view-btn" href="receipt.php?order_id=<?= $order->id ?>" target="_blank">View</a>
                                        
                                        <form method="post" onsubmit="return confirm('Are you sure? This will permanently delete this order and its items.');">
                                            <input type="hidden" name="id" value="<?= $order->id ?>">
                                            <button type="submit" name="delete" class="btn delete-btn">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>