<?php 
include __DIR__ . '/admin/config.php'; 

if (!isset($_GET['id'])) {
    die("Produk tidak ditemukan");
}

$id = intval($_GET['id']);

// Ambil data produk
$stmt = $conn->prepare("SELECT * FROM phones WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Data tidak ditemukan");
}

// Ambil semua gambar
$stmtImg = $conn->prepare("SELECT * FROM phone_images WHERE phone_id=?");
$stmtImg->bind_param("i", $id);
$stmtImg->execute();
$images = $stmtImg->get_result();

$gambarList = [];
while ($img = $images->fetch_assoc()) {
    $path = "admin/uploads/" . $img['image'];
    if (file_exists(__DIR__ . "/" . $path)) {
        $gambarList[] = $path;
    }
}

// Fallback jika tidak ada gambar
if (empty($gambarList)) {
    $gambarList[] = "https://via.placeholder.com/600x600?text=No+Image";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['nama_hp']) ?> - PhoneStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .thumbnail-active { border-color: #2563eb !important; ring: 2px; --tw-ring-color: #2563eb; }
    </style>
</head>

<body class="bg-slate-50 min-h-screen pb-12">

<div class="max-w-6xl mx-auto p-4 md:p-8">
    
    <nav class="flex mb-6 text-sm font-medium text-gray-500">
        <a href="index.php" class="hover:text-blue-600 transition flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Beranda
        </a>
        <span class="mx-2 text-gray-300">/</span>
        <span class="text-gray-800 line-clamp-1"><?= htmlspecialchars($data['nama_hp']) ?></span>
    </nav>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="grid md:grid-cols-2 gap-0">

            <div class="p-6 md:p-10 bg-gray-50/50 border-r border-gray-100">
                <div class="sticky top-10">
                    <div class="w-full aspect-square bg-white rounded-[2rem] shadow-inner mb-6 flex items-center justify-center overflow-hidden border border-gray-100 p-8">
                        <img id="mainImage" 
                             src="<?= $gambarList[0] ?>" 
                             class="max-h-full max-w-full object-contain transition-all duration-500 transform hover:scale-110">
                    </div>

                    <div class="flex gap-3 justify-center flex-wrap">
                        <?php foreach ($gambarList as $index => $g): ?>
                            <div onclick="changeImage('<?= $g ?>', this)" 
                                 class="thumbnail-item w-20 h-20 bg-white rounded-2xl overflow-hidden border-2 border-transparent shadow-sm cursor-pointer flex items-center justify-center transition-all hover:border-blue-300 <?= $index === 0 ? 'thumbnail-active' : '' ?>">
                                <img src="<?= $g ?>" class="max-w-[80%] max-h-[80%] object-contain">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="p-6 md:p-12">
                <div class="mb-2">
                    <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-widest">
                        <?= htmlspecialchars($data['brand']) ?>
                    </span>
                </div>

                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 leading-tight">
                    <?= htmlspecialchars($data['nama_hp']) ?>
                </h1>

                <div class="flex items-center gap-4 mb-8">
                    <div class="text-3xl font-black text-blue-600">
                        Rp <?= number_format($data['harga'],0,',','.') ?>
                    </div>
                    <?php if($data['stok'] > 0): ?>
                        <div class="flex items-center gap-1.5 text-green-600 bg-green-50 px-3 py-1 rounded-lg text-sm font-bold">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            Ready Stock
                        </div>
                    <?php else: ?>
                        <div class="text-red-500 bg-red-50 px-3 py-1 rounded-lg text-sm font-bold">Habis</div>
                    <?php endif; ?>
                </div>

                <form action="cart.php" method="POST" class="flex flex-col sm:flex-row gap-4 mb-10 pb-10 border-b border-gray-100">
                    <input type="hidden" name="phone_id" value="<?= $data['id'] ?>">
                    
                    <div class="flex items-center bg-gray-100 rounded-2xl p-1 shadow-inner">
                        <button type="button" onclick="adjustQty(-1)" class="w-12 h-12 flex items-center justify-center font-bold text-gray-600 hover:text-blue-600 transition">-</button>
                        <input type="number" id="qtyInput" name="qty" value="1" min="1" max="<?= $data['stok'] ?>" readonly
                               class="w-14 bg-transparent text-center font-extrabold text-gray-900 border-none focus:ring-0">
                        <button type="button" onclick="adjustQty(1)" class="w-12 h-12 flex items-center justify-center font-bold text-gray-600 hover:text-blue-600 transition">+</button>
                    </div>

                    <button type="submit"
                        <?= $data['stok'] == 0 ? 'disabled' : '' ?>
                        class="flex-1 bg-blue-600 text-white font-bold py-4 px-8 rounded-2xl hover:bg-blue-700 transition shadow-lg shadow-blue-100 active:scale-95 disabled:bg-gray-300 disabled:shadow-none flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        Tambahkan ke Keranjang
                    </button>
                </form>

                <div class="space-y-6 text-gray-700">
                    <div class="bg-slate-50 p-6 rounded-3xl">
                        <h3 class="font-black text-gray-900 mb-3 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Deskripsi Produk
                        </h3>
                        <p class="text-sm leading-relaxed text-gray-600">
                            <?= nl2br(htmlspecialchars($data['deskripsi'])) ?>
                        </p>
                    </div>

                    <div class="p-6 border border-gray-100 rounded-3xl">
                        <h3 class="font-black text-gray-900 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                            Spesifikasi Teknis
                        </h3>
                        <div class="text-sm leading-relaxed text-gray-600 grid grid-cols-1 gap-2">
                             <?php 
                             // Contoh merapikan tampilan spesifikasi jika menggunakan format baris baru
                             $specs = explode("\n", $data['spesifikasi']);
                             foreach($specs as $spec) {
                                if(trim($spec) != "") {
                                    echo "<div class='flex border-b border-gray-50 py-2'><span class='w-1/3 font-bold text-gray-400 uppercase text-[10px]'>• Info</span> <span class='w-2/3'>".htmlspecialchars($spec)."</span></div>";
                                }
                             }
                             ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function changeImage(src, el) {
    // Ganti gambar utama
    const mainImg = document.getElementById('mainImage');
    mainImg.style.opacity = '0';
    setTimeout(() => {
        mainImg.src = src;
        mainImg.style.opacity = '1';
    }, 200);

    // Update styling thumbnail
    document.querySelectorAll('.thumbnail-item').forEach(item => {
        item.classList.remove('thumbnail-active');
    });
    el.classList.add('thumbnail-active');
}

function adjustQty(amount) {
    const input = document.getElementById('qtyInput');
    let current = parseInt(input.value);
    let next = current + amount;
    if (next >= 1 && next <= <?= $data['stok'] ?>) {
        input.value = next;
    }
}
</script>

</body>
</html>