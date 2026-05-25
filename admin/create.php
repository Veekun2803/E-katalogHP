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

if (isset($_POST['submit'])) {
    $conn->begin_transaction();

    try {
        $nama_hp = $_POST['nama_hp'];
        $brand = $_POST['brand'];
        
        // --- PERBAIKAN DI SINI ---
        // Menghapus titik/koma dari input harga sebelum diubah ke integer
        $harga_raw = str_replace(['.', ','], '', $_POST['harga']);
        $harga = intval($harga_raw);
        // -------------------------

        $stok = intval($_POST['stok']);
        $deskripsi = $_POST['deskripsi'];
        $spesifikasi = $_POST['spesifikasi'];
        $kategori = $_POST['kategori'];

        if ($harga < 0 || $stok < 0) {
            throw new Exception("Harga atau Stok tidak boleh negatif!");
        }

        $stmt = $conn->prepare("INSERT INTO phones (nama_hp, brand, harga, stok, deskripsi, spesifikasi, kategori) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiisss", $nama_hp, $brand, $harga, $stok, $deskripsi, $spesifikasi, $kategori);
        $stmt->execute();
        $phone_id = $conn->insert_id;

        $uploadDir = __DIR__ . "/uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $nama_file = $_FILES['images']['name'][$key];
                $ext = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed) && $_FILES['images']['size'][$key] <= 2000000) {
                    $file_name = uniqid() . '.' . $ext;
                    if (move_uploaded_file($tmp_name, $uploadDir . $file_name)) {
                        $stmtImg = $conn->prepare("INSERT INTO phone_images (phone_id, image) VALUES (?, ?)");
                        $stmtImg->bind_param("is", $phone_id, $file_name);
                        $stmtImg->execute();
                    }
                }
            }
        }

        $conn->commit();
        $_SESSION['msg'] = "Data berhasil disimpan!";
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Tambah HP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 p-4 md:p-10 font-sans">

<div class="max-w-3xl mx-auto bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-3xl font-black text-slate-800">Tambah Data HP</h2>
        <a href="index.php" class="text-slate-400 hover:text-slate-600 transition">✕</a>
    </div>

    <?php if(isset($error)): ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Smartphone</label>
            <input type="text" name="nama_hp" required placeholder="Contoh: iPhone 15 Pro"
                class="w-full border-gray-200 border rounded-2xl p-3 focus:ring-2 focus:ring-blue-500 outline-none transition">
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Brand</label>
            <select name="brand" class="w-full border-gray-200 border rounded-2xl p-3 outline-none">
                <option>Apple</option>
                <option>Samsung</option>
                <option>Xiaomi</option>
                <option>Oppo</option>
                <option>Vivo</option>
                <option>Realme</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Kategori</label>
            <select name="kategori" class="w-full border-gray-200 border rounded-2xl p-3 outline-none">
                <option value="Android">Android</option>
                <option value="iPhone">iPhone</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Harga (Rp)</label>
            <input type="text" name="harga" id="inputHarga" required placeholder="0"
                class="w-full border-gray-200 border rounded-2xl p-3 outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 mb-2">Stok Unit</label>
            <input type="number" name="stok" required min="0" placeholder="0"
                class="w-full border-gray-200 border rounded-2xl p-3 outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-slate-700 mb-2">Deskripsi Singkat</label>
            <textarea name="deskripsi" rows="3" class="w-full border-gray-200 border rounded-2xl p-3 outline-none"></textarea>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-slate-700 mb-2">Spesifikasi Lengkap</label>
            <textarea name="spesifikasi" rows="5" placeholder="Gunakan baris baru untuk setiap spek"
                class="w-full border-gray-200 border rounded-2xl p-3 outline-none"></textarea>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-bold text-slate-700 mb-2">Upload Gambar Produk</label>
            <div class="border-2 border-dashed border-gray-200 rounded-3xl p-8 text-center hover:border-blue-400 transition cursor-pointer relative">
                <input type="file" name="images[]" id="imgInput" multiple accept="image/*"
                    class="absolute inset-0 opacity-0 cursor-pointer">
                <p class="text-slate-500">Klik atau tarik gambar ke sini</p>
                <p class="text-xs text-slate-400 mt-1">Maksimal 2MB per foto (JPG, PNG, WEBP)</p>
            </div>
            <div id="previewArea" class="flex flex-wrap gap-4 mt-4"></div>
        </div>

        <div class="md:col-span-2 flex gap-3 pt-4">
            <button type="submit" name="submit"
                class="flex-1 bg-blue-600 text-white font-bold py-4 rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-100">
                Simpan Produk
            </button>
            <a href="index.php"
                class="px-8 py-4 bg-slate-100 text-slate-600 font-bold rounded-2xl hover:bg-slate-200 transition">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
// FORMAT HARGA REAL-TIME
const inputHarga = document.getElementById('inputHarga');
inputHarga.addEventListener('keyup', function(e) {
    // Tambahkan event listener untuk memformat angka dengan titik
    this.value = formatRupiah(this.value);
});

function formatRupiah(angka) {
    let number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa  = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return rupiah;
}

// PREVIEW GAMBAR
document.getElementById('imgInput').addEventListener('change', function(e) {
    const previewArea = document.getElementById('previewArea');
    previewArea.innerHTML = ''; 
    [...e.target.files].forEach(file => {
        const reader = new FileReader();
        reader.onload = function(event) {
            const div = document.createElement('div');
            div.className = 'w-20 h-20 rounded-xl overflow-hidden border border-gray-100 shadow-sm';
            div.innerHTML = `<img src="${event.target.result}" class="w-full h-full object-cover">`;
            previewArea.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
});
</script>

</body>
</html>