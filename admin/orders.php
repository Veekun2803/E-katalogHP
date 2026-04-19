<?php
session_start();
include 'config.php';

/*
|--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

// =======================
// HANDLE AKSI (Sama seperti logika Anda, hanya ditambahkan feedback)
// =======================
if (isset($_GET['aksi'], $_GET['id'])) {
    $id   = intval($_GET['id']);
    $aksi = $_GET['aksi'];
    $allowed = ['verifikasi','tolak','hapus'];
    if (!in_array($aksi, $allowed)) die("Aksi tidak valid");

    $stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        if ($aksi == 'verifikasi') {
            $conn->query("UPDATE orders SET status='lunas' WHERE id=$id");
        }
        if ($aksi == 'tolak') {
            $conn->query("UPDATE orders SET status='ditolak' WHERE id=$id");
        }
        if ($aksi == 'hapus') {
            $conn->begin_transaction();
            try {
                if (!empty($order['bukti'])) {
                    $file = "../uploads/" . $order['bukti'];
                    if (file_exists($file)) unlink($file);
                }
                $conn->query("DELETE FROM order_items WHERE order_id=$id");
                $conn->query("DELETE FROM orders WHERE id=$id");
                $conn->commit();
            } catch (Exception $e) { $conn->rollback(); }
        }
    }
    header("Location: orders.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Pesanan | Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important; color: white !important; border-radius: 10px; border: none;
        }
        table.dataTable dtr-inline.collapsed>tbody>tr>td:first-child:before { background-color: #2563eb; }
        .badge { @apply px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider; }
    </style>
</head>

<body class="bg-slate-50 min-h-screen pb-20">

<nav class="bg-white border-b border-slate-200 mb-10">
    <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">📦 Order<span class="text-blue-600">Master</span></h1>
        <a href="tampil.php" class="flex items-center gap-2 text-slate-500 hover:text-blue-600 font-bold transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Dashboard
        </a>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center font-bold">∑</div>
            <div>
                <p class="text-slate-400 text-sm font-semibold">Total Order</p>
                <h3 class="text-xl font-bold"><?= $conn->query("SELECT id FROM orders")->num_rows ?></h3>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-2xl flex items-center justify-center">⏳</div>
            <div>
                <p class="text-slate-400 text-sm font-semibold">Pending</p>
                <h3 class="text-xl font-bold"><?= $conn->query("SELECT id FROM orders WHERE status='pending'")->num_rows ?></h3>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center">✅</div>
            <div>
                <p class="text-slate-400 text-sm font-semibold">Selesai</p>
                <h3 class="text-xl font-bold"><?= $conn->query("SELECT id FROM orders WHERE status='lunas'")->num_rows ?></h3>
            </div>
        </div>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-slate-100">
        <table id="tableOrders" class="w-full text-slate-700">
            <thead>
                <tr class="text-left text-slate-400 text-xs uppercase tracking-widest">
                    <th class="pb-4">Order ID</th>
                    <th class="pb-4">Pelanggan</th>
                    <th class="pb-4">Total Bayar</th>
                    <th class="pb-4">Metode</th>
                    <th class="pb-4">Bukti</th>
                    <th class="pb-4">Status</th>
                    <th class="pb-4">Tanggal</th>
                    <th class="pb-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
            <?php
            $orders = $conn->query("SELECT * FROM orders ORDER BY id DESC");
            while ($o = $orders->fetch_assoc()) {
                $wa = preg_replace('/[^0-9]/', '', $o['no_wa']);
                if (substr($wa,0,1) == '0') $wa = '62' . substr($wa,1);
                
                $status = $o['status'] ?? 'pending';
                $badgeClass = ($status == 'lunas') ? 'bg-green-100 text-green-600' : (($status == 'ditolak') ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600');
            ?>
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="py-4 font-bold text-blue-600">#<?= $o['id'] ?></td>
                    <td class="py-4">
                        <div class="font-bold text-slate-800"><?= htmlspecialchars($o['nama']) ?></div>
                        <a href="https://wa.me/<?= $wa ?>" target="_blank" class="text-xs text-green-500 font-semibold hover:underline"><?= $o['no_wa'] ?></a>
                    </td>
                    <td class="py-4 font-black">Rp <?= number_format($o['total'],0,',','.') ?></td>
                    <td class="py-4"><span class="bg-slate-100 px-2 py-1 rounded text-[10px] font-bold"><?= strtoupper($o['metode']) ?></span></td>
                    <td class="py-4">
                        <?php if($o['bukti']): ?>
                            <a href="../uploads/<?= $o['bukti'] ?>" target="_blank" class="text-blue-500 hover:text-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </a>
                        <?php else: ?>
                            <span class="text-slate-300">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter <?= $badgeClass ?>">
                            <?= $status ?>
                        </span>
                    </td>
                    <td class="py-4 text-slate-400 text-xs italic"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
                    <td class="py-4">
                        <div class="flex items-center justify-center gap-2">
                            <button class="detailBtn p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition" data-id="<?= $o['id'] ?>" title="Detail Item">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>
                            </button>

                            <?php if ($status == 'pending'): ?>
                                <a href="javascript:confirmAction('?aksi=verifikasi&id=<?= $o['id'] ?>', 'Verifikasi pembayaran ini?')" class="p-2 bg-green-50 text-green-600 rounded-xl hover:bg-green-600 hover:text-white transition">✔</a>
                                <a href="javascript:confirmAction('?aksi=tolak&id=<?= $o['id'] ?>', 'Tolak pesanan ini?')" class="p-2 bg-orange-50 text-orange-600 rounded-xl hover:bg-orange-600 hover:text-white transition">✖</a>
                            <?php endif; ?>

                            <a href="javascript:confirmAction('?aksi=hapus&id=<?= $o['id'] ?>', 'Hapus permanen data ini?')" class="p-2 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition">🗑</a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    let table = $('#tableOrders').DataTable({
        responsive: true,
        language: { search: "_INPUT_", searchPlaceholder: "Cari Pesanan..." }
    });

    // Detail Button
    $('#tableOrders tbody').on('click', '.detailBtn', function () {
        let tr = $(this).closest('tr');
        let row = table.row(tr);
        let id = $(this).data('id');

        if (row.child.isShown()) {
            row.child.hide();
            $(this).removeClass('bg-blue-600 text-white');
        } else {
            $(this).addClass('bg-blue-600 text-white');
            fetch('detail_order.php?id=' + id)
                .then(res => res.text())
                .then(html => {
                    row.child(`<div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 m-2 shadow-inner">${html}</div>`).show();
                });
        }
    });
});

// SweetAlert Confirmation
function confirmAction(url, text) {
    Swal.fire({
        title: 'Apakah anda yakin?',
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Lakukan!',
        cancelButtonText: 'Batal',
        borderRadius: '20px'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    })
}
</script>

</body>
</html>