<?php
include 'config.php';

$id = intval($_GET['id']);

// update status jadi lunas
$stmt = $conn->prepare("UPDATE orders SET status='lunas' WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: orders.php");
exit;