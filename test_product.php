<?php
include 'database.php';

// Handle stock update (individual product)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']) && isset($_POST['add_qty'])) {
    $product_id = (int)$_POST['product_id'];
    $add_qty = (int)$_POST['add_qty'];

    if ($add_qty > 0) {
        $update = $_db->prepare("UPDATE product SET stock = stock + ? WHERE id = ?");
        $update->execute([$add_qty, $product_id]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle global stock update (all product)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_all']) && isset($_POST['global_add_qty'])) {
    $qty = (int)$_POST['global_add_qty'];
    if ($qty > 0) {
        $update_all = $_db->prepare("UPDATE product SET stock = stock + ?");
        $update_all->execute([$qty]);
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Fetch all products
$stm = $_db->prepare('SELECT * FROM product');
$stm->execute();
$stock = $stm->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <title>Stock</title>
    <style>
        .stock-section {
            font-family: Times, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .stock-section h1 {
            text-align: center;
            color: black;
        }

        .stock-section table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            background: white;
        }

        .stock-section th, .stock-section td {
            border: 2px solid #4CAF50;
            padding: 10px;
            text-align: center;
        }

        .stock-section th {
            background-color: #4CAF50;
            color: white;
            font-size: 18px;
        }

        .stock-section td {
            font-size: 16px;
        }

        .stock-section .product {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #4CAF50;
            padding: 5px;
            background: #f0fff0;
        }

        .stock-section tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .stock-section tr:hover {
            background-color: #dff0d8;
        }

        .add-stock-form {
            margin-top: 5px;
        }

        .add-stock-form input[type="number"] {
            width: 60px;
            padding: 5px;
        }

        .add-stock-form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            margin-left: 5px;
            cursor: pointer;
            border-radius: 5px;
        }

        .add-stock-form button:hover {
            background-color: #45a049;
        }

        .global-stock-form {
            text-align: center;
            margin-top: 30px;
        }

        .global-stock-form input[type="number"] {
            width: 100px;
            padding: 7px;
            margin-left: 10px;
        }

        .global-stock-form button {
            background-color: #2196F3;
            color: white;
            border: none;
            padding: 7px 15px;
            margin-left: 10px;
            cursor: pointer;
            border-radius: 5px;
        }

        .global-stock-form button:hover {
            background-color: #0b7dda;
        }
    </style>
</head>
<body>
    <div class="stock-section">
        <h1>Stocks</h1>
        <table>
            <tr>
                <th>Product</th>
                <th>Photo</th>
                <th>Qty</th>
                <th>Add Stock</th>
            </tr>

            <?php foreach ($stock as $s): ?>
            <tr <?php if ($s['stock'] < 5) echo 'style="background-color: #ffcccc;"'; ?>>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><img src="view.php?f=<?= htmlspecialchars($s['photo']) ?>" class="product"></td>
                <td>
                    <?= htmlspecialchars($s['stock']) ?>
                    <?php if ($s['stock'] < 5): ?>
                        <span style="color: red; font-weight: bold;">Low Stock!</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" class="add-stock-form">
                        <input type="hidden" name="product_id" value="<?= $s['id'] ?>">
                        <input type="number" name="add_qty" min="1" required>
                        <button type="submit">Add</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- 🆕 Add Stock to All Products Form -->
        <form method="post" class="global-stock-form">
            <label for="global_add_qty"><strong>Add stock to all products:</strong></label>
            <input type="number" name="global_add_qty" id="global_add_qty" min="1" required>
            <button type="submit" name="add_to_all">Add to All</button>
        </form>
    </div>
</body>
</html>