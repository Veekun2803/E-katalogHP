<?php
include __DIR__ . '/admin/config.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = intval($_GET['id']);

// ambil order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Pesanan tidak ditemukan");
}

// ambil item
$items = $conn->query("
    SELECT oi.*, p.nama_hp 
    FROM order_items oi
    JOIN phones p ON p.id = oi.phone_id
    WHERE oi.order_id = $id
");

/* =========================
   ✅ WHATSAPP CONFIG
========================= */
$no_admin = "6281234567890"; // GANTI NOMOR ADMIN

$pesan = "Halo Admin,%0A";
$pesan .= "Saya sudah melakukan pemesanan.%0A%0A";
$pesan .= "Invoice ID: #".$order['id']."%0A";
$pesan .= "Nama: ".$order['nama']."%0A";
$pesan .= "Total: Rp ".number_format($order['total'],0,',','.')."%0A";
$pesan .= "Metode: ".strtoupper($order['metode'])."%0A%0A";
$pesan .= "Detail:%0A";

// ambil ulang item untuk WA
$itemsWA = $conn->query("
    SELECT oi.qty, p.nama_hp 
    FROM order_items oi
    JOIN phones p ON p.id = oi.phone_id
    WHERE oi.order_id = $id
");

while ($i = $itemsWA->fetch_assoc()) {
    $pesan .= "- ".$i['nama_hp']." x".$i['qty']."%0A";
}

// link bukti (opsional tapi keren)
if (!empty($order['bukti'])) {
    $pesan .= "%0ABukti: http://localhost/ecommerce-hp/uploads/".$order['bukti'];
}

$linkWA = "https://wa.me/".$no_admin."?text=".$pesan;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Invoice #<?= $order['id'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">

<div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow">

    <!-- HEADER -->
    <div class="flex justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-blue-600">📱 Toko HP</h1>
            <p class="text-sm text-gray-500">Invoice Pembelian</p>
        </div>

        <div class="text-right">
            <p><b>Invoice #<?= $order['id'] ?></b></p>
            <p><?= $order['created_at'] ?></p>
        </div>
    </div>

    <!-- CUSTOMER -->
    <div class="mb-6">
        <p><b>Nama:</b> <?= htmlspecialchars($order['nama']) ?></p>
        <p><b>Alamat:</b> <?= htmlspecialchars($order['alamat']) ?></p>
        <p><b>Metode:</b> <?= strtoupper($order['metode']) ?></p>
        <p><b>Status:</b> 
            <span class="px-2 py-1 rounded 
                <?= $order['status']=='pending'?'bg-yellow-200':'bg-green-200' ?>">
                <?= $order['status'] ?>
            </span>
        </p>
    </div>

    <!-- TABLE -->
    <table class="w-full border mb-6">
        <thead class="bg-gray-200">
            <tr>
                <th class="p-2">Produk</th>
                <th>Harga</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>

        <?php while ($row = $items->fetch_assoc()): ?>
            <tr class="border-t">
                <td class="p-2"><?= htmlspecialchars($row['nama_hp']) ?></td>
                <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>
                <td><?= $row['qty'] ?></td>
                <td>Rp <?= number_format($row['harga'] * $row['qty'],0,',','.') ?></td>
            </tr>
        <?php endwhile; ?>

        </tbody>
    </table>

    <!-- TOTAL -->
    <div class="text-right mb-6">
        <p class="text-xl font-bold">
            Total: Rp <?= number_format($order['total'],0,',','.') ?>
        </p>
    </div>

    <!-- BUKTI -->
    <?php if ($order['bukti']): ?>
        <div class="mb-6">
            <p class="font-semibold">Bukti Pembayaran:</p>
            <img src="uploads/<?= $order['bukti'] ?>" 
                 class="w-48 border rounded mt-2">
        </div>
    <?php endif; ?>

    <!-- BUTTON -->
    <div class="flex flex-wrap gap-2 justify-between">

        <a href="index.php" 
           class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
           Kembali
        </a>

        <button onclick="window.print()"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            🖨 Print
        </button>

        <!-- ✅ WHATSAPP -->
        <a href="<?= $linkWA ?>" target="_blank"
           class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
           💬 Konfirmasi WA
        </a>

    </div>

</div>

</body>
</html>