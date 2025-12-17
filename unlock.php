<?php
// unlock.php - Script to clear lock
// Place this file alongside your index.php
require '_base.php';
include 'database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $stmt = $_db->prepare('UPDATE member SET is_locked = 0, lock_expires = NULL WHERE id = ?');
    $stmt->execute([$id]);
}
header('Location: index.php');
exit;
?>
