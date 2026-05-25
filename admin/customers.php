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
| HANDLE HAPUS PELANGGAN (Opsional)
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] == 'hapus') {
    $id = intval($_POST['id']);
    
    // Perhatian: Menghapus pelanggan bisa error jika ada data pesanan yang berelasi (Foreign Key)
    // Sebaiknya pastikan tabel orders memiliki ON DELETE CASCADE pada customer_id
    // atau hapus orders nya terlebih dahulu jika ingin benar-benar menghapus.
    try {
        $stmt = $conn->prepare("DELETE FROM customers WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['flash_msg'] = "Data pelanggan berhasil dihapus.";
            $_SESSION['flash_type'] = "success";
        }
    } catch (Exception $e) {
        $_SESSION['flash_msg'] = "Gagal! Pelanggan ini memiliki riwayat pesanan aktif.";
        $_SESSION['flash_type'] = "error";
    }
    
    header("Location: customers.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pelanggan | Admin</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .dataTables_wrapper .dataTables_filter { display: none; } 
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important; color: white !important; border-radius: 12px; border: none; padding: 5px 12px;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen pb-20">

<nav class="bg-white border-b border-slate-200 mb-10 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
        <h1 class="text-2xl font-black text-slate-800 tracking-tight">👥 Customer<span class="text-blue-600">Master</span></h1>
        
        <div class="flex gap-3">
            <a href="orders.php" class="flex items-center gap-2 bg-slate-50 px-4 py-2 rounded-xl text-slate-500 hover:bg-slate-200 font-bold transition">
                📦 Data Pesanan
            </a>
            <a href="index.php" class="flex items-center gap-2 bg-blue-50 px-4 py-2 rounded-xl text-blue-600 hover:bg-blue-600 hover:text-white font-bold transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Dashboard Utama
            </a>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-6">
    
    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-blue-900/5 border border-slate-100">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl font-black text-slate-800">Daftar Pelanggan</h2>
                <p class="text-sm font-bold text-slate-400 mt-1">Kelola data dan riwayat belanja pelanggan Anda</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <input type="text" id="customSearch" placeholder="Cari Nama/WA..." 
                        class="bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 pl-11 text-sm outline-none focus:ring-2 focus:ring-blue-500 w-72 transition font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 absolute left-4 top-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <table id="tableCustomers" class="w-full text-slate-700">
            <thead>
                <tr class="text-left text-slate-400 text-[10px] uppercase tracking-[0.2em] border-b-2 border-slate-100">
                    <th class="pb-4 pl-2">ID</th>
                    <th class="pb-4">Profil Pelanggan</th>
                    <th class="pb-4">Alamat Terdaftar</th>
                    <th class="pb-4 text-center">Statistik Belanja</th>
                    <th class="pb-4 text-right pr-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
            <?php
            // Query untuk mengambil data pelanggan beserta total pesanan dan total uang yang dibelanjakan (status lunas)
            $sql = "SELECT 
                        c.*,
                        (SELECT COUNT(id) FROM orders WHERE customer_id = c.id) as total_order,
                        (SELECT SUM(total) FROM orders WHERE customer_id = c.id AND status = 'lunas') as total_spent
                    FROM customers c 
                    ORDER BY c.id DESC";
            $customers = $conn->query($sql);
            
            while ($c = $customers->fetch_assoc()):
                // Format nomor WA agar bisa diklik menuju WhatsApp
                $wa = preg_replace('/[^0-9]/', '', $c['no_wa'] ?? '');
                if (substr($wa,0,1) == '0') $wa = '62' . substr($wa,1);
                
                // Set default nama jika belum mengisi profil (baru daftar)
                $nama = !empty($c['nama_lengkap']) ? $c['nama_lengkap'] : (!empty($c['username']) ? $c['username'] : 'Customer Tanpa Nama');
            ?>
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="py-6 pl-2 font-black text-slate-300">#<?= $c['id'] ?></td>
                    
                    <td class="py-6">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white flex items-center justify-center font-black text-lg shadow-lg shadow-blue-200">
                                <?= strtoupper(substr($nama, 0, 1)) ?>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 text-base"><?= htmlspecialchars($nama) ?></div>
                                <?php if(!empty($wa)): ?>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                        <span class="text-xs text-slate-500 font-semibold"><?= htmlspecialchars($c['no_wa']) ?></span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[10px] text-red-400 font-bold italic">Belum ada nomor WA</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    
                    <td class="py-6 pr-6">
                        <div class="text-xs text-slate-500 leading-relaxed font-medium max-w-xs truncate" title="<?= htmlspecialchars($c['alamat'] ?? '') ?>">
                            <?= !empty($c['alamat']) ? htmlspecialchars($c['alamat']) : '<span class="italic text-slate-300">Alamat belum diatur</span>' ?>
                        </div>
                    </td>
                    
                    <td class="py-6 text-center">
                        <?php if($c['total_order'] > 0): ?>
                            <div class="bg-blue-50 border border-blue-100 rounded-xl py-2 px-3 inline-block">
                                <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-0.5"><?= $c['total_order'] ?>x Order</p>
                                <p class="text-sm font-black text-blue-700">Rp <?= number_format($c['total_spent'] ?? 0, 0, ',', '.') ?></p>
                            </div>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-slate-100 text-slate-400 rounded-lg text-[10px] font-black uppercase tracking-widest">Belum Order</span>
                        <?php endif; ?>
                    </td>
                    
                    <td class="py-6 text-right pr-4">
                        <div class="flex items-center justify-end gap-2">
                            
                            <?php if(!empty($wa)): ?>
                            <a href="https://wa.me/<?= $wa ?>" target="_blank" title="Chat WhatsApp" 
                               class="p-2.5 bg-green-50 text-green-600 rounded-xl hover:bg-green-500 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            </a>
                            <?php endif; ?>

                            <button onclick="hapusPelanggan(<?= $c['id'] ?>, '<?= addslashes($nama) ?>')" title="Hapus Pelanggan"
                                    class="p-2.5 bg-red-50 text-red-500 rounded-xl hover:bg-red-500 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
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
    let table = $('#tableCustomers').DataTable({
        responsive: true,
        order: [[0, 'desc']], // Urutkan dari pelanggan terbaru
        dom: 'rtp', // Hanya tabel dan pagination (tanpa fitur pencarian default)
        language: { 
            paginate: { previous: "Prev", next: "Next" },
            emptyTable: "Belum ada data pelanggan terdaftar."
        }
    });

    // Fitur Live Search
    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Menampilkan Flash Message jika ada
    <?php if(isset($_SESSION['flash_msg'])): ?>
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: '<?= $_SESSION['flash_type'] ?>',
        title: '<?= $_SESSION['flash_msg'] ?>',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true
    });
    <?php unset($_SESSION['flash_msg'], $_SESSION['flash_type']); endif; ?>
});

// Fungsi Hapus via Form Tersembunyi (Aman)
function hapusPelanggan(id, nama) {
    Swal.fire({
        title: 'Hapus Pelanggan?',
        html: `Anda yakin ingin menghapus <b>${nama}</b>?<br><span class="text-sm text-red-500">Perhatian: Jika pelanggan ini memiliki riwayat pesanan, proses hapus mungkin akan digagalkan oleh sistem database.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-[2rem]' }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = 'customers.php';

            let inputAksi = document.createElement('input');
            inputAksi.type = 'hidden';
            inputAksi.name = 'aksi';
            inputAksi.value = 'hapus';
            form.appendChild(inputAksi);

            let inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'id';
            inputId.value = id;
            form.appendChild(inputId);

            document.body.appendChild(form);
            form.submit();
        }
    })
}
</script>

</body>
</html>