<?php
include 'config.php';

$id = intval($_GET['id']); // amankan input

// 1. Ambil semua gambar dulu
$stmt = $conn->prepare("SELECT image FROM phone_images WHERE phone_id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// 2. Hapus file dari folder
while ($row = $result->fetch_assoc()) {
    $file = __DIR__ . "/uploads/" . $row['image'];

    if (file_exists($file)) {
        unlink($file);
    }
}

// 3. Hapus data dari DB (phones + relasi kehapus otomatis)
$stmt = $conn->prepare("DELETE FROM phones WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

// 4. Redirect
header("Location: index.php");
exit;
?>