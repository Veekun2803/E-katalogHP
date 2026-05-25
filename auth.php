<?php
session_start();
include __DIR__ . '/admin/config.php';

// Jika sudah login, langsung lempar ke index
if (isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";
$active_tab = "login"; // Default tab aktif

// PROSES REGISTRASI
if (isset($_POST['register'])) {
    $active_tab = "register";
    $nama = $conn->real_escape_string($_POST['nama']);
    $email = $conn->real_escape_string($_POST['email']);
    $pass = $_POST['password'];
    
    // Verifikasi Centang Manual
    if (!isset($_POST['human_verify'])) {
        $error = "Silakan centang verifikasi keamanan.";
    } else {
        $checkEmail = $conn->query("SELECT email FROM customers WHERE email='$email'");
        if ($checkEmail->num_rows > 0) {
            $error = "Email sudah digunakan!";
        } else {
            $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
            $query = "INSERT INTO customers (nama_lengkap, email, password) VALUES ('$nama', '$email', '$hashed_password')";
            if ($conn->query($query)) {
                $success = "Akun berhasil dibuat! Silakan login.";
                $active_tab = "login"; // Pindah ke login setelah sukses
            } else {
                $error = "Gagal mendaftar, coba lagi.";
            }
        }
    }
}

// PROSES LOGIN
if (isset($_POST['login'])) {
    $active_tab = "login";
    $email = $conn->real_escape_string($_POST['email']);
    $pass = $_POST['password'];

    $res = $conn->query("SELECT * FROM customers WHERE email='$email'");
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        if (password_verify($pass, $user['password'])) {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_name'] = $user['nama_lengkap'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PhoneStore - Gadget Impian Anda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-white text-slate-900">

    <nav class="fixed top-0 w-full z-50 glass border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 onclick="hideAuth()" class="text-2xl font-black text-blue-600 tracking-tighter cursor-pointer">Phone<span class="text-slate-800">Store.</span></h1>
            <button onclick="showAuth('login')" class="bg-slate-900 text-white px-6 py-2.5 rounded-full font-bold text-sm hover:bg-blue-600 transition-all">Masuk</button>
        </div>
    </nav>

    <!-- LANDING PAGE -->
    <main id="landing-section" class="<?= (isset($_POST['login']) || isset($_POST['register']) || $error || $success) ? 'hidden' : '' ?>">
        <section class="pt-32 pb-20 px-6">
            <div class="max-w-7xl mx-auto text-center">
                <span class="bg-blue-50 text-blue-600 px-4 py-2 rounded-full text-xs font-extrabold uppercase tracking-widest">Gadget Masa Depan</span>
                <h2 class="text-5xl md:text-7xl font-black text-slate-900 mt-6 leading-tight">Ganti HP Jadi <br><span class="text-blue-600">Lebih Mudah.</span></h2>
                <p class="text-gray-500 mt-6 text-lg max-w-2xl mx-auto">Dapatkan koleksi smartphone terbaru dengan harga terbaik, garansi resmi, dan pengiriman super cepat hanya di PhoneStore.</p>
                <div class="mt-10 flex flex-col sm:flex-row justify-center gap-4">
                    <button onclick="showAuth('register')" class="bg-blue-600 text-white px-10 py-5 rounded-2xl font-black text-lg shadow-xl shadow-blue-200 hover:scale-105 transition-all">Mulai Belanja — Gratis</button>
                    <a href="#features" class="bg-white border border-gray-200 text-slate-700 px-10 py-5 rounded-2xl font-bold text-lg hover:bg-gray-50 transition-all">Lihat Fitur</a>
                </div>
            </div>
        </section>

        <section id="features" class="py-20 bg-slate-50">
            <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mb-6 text-2xl">🚚</div>
                    <h3 class="font-bold text-xl mb-3">Pengiriman Cepat</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Pesanan Anda akan sampai dalam waktu singkat dengan kurir terpercaya kami.</p>
                </div>
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center mb-6 text-2xl">🛡️</div>
                    <h3 class="font-bold text-xl mb-3">Garansi Resmi</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Semua produk yang kami jual memiliki garansi resmi pabrikan 100%.</p>
                </div>
                <div class="bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm">
                    <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center mb-6 text-2xl">💳</div>
                    <h3 class="font-bold text-xl mb-3">Pembayaran Aman</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Berbagai metode pembayaran yang aman dan terenkripsi untuk Anda.</p>
                </div>
            </div>
        </section>
    </main>

    <!-- AUTH SECTION -->
    <section id="auth-section" class="min-h-screen flex items-center justify-center p-4 <?= (isset($_POST['login']) || isset($_POST['register']) || $error || $success) ? '' : 'hidden' ?>">
        <div class="w-full max-w-md pt-20">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-blue-600">Phone<span class="text-gray-800">Store</span></h1>
                <p class="text-gray-500 mt-2">Silakan masuk atau daftar akun baru</p>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl border border-gray-100">
                <?php if($error): ?>
                    <div class="mb-4 p-3 bg-red-50 text-red-600 text-sm rounded-xl border border-red-100 text-center font-bold italic">⚠️ <?= $error ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="mb-4 p-3 bg-green-50 text-green-600 text-sm rounded-xl border border-green-100 text-center font-bold"><?= $success ?></div>
                <?php endif; ?>

                <div class="flex bg-gray-100 p-1 rounded-2xl mb-8">
                    <button onclick="switchTab('login')" id="tab-login" class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all">Login</button>
                    <button onclick="switchTab('register')" id="tab-register" class="flex-1 py-2.5 rounded-xl text-sm font-bold transition-all text-gray-500">Daftar</button>
                </div>

                <!-- FORM LOGIN -->
                <form id="form-login" method="POST" class="space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Email</label>
                        <input type="email" name="email" required class="w-full mt-1 p-3.5 bg-slate-50 border-none rounded-2xl ring-1 ring-gray-200 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Password</label>
                        <input type="password" name="password" required class="w-full mt-1 p-3.5 bg-slate-50 border-none rounded-2xl ring-1 ring-gray-200 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <button type="submit" name="login" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-extrabold rounded-2xl shadow-lg shadow-blue-200 transition-all active:scale-[0.98]">Masuk ke Akun</button>
                </form>

                <!-- FORM REGISTER -->
                <form id="form-register" method="POST" class="hidden space-y-4">
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Nama Lengkap</label>
                        <input type="text" name="nama" required class="w-full mt-1 p-3.5 bg-slate-50 border-none rounded-2xl ring-1 ring-gray-200 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Email</label>
                        <input type="email" name="email" required class="w-full mt-1 p-3.5 bg-slate-50 border-none rounded-2xl ring-1 ring-gray-200 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider ml-1">Buat Password</label>
                        <input type="password" name="password" required class="w-full mt-1 p-3.5 bg-slate-50 border-none rounded-2xl ring-1 ring-gray-200 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    
                    <!-- BOX VERIFIKASI CENTANG -->
                    <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <input type="checkbox" name="human_verify" id="human_verify" class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <label for="human_verify" class="text-sm font-bold text-slate-600 cursor-pointer select-none">Saya bukan robot</label>
                    </div>

                    <button type="submit" name="register" class="w-full py-4 bg-gray-800 hover:bg-black text-white font-extrabold rounded-2xl shadow-lg shadow-gray-200 transition-all active:scale-[0.98]">Daftar Sekarang</button>
                </form>

                <div class="mt-6 text-center">
                    <button onclick="hideAuth()" class="text-sm font-bold text-gray-400 hover:text-blue-600 transition-all">Kembali ke Beranda</button>
                </div>
            </div>
        </div>
    </section>

    <script>
        const landing = document.getElementById('landing-section');
        const auth = document.getElementById('auth-section');

        function showAuth(type) {
            landing.classList.add('hidden');
            auth.classList.remove('hidden');
            switchTab(type);
            window.scrollTo(0, 0);
        }

        function hideAuth() {
            landing.classList.remove('hidden');
            auth.classList.add('hidden');
        }

        function switchTab(type) {
            const fLogin = document.getElementById('form-login');
            const fReg = document.getElementById('form-register');
            const tLogin = document.getElementById('tab-login');
            const tReg = document.getElementById('tab-register');

            if(type === 'login') {
                fLogin.classList.remove('hidden'); 
                fReg.classList.add('hidden');
                tLogin.className = "flex-1 py-2.5 rounded-xl text-sm font-bold transition-all bg-white shadow-sm text-blue-600";
                tReg.className = "flex-1 py-2.5 rounded-xl text-sm font-bold transition-all text-gray-500";
            } else {
                fReg.classList.remove('hidden'); 
                fLogin.classList.add('hidden');
                tReg.className = "flex-1 py-2.5 rounded-xl text-sm font-bold transition-all bg-white shadow-sm text-blue-600";
                tLogin.className = "flex-1 py-2.5 rounded-xl text-sm font-bold transition-all text-gray-500";
            }
        }

        // Inisialisasi tab saat halaman load (menangani error PHP)
        window.onload = () => {
            <?php if ($active_tab === 'register'): ?>
                switchTab('register');
            <?php else: ?>
                switchTab('login');
            <?php endif; ?>
        };
    </script>
</body>
</html>