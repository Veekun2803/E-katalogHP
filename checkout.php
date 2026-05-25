<?php
session_start();
include __DIR__ . '/admin/config.php';

/* =========================
   WAJIB LOGIN CUSTOMER
========================= */
if (!isset($_SESSION['customer_id'])) {
    header("Location: auth.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

/* =========================
   AMBIL DATA KERANJANG DARI DB
========================= */
$sql_cart = "SELECT c.qty, p.id, p.nama_hp, p.harga, p.stok, p.brand 
             FROM cart c 
             JOIN phones p ON c.phone_id = p.id 
             WHERE c.customer_id = $customer_id";
$res_cart = $conn->query($sql_cart);

if ($res_cart->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$items = [];
$grandTotal = 0;
$wa_admin = "6285862030566";

while ($row = $res_cart->fetch_assoc()) {
    $qty = intval($row['qty']);
    if ($qty > $row['stok']) $qty = $row['stok'];
    if ($qty <= 0) continue;

    $total = $row['harga'] * $qty;
    $grandTotal += $total;

    $items[] = [
        'id' => $row['id'],
        'nama' => $row['nama_hp'],
        'brand' => $row['brand'],
        'harga' => $row['harga'],
        'qty' => $qty,
        'total' => $total
    ];
}

/* =========================
   AMBIL DATA CUSTOMER
========================= */
$user = $conn->query("SELECT * FROM customers WHERE id = $customer_id")->fetch_assoc();

/* =========================
   PROSES CHECKOUT
========================= */
if (isset($_POST['checkout'])) {
    $nama   = htmlspecialchars($_POST['nama']);
    $wa     = htmlspecialchars($_POST['no_wa']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $metode = $_POST['metode'];

    $bukti = '';
    if (!empty($_FILES['bukti']['name'])) {
        $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png'];

        if (in_array($ext, $allowed)) {
            $bukti = uniqid() . '.' . $ext;
            $uploadDir = __DIR__ . "/admin/uploads/bukti_bayar/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            move_uploaded_file($_FILES['bukti']['tmp_name'], $uploadDir . $bukti);
        }
    }

    // 1. Simpan Pesanan ke Tabel Orders
    $stmt = $conn->prepare("INSERT INTO orders (customer_id, nama, alamat, no_wa, total, metode, bukti, status) VALUES (?,?,?,?,?,?,?, 'pending')");
    $stmt->bind_param("isssiss", $customer_id, $nama, $alamat, $wa, $grandTotal, $metode, $bukti);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // 2. Simpan/Perbarui Alamat & Kontak ke Tabel Customers (Auto-Save Profil)
    $stmtUpdate = $conn->prepare("UPDATE customers SET nama_lengkap=?, no_wa=?, alamat=? WHERE id=?");
    $stmtUpdate->bind_param("sssi", $nama, $wa, $alamat, $customer_id);
    $stmtUpdate->execute();

    // 3. Masukkan Detail Barang ke Order Items dan Kurangi Stok
    foreach ($items as $item) {
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, phone_id, qty, harga) VALUES (?,?,?,?)");
        $stmtItem->bind_param("iiii", $order_id, $item['id'], $item['qty'], $item['harga']);
        $stmtItem->execute();
        $conn->query("UPDATE phones SET stok = stok - {$item['qty']} WHERE id = {$item['id']}");
    }

    // 4. Kosongkan Keranjang Belanja
    $conn->query("DELETE FROM cart WHERE customer_id = $customer_id");

    // 5. Buat Pesan WhatsApp
    $pesan = "*PESANAN BARU - #INV$order_id*\n\n";
    $pesan .= "Nama: $nama\n";
    $pesan .= "Metode: " . strtoupper($metode) . "\n";
    $pesan .= "Total: Rp " . number_format($grandTotal,0,',','.') . "\n\n";
    $pesan .= "Detail Barang:\n";
    foreach($items as $i) { $pesan .= "- {$i['nama']} ({$i['qty']}x)\n"; }

    $link_wa = "https://wa.me/$wa_admin?text=" . urlencode($pesan);

    // Redirect ke Halaman Sukses
    header("Location: success.php?wa=".urlencode($link_wa));
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - PhoneStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen p-4 md:p-8 lg:p-12 text-slate-800">

<div class="max-w-6xl mx-auto">
    <div class="mb-10">
        <h1 class="text-4xl font-black text-slate-800 tracking-tighter italic">Checkout <span class="text-blue-600">Pesanan</span></h1>
        <div class="mt-4 flex items-center gap-3">
            <span class="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Sesi Enkripsi Checkout Aman</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
        
        <div class="lg:col-span-2">
            <div class="bg-white p-8 md:p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-100">
                <form method="POST" enctype="multipart/form-data" class="space-y-12">
                    
                    <section>
                        <div class="flex items-center gap-4 mb-8">
                            <span class="w-10 h-10 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-sm font-black shadow-lg shadow-blue-100">01</span>
                            <h2 class="text-xl font-black text-slate-800 tracking-tight">Detail Pengiriman</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Nama Penerima</label>
                                <input type="text" name="nama" required value="<?= htmlspecialchars($user['nama_lengkap'] ?? '') ?>"
                                class="w-full bg-slate-50 border-none p-4 rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-sm transition-all">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">WhatsApp</label>
                                <input type="text" name="no_wa" required value="<?= htmlspecialchars($user['no_wa'] ?? '') ?>"
                                class="w-full bg-slate-50 border-none p-4 rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-sm transition-all">
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Alamat Pengiriman</label>
                                <textarea name="alamat" required rows="3"
                                class="w-full bg-slate-50 border-none p-4 rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-sm transition-all"><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </section>

                    <section>
                        <div class="flex items-center gap-4 mb-8">
                            <span class="w-10 h-10 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-sm font-black shadow-lg shadow-blue-100">02</span>
                            <h2 class="text-xl font-black text-slate-800 tracking-tight">Metode Pembayaran</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Pilih Metode</label>
                                <select name="metode" id="metode_pembayaran" required onchange="updatePaymentInfo()"
                                    class="w-full bg-slate-50 border-none p-4 rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-blue-500 outline-none font-black text-sm cursor-pointer appearance-none">
                                    <option value="bank">Transfer Bank (SeaBank)</option>
                                    <option value="qris">QRIS All Payment</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Bukti Transfer</label>
                                <input type="file" name="bukti" required class="block w-full text-[10px] text-slate-400
                                    file:mr-4 file:py-3.5 file:px-6 file:rounded-xl file:border-0
                                    file:text-[10px] file:font-black file:bg-blue-600 file:text-white
                                    hover:file:bg-blue-700 transition-all cursor-pointer">
                            </div>
                        </div>

                        <div id="payment_info" class="p-8 rounded-[2.5rem] border-2 border-dashed border-slate-100 bg-slate-50/50 transition-all min-h-[160px] flex items-center justify-center">
                        </div>
                    </section>

                    <button name="checkout" class="group flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-blue-100 active:scale-[0.98] text-sm uppercase tracking-wider">
                        Konfirmasi & Bayar
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-1 space-y-4 sticky top-10">
            <a href="cart.php" class="flex items-center justify-center gap-2 w-full bg-white px-4 py-3 rounded-xl border border-slate-200 shadow-sm text-slate-500 hover:text-blue-600 hover:border-blue-200 transition-all active:scale-[0.98] group">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="text-[9px] font-black uppercase tracking-widest">Kembali ke Keranjang</span>
            </a>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-100">
                <h2 class="text-lg font-black mb-6 text-slate-800 flex justify-between items-center tracking-tight">
                    Pesananmu
                    <span class="text-[10px] font-black bg-slate-100 text-slate-400 px-3 py-1 rounded-full tracking-widest uppercase"><?= count($items) ?> Item</span>
                </h2>
                
                <div class="space-y-4 max-h-[280px] overflow-y-auto pr-2 custom-scrollbar mb-8">
                    <?php foreach ($items as $i): ?>
                    <div class="flex justify-between items-start gap-4 pb-4 border-b border-slate-50 last:border-0 group">
                        <div class="flex-1">
                            <p class="font-bold text-slate-700 text-xs leading-tight group-hover:text-blue-600 transition-colors"><?= $i['nama'] ?></p>
                            <p class="text-[9px] font-black text-slate-400 uppercase mt-1 tracking-wider italic"><?= $i['qty'] ?> Unit × Rp<?= number_format($i['harga'],0,',','.') ?></p>
                        </div>
                        <span class="font-black text-slate-800 text-[11px] text-right">Rp<?= number_format($i['total'],0,',','.') ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="space-y-3 pt-6 border-t-2 border-dashed border-slate-100">
                    <div class="flex justify-between text-[9px] font-black text-slate-400 uppercase tracking-[0.2em]">
                        <span>Pengiriman</span>
                        <span class="text-emerald-500 font-bold">Gratis Ongkir</span>
                    </div>
                    <div class="pt-2">
                        <p class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] mb-1">Total Tagihan</p>
                        <p class="text-3xl font-black text-blue-600 tracking-tighter">Rp<?= number_format($grandTotal,0,',','.') ?></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function updatePaymentInfo() {
    const metode = document.getElementById('metode_pembayaran').value;
    const infoDiv = document.getElementById('payment_info');
    
    if (metode === 'bank') {
        infoDiv.innerHTML = `
            <div class="flex flex-col md:flex-row items-center gap-6 w-full animate-in fade-in zoom-in duration-300">
                <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 flex-shrink-0">
                    <img src="https://images.seeklogo.com/logo-png/62/2/seabank-logo-png_seeklogo-620133.png" 
                         class="h-8 w-auto object-contain" alt="SeaBank">
                </div>
                <div class="text-center md:text-left">
                    <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.2em] mb-1 italic">Rekening Transfer (SeaBank)</p>
                    <p class="text-2xl font-black text-slate-800 tracking-widest">9017 5486 5539</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">a.n FAISAL DWIKI NURDIANSYAH</p>
                </div>
            </div>
        `;
    } else if (metode === 'qris') {
        infoDiv.innerHTML = `
            <div class="text-center w-full py-4 animate-in fade-in zoom-in duration-300">
                <p class="text-[9px] font-black text-blue-500 uppercase tracking-[0.2em] mb-8 italic">Scan QRIS All Payment</p>
                <div class="bg-white p-6 rounded-[3rem] inline-block shadow-2xl border border-slate-50 mb-8 group transition-transform hover:scale-105">
                    <img src="qris.jpeg" class="w-64 mx-auto rounded-xl" alt="QRIS">
                </div>
                <div class="flex items-center justify-center gap-2">
                    <span class="flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">OVO • DANA • GOPAY • SHOPEEPAY • LINKAJA</p>
                </div>
            </div>
        `;
    }
}
window.onload = updatePaymentInfo;
</script>

</body>
</html>