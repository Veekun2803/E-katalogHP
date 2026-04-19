<?php
// ... (Bagian PHP AJAX HANDLER & renderProductCard tetap sama seperti sebelumnya)
session_start();
include __DIR__ . '/admin/config.php';

$no_admin = "6285862030566"; 

function renderProductCard($row, $conn, $no_admin) {
    $imgQuery = $conn->query("SELECT image FROM phone_images WHERE phone_id=".$row['id']." LIMIT 1")->fetch_assoc();
    $gambar = ($imgQuery && file_exists(__DIR__."/admin/uploads/".$imgQuery['image'])) 
              ? "admin/uploads/".$imgQuery['image'] 
              : "https://via.placeholder.com/400x300?text=No+Image";
    
    $pesanWA = urlencode("Halo admin, saya tertarik dengan produk:\n\n📱 *".$row['nama_hp']."*\n💰 Harga: *Rp ".number_format($row['harga'],0,',','.')."*");
    ?>
    <div class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden flex flex-col">
        <div class="relative overflow-hidden aspect-video">
            <img src="<?= $gambar ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
            <div class="absolute top-2 right-2 bg-white/80 backdrop-blur px-2 py-1 rounded-lg text-xs font-bold text-gray-600 shadow-sm">
                <?= htmlspecialchars($row['brand']) ?>
            </div>
        </div>
        
        <div class="p-5 flex flex-col flex-grow">
            <h3 class="font-bold text-lg text-gray-800 line-clamp-1 mb-1"><?= htmlspecialchars($row['nama_hp']) ?></h3>
            <p class="text-blue-600 font-extrabold text-xl mb-3">Rp <?= number_format($row['harga'],0,',','.') ?></p>
            
            <div class="flex items-center gap-2 mb-4 text-sm text-gray-500">
                <span class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full <?= $row['stok'] > 0 ? 'bg-green-500' : 'bg-red-500' ?>"></span>
                    Stok: <?= $row['stok'] ?>
                </span>
            </div>

            <div class="mt-auto space-y-2">
                <div class="flex gap-2">
                    <a href="detail.php?id=<?= $row['id'] ?>" class="flex-1 text-center py-2.5 rounded-xl border border-gray-200 text-gray-600 font-medium hover:bg-gray-50 transition">Detail</a>
                    <button onclick="addToCart(<?= $row['id'] ?>, event)"
                        <?= $row['stok']==0 ? 'disabled' : '' ?>
                        class="flex-1 py-2.5 rounded-xl font-bold text-white transition shadow-md shadow-blue-100 
                        <?= $row['stok']==0 ? 'bg-gray-300 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 active:scale-95' ?>">
                        + Keranjang
                    </button>
                </div>
                <a href="https://wa.me/<?= $no_admin ?>?text=<?= $pesanWA ?>" target="_blank"
                   class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-green-500 hover:bg-green-600 text-white font-bold transition shadow-md shadow-green-100">
                    💬 Tanya Admin
                </a>
            </div>
        </div>
    </div>
    <?php
}

if (isset($_POST['ajax'])) {
    if ($_POST['ajax'] == 'add_cart') {
        $id  = intval($_POST['phone_id']);
        $qty = intval($_POST['qty']);
        $stok = $conn->query("SELECT stok FROM phones WHERE id=$id")->fetch_assoc()['stok'];
        $currentQty = $_SESSION['cart'][$id] ?? 0;
        $_SESSION['cart'][$id] = min($currentQty + ($qty < 1 ? 1 : $qty), $stok);
        echo json_encode(['total' => array_sum($_SESSION['cart'] ?? [])]);
        exit;
    }

    if ($_POST['ajax'] == 'search') {
        $search = $conn->real_escape_string($_POST['search']);
        $min_price = !empty($_POST['min_price']) ? intval(str_replace('.', '', $_POST['min_price'])) : 0;
        $max_price = !empty($_POST['max_price']) ? intval(str_replace('.', '', $_POST['max_price'])) : 999999999;

        $sql = "SELECT * FROM phones WHERE 
                (nama_hp LIKE '%$search%' OR brand LIKE '%$search%') 
                AND harga BETWEEN $min_price AND $max_price 
                ORDER BY id DESC";

        $result = $conn->query($sql);
        ob_start();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) renderProductCard($row, $conn, $no_admin);
        } else {
            echo '<div class="col-span-full text-center py-20 text-gray-400 font-medium">Produk tidak ditemukan dalam rentang harga ini...</div>';
        }
        echo ob_get_clean();
        exit;
    }
}

$total_cart = array_sum($_SESSION['cart'] ?? []);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PhoneStore - Gadget Terbaru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen pb-20">

<nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 h-16 flex justify-between items-center">
        <h1 class="text-2xl font-extrabold text-blue-600 tracking-tight">📱 Phone<span class="text-gray-800">Store</span></h1>
        <div class="flex items-center gap-4">
            <a href="cart.php" class="relative p-2 bg-gray-100 rounded-full hover:bg-gray-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span id="cartBadge" class="<?= $total_cart?'':'hidden' ?> absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white">
                    <?= $total_cart ?>
                </span>
            </a>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-[2rem] shadow-xl shadow-blue-100/50 border border-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-1 relative">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Cari Produk</label>
                <input type="text" id="searchInput" placeholder="iPhone, Samsung..." 
                       class="mt-1 w-full bg-gray-50 border-none rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-500 transition-all text-sm">
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Harga Min (Rp)</label>
                <input type="text" id="minPriceDisplay" placeholder="0" 
                       class="mt-1 w-full bg-gray-50 border-none rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-500 transition-all text-sm price-format">
            </div>
            <div>
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest ml-1">Harga Max (Rp)</label>
                <input type="text" id="maxPriceDisplay" placeholder="15.000.000" 
                       class="mt-1 w-full bg-gray-50 border-none rounded-xl py-3 px-4 focus:ring-2 focus:ring-blue-500 transition-all text-sm price-format">
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4">
    <div id="productsContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php
        $result = $conn->query("SELECT * FROM phones ORDER BY id DESC");
        while ($row = $result->fetch_assoc()) renderProductCard($row, $conn, $no_admin);
        ?>
    </div>
</div>

<script>
const searchInput = document.getElementById('searchInput');
const minPriceDisp = document.getElementById('minPriceDisplay');
const maxPriceDisp = document.getElementById('maxPriceDisplay');
const container = document.getElementById('productsContainer');

// Fungsi untuk memformat angka menjadi format ribuan (titik)
function formatNumber(n) {
    return n.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Fungsi Filter
function filterProducts() {
    container.style.opacity = "0.5";
    
    let formData = new FormData();
    formData.append('ajax', 'search');
    formData.append('search', searchInput.value);
    // Hapus titik sebelum dikirim ke PHP
    formData.append('min_price', minPriceDisp.value.replace(/\./g, ''));
    formData.append('max_price', maxPriceDisp.value.replace(/\./g, ''));

    fetch('index.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(res => res.text())
    .then(html => {
        container.innerHTML = html;
        container.style.opacity = "1";
    });
}

// Event listener untuk input harga agar otomatis ada titiknya
document.querySelectorAll('.price-format').forEach(input => {
    input.addEventListener('input', function(e) {
        // Simpan posisi kursor
        let cursorPosition = this.selectionStart;
        let oldLength = this.value.length;
        
        // Format nilai
        this.value = formatNumber(this.value);
        
        // Sesuaikan posisi kursor setelah format
        let newLength = this.value.length;
        cursorPosition = cursorPosition + (newLength - oldLength);
        this.setSelectionRange(cursorPosition, cursorPosition);
        
        filterProducts();
    });
});

searchInput.addEventListener('input', filterProducts);

function addToCart(id, event){
    event.preventDefault();
    fetch('index.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'ajax=add_cart&phone_id='+id+'&qty=1'
    })
    .then(res=>res.json())
    .then(data=>{
        let badge = document.getElementById('cartBadge');
        badge.innerText = data.total;
        badge.classList.remove('hidden');
    });
}
</script>
</body>
</html>