<?php
session_start();

// PROTEKSI: Cek apakah user sudah login
if (!isset($_SESSION['customer_id'])) {
    header("Location: auth.php");
    exit;
}

include __DIR__ . '/admin/config.php';

$no_admin = "6285862030566"; 
$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? 'Guest';

// HANDLE LOGOUT
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: auth.php");
    exit;
}

// HANDLE AJAX
if (isset($_POST['ajax'])) {
    if ($_POST['ajax'] == 'add_cart') {
        $id  = intval($_POST['phone_id']);
        $qty = intval($_POST['qty']);
        
        $resStok = $conn->query("SELECT stok FROM phones WHERE id=$id");
        $stok = ($resStok && $resStok->num_rows > 0) ? $resStok->fetch_assoc()['stok'] : 0;
        
        $check = $conn->query("SELECT id, qty FROM cart WHERE customer_id = $customer_id AND phone_id = $id");
        
        if ($check->num_rows > 0) {
            $current = $check->fetch_assoc();
            $newQty = min($current['qty'] + $qty, $stok);
            $conn->query("UPDATE cart SET qty = $newQty WHERE id = " . $current['id']);
        } else {
            $newQty = min($qty, $stok);
            $conn->query("INSERT INTO cart (customer_id, phone_id, qty) VALUES ($customer_id, $id, $newQty)");
        }

        $totalItems = $conn->query("SELECT COUNT(*) as total FROM cart WHERE customer_id = $customer_id")->fetch_assoc()['total'];
        
        echo json_encode(['total' => $totalItems]);
        exit;
    }

    if ($_POST['ajax'] == 'search') {
        $search = $conn->real_escape_string($_POST['search']);
        $min = !empty($_POST['min_price']) ? intval(str_replace('.', '', $_POST['min_price'])) : 0;
        $max = !empty($_POST['max_price']) ? intval(str_replace('.', '', $_POST['max_price'])) : 999999999;

        $sql = "SELECT * FROM phones WHERE (nama_hp LIKE '%$search%' OR brand LIKE '%$search%') 
                AND harga BETWEEN $min AND $max 
                AND stok > 0 
                ORDER BY id DESC";
        $result = $conn->query($sql);
        
        ob_start();
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) renderProductCard($row, $conn, $no_admin);
        } else {
            echo '<div class="col-span-full text-center py-20 text-gray-400 font-medium italic">Produk tidak ditemukan atau stok habis...</div>';
        }
        echo ob_get_clean();
        exit;
    }
}

function renderProductCard($row, $conn, $no_admin) {
    $imgQuery = $conn->query("SELECT image FROM phone_images WHERE phone_id=".$row['id']." LIMIT 1")->fetch_assoc();
    $gambar = ($imgQuery && file_exists(__DIR__."/admin/uploads/".$imgQuery['image'])) 
              ? "admin/uploads/".$imgQuery['image'] : "https://via.placeholder.com/400x300?text=No+Image";
    
    $pesanWA = urlencode("Halo admin, saya tertarik dengan: " . $row['nama_hp']);
    ?>
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden flex flex-col group hover:shadow-xl transition-all duration-500">
        <div class="relative overflow-hidden aspect-[4/3]">
            <img src="<?= $gambar ?>" class="w-full h-full object-cover group-hover:scale-110 transition-all duration-700">
            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full shadow-sm">
                <p class="text-[10px] font-black text-gray-800 uppercase">Sisa: <?= $row['stok'] ?></p>
            </div>
        </div>
        <div class="p-6 flex flex-col flex-grow">
            <span class="text-[10px] font-bold text-blue-500 uppercase tracking-widest mb-1"><?= htmlspecialchars($row['brand']) ?></span>
            <h3 class="font-bold text-gray-800 text-lg mb-1 line-clamp-1"><?= htmlspecialchars($row['nama_hp']) ?></h3>
            <p class="text-xl font-black text-gray-900 mb-5">Rp <?= number_format($row['harga'],0,',','.') ?></p>
            
            <div class="mt-auto space-y-2">
                <div class="flex gap-2">
                    <a href="detail.php?id=<?= $row['id'] ?>" class="flex-1 py-3 text-center text-xs font-bold border border-gray-100 rounded-2xl hover:bg-gray-50 transition">Detail</a>
                    <button onclick="addToCart(<?= $row['id'] ?>, event)" class="flex-1 py-3 text-xs font-bold bg-blue-600 text-white rounded-2xl hover:bg-blue-700 shadow-lg shadow-blue-100 transition active:scale-95">
                        + Keranjang
                    </button>
                </div>
                <a href="https://wa.me/<?= $no_admin ?>?text=<?= $pesanWA ?>" class="flex items-center justify-center gap-2 py-3 bg-green-500 text-white text-xs font-bold rounded-2xl hover:bg-green-600 transition shadow-lg shadow-green-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.316 1.592 5.43.001 9.85-4.417 9.853-9.849.002-5.432-4.416-9.851-9.847-9.852-5.431 0-9.85 4.417-9.853 9.849-.001 2.26.611 4.45 1.734 6.197l-1.019 3.725 3.816-.995zm11.016-14.711c-.332-.332-.71-.335-1.003-.335h-.408c-.293 0-.766.111-1.166.556-.4.444-1.533 1.498-1.533 3.663s1.577 4.256 1.799 4.552c.222.296 3.103 4.739 7.518 6.65 1.05.453 1.869.725 2.508.928 1.056.334 2.019.287 2.78.175.847-.125 2.61-.432 3.125-.85 1.085-.85.516-1.085 1.498-1.533.444-.4 1.111-1.085 1.111-2.074 0-.444-.4-.741-1.085-1.085z"/></svg>
                    Tanya Admin
                </a>
            </div>
        </div>
    </div>
    <?php
}

$cartCountRes = $conn->query("SELECT COUNT(*) as total FROM cart WHERE customer_id = $customer_id");
$total_cart = ($cartCountRes) ? $cartCountRes->fetch_assoc()['total'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PhoneStore - Katalog Gadget</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .price-format::-webkit-inner-spin-button, .price-format::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen">

<nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-20 flex justify-between items-center">
        <a href="index.php" class="text-2xl font-black text-blue-600 tracking-tighter">Phone<span class="text-slate-800">Store.</span></a>
        
        <div class="flex items-center gap-2 sm:gap-6">
            
            <a href="my_orders.php" class="hidden md:flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-blue-600 transition px-4 py-2 rounded-xl hover:bg-blue-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                Pesanan Saya
            </a>

            <a href="profile.php" class="hidden md:flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-blue-600 transition px-4 py-2 rounded-xl hover:bg-blue-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                Profil
            </a>

            <div class="h-8 w-[1px] bg-slate-200 hidden md:block"></div>

            <div class="flex items-center gap-3">
                <a href="profile.php" class="text-right hidden sm:block hover:opacity-75 transition-opacity cursor-pointer" title="Pengaturan Akun">
                    <p class="text-[10px] font-bold text-slate-400 uppercase leading-none">Welcome back,</p>
                    <p class="text-sm font-black text-blue-600 hover:underline"><?= htmlspecialchars(explode(' ', $customer_name)[0]) ?></p>
                </a>
                
                <a href="cart.php" class="relative p-3 bg-slate-100 rounded-2xl hover:bg-blue-50 transition-colors group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-slate-600 group-hover:text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span id="cartBadge" class="<?= $total_cart > 0 ? '' : 'hidden' ?> absolute -top-1 -right-1 bg-red-500 text-white text-[10px] w-5 h-5 flex items-center justify-center rounded-full border-2 border-white font-bold animate-bounce"><?= $total_cart ?></span>
                </a>

                <a href="?logout=true" class="p-3 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-2xl transition-all" title="Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</nav>

<header class="max-w-7xl mx-auto px-4 pt-10 pb-6">
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 flex flex-col md:flex-row gap-6 items-center">
        <div class="flex-1 w-full relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" id="searchInput" placeholder="Cari gadget impian Anda..." class="w-full pl-12 pr-4 py-4 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 transition text-sm font-medium border-none ring-1 ring-slate-100">
        </div>
        <div class="flex gap-3 w-full md:w-auto">
            <input type="text" id="minPrice" placeholder="Harga Min" class="flex-1 md:w-40 p-4 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 text-sm price-format border-none ring-1 ring-slate-100 font-medium uppercase tracking-tight">
            <input type="text" id="maxPrice" placeholder="Harga Max" class="flex-1 md:w-40 p-4 bg-slate-50 rounded-2xl outline-none focus:ring-2 focus:ring-blue-500 text-sm price-format border-none ring-1 ring-slate-100 font-medium uppercase tracking-tight">
        </div>
    </div>
</header>

<main class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-baseline justify-between mb-8 px-2">
        <h2 class="text-xl font-black text-slate-800 uppercase tracking-wider">Koleksi Terbaru</h2>
        
        <div class="md:hidden flex gap-3">
            <a href="profile.php" class="text-xs font-bold text-blue-600 underline">Profil</a>
            <span class="text-slate-300">|</span>
            <a href="my_orders.php" class="text-xs font-bold text-blue-600 underline">Pesanan</a>
        </div>
    </div>

    <div id="productsContainer" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php
        $res = $conn->query("SELECT * FROM phones WHERE stok > 0 ORDER BY id DESC");
        if ($res && $res->num_rows > 0) {
            while($row = $res->fetch_assoc()) renderProductCard($row, $conn, $no_admin);
        } else {
            echo '<div class="col-span-full text-center py-20 text-slate-400 font-medium italic">Belum ada stok barang tersedia...</div>';
        }
        ?>
    </div>
</main>

<script>
    const container = document.getElementById('productsContainer');
    
    // Fungsi Search & Filter
    function filter() {
        let fd = new FormData();
        fd.append('ajax', 'search');
        fd.append('search', document.getElementById('searchInput').value);
        fd.append('min_price', document.getElementById('minPrice').value.replace(/\./g, ''));
        fd.append('max_price', document.getElementById('maxPrice').value.replace(/\./g, ''));

        container.style.opacity = '0.5'; // Efek visual loading

        fetch('index.php', { method: 'POST', body: fd })
        .then(r => r.text()).then(h => { 
            container.innerHTML = h; 
            container.style.opacity = '1';
        });
    }

    document.getElementById('searchInput').addEventListener('input', filter);
    document.querySelectorAll('.price-format').forEach(i => {
        i.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            filter();
        });
    });

    // Fungsi Tambah Keranjang AJAX
    function addToCart(id, e) {
        let fd = new FormData();
        fd.append('ajax', 'add_cart');
        fd.append('phone_id', id);
        fd.append('qty', 1);

        fetch('index.php', { method: 'POST', body: fd })
        .then(r => r.json()).then(d => {
            let b = document.getElementById('cartBadge');
            b.innerText = d.total;
            b.classList.remove('hidden');
            
            // Animasi feedback tombol
            const btn = e.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '✅ Berhasil';
            btn.classList.replace('bg-blue-600', 'bg-green-600');
            btn.classList.remove('shadow-blue-100');
            btn.classList.add('shadow-green-100');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.replace('bg-green-600', 'bg-blue-600');
                btn.classList.remove('shadow-green-100');
                btn.classList.add('shadow-blue-100');
            }, 1500);
        });
    }
</script>
</body>
</html>