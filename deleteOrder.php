<?php
// deleteOrder.php

include 'db.php';
include '_base.php';

if (isset($pdo)) { $_db = $pdo; }
if (isset($conn)) { $_db = $conn; }

if (!is_logged_in()) {
    redirect('login.php');
}

// 获取 order_id
if (!isset($_GET['order_id'])) {
    redirect('orderhistory.php');
}

$order_id = intval($_GET['order_id']);
$user = $_SESSION['user'];


$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND (member_user_id = ? OR users_name = ?)");
$stmt->execute([$order_id, $user->user_id, $user->user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect('orderhistory.php');
}


$stmt = $pdo->prepare("DELETE FROM order_item WHERE order_id = ?");
$stmt->execute([$order_id]);


$stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
$stmt->execute([$order_id]);

redirect('orderhistory.php');
?>