<?php
session_start();
include __DIR__ . '/admin/config.php';

// PROTEKSI: Wajib login
if (!isset($_SESSION['customer_id'])) {
    header("Location: auth.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];

/* ==========================================
    LOGIKA AJAX UPDATE (Real-time)
========================================== */
if (isset($_POST['ajax_update'])) {
    $id = intval($_POST['id']);
    $qty = max(1, intval($_POST['qty']));
    
    $produk = $conn->query("SELECT stok, harga FROM phones WHERE id=$id")->fetch_assoc();
    if ($produk) {
        $finalQty = min($qty, $produk['stok']);
        $conn->query("UPDATE cart SET qty = $finalQty WHERE customer_id = $customer_id AND phone_id = $id");
        
        $res = $conn->query("SELECT SUM(c.qty * p.harga) as grand_total, SUM(c.qty) as total_qty 
                             FROM cart c JOIN phones p ON c.phone_id = p.id 
                             WHERE c.customer_id = $customer_id");
        $row = $res->fetch_assoc();
        
        echo json_encode([
            'status' => 'success',
            'new_qty' => $finalQty,
            'subtotal' => "Rp " . number_format($produk['harga'] * $finalQty, 0, ',', '.'),
            'grand_total' => "Rp " . number_format($row['grand_total'] ?? 0, 0, ',', '.'),
            'total_qty' => $row['total_qty'] ?? 0
        ]);
    }
    exit;
}

/* ==========================================
    LOGIKA AJAX DELETE
========================================== */
if (isset($_POST['ajax_delete'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM cart WHERE customer_id = $customer_id AND phone_id = $id");
    echo json_encode(['status' => 'success']);
    exit;
}

/* ==========================================
    AMBIL DATA TERBARU
========================================== */
$dbCart = $conn->query("SELECT c.qty, p.* FROM cart c JOIN phones p ON c.phone_id = p.id WHERE c.customer_id = $customer_id");
$cartItems = [];
$grandTotal = 0;
$totalQty = 0;
while ($row = $dbCart->fetch_assoc()) {
    $cartItems[] = $row;
    $grandTotal += ($row['harga'] * $row['qty']);
    $totalQty += $row['qty'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - PhoneStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .swal2-popup { border-radius: 2rem !important; padding: 2rem !important; }
        .swal2-styled.swal2-confirm { background-color: #ef4444 !important; border-radius: 1rem !important; padding: 0.8rem 2rem !important; font-weight: 800 !important; }
        .swal2-styled.swal2-cancel { background-color: #f1f5f9 !important; color: #64748b !important; border-radius: 1rem !important; padding: 0.8rem 2rem !important; font-weight: 800 !important; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 p-4 md:p-8 lg:p-12">

<div class="max-w-6xl mx-auto">
    <!-- HEADER -->
    <div class="mb-10 flex flex-col md:flex-row md:justify-between md:items-end gap-4">
        <div>
            <h1 class="text-4xl font-black text-slate-800 tracking-tighter">Keranjang <span class="text-blue-600">Belanja</span></h1>
            <p class="text-slate-400 mt-2 text-sm font-medium">
                Halo, <span class="text-slate-700 font-bold"><?= htmlspecialchars($_SESSION['customer_name'] ?? 'Pelanggan') ?></span>.
            </p>
        </div>
    </div>

    <?php if (empty($cartItems)): ?>
        <!-- State Kosong -->
        <div class="bg-white rounded-[3rem] p-16 text-center shadow-xl shadow-slate-200/50 border border-slate-100 max-w-2xl mx-auto mt-20">
            <div class="bg-blue-50 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-8 text-blue-500 rotate-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
            </div>
            <h2 class="text-3xl font-black text-slate-800 mb-4 tracking-tight">Wah, Kosong Banget!</h2>
            <a href="index.php" class="bg-blue-600 text-white px-10 py-4 rounded-2xl font-black hover:bg-blue-700 transition inline-block">Mulai Belanja</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
            <!-- DAFTAR ITEM (Kiri) -->
            <div class="lg:col-span-2 space-y-4">
                <?php foreach ($cartItems as $item): 
                    $id = $item['id'];
                    $imgRes = $conn->query("SELECT image FROM phone_images WHERE phone_id=$id LIMIT 1")->fetch_assoc();
                    $gambar = ($imgRes && file_exists(__DIR__."/admin/uploads/".$imgRes['image'])) 
                              ? "admin/uploads/".$imgRes['image'] : "https://via.placeholder.com/200";
                ?>
                <div id="item-card-<?= $id ?>" class="bg-white rounded-[2rem] p-5 shadow-sm border border-slate-100 flex flex-col sm:flex-row items-center gap-6 group hover:border-blue-200 transition-all">
                    <img src="<?= $gambar ?>" class="w-28 h-28 object-cover rounded-[1.5rem] bg-slate-50">
                    
                    <div class="flex-grow w-full">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-black text-lg text-slate-800 leading-tight mb-1"><?= htmlspecialchars($item['nama_hp']) ?></h4>
                                <span class="text-[10px] font-black uppercase tracking-widest text-blue-500 bg-blue-50 px-2.5 py-1 rounded-lg"><?= htmlspecialchars($item['brand']) ?></span>
                            </div>
                            <button type="button" onclick="confirmDelete(<?= $id ?>, '<?= htmlspecialchars($item['nama_hp']) ?>')" class="text-slate-200 hover:text-red-500 transition-colors p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>

                        <div class="flex justify-between items-end mt-4">
                            <div class="flex items-center bg-slate-50 rounded-xl p-1.5 ring-1 ring-slate-100">
                                <label class="text-[9px] font-black text-slate-400 px-2 uppercase tracking-widest">Jumlah</label>
                                <input type="number" onchange="updateCart(<?= $id ?>, this.value)" value="<?= $item['qty'] ?>" min="1" max="<?= $item['stok'] ?>" 
                                       class="bg-white w-14 py-1.5 text-center font-black text-slate-800 rounded-lg border-none outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest">Subtotal</p>
                                <p class="font-black text-lg text-blue-600" id="subtotal-<?= $id ?>">Rp<?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- SIDEBAR (Kanan) -->
            <div class="lg:col-span-1 space-y-4 sticky top-12">
                
                <!-- TOMBOL KEMBALI (Sama Ukuran & Posisi dengan Checkout) -->
                <a href="index.php" class="flex items-center justify-center gap-2 w-full bg-white px-4 py-3 rounded-xl border border-slate-200 shadow-sm text-slate-500 hover:text-blue-600 hover:border-blue-200 transition-all active:scale-[0.98] group text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="text-[9px] font-black uppercase tracking-widest">Kembali Belanja</span>
                </a>

                <!-- BOX RINGKASAN -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 border border-slate-100">
                    <h3 class="text-xl font-black mb-8 text-slate-800 border-b border-slate-50 pb-4">Ringkasan</h3>
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Barang</span>
                            <span class="font-black text-slate-800" id="total-qty"><?= $totalQty ?> Unit</span>
                        </div>
                        <div class="pt-6 border-t-2 border-dashed border-slate-50">
                            <p class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-1">Total Tagihan</p>
                            <p class="text-3xl font-black text-blue-600 tracking-tighter" id="grand-total">Rp<?= number_format($grandTotal, 0, ',', '.') ?></p>
                        </div>
                    </div>
                    <a href="checkout.php" class="flex items-center justify-center gap-3 w-full bg-blue-600 text-white py-5 rounded-2xl font-black text-lg hover:bg-blue-700 transition shadow-xl shadow-blue-100 active:scale-95">
                        Checkout
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus dari keranjang?',
        text: name + " akan dihapus.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            let fd = new FormData();
            fd.append('ajax_delete', '1');
            fd.append('id', id);

            fetch('cart.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    const card = document.getElementById('item-card-' + id);
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                }
            });
        }
    })
}

function updateCart(id, qty) {
    if(qty < 1 || qty === "") return;
    let fd = new FormData();
    fd.append('ajax_update', '1');
    fd.append('id', id);
    fd.append('qty', qty);

    fetch('cart.php', { method: 'POST', body: fd })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            document.getElementById('subtotal-' + id).innerText = data.subtotal;
            document.getElementById('grand-total').innerText = data.grand_total;
            document.getElementById('total-qty').innerText = data.total_qty + " Unit";
        }
    });
}
</script>

</body>
</html>