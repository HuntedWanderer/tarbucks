<?php
include '_base.php';


$stm = $_db->query('SELECT * FROM product');
$products = $stm->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Listing</title>
    <link rel="icon" href="/images/logo.webp">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="prodlist-head-content">
        <h1 id="prodlistTitleHeader">Product Listing</h1>
        <a href="try.php" class="prodnav"><span>HOME</span></a>
    </div>

   
    <form action="delete.php" method="post" id="deleteForm">
    <div class="gallery">
        <?php foreach ($products as $product): ?>
            <div class="image-box">
                <a href="product_detail.php?id=<?= htmlspecialchars($product['id']) ?>">
                    <img class="product" src="view.php?image=<?= htmlspecialchars($product['photo']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                </a>
                <div class="description"><?= htmlspecialchars($product['name']) ?></div>
                <input type="checkbox" name="id" value="<?= htmlspecialchars($product['id']) ?>" class="delete-checkbox">
            </div>
        <?php endforeach; ?>
    </div>

 
    <input type="hidden" name="id" id="id">

    <div style="width: 100%; display: flex; justify-content: center; margin: 40px 0;">
    <button type="submit" id="delete">Delete</button>
</div>

</form>

<script>
document.getElementById('deleteForm').addEventListener('submit', function (e) {
    const checkboxes = document.querySelectorAll('.delete-checkbox');
    const selectedIds = Array.from(checkboxes)
                            .filter(cb => cb.checked)
                            .map(cb => cb.value);

    if (selectedIds.length === 0) {
        e.preventDefault();
        alert('Please select at least one product to delete.');
        return;
    }

   
    const confirmed = confirm('Are you sure you want to delete the selected product(s)?');
    if (!confirmed) {
        e.preventDefault(); 
        return;
    }

    document.getElementById('id').value = selectedIds.join(',');
});
</script>
<footer>
    <?php

?></footer>

</body>
</html>
