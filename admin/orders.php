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

/* |--------------------------------------------------------------------------
| HANDLE AKSI (POST)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'], $_POST['id'])) {
    $id   = intval($_POST['id']);
    $aksi = $_POST['aksi'];
    
    // Tambahkan 'input_resi' ke daftar aksi yang diizinkan
    $allowed = ['verifikasi','tolak','hapus', 'input_resi'];
    if (!in_array($aksi, $allowed)) die("Aksi tidak valid");

    $stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    if ($order) {
        $msg = "";
        $type = "success";

        if ($aksi == 'verifikasi') {
            $conn->query("UPDATE orders SET status='lunas' WHERE id=$id");
            $msg = "Pesanan #$id berhasil diverifikasi.";
        }
        if ($aksi == 'tolak') {
            $conn->query("UPDATE orders SET status='ditolak' WHERE id=$id");
            $msg = "Pesanan #$id telah ditolak.";
            $type = "info";
        }
        if ($aksi == 'hapus') {
            $conn->begin_transaction();
            try {
                if (!empty($order['bukti'])) {
                    $file = "uploads/bukti_bayar/" . $order['bukti'];
                    if (file_exists($file)) unlink($file);
                }
                $conn->query("DELETE FROM order_items WHERE order_id=$id");
                $conn->query("DELETE FROM orders WHERE id=$id");
                $conn->commit();
                $msg = "Data pesanan berhasil dihapus secara permanen.";
            } catch (Exception $e) { 
                $conn->rollback(); 
                $msg = "Gagal menghapus data.";
                $type = "error";
            }
        }
        // AKSI BARU: Simpan Nomor Resi
        if ($aksi == 'input_resi' && isset($_POST['no_resi'])) {
            $no_resi = htmlspecialchars($_POST['no_resi']);
            $stmtResi = $conn->prepare("UPDATE orders SET no_resi=? WHERE id=?");
            $stmtResi->bind_param("si", $no_resi, $id);
            if($stmtResi->execute()){
                $msg = "Nomor resi untuk Pesanan #$id berhasil disimpan.";
            } else {
                $msg = "Gagal menyimpan nomor resi.";
                $type = "error";
            }
        }
        
        $_SESSION['flash_msg'] = $msg;
        $_SESSION['flash_type'] = $type;
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
        .dataTables_wrapper .dataTables_filter { display: none; } 
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important; color: white !important; border-radius: 12px; border: none; padding: 5px 12px;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen pb-20">

<nav class="bg-white border-b border-slate-200 mb-10 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">📦 Order<span class="text-blue-600">Master</span></h1>
        <a href="index.php" class="flex items-center gap-2 bg-slate-100 px-4 py-2 rounded-xl text-slate-600 hover:bg-blue-600 hover:text-white font-bold transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            Dashboard
        </a>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl">📊</div>
            <div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">Total Order</p>
                <h3 class="text-2xl font-black"><?= $conn->query("SELECT id FROM orders")->num_rows ?></h3>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-14 h-14 bg-yellow-50 text-yellow-600 rounded-2xl flex items-center justify-center text-xl">⏳</div>
            <div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">Perlu Verifikasi</p>
                <h3 class="text-2xl font-black"><?= $conn->query("SELECT id FROM orders WHERE status='pending'")->num_rows ?></h3>
            </div>
        </div>
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-14 h-14 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center text-xl">💰</div>
            <div>
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest">Lunas</p>
                <h3 class="text-2xl font-black"><?= $conn->query("SELECT id FROM orders WHERE status='lunas'")->num_rows ?></h3>
            </div>
        </div>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-slate-100">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <h2 class="text-xl font-bold text-slate-800">Daftar Transaksi</h2>
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <input type="text" id="customSearch" placeholder="Cari Nama/ID/Resi..." 
                        class="bg-slate-50 border border-slate-200 rounded-2xl px-5 py-2.5 pl-11 text-sm outline-none focus:ring-2 focus:ring-blue-500 w-64 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 absolute left-4 top-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>

                <select id="filterStatus" class="bg-white border border-slate-200 rounded-2xl px-4 py-2.5 text-sm font-bold text-slate-600 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="pending">PENDING</option>
                    <option value="lunas">LUNAS</option>
                    <option value="ditolak">DITOLAK</option>
                </select>
            </div>
        </div>

        <table id="tableOrders" class="w-full text-slate-700">
            <thead>
                <tr class="text-left text-slate-400 text-[10px] uppercase tracking-[0.2em]">
                    <th class="pb-4">Order ID</th>
                    <th class="pb-4">Pelanggan & Alamat</th>
                    <th class="pb-4">Total</th>
                    <th class="pb-4 text-center">Bukti</th>
                    <th class="pb-4">Status</th>
                    <th class="pb-4">Waktu</th>
                    <th class="pb-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
            <?php
            $orders = $conn->query("SELECT * FROM orders ORDER BY id DESC");
            while ($o = $orders->fetch_assoc()):
                $wa = preg_replace('/[^0-9]/', '', $o['no_wa']);
                if (substr($wa,0,1) == '0') $wa = '62' . substr($wa,1);
                
                $status = $o['status'] ?? 'pending';
                $badgeClass = match($status) {
                    'lunas' => 'bg-green-100 text-green-600',
                    'ditolak' => 'bg-red-100 text-red-600',
                    default => 'bg-yellow-100 text-yellow-600'
                };
            ?>
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="py-5 font-black text-blue-600 align-top">#<?= $o['id'] ?></td>
                    <td class="py-5 align-top">
                        <div class="font-bold text-slate-800"><?= htmlspecialchars($o['nama']) ?></div>
                        <div class="flex items-center gap-1 mt-1 mb-3">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            <a href="https://wa.me/<?= $wa ?>" target="_blank" class="text-xs text-slate-400 hover:text-green-500 font-medium"><?= $o['no_wa'] ?></a>
                        </div>
                        
                        <div class="mt-2 text-[11px] text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100 max-w-xs">
                            <span class="font-bold text-slate-400 uppercase tracking-widest text-[9px]">📍 Alamat Pengiriman:</span><br>
                            <?= nl2br(htmlspecialchars($o['alamat'] ?? '-')) ?>
                        </div>
                        
                    </td>
                    <td class="py-5 align-top">
                        <div class="text-xs text-slate-400 font-bold"><?= strtoupper($o['metode']) ?></div>
                        <div class="font-black text-slate-900">Rp <?= number_format($o['total'],0,',','.') ?></div>
                    </td>
                    <td class="py-5 text-center align-top">
                        <?php if($o['bukti']): ?>
                            <a href="uploads/bukti_bayar/<?= $o['bukti'] ?>" target="_blank" class="inline-flex p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </a>
                        <?php else: ?>
                            <span class="text-slate-300 italic text-xs">Belum upload</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-5 align-top">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider <?= $badgeClass ?>">
                            <?= $status ?>
                        </span>
                        
                        <?php if ($status == 'lunas' && !empty($o['no_resi'])): ?>
                            <div class="mt-2 text-[10px] font-bold text-slate-500 border border-slate-200 inline-block px-2 py-1 rounded-lg">
                                Resi: <span class="text-blue-600"><?= htmlspecialchars($o['no_resi']) ?></span>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="py-5 text-slate-400 text-[11px] leading-tight align-top">
                        <?= date('d M Y', strtotime($o['created_at'])) ?><br>
                        <span class="font-bold text-slate-300"><?= date('H:i', strtotime($o['created_at'])) ?> WIB</span>
                    </td>
                    <td class="py-5 align-top">
                        <div class="flex items-center justify-center gap-2">
                            
                            <a href="invoice.php?id=<?= $o['id'] ?>" target="_blank" title="Cetak Invoice" class="p-2.5 bg-purple-50 text-purple-600 rounded-xl hover:bg-purple-600 hover:text-white transition font-bold">
                                🖨️
                            </a>

                            <button class="detailBtn p-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-blue-600 hover:text-white transition" data-id="<?= $o['id'] ?>" title="Detail Pesanan">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                            </button>

                            <?php if ($status == 'pending'): ?>
                                <button onclick="confirmAction('verifikasi', <?= $o['id'] ?>, 'Terima pembayaran ini?')" class="p-2.5 bg-green-50 text-green-600 rounded-xl hover:bg-green-600 hover:text-white transition font-bold" title="Verifikasi Pesanan">✔</button>
                                <button onclick="confirmAction('tolak', <?= $o['id'] ?>, 'Tolak pesanan ini?')" class="p-2.5 bg-orange-50 text-orange-600 rounded-xl hover:bg-orange-600 hover:text-white transition font-bold" title="Tolak Pesanan">✖</button>
                            <?php endif; ?>

                            <?php if ($status == 'lunas'): ?>
                                <button onclick="inputResi(<?= $o['id'] ?>, '<?= htmlspecialchars($o['no_resi'] ?? '') ?>')" class="p-2.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition font-bold" title="Input/Edit Nomor Resi">
                                    🚚
                                </button>
                            <?php endif; ?>

                            <button onclick="confirmAction('hapus', <?= $o['id'] ?>, 'Hapus permanen data ini?')" class="p-2.5 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition" title="Hapus Permanen">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    let table = $('#tableOrders').DataTable({
        responsive: true,
        order: [[0, 'desc']],
        dom: 'rtp', 
        language: { 
            paginate: { previous: "Prev", next: "Next" }
        }
    });

    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    $('#filterStatus').on('change', function() {
        table.column(4).search(this.value).draw();
    });

    $('#tableOrders tbody').on('click', '.detailBtn', function () {
        let tr = $(this).closest('tr');
        let row = table.row(tr);
        let id = $(this).data('id');
        let btn = $(this);

        if (row.child.isShown()) {
            row.child.hide();
            btn.removeClass('bg-blue-600 text-white').addClass('bg-slate-100 text-slate-600');
        } else {
            btn.html('<svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>');
            
            fetch('detail_order.php?id=' + id)
                .then(res => res.text())
                .then(html => {
                    row.child(`<div class="p-6 bg-blue-50/50 rounded-3xl border border-blue-100 m-2 shadow-inner">${html}</div>`).show();
                    btn.html('<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>');
                    btn.addClass('bg-blue-600 text-white').removeClass('bg-slate-100 text-slate-600');
                });
        }
    });

    <?php if(isset($_SESSION['flash_msg'])): ?>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: '<?= $_SESSION['flash_type'] ?>',
        title: '<?= $_SESSION['flash_msg'] ?>',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); endif; ?>
});

function confirmAction(aksi, id, text) {
    Swal.fire({
        title: 'Konfirmasi',
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Proses',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-[2rem]' }
    }).then((result) => {
        if (result.isConfirmed) {
            submitForm(aksi, id);
        }
    })
}

function inputResi(id, currentResi) {
    Swal.fire({
        title: 'Input Nomor Resi',
        input: 'text',
        inputValue: currentResi,
        inputPlaceholder: 'Contoh: JNT1234567890',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Simpan',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-[2rem]' },
        inputValidator: (value) => {
            if (!value) {
                return 'Nomor resi tidak boleh kosong!'
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitForm('input_resi', id, result.value);
        }
    })
}

function submitForm(aksi, id, resiValue = null) {
    let form = document.createElement('form');
    form.method = 'POST';
    form.action = 'orders.php';

    let inputAksi = document.createElement('input');
    inputAksi.type = 'hidden';
    inputAksi.name = 'aksi';
    inputAksi.value = aksi;
    form.appendChild(inputAksi);

    let inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'id';
    inputId.value = id;
    form.appendChild(inputId);

    if (resiValue !== null) {
        let inputResi = document.createElement('input');
        inputResi.type = 'hidden';
        inputResi.name = 'no_resi';
        inputResi.value = resiValue;
        form.appendChild(inputResi);
    }

    document.body.appendChild(form);
    form.submit();
}
</script>

</body>
</html>