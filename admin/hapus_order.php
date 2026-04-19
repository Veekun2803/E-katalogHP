<?php
include 'config.php';

$id = intval($_GET['id']);

// ambil data dulu
$order = $conn->query("SELECT * FROM orders WHERE id=$id")->fetch_assoc();

if ($order) {

    // hapus file bukti
    if (!empty($order['bukti'])) {
        $file = __DIR__ . "/../uploads/" . $order['bukti'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // hapus item
    $conn->query("DELETE FROM order_items WHERE order_id=$id");

    // hapus order
    $conn->query("DELETE FROM orders WHERE id=$id");
}

header("Location: orders.php");
exit;