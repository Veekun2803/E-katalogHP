<?php
session_start();
include __DIR__ . '/admin/config.php';

if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

$cart = $_SESSION['cart'];
$items = [];
$grandTotal = 0;
$wa_admin = "6285862030566"; // Nomor WhatsApp Admin

foreach ($cart as $id => $qty) {
    $id = intval($id);
    $result = $conn->query("SELECT * FROM phones WHERE id=$id");
    $row = $result->fetch_assoc();
    if (!$row) continue;

    if ($qty > $row['stok']) $qty = $row['stok'];
    $total = $row['harga'] * $qty;
    $grandTotal += $total;

    $img = $conn->query("SELECT image FROM phone_images WHERE phone_id=$id LIMIT 1")->fetch_assoc();
    $gambar = ($img && file_exists(__DIR__."/admin/uploads/".$img['image'])) ? "admin/uploads/".$img['image'] : "https://via.placeholder.com/150";

    $items[] = [
        'id' => $id,
        'nama' => $row['nama_hp'],
        'harga' => $row['harga'],
        'qty' => $qty,
        'total' => $total,
        'gambar' => $gambar
    ];
}

if (isset($_POST['checkout'])) {
    $nama   = $_POST['nama'];
    $no_wa  = $_POST['no_wa'];
    $alamat = $_POST['alamat'];
    $metode = $_POST['metode'];

    // Proses Bukti Pembayaran
    $bukti = '';
    if (!empty($_FILES['bukti']['name'])) {
        $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));
        $bukti = uniqid() . '.' . $ext;
        $uploadDir = __DIR__ . "/uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        move_uploaded_file($_FILES['bukti']['tmp_name'], $uploadDir . $bukti);
    }

    // Simpan ke DB
    $stmt = $conn->prepare("INSERT INTO orders (nama, alamat, no_wa, total, metode, bukti, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("sssiss", $nama, $alamat, $no_wa, $grandTotal, $metode, $bukti);
    $stmt->execute();
    $order_id = $conn->insert_id;

    foreach ($items as $item) {
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, phone_id, qty, harga) VALUES (?, ?, ?, ?)");
        $stmtItem->bind_param("iiii", $order_id, $item['id'], $item['qty'], $item['harga']);
        $stmtItem->execute();
        $conn->query("UPDATE phones SET stok = stok - {$item['qty']} WHERE id = {$item['id']}");
    }

    unset($_SESSION['cart']);

    // Pesan WA
    $pesan = "*PESANAN BARU - #INV$order_id*\n\n";
    $pesan .= "Nama: $nama\nAlamat: $alamat\nTotal: Rp " . number_format($grandTotal,0,',','.') . "\nMetode: $metode\n\n_Mohon segera diproses admin._";
    $link_wa = "https://wa.me/$wa_admin?text=" . urlencode($pesan);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8"><title>Success - PhoneStore</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700&display=swap" rel="stylesheet">
    </head>
    <body class="bg-slate-50 font-['Plus_Jakarta_Sans'] flex items-center justify-center min-h-screen p-4">
        <div class="bg-white p-8 rounded-[2rem] shadow-xl shadow-blue-100 text-center max-w-md border border-blue-50">
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h2 class="text-3xl font-black text-gray-900 mb-2">Terima Kasih!</h2>
            <p class="text-gray-500 mb-8 font-medium">Pesanan <span class="text-blue-600 font-bold">#INV<?= $order_id ?></span> berhasil dibuat. Konfirmasi via WhatsApp untuk mempercepat proses pengiriman.</p>
            <div class="space-y-3">
                <a href="<?= $link_wa ?>" target="_blank" class="block w-full bg-[#25D366] hover:bg-[#128C7E] text-white font-bold py-4 rounded-2xl transition shadow-lg shadow-green-100 flex items-center justify-center gap-2">
                    Kirim Konfirmasi WA
                </a>
                <a href="index.php" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold py-4 rounded-2xl transition">Kembali Belanja</a>
            </div>
        </div>
        <script>setTimeout(() => { window.open("<?= $link_wa ?>", "_blank"); }, 2000);</script>
    </body>
    </html>
    <?php exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - PhoneStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 pb-20">

<div class="max-w-6xl mx-auto px-4 py-10">
    <div class="flex items-center gap-4 mb-10">
        <a href="cart.php" class="bg-white p-2 rounded-xl border border-gray-100 shadow-sm hover:bg-gray-50 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </a>
        <h1 class="text-3xl font-black text-gray-900">Checkout</h1>
    </div>

    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center text-sm">1</span>
                    Informasi Pengiriman
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">Nama Lengkap</label>
                        <input type="text" name="nama" required class="w-full bg-gray-50 border-none ring-1 ring-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="John Doe">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">WhatsApp</label>
                        <input type="text" name="no_wa" required class="w-full bg-gray-50 border-none ring-1 ring-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="628xxx">
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">Alamat Lengkap</label>
                        <textarea name="alamat" rows="3" required class="w-full bg-gray-50 border-none ring-1 ring-gray-200 rounded-xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Nama Jalan, No. Rumah, Kota, Kode Pos"></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <span class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center text-sm">2</span>
                    Pembayaran
                </h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center justify-center p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 transition">
                            <input type="radio" name="metode" value="bank" required class="sr-only">
                            <span class="font-bold text-gray-700">Transfer Bank</span>
                        </label>
                        <label class="relative flex items-center justify-center p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-gray-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 transition">
                            <input type="radio" name="metode" value="qris" required class="sr-only">
                            <span class="font-bold text-gray-700">QRIS</span>
                        </label>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                        <p class="text-sm text-blue-800 leading-relaxed">
                            <b>BCA:</b> 123456789 a.n Toko HP <br>
                            <b>Mandiri:</b> 987654321 a.n Toko HP
                        </p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-bold text-gray-700 ml-1">Upload Bukti Transfer</label>
                        <div class="border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center hover:border-blue-400 transition cursor-pointer relative">
                            <input type="file" name="bukti" class="absolute inset-0 opacity-0 cursor-pointer" onchange="previewFile(this)">
                            <div id="preview-text" class="text-gray-400 text-sm">Klik atau seret foto bukti di sini</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-5">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 sticky top-24">
                <h3 class="text-xl font-bold mb-6">Ringkasan Pesanan</h3>
                <div class="max-h-[400px] overflow-y-auto pr-2 space-y-4 mb-6">
                    <?php foreach ($items as $item): ?>
                    <div class="flex items-center gap-4">
                        <img src="<?= $item['gambar'] ?>" class="w-16 h-16 object-cover rounded-xl shadow-sm border border-gray-50">
                        <div class="flex-1">
                            <h4 class="font-bold text-gray-800 leading-tight line-clamp-1"><?= $item['nama'] ?></h4>
                            <p class="text-sm text-gray-400"><?= $item['qty'] ?> x Rp <?= number_format($item['harga'],0,',','.') ?></p>
                        </div>
                        <div class="font-bold text-gray-900 text-sm">Rp <?= number_format($item['total'],0,',','.') ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="border-t border-dashed border-gray-100 pt-6 space-y-3">
                    <div class="flex justify-between text-gray-500 font-medium">
                        <span>Subtotal</span>
                        <span>Rp <?= number_format($grandTotal,0,',','.') ?></span>
                    </div>
                    <div class="flex justify-between text-gray-500 font-medium">
                        <span>Biaya Admin</span>
                        <span class="text-green-600">Free</span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="text-lg font-black text-gray-900">Total</span>
                        <span class="text-2xl font-black text-blue-600">Rp <?= number_format($grandTotal,0,',','.') ?></span>
                    </div>
                </div>

                <button name="checkout" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl mt-8 transition shadow-lg shadow-blue-100 flex items-center justify-center gap-2 group">
                    Bayar Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </button>
            </div>
        </div>

    </form>
</div>

<script>
    function previewFile(input) {
        const text = document.getElementById('preview-text');
        if (input.files && input.files[0]) {
            text.innerHTML = "📄 " + input.files[0].name;
            text.classList.add('text-blue-600', 'font-bold');
        }
    }
</script>

</body>
</html>