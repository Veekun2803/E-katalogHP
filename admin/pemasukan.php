<?php
session_start();
include __DIR__ . '/config.php';

// PROTEKSI: Pastikan hanya admin yang bisa mengakses halaman ini
// if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

/* ===========================================================
   1. AMBIL DATA RINGKASAN KARTU (TOTAL & BULAN INI)
   =========================================================== */
$sql_total = "SELECT SUM(total) as grand_pemasukan FROM orders WHERE status = 'lunas'";
$res_total = $conn->query($sql_total)->fetch_assoc();
$grandPemasukan = $res_total['grand_pemasukan'] ?? 0;

$bln_ini = date('m');
$thn_ini = date('Y');
$sql_bulan = "SELECT SUM(total) as bulan_pemasukan FROM orders WHERE status = 'lunas' AND MONTH(created_at) = '$bln_ini' AND YEAR(created_at) = '$thn_ini'";
$res_bulan = $conn->query($sql_bulan)->fetch_assoc();
$bulanPemasukan = $res_bulan['bulan_pemasukan'] ?? 0;

/* ===========================================================
   2. LOGIKA FILTER GRAFIK (HARIAN / MINGGUAN / BULANAN / KUSTOM)
   =========================================================== */
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'monthly';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$chartLabels = [];
$chartData = [];
$chartTitle = "";
$kondisi_tabel = "status = 'lunas'"; // Default kondisi untuk tabel histori

if ($filter == 'custom' && !empty($start_date) && !empty($end_date)) {
    // FILTER RENTANG TANGGAL KHUSUS
    $sql_chart = "SELECT DATE(created_at) as tgl, SUM(total) as val 
                  FROM orders 
                  WHERE status = 'lunas' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                  GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC";
    $res = $conn->query($sql_chart);
    while($row = $res->fetch_assoc()) {
        $chartLabels[] = date('d M Y', strtotime($row['tgl']));
        $chartData[] = (int)$row['val'];
    }
    $chartTitle = "Periode: " . date('d M Y', strtotime($start_date)) . " - " . date('d M Y', strtotime($end_date));
    $kondisi_tabel = "status = 'lunas' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";

} elseif ($filter == 'daily') {
    // Laporan Harian (14 Hari Terakhir)
    $sql_chart = "SELECT DATE(created_at) as tgl, SUM(total) as val 
                  FROM orders WHERE status = 'lunas' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
                  GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC";
    $res = $conn->query($sql_chart);
    while($row = $res->fetch_assoc()) {
        $chartLabels[] = date('d M', strtotime($row['tgl']));
        $chartData[] = (int)$row['val'];
    }
    $chartTitle = "Laporan Harian (14 Hari Terakhir)";
    $kondisi_tabel = "status = 'lunas' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)";

} elseif ($filter == 'weekly') {
    // Laporan Mingguan (8 Minggu Terakhir)
    $sql_chart = "SELECT YEARWEEK(created_at, 1) as pekan, SUM(total) as val 
                  FROM orders WHERE status = 'lunas' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
                  GROUP BY YEARWEEK(created_at, 1) ORDER BY YEARWEEK(created_at, 1) ASC";
    $res = $conn->query($sql_chart);
    while($row = $res->fetch_assoc()) {
        $chartLabels[] = 'Pekan ' . substr($row['pekan'], 4);
        $chartData[] = (int)$row['val'];
    }
    $chartTitle = "Laporan Mingguan (8 Minggu Terakhir)";
    $kondisi_tabel = "status = 'lunas' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)";

} else {
    // Laporan Bulanan (Tahun Berjalan)
    $sql_chart = "SELECT MONTH(created_at) as bln, SUM(total) as val 
                  FROM orders WHERE status = 'lunas' AND YEAR(created_at) = YEAR(CURDATE())
                  GROUP BY MONTH(created_at) ORDER BY MONTH(created_at) ASC";
    $res = $conn->query($sql_chart);
    $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    while($row = $res->fetch_assoc()) {
        $chartLabels[] = $namaBulan[$row['bln'] - 1];
        $chartData[] = (int)$row['val'];
    }
    $chartTitle = "Laporan Bulanan Berjalan Tahun " . date('Y');
    $kondisi_tabel = "status = 'lunas' AND YEAR(created_at) = YEAR(CURDATE())";
}

/* ===========================================================
   3. TABEL HISTORI
   =========================================================== */
// Tabel histori sekarang juga menyesuaikan dengan filter yang dipilih
$sql_history = "SELECT id, nama, metode, total, created_at, no_resi FROM orders WHERE $kondisi_tabel ORDER BY created_at DESC";
$res_history = $conn->query($sql_history);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pemasukan - Dashboard Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen p-4 md:p-8 lg:p-12 text-slate-800">

<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-transparent">
        <div>
            <h1 class="text-4xl font-black text-slate-800 tracking-tighter">Laporan <span class="text-emerald-600">Pemasukan</span></h1>
            <p class="text-slate-400 mt-1 text-sm font-medium">Memantau analitik keuangan dari pesanan yang lunas.</p>
        </div>
        
        <!-- Grup Tombol Aksi -->
        <div class="flex items-center gap-3">
            <!-- Link Cetak PDF dinamis sesuai parameter URL -->
            <?php 
                $printUrl = "cetak_laporan.php?filter=$filter";
                if($filter == 'custom') {
                    $printUrl .= "&start_date=$start_date&end_date=$end_date";
                }
            ?>
            <a href="<?= $printUrl ?>" target="_blank" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-emerald-100 transition-all text-xs font-bold uppercase tracking-widest active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Unduh PDF
            </a>
            
            <a href="index.php" class="flex items-center gap-2 bg-white px-5 py-2.5 rounded-xl border border-slate-200 shadow-sm text-slate-600 hover:text-blue-600 hover:border-blue-200 transition-all text-xs font-bold uppercase tracking-widest">
                Dashboard
            </a>
        </div>
    </div>

    <!-- Ringkasan Angka (Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-100 relative overflow-hidden group">
            <div class="relative z-10">
                <span class="text-[10px] font-black text-emerald-500 bg-emerald-50 px-3 py-1 rounded-full uppercase tracking-widest">Total Keseluruhan</span>
                <p class="text-4xl font-black text-slate-900 tracking-tighter mt-4">Rp<?= number_format($grandPemasukan, 0, ',', '.') ?></p>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl transition-all group-hover:bg-emerald-500/10"></div>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-slate-100 relative overflow-hidden group">
            <div class="relative z-10">
                <span class="text-[10px] font-black text-blue-500 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-widest">Bulan Ini (<?= date('M Y') ?>)</span>
                <p class="text-4xl font-black text-slate-900 tracking-tighter mt-4">Rp<?= number_format($bulanPemasukan, 0, ',', '.') ?></p>
            </div>
            <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl transition-all group-hover:bg-blue-500/10"></div>
        </div>
    </div>

    <!-- BOX FILTER TANGGAL KHUSUS -->
    <div class="bg-white rounded-3xl p-6 mb-8 shadow-sm border border-slate-200">
        <form method="GET" class="flex flex-col md:flex-row items-end gap-4">
            <input type="hidden" name="filter" value="custom">
            
            <div class="w-full md:w-auto flex-1">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Mulai Tanggal</label>
                <input type="date" name="start_date" required value="<?= $start_date ?>" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
            </div>
            
            <div class="w-full md:w-auto flex-1">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Sampai Tanggal</label>
                <input type="date" name="end_date" required value="<?= $end_date ?>" class="w-full px-4 py-3 bg-slate-50 border-none rounded-xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
            </div>
            
            <div class="w-full md:w-auto">
                <button type="submit" class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-md shadow-blue-200 transition-all active:scale-95">Terapkan Filter</button>
            </div>
        </form>
    </div>

    <!-- GRAFIK PEMASUKAN -->
    <div class="bg-white rounded-[2.5rem] p-6 md:p-8 shadow-xl shadow-slate-200/40 border border-slate-100 mb-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4">
            <div>
                <h3 class="text-xl font-black text-slate-800 tracking-tight">Grafik Analitik Pemasukan</h3>
                <p class="text-xs font-bold text-slate-400 mt-1 uppercase tracking-widest"><?= $chartTitle ?></p>
            </div>
            
            <!-- Tombol Filter Cepat -->
            <div class="flex flex-wrap gap-2 bg-slate-50 p-1.5 rounded-2xl border border-slate-100">
                <a href="?filter=daily" class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all <?= $filter == 'daily' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-400 hover:text-slate-600' ?>">Harian</a>
                <a href="?filter=weekly" class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all <?= $filter == 'weekly' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-400 hover:text-slate-600' ?>">Mingguan</a>
                <a href="?filter=monthly" class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all <?= $filter == 'monthly' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-400 hover:text-slate-600' ?>">Bulanan</a>
            </div>
        </div>

        <div class="relative w-full h-[300px]">
            <?php if(empty($chartData)): ?>
                <div class="absolute inset-0 flex items-center justify-center bg-slate-50/50 rounded-2xl border-2 border-dashed border-slate-200">
                    <p class="text-slate-400 font-bold">Tidak ada data pemasukan pada periode ini.</p>
                </div>
            <?php else: ?>
                <canvas id="revenueChart"></canvas>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabel Histori Penjualan -->
    <div class="bg-white rounded-[2.5rem] p-6 md:p-8 shadow-xl shadow-slate-200/40 border border-slate-100">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-black text-slate-800 tracking-tight">Daftar Transaksi (<?= $res_history->num_rows ?>)</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="pb-4 pl-2">ID Inv</th>
                        <th class="pb-4">Pelanggan</th>
                        <th class="pb-4">Metode</th>
                        <th class="pb-4">Waktu Transaksi</th>
                        <th class="pb-4 text-right pr-2">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 text-sm font-bold text-slate-700">
                    <?php if ($res_history->num_rows > 0): ?>
                        <?php while ($row = $res_history->fetch_assoc()): ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-4 pl-2 text-blue-600 font-black">#INV<?= $row['id'] ?></td>
                            <td class="py-4 text-slate-800"><?= htmlspecialchars($row['nama']) ?></td>
                            <td class="py-4">
                                <span class="text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 px-2.5 py-1 rounded-lg">
                                    <?= strtoupper($row['metode']) ?>
                                </span>
                            </td>
                            <td class="py-4 text-slate-400 font-medium"><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                            <td class="py-4 text-right pr-2 font-black text-slate-900">Rp<?= number_format($row['total'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-8 text-slate-400 font-medium">Belum ada transaksi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if(!empty($chartData)): ?>
<script>
    const labels = <?= json_encode($chartLabels) ?>;
    const dataValues = <?= json_encode($chartData) ?>;

    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(37, 99, 235, 0.2)');
    gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: dataValues,
                borderColor: '#2563eb',
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 2,
                pointRadius: 4,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: 'bold' },
                    bodyFont: { family: 'Plus Jakarta Sans', size: 14, weight: 'bold' },
                    callbacks: {
                        label: function(context) {
                            let value = context.raw || 0;
                            return ' Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Plus Jakarta Sans', weight: '600' }, color: '#94a3b8' }
                },
                y: {
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { family: 'Plus Jakarta Sans', weight: '600' },
                        color: '#94a3b8',
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000) + ' Jt';
                            if (value >= 1000) return (value / 1000) + ' Rb';
                            return value;
                        }
                    },
                    beginAtZero: true
                }
            }
        }
    });
</script>
<?php endif; ?>

</body>
</html>