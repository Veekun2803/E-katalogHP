<?php
session_start();
include 'config.php';

// 1. Jika admin sudah login, jangan tampilkan halaman login, langsung lempar ke dashboard
if (isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit;
}

$error = "";

// 2. Proses Validasi Login
if (isset($_POST['login'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Ambil data admin berdasarkan username
    $result = $conn->query("SELECT * FROM admin WHERE username = '$username'");

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 3. Verifikasi Password (menggunakan password_verify untuk keamanan hash)
        if (password_verify($password, $row['password'])) {
            // Set session admin
            $_SESSION['admin'] = [
                'id' => $row['id'],
                'username' => $row['username'],
                'nama' => $row['nama_lengkap']
            ];
            
            // Berhasil login, arahkan ke dashboard utama
            header("Location: index.php");
            exit;
        } else {
            $error = "Password yang Anda masukkan salah.";
        }
    } else {
        $error = "Username tidak terdaftar.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Inventory System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

<div class="w-full max-w-md">
    <div class="text-center mb-10">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-3xl shadow-lg shadow-blue-200 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>
        <h1 class="text-3xl font-black text-slate-800 tracking-tight">
            Inventory<span class="text-blue-600">Admin</span>
        </h1>
        <p class="text-slate-500 mt-2 font-medium">Silakan login untuk mengelola produk</p>
    </div>

    <div class="bg-white p-8 rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100">
        
        <?php if($error): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-600 text-sm rounded-2xl border border-red-100 flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span class="font-bold"><?= $error ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Username</label>
                <div class="relative mt-1.5">
                    <input type="text" name="username" required 
                           placeholder="Masukkan username"
                           class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-500 transition-all outline-none font-semibold pl-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-4 top-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>

            <div>
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Password</label>
                <div class="relative mt-1.5">
                    <input type="password" name="password" required 
                           placeholder="••••••••"
                           class="w-full p-4 bg-slate-50 border-none rounded-2xl ring-1 ring-slate-200 focus:ring-2 focus:ring-blue-500 transition-all outline-none font-semibold pl-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-4 top-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
            </div>

            <button type="submit" name="login" 
                    class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black rounded-2xl shadow-lg shadow-blue-100 transition-all active:scale-[0.98] mt-4 flex items-center justify-center gap-2">
                <span>Login Dashboard</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </form>
    </div>

    <p class="text-center mt-10 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em]">
        &copy; 2026 SmartShop Management
    </p>
</div>

</body>
</html>