<?php
include 'base.php';
include 'database.php';

if (isset($_GET['search'])) {
    $keyword = $_GET['search'];

    $stmt = $_db->prepare("SELECT id, name FROM product WHERE name LIKE :keyword LIMIT 5");
    $stmt->bindValue(':keyword', "$keyword%", PDO::PARAM_STR);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        foreach ($results as $row) {
            echo '<div class="suggestion-item" data-id="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</div>';
        }        
    } else {
        echo '<div class="suggestion-item no-click">No item found!</div>';
    }
}
?>
