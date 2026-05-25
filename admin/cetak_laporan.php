<?php
session_start();
include __DIR__ . '/config.php';

// PROTEKSI: Pastikan hanya admin yang bisa mengakses halaman ini
// if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit; }

// Tangkap filter dari URL
$filter = $_GET['filter'] ?? 'monthly';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$periode = "";
$kondisi = "status = 'lunas'";

// Logika Kondisi Kueri SQL Berdasarkan Filter
if ($filter == 'custom' && !empty($start_date) && !empty($end_date)) {
    // Jika menggunakan filter Rentang Tanggal
    $kondisi .= " AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
    // Format tampilan periode di kertas (contoh: 01/05/2026 - 20/05/2026)
    $periode = date('d/m/Y', strtotime($start_date)) . " s.d " . date('d/m/Y', strtotime($end_date));
    $nama_file_pdf = "Laporan_Pemasukan_" . date('dmY', strtotime($start_date)) . "_" . date('dmY', strtotime($end_date)) . ".pdf";

} elseif ($filter == 'daily') {
    $kondisi .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)";
    $periode = "14 Hari Terakhir";
    $nama_file_pdf = "Laporan_Pemasukan_Harian.pdf";

} elseif ($filter == 'weekly') {
    $kondisi .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)";
    $periode = "8 Minggu Terakhir";
    $nama_file_pdf = "Laporan_Pemasukan_Mingguan.pdf";

} else {
    $kondisi .= " AND YEAR(created_at) = YEAR(CURDATE())";
    $periode = "Tahun Berjalan (" . date('Y') . ")";
    $nama_file_pdf = "Laporan_Pemasukan_Tahun_" . date('Y') . ".pdf";
}

// Ambil data transaksi sesuai filter yang sudah ditentukan di atas
$sql = "SELECT id, nama, metode, total, created_at FROM orders WHERE $kondisi ORDER BY created_at ASC";
$result = $conn->query($sql);
$total_semua = 0;

// Format Tanggal Indonesia untuk Tanda Tangan
$bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tanggal_sekarang = date('d') . ' ' . $bulanIndo[date('n') - 1] . ' ' . date('Y');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan Pemasukan</title>
    
    <!-- Library html2pdf.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        /* Desain Background Web */
        body { 
            font-family: 'Arial', sans-serif; 
            background-color: #e2e8f0; 
            margin: 0; 
            padding: 20px; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
        }
        
        /* Tombol Download */
        .action-bar { margin-bottom: 20px; text-align: center; }
        .btn-download { 
            background-color: #2563eb; 
            color: white; 
            border: none; 
            padding: 12px 24px; 
            font-size: 16px; 
            font-weight: bold; 
            border-radius: 8px; 
            cursor: pointer; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
            transition: 0.2s;
        }
        .btn-download:hover { background-color: #1d4ed8; }

        /* Desain Kertas Laporan A4 */
        #kertas-laporan {
            background-color: white;
            width: 210mm;
            min-height: 297mm; /* A4 Height */
            padding: 20mm;
            box-sizing: border-box;
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }

        /* Kop Surat */
        .kop-surat { 
            text-align: center; 
            border-bottom: 4px solid #000; 
            padding-bottom: 15px; 
            margin-bottom: 25px; 
        }
        .kop-surat h1 { 
            margin: 0; 
            font-size: 26px; 
            text-transform: uppercase; 
            font-family: 'Times New Roman', serif; 
        }
        .kop-surat p { margin: 5px 0 0 0; font-size: 14px; }

        /* Judul Laporan */
        .judul-laporan { text-align: center; margin-bottom: 30px; }
        .judul-laporan h2 { margin: 0; font-size: 18px; text-decoration: underline; }
        .judul-laporan p { margin: 5px 0 0 0; font-size: 14px; font-weight: bold;}

        /* Tabel Data */
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border: 1px solid #000; padding: 10px; font-size: 14px; }
        th { background-color: #f3f4f6; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Area Tanda Tangan */
        .tanda-tangan { 
            float: right; 
            width: 250px; 
            text-align: center; 
            font-size: 14px; 
            margin-top: 20px; 
        }
        .tanda-tangan p { margin: 0; }
        .tanda-tangan .nama-ttd { 
            margin-top: 80px; 
            font-weight: bold; 
            text-decoration: underline; 
        }
    </style>
</head>
<body>

    <!-- Area Tombol -->
    <div class="action-bar">
        <button onclick="unduhPDF()" class="btn-download" id="btn-dl">⬇️ Unduh File PDF</button>
        <p style="font-size: 12px; color: #475569; margin-top: 10px;">Klik tombol di atas untuk mendownload laporan ini sebagai PDF.</p>
    </div>

    <!-- Area Kertas Laporan (Elemen ini yang akan diconvert jadi PDF) -->
    <div id="kertas-laporan">
        <!-- KOP SURAT -->
        <div class="kop-surat">
            <h1>PHONESTORE OFFICIAL</h1>
            <p>Pusat Penjualan Smartphone Resmi & Terpercaya</p>
            <p>Jl. Dolog Raya, Semarang, Jawa Tengah | Telp: 085862030566</p>
        </div>

        <!-- JUDUL -->
        <div class="judul-laporan">
            <h2>LAPORAN PEMASUKAN PENJUALAN</h2>
            <p>Periode: <?= $periode ?></p>
        </div>

        <!-- TABEL TRANSAKSI -->
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Tanggal</th>
                    <th width="20%">ID Transaksi</th>
                    <th width="25%">Nama Pelanggan</th>
                    <th width="15%">Metode</th>
                    <th width="20%">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                        $total_semua += $row['total'];
                        echo "<tr>";
                        echo "<td class='text-center'>{$no}</td>";
                        echo "<td class='text-center'>" . date('d/m/Y', strtotime($row['created_at'])) . "</td>";
                        echo "<td class='text-center'>#INV" . $row['id'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                        echo "<td class='text-center'>" . strtoupper($row['metode']) . "</td>";
                        echo "<td class='text-right'>" . number_format($row['total'], 0, ',', '.') . "</td>";
                        echo "</tr>";
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>Tidak ada transaksi lunas pada periode tanggal yang dipilih.</td></tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-right">TOTAL PENDAPATAN BERSIH</th>
                    <th class="text-right">Rp <?= number_format($total_semua, 0, ',', '.') ?></th>
                </tr>
            </tfoot>
        </table>

        <!-- TANDA TANGAN -->
        <div class="tanda-tangan">
            <p>Semarang, <?= $tanggal_sekarang ?></p>
            <p>Mengetahui,</p>
            <div class="nama-ttd">FAISAL DWIKI NURDIANSYAH</div>
            <p>Administrator</p>
        </div>
    </div>

    <!-- SCRIPT CONVERT HTML TO PDF -->
    <script>
        function unduhPDF() {
            const element = document.getElementById('kertas-laporan');
            const tombol = document.getElementById('btn-dl');
            
            // Animasi tombol
            tombol.innerText = "⏳ Sedang memproses PDF...";
            tombol.style.backgroundColor = "#94a3b8";
            tombol.disabled = true;
            
            // Konfigurasi PDF
            const opt = {
                margin:       10, 
                filename:     '<?= $nama_file_pdf ?>', // Nama file otomatis sesuai filter
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 }, 
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // Proses generate
            html2pdf().set(opt).from(element).save().then(() => {
                tombol.innerText = "⬇️ Unduh File PDF";
                tombol.style.backgroundColor = "#2563eb";
                tombol.disabled = false;
            });
        }
    </script>
</body>
</html>