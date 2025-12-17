<?php
include 'database.php';

// Safe: $_db comes from database.php
$stm = $_db->query('SELECT * FROM product');
$products = $stm->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (!empty($products)): ?>
    <div class="topHeader">
        <h1>Quick View</h1>
        <a href="product_listing.php" id="moreButton">More</a>
    </div>

    <div class="scrollable-container">
    <div class="product-scroll">
        <?php foreach ($products as $product): ?>
            <div class="product-box">
                <a href="product_detail.php?id=<?= htmlspecialchars($product['id']) ?>">
                    
                    <img src="view.php?f=<?= htmlspecialchars($product['photo']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                </a>
                
                <div class="product-name" title="<?= htmlspecialchars($product['name']) ?>">
                    <?= strlen($product['name']) > 15 ? htmlspecialchars(substr($product['name'], 0, 15)) . '...' : htmlspecialchars($product['name']) ?>
                </div>
                <div class="tooltip"><?= htmlspecialchars($product['name']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
