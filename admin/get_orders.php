<?php
include 'config.php';

header('Content-Type: application/json');

$result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE status='pending'");
$data = $result->fetch_assoc();

echo json_encode([
    'total' => (int)$data['total']
]);