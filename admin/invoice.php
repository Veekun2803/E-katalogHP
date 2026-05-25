<?php
session_start();
include 'config.php';

/* |--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

date_default_timezone_set('Asia/Jakarta');

// Cek apakah ada parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID Pesanan tidak ditemukan!");
}

$id = intval($_GET['id']);

// Ambil data pesanan
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Data pesanan tidak ditemukan di database!");
}

// Ambil detail barang (Order Items)
$sql_items = "SELECT oi.*, p.nama_hp, p.brand 
              FROM order_items oi 
              JOIN phones p ON oi.phone_id = p.id 
              WHERE oi.order_id = $id";
$items = $conn->query($sql_items);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= $order['id'] ?> - <?= htmlspecialchars($order['nama']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc;
        }
        
        /* Pengaturan khusus saat di-print */
        @media print {
            body { 
                background-color: white !important; 
                margin: 0; 
                padding: 0;
            }
            .no-print { 
                display: none !important; 
            }
            .print-border {
                border-color: #000 !important;
            }
            /* Memaksa warna background muncul saat diprint (opsional) */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body class="text-slate-800">

<div class="max-w-3xl mx-auto mt-8 mb-4 px-8 no-print flex justify-end">
    <button onclick="window.print()" class="flex items-center gap-2 bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Cetak Invoice / Surat Jalan
    </button>
</div>

<div class="max-w-3xl mx-auto bg-white p-10 md:p-12 md:rounded-2xl md:shadow-xl border border-slate-100 print-border mb-20">
    
    <div class="flex justify-between items-start border-b-2 border-slate-900 pb-6 mb-8 print-border">
        <div>
            <h1 class="text-3xl font-black tracking-tighter uppercase italic">Phone<span class="text-slate-400">Store</span></h1>
            <p class="text-sm font-semibold text-slate-500 mt-1">Pusat Smartphone Original & Bergaransi</p>
        </div>
        <div class="text-right">
            <h2 class="text-2xl font-black uppercase tracking-widest">INVOICE</h2>
            <p class="text-sm font-bold text-slate-500 mt-1">#INV-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-8 mb-8">
        
        <div>
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Diterbitkan Oleh:</h3>
            <p class="font-bold text-sm">PhoneStore Indonesia</p>
            <p class="text-sm text-slate-600 mt-1">Jl. Teknologi No. 99, Jakarta Pusat<br>WhatsApp: 0858-6203-0566</p>
        </div>

        <div>
            <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Ditujukan Kepada:</h3>
            <p class="font-bold text-sm uppercase"><?= htmlspecialchars($order['nama']) ?></p>
            <p class="text-sm text-slate-600 mt-1">
                <?= nl2br(htmlspecialchars($order['alamat'])) ?><br>
                <span class="font-bold mt-1 inline-block">Telp/WA: <?= htmlspecialchars($order['no_wa']) ?></span>
            </p>
        </div>

    </div>

    <div class="flex flex-wrap gap-6 mb-8 p-4 bg-slate-50 rounded-xl border border-slate-200 print-border">
        <div>
            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Tanggal Order</span>
            <span class="font-bold text-sm"><?= date('d F Y', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="border-l border-slate-300 pl-6 print-border">
            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Metode Pembayaran</span>
            <span class="font-bold text-sm uppercase"><?= htmlspecialchars($order['metode']) ?></span>
        </div>
        <div class="border-l border-slate-300 pl-6 print-border">
            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Status Pesanan</span>
            <span class="font-bold text-sm uppercase"><?= htmlspecialchars($order['status']) ?></span>
        </div>
        <?php if(!empty($order['no_resi'])): ?>
        <div class="border-l border-slate-300 pl-6 print-border">
            <span class="block text-[9px] font-black text-slate-400 uppercase tracking-widest">Nomor Resi</span>
            <span class="font-black text-sm uppercase"><?= htmlspecialchars($order['no_resi']) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <table class="w-full mb-8">
        <thead>
            <tr class="border-b-2 border-slate-900 print-border text-left">
                <th class="py-3 text-[10px] font-black uppercase tracking-widest text-slate-500">Deskripsi Barang</th>
                <th class="py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 text-center">Qty</th>
                <th class="py-3 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Harga Satuan</th>
                <th class="py-3 text-[10px] font-black uppercase tracking-widest text-slate-900 text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody class="border-b border-slate-300 print-border">
            <?php 
            $subtotal_all = 0;
            while($item = $items->fetch_assoc()): 
                $subtotal_item = $item['qty'] * $item['harga'];
                $subtotal_all += $subtotal_item;
            ?>
            <tr class="border-b border-slate-100 print-border last:border-0">
                <td class="py-4">
                    <p class="font-bold text-sm"><?= htmlspecialchars($item['nama_hp']) ?></p>
                    <p class="text-[10px] text-slate-400 uppercase font-bold tracking-widest"><?= htmlspecialchars($item['brand']) ?></p>
                </td>
                <td class="py-4 text-center text-sm font-bold"><?= $item['qty'] ?></td>
                <td class="py-4 text-right text-sm text-slate-600">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                <td class="py-4 text-right text-sm font-black">Rp <?= number_format($subtotal_item, 0, ',', '.') ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="flex justify-end mb-12">
        <div class="w-1/2">
            <div class="flex justify-between py-2 border-b border-slate-100 print-border">
                <span class="text-sm font-bold text-slate-500">Subtotal</span>
                <span class="text-sm font-bold">Rp <?= number_format($subtotal_all, 0, ',', '.') ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-100 print-border">
                <span class="text-sm font-bold text-slate-500">Ongkos Kirim</span>
                <span class="text-sm font-bold uppercase text-slate-800">Gratis</span>
            </div>
            <div class="flex justify-between py-4 border-b-2 border-slate-900 print-border mt-2">
                <span class="text-lg font-black uppercase tracking-widest">Total Bayar</span>
                <span class="text-xl font-black">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <div class="text-center pt-8 border-t border-slate-200 print-border">
        <p class="text-sm font-bold italic text-slate-800">"Terima kasih atas pesanan Anda!"</p>
        <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest mt-2">
            Barang yang sudah dibeli dan dibuka segelnya tidak dapat dikembalikan kecuali ada cacat pabrik (Garansi Resmi).<br>
            Harap lakukan video unboxing saat menerima paket.
        </p>
    </div>

</div>

</body>
</html>