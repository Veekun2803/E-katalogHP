<?php
session_start();
include __DIR__ . '/admin/config.php';

// =======================
// INIT & LOGIC (Tetap Sama)
// =======================
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Hapus Item
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    unset($_SESSION['cart'][$id]);
    if (empty($_SESSION['cart'])) unset($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

// Update Qty
if (isset($_POST['update'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        $id = intval($id);
        $qty = max(1, intval($qty));
        $produk = $conn->query("SELECT stok FROM phones WHERE id=$id")->fetch_assoc();
        if ($produk) {
            $_SESSION['cart'][$id] = min($qty, $produk['stok']);
        }
    }
}

// Tambah ke Keranjang (dari index/detail)
if (isset($_POST['phone_id'])) {
    $id  = intval($_POST['phone_id']);
    $qty = max(1, intval($_POST['qty']));
    $produk = $conn->query("SELECT stok FROM phones WHERE id=$id")->fetch_assoc();
    if ($produk) {
        $currentQty = $_SESSION['cart'][$id] ?? 0;
        $_SESSION['cart'][$id] = min($currentQty + $qty, $produk['stok']);
    }
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - PhoneStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen text-gray-800">

<div class="max-w-6xl mx-auto px-4 py-10">
    
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight">Keranjang Belanja</h1>
            <p class="text-gray-500 mt-1">Anda memiliki <?= count($_SESSION['cart'] ?? []) ?> item di keranjang.</p>
        </div>
        <a href="index.php" class="text-blue-600 font-semibold flex items-center gap-2 hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali Belanja
        </a>
    </div>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-gray-100">
            <div class="bg-blue-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Keranjang Anda Kosong</h3>
            <p class="text-gray-500 mb-8">Sepertinya Anda belum memilih gadget impian.</p>
            <a href="index.php" class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                Mulai Belanja
            </a>
        </div>
    <?php else: ?>

    <form method="POST" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-4">
            <?php
            $grandTotal = 0;
            foreach ($_SESSION['cart'] as $id => $qty):
                $result = $conn->query("SELECT * FROM phones WHERE id=$id");
                $row = $result->fetch_assoc();
                if (!$row) continue;

                $img = $conn->query("SELECT image FROM phone_images WHERE phone_id=$id LIMIT 1")->fetch_assoc();
                $gambar = ($img && file_exists(__DIR__."/admin/uploads/".$img['image'])) ? "admin/uploads/".$img['image'] : "https://via.placeholder.com/200";
                
                $total = $row['harga'] * $qty;
                $grandTotal += $total;
            ?>
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
                <img src="<?= $gambar ?>" class="w-24 h-24 object-cover rounded-xl bg-gray-50">
                
                <div class="flex-grow">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-bold text-lg text-gray-900 leading-tight"><?= htmlspecialchars($row['nama_hp']) ?></h4>
                            <p class="text-sm text-gray-400 font-medium uppercase tracking-wider"><?= htmlspecialchars($row['brand']) ?></p>
                        </div>
                        <a href="cart.php?remove=<?= $id ?>" onclick="return confirm('Hapus item ini?')" class="text-gray-300 hover:text-red-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </a>
                    </div>

                    <div class="flex justify-between items-end mt-4">
                        <div class="flex items-center bg-gray-100 rounded-xl p-1">
                            <input type="number" name="qty[<?= $id ?>]" value="<?= $qty ?>" min="1" max="<?= $row['stok'] ?>"
                                   class="bg-transparent w-12 text-center font-bold text-gray-800 focus:outline-none">
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">Total Harga</p>
                            <p class="font-bold text-blue-600">Rp <?= number_format($total,0,',','.') ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="flex justify-start">
                <button name="update" class="text-sm font-bold text-blue-600 bg-blue-50 px-6 py-2 rounded-xl hover:bg-blue-100 transition">
                    🔄 Update Jumlah
                </button>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 sticky top-24">
                <h3 class="text-xl font-bold mb-6">Ringkasan Pesanan</h3>
                
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between text-gray-500">
                        <span>Total Item</span>
                        <span class="font-semibold text-gray-800"><?= array_sum($_SESSION['cart']) ?></span>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>Pajak (PPN 0%)</span>
                        <span class="font-semibold text-gray-800">Rp 0</span>
                    </div>
                    <hr class="border-gray-100">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold">Total Tagihan</span>
                        <span class="text-2xl font-black text-blue-600">Rp <?= number_format($grandTotal,0,',','.') ?></span>
                    </div>
                </div>

                <a href="checkout.php" class="block w-full text-center bg-blue-600 text-white py-4 rounded-2xl font-bold text-lg hover:bg-blue-700 transition shadow-lg shadow-blue-100 active:scale-[0.98]">
                    Lanjut Checkout
                </a>

                <div class="mt-6 flex items-center justify-center gap-2 text-xs text-gray-400 uppercase tracking-widest font-bold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Secure Checkout
                </div>
            </div>
        </div>

    </form>
    <?php endif; ?>

</div>

</body>
</html>