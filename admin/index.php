<?php
session_start();
include 'config.php';

/*
|--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin'])) {
    header("Location: auth.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SmartShop</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .dataTable { border-collapse: collapse !important; border-radius: 1rem; overflow: hidden; }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #2563eb !important;
            color: white !important;
            border-radius: 10px;
            border: none;
        }
        table.dataTable thead th {
            border-bottom: 1px solid #f1f5f9 !important;
            background: #f8fafc;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen pb-20">

<nav class="bg-white border-b border-slate-200 mb-8">
    <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">

        <h1 class="text-2xl font-black text-slate-800 tracking-tight">
            📱 Inventory<span class="text-blue-600">Admin</span>
        </h1>

        <div class="flex items-center gap-3">

            <span class="text-sm text-slate-500 font-semibold mr-2">
                Halo, <?= htmlspecialchars($_SESSION['admin']['username']) ?>
            </span>

            <!-- TOMBOL PELANGGAN -->
            <a href="customers.php" title="Manajemen Pelanggan"
               class="relative group p-3 bg-slate-100 rounded-2xl hover:bg-blue-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="w-6 h-6 text-slate-600 group-hover:text-blue-600" 
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </a>

            <!-- TOMBOL PESANAN -->
            <a href="orders.php" title="Manajemen Pesanan"
               class="relative group p-3 bg-slate-100 rounded-2xl hover:bg-orange-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-6 h-6 text-slate-600 group-hover:text-orange-500"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2"
                          d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span id="notifBadge"
                      class="hidden absolute top-0 right-0 transform translate-x-1/3 -translate-y-1/3 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-full border-2 border-white">
                    0
                </span>
            </a>

            <!-- TOMBOL PEMASUKAN (BARU) -->
            <a href="pemasukan.php" title="Laporan Pemasukan"
               class="relative group p-3 bg-slate-100 rounded-2xl hover:bg-emerald-50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="w-6 h-6 text-slate-600 group-hover:text-emerald-600" 
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </a>

            <!-- TOMBOL TAMBAH HP -->
            <a href="create.php"
               class="bg-blue-600 text-white px-5 py-2.5 rounded-2xl font-bold hover:bg-blue-700 transition">
                + Tambah HP
            </a>

            <!-- TOMBOL LOGOUT -->
            <a href="logout.php"
               onclick="return confirm('Yakin ingin logout?')"
               class="bg-red-500 text-white px-5 py-2.5 rounded-2xl font-bold hover:bg-red-600 transition">
                Logout
            </a>

        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-6">

<?php
$count = $conn->query("SELECT COUNT(*) total FROM phones")->fetch_assoc();
$stok  = $conn->query("SELECT SUM(stok) total FROM phones")->fetch_assoc();
?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

    <div class="bg-white p-6 rounded-3xl shadow">
        <p class="text-slate-400 text-sm font-semibold">Model Terdaftar</p>
        <h3 class="text-2xl font-black"><?= $count['total'] ?> HP</h3>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow">
        <p class="text-slate-400 text-sm font-semibold">Total Stok</p>
        <h3 class="text-2xl font-black"><?= number_format($stok['total']) ?> Unit</h3>
    </div>

</div>

<div class="bg-white p-8 rounded-3xl shadow">

<table id="tableHP" class="w-full">
<thead>
<tr>
    <th>Produk</th>
    <th>Brand</th>
    <th>Harga</th>
    <th>Stok</th>
    <th>Kategori</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>
<?php
$result = $conn->query("SELECT * FROM phones ORDER BY id DESC");

while ($row = $result->fetch_assoc()) {

    $imgRes = $conn->query("SELECT image FROM phone_images WHERE phone_id=".$row['id']." LIMIT 1");
    $img = $imgRes->fetch_assoc();
?>
<tr>
    <td class="py-3">
        <div class="flex items-center gap-3">
            <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100">
                <?php if($img): ?>
                    <img src="uploads/<?= $img['image'] ?>" class="w-full h-full object-cover">
                <?php endif; ?>
            </div>
            <b><?= htmlspecialchars($row['nama_hp']) ?></b>
        </div>
    </td>

    <td><?= htmlspecialchars($row['brand']) ?></td>

    <td>Rp <?= number_format($row['harga'],0,',','.') ?></td>

    <td>
        <?php if($row['stok'] <= 5): ?>
            <span class="text-red-500 font-bold"><?= $row['stok'] ?></span>
        <?php else: ?>
            <?= $row['stok'] ?>
        <?php endif; ?>
    </td>

    <td><?= $row['kategori'] ?></td>

    <td>
        <a href="edit.php?id=<?= $row['id'] ?>" class="text-blue-500 hover:underline">Edit</a> |
        <a href="delete.php?id=<?= $row['id'] ?>"
           onclick="return confirm('Hapus data ini?')"
           class="text-red-500 hover:underline">Hapus</a>
    </td>
</tr>
<?php } ?>
</tbody>
</table>

</div>
</div>

<audio id="notifSound" preload="auto">
    <source src="https://www.soundjay.com/buttons/sounds/button-3.mp3" type="audio/mpeg">
</audio>

<script>
$(document).ready(function() {
    $('#tableHP').DataTable({
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari Produk..."
        }
    });
});

/* NOTIF ORDER */
let lastTotal = 0;
let firstLoad = true;

function loadNotif() {
    fetch('get_orders.php')
        .then(res => res.json())
        .then(data => {

            let badge = document.getElementById('notifBadge');

            if (data.total > 0) {
                badge.innerText = data.total;
                badge.classList.remove('hidden');

                if (!firstLoad && data.total > lastTotal) {
                    document.getElementById('notifSound').play();

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Ada Pesanan Baru!',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }

            } else {
                badge.classList.add('hidden');
            }

            lastTotal = data.total;
            firstLoad = false;
        });
}

loadNotif();
setInterval(loadNotif, 5000);
</script>

</body>
</html>