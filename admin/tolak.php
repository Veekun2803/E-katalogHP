<?php
include 'config.php';

$id = intval($_GET['id']);

// update status
$conn->query("UPDATE orders SET status='ditolak' WHERE id=$id");

// ambil data
$order = $conn->query("SELECT * FROM orders WHERE id=$id")->fetch_assoc();

// format WA
$wa = preg_replace('/[^0-9]/', '', $order['no_wa']);
if (substr($wa,0,1) == '0') {
    $wa = '62' . substr($wa,1);
}

// pesan
$pesan = urlencode("Halo {$order['nama']}, maaf pesanan kamu ditolak ❌\nSilakan hubungi admin.\nInvoice: {$id}");

header("Location: https://wa.me/$wa?text=$pesan");
exit;