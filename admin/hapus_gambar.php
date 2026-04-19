<?php
include 'config.php';

// validasi parameter
if (!isset($_GET['id']) || !isset($_GET['phone_id'])) {
    die("Parameter tidak lengkap");
}

$id = intval($_GET['id']);
$phone_id = intval($_GET['phone_id']);

// ambil data gambar dari DB
$stmt = $conn->prepare("SELECT image FROM phone_images WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Data gambar tidak ditemukan");
}

// path file (WAJIB pakai __DIR__)
$file = __DIR__ . "/uploads/" . $data['image'];

// cek & hapus file fisik
if (file_exists($file)) {
    if (!unlink($file)) {
        die("Gagal menghapus file gambar");
    }
}

// hapus dari database
$stmtDel = $conn->prepare("DELETE FROM phone_images WHERE id=?");
$stmtDel->bind_param("i", $id);
$stmtDel->execute();

// redirect kembali ke edit
header("Location: edit.php?id=" . $phone_id . "&hapus=success");
exit;
?>