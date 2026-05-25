<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: auth.php");
    exit;
}

include __DIR__ . '/admin/config.php';
$customer_id = $_SESSION['customer_id'];

// HANDLE AJAX UNTUK DETAIL
if (isset($_GET['ajax_detail'])) {
    $order_id = intval($_GET['ajax_detail']);
    $sql = "SELECT oi.*, p.nama_hp, p.brand 
            FROM order_items oi 
            JOIN phones p ON oi.phone_id = p.id 
            WHERE oi.order_id = $order_id";
    $res = $conn->query($sql);
    
    if ($res->num_rows > 0) {
        while ($item = $res->fetch_assoc()) {
            echo "
            <div class='flex justify-between items-center py-4 border-b border-gray-50 last:border-0'>
                <div>
                    <p class='text-[10px] font-black text-blue-500 uppercase tracking-widest'>{$item['brand']}</p>
                    <p class='text-sm font-bold text-gray-800'>{$item['nama_hp']}</p>
                    <p class='text-xs text-gray-500 font-medium'>{$item['qty']} unit x Rp " . number_format($item['harga'], 0, ',', '.') . "</p>
                </div>
                <div class='text-right'>
                    <p class='text-sm font-black text-gray-900'>Rp " . number_format($item['harga'] * $item['qty'], 0, ',', '.') . "</p>
                </div>
            </div>";
        }
    } else {
        echo "<p class='text-center text-gray-400 py-10 font-bold uppercase text-xs tracking-widest'>Data tidak ditemukan</p>";
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - PhoneStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">

<nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm mb-8 sticky top-0 z-40">
    <div class="max-w-5xl mx-auto px-4 h-16 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-2 text-blue-600 font-bold group">
            <div class="p-2 bg-blue-50 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </div>
            <span class="text-sm uppercase tracking-widest font-black">Belanja</span>
        </a>
        <h1 class="text-lg font-black text-gray-800 tracking-tighter italic uppercase">My <span class="text-blue-600">Orders</span></h1>
        <div class="w-20"></div>
    </div>
</nav>

<main class="max-w-5xl mx-auto px-4 pb-20">
    <div class="space-y-4">
        <?php
        $sql = "SELECT * FROM orders WHERE customer_id = $customer_id ORDER BY id DESC";
        $res = $conn->query($sql);

        if ($res->num_rows > 0):
            while ($row = $res->fetch_assoc()):
                $status = $row['status'];
                
                // Logika Label Sesuai Permintaan
                if ($status == 'lunas') {
                    $bg_status = "bg-green-100 text-green-600 border-green-200";
                    $label = "Siap Dikirim / Diambil";
                } elseif ($status == 'ditolak') {
                    $bg_status = "bg-red-100 text-red-600 border-red-200";
                    $label = "Pembayaran Tidak Valid";
                } else {
                    $bg_status = "bg-amber-100 text-amber-600 border-amber-200";
                    $label = "Menunggu Verifikasi";
                }
        ?>
            <div class="bg-white p-6 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3 mb-3">
                        <span class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] bg-blue-50 px-3 py-1 rounded-full italic">INV #<?= $row['id'] ?></span>
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border <?= $bg_status ?>">
                            <?= $label ?>
                        </span>
                    </div>
                    <h3 class="font-black text-gray-900 text-2xl tracking-tighter">Rp <?= number_format($row['total'], 0, ',', '.') ?></h3>
                    <p class="text-[10px] font-bold text-gray-400 uppercase mt-1 italic tracking-wider"><?= date('d F Y • H:i', strtotime($row['created_at'])) ?> via <?= strtoupper($row['metode']) ?></p>
                    
                    <?php if (!empty($row['no_resi'])): ?>
                        <div class="mt-4 inline-flex items-center gap-3 px-4 py-2 bg-slate-50 border border-slate-200 rounded-2xl">
                            <span class="text-xl">🚚</span>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Nomor Resi Pengiriman</p>
                                <p class="text-sm font-black text-blue-600 tracking-widest select-all"><?= htmlspecialchars($row['no_resi']) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button onclick="showDetail(<?= $row['id'] ?>)" class="px-6 py-3 bg-slate-900 text-white text-[10px] font-black rounded-2xl hover:bg-blue-600 transition-all uppercase tracking-[0.15em] shadow-lg shadow-slate-100">
                        Lihat Detail
                    </button>
                    
                    <?php if ($status == 'pending'): ?>
                        <div class="flex items-center gap-2 text-amber-500 bg-amber-50 px-4 py-3 rounded-2xl border border-amber-100">
                            <span class="flex h-2 w-2 rounded-full bg-amber-500 animate-ping"></span>
                            <span class="text-[10px] font-black uppercase tracking-widest italic">Checking...</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php 
            endwhile;
        else: 
        ?>
            <div class="text-center py-24 bg-white rounded-[3.5rem] border-2 border-dashed border-gray-100">
                <p class="text-5xl mb-6">🏜️</p>
                <p class="text-gray-400 font-black uppercase tracking-[0.2em] text-xs">Belum ada aktivitas pesanan</p>
                <a href="index.php" class="inline-block mt-6 px-8 py-3 bg-blue-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-blue-100 hover:scale-105 transition-transform">Mulai Belanja</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<div id="modalDetail" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" onclick="closeModal()"></div>
    <div class="bg-white w-full max-w-lg rounded-[3rem] relative z-10 shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-10 pb-6 flex justify-between items-start border-b border-gray-50">
            <div>
                <h2 class="text-2xl font-black text-gray-900 tracking-tighter uppercase italic">Detail <span class="text-blue-600">Items</span></h2>
                <p id="modalInvId" class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1"></p>
            </div>
            <button onclick="closeModal()" class="group p-2 bg-slate-50 rounded-full hover:bg-red-50 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400 group-hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
        
        <div id="modalBody" class="p-10 max-h-[50vh] overflow-y-auto">
            <div class="flex flex-col items-center justify-center py-10 gap-3">
                <div class="animate-spin rounded-full h-8 w-8 border-[4px] border-blue-600 border-t-transparent"></div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Memuat Data...</span>
            </div>
        </div>

        <div class="p-10 bg-slate-50 flex flex-col gap-4">
            <button onclick="closeModal()" class="w-full py-4 bg-blue-600 text-white font-black text-xs rounded-[1.5rem] shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all uppercase tracking-widest active:scale-[0.98]">
                Selesai
            </button>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modalDetail');
    const modalBody = document.getElementById('modalBody');
    const modalInvId = document.getElementById('modalInvId');

    function showDetail(orderId) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Lock scroll
        modalInvId.innerText = 'Purchase Invoice #' + orderId;
        modalBody.innerHTML = '<div class="flex flex-col items-center justify-center py-10 gap-3"><div class="animate-spin rounded-full h-8 w-8 border-[4px] border-blue-600 border-t-transparent"></div><span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Memuat Data...</span></div>';
        
        fetch('my_orders.php?ajax_detail=' + orderId)
            .then(response => response.text())
            .then(data => {
                setTimeout(() => { modalBody.innerHTML = data; }, 300); // Small delay for smooth feel
            });
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto'; // Unlock scroll
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
</script>

</body>
</html>