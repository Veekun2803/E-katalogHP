<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: auth.php");
    exit;
}

include __DIR__ . '/admin/config.php';
$customer_id = $_SESSION['customer_id'];

/* ==========================================================================
   HANDLE UPDATE PROFIL
   ========================================================================== */
if (isset($_POST['update_profile'])) {
    $nama   = htmlspecialchars($_POST['nama_lengkap']);
    $wa     = htmlspecialchars($_POST['no_wa']);
    $alamat = htmlspecialchars($_POST['alamat']);

    $stmt = $conn->prepare("UPDATE customers SET nama_lengkap=?, no_wa=?, alamat=? WHERE id=?");
    $stmt->bind_param("sssi", $nama, $wa, $alamat, $customer_id);
    
    if ($stmt->execute()) {
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Profil berhasil diperbarui!'];
    } else {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Gagal memperbarui profil.'];
    }
    header("Location: profile.php");
    exit;
}

/* ==========================================================================
   HANDLE GANTI PASSWORD
   ========================================================================== */
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $cfm_pass = $_POST['confirm_password'];

    // Ambil password lama dari DB
    $user = $conn->query("SELECT password FROM customers WHERE id = $customer_id")->fetch_assoc();

    // Verifikasi (Asumsi: Menggunakan password_hash. Jika plain text, ganti password_verify)
    if (!password_verify($old_pass, $user['password'])) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Kata sandi lama salah!'];
    } elseif ($new_pass !== $cfm_pass) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Konfirmasi kata sandi tidak cocok!'];
    } else {
        $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE customers SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed_pass, $customer_id);
        $stmt->execute();
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Kata sandi berhasil diganti!'];
    }
    header("Location: profile.php");
    exit;
}

// Ambil data terbaru customer
$data = $conn->query("SELECT * FROM customers WHERE id = $customer_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - PhoneStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen pb-20">

<nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm mb-8 sticky top-0 z-40">
    <div class="max-w-6xl mx-auto px-4 h-16 flex justify-between items-center">
        
        <div class="flex items-center gap-2 sm:gap-4">
            <a href="index.php" class="flex items-center gap-2 text-slate-500 font-bold group" title="Kembali ke Beranda">
                <div class="p-2 bg-slate-50 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-all text-xs">
                    ←
                </div>
                <span class="text-[10px] uppercase tracking-widest font-black hidden sm:block">Beranda</span>
            </a>
            
            <a href="my_orders.php" class="flex items-center gap-2 text-slate-500 font-bold group" title="Lihat Pesanan Saya">
                <div class="p-2 bg-slate-50 rounded-lg group-hover:bg-slate-900 group-hover:text-white transition-all text-xs">
                    📦
                </div>
                <span class="text-[10px] uppercase tracking-widest font-black hidden sm:block">Pesanan</span>
            </a>
        </div>

        <h1 class="text-lg font-black text-gray-800 tracking-tighter italic uppercase">Account <span class="text-blue-600">Settings</span></h1>
        
        <a href="logout.php" class="text-[10px] font-black text-red-500 uppercase tracking-widest hover:underline px-2 py-1 rounded-md hover:bg-red-50 transition-colors">
            Logout
        </a>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div class="md:col-span-1">
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm text-center">
                <div class="w-24 h-24 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-[2rem] mx-auto mb-4 flex items-center justify-center text-3xl font-black text-white shadow-xl shadow-blue-100">
                    <?= strtoupper(substr($data['nama_lengkap'] ?? $data['username'], 0, 1)) ?>
                </div>
                <h2 class="font-black text-gray-900 text-xl tracking-tight"><?= htmlspecialchars($data['nama_lengkap'] ?? $data['username']) ?></h2>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Pelanggan Setia</p>
            </div>
        </div>

        <div class="md:col-span-2 space-y-6">
            
            <div class="bg-white p-8 md:p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
                <h3 class="text-lg font-black text-gray-900 mb-8 flex items-center gap-3 italic uppercase tracking-tighter">
                    <span class="w-8 h-8 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-sm">👤</span>
                    Informasi Pribadi
                </h3>

                <form method="POST" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap'] ?? '') ?>" required
                            class="w-full bg-slate-50 border-none p-4 rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-sm transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Nomor WhatsApp</label>
                        <input type="text" name="no_wa" value="<?= htmlspecialchars($data['no_wa'] ?? '') ?>" required
                            class="w-full bg-slate-50 border-none p-4 rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-sm transition-all">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Alamat Pengiriman Utama</label>
                        <textarea name="alamat" rows="3" required
                            class="w-full bg-slate-50 border-none p-4 rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-sm transition-all"><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" name="update_profile" class="w-full py-4 bg-blue-600 text-white font-black text-xs rounded-2xl shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all uppercase tracking-widest active:scale-[0.98]">
                        Simpan Perubahan
                    </button>
                </form>
            </div>

            <div class="bg-white p-8 md:p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
                <h3 class="text-lg font-black text-gray-900 mb-8 flex items-center gap-3 italic uppercase tracking-tighter">
                    <span class="w-8 h-8 bg-red-50 text-red-600 rounded-xl flex items-center justify-center text-sm">🔒</span>
                    Keamanan Akun
                </h3>

                <form method="POST" class="space-y-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Kata Sandi Lama</label>
                        <input type="password" name="old_password" required
                            class="w-full bg-slate-50 border-none p-4 rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-sm transition-all">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Sandi Baru</label>
                            <input type="password" name="new_password" required
                                class="w-full bg-slate-50 border-none p-4 rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-sm transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Konfirmasi Sandi</label>
                            <input type="password" name="confirm_password" required
                                class="w-full bg-slate-50 border-none p-4 rounded-2xl ring-1 ring-slate-100 focus:ring-2 focus:ring-blue-500 outline-none font-bold text-sm transition-all">
                        </div>
                    </div>

                    <button type="submit" name="change_password" class="w-full py-4 bg-slate-900 text-white font-black text-xs rounded-2xl shadow-xl shadow-slate-100 hover:bg-black transition-all uppercase tracking-widest active:scale-[0.98]">
                        Update Kata Sandi
                    </button>
                </form>
            </div>

        </div>
    </div>
</main>

<?php if(isset($_SESSION['flash'])): ?>
<script>
    Swal.fire({
        icon: '<?= $_SESSION['flash']['type'] ?>',
        title: '<?= $_SESSION['flash']['type'] == "success" ? "Berhasil!" : "Ups!" ?>',
        text: '<?= $_SESSION['flash']['msg'] ?>',
        confirmButtonColor: '#2563eb',
        customClass: { popup: 'rounded-[2rem]' }
    });
</script>
<?php unset($_SESSION['flash']); endif; ?>

</body>
</html>