<?php
session_start();

// Jika tidak ada parameter WA, arahkan kembali ke beranda
if (!isset($_GET['wa'])) {
    header("Location: index.php");
    exit;
}

$link_wa = $_GET['wa'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - PhoneStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-[3rem] shadow-xl shadow-blue-100/50 border border-gray-100 p-8 md:p-12 text-center">
        
        <div class="flex justify-center mb-6">
            <dotlottie-player src="https://lottie.host/8040d9d4-1422-4824-9f87-a00636b1393a/9M6n2zD0r6.json" background="transparent" speed="1" style="width: 150px; height: 150px;" loop autoplay></dotlottie-player>
        </div>

        <h1 class="text-3xl font-black text-gray-900 mb-2">Pesanan Diterima!</h1>
        <p class="text-gray-500 text-sm leading-relaxed mb-8">
            Terima kasih telah berbelanja di <strong>PhoneStore</strong>. Pesanan Anda telah tercatat dalam sistem kami dan sedang menunggu verifikasi pembayaran.
        </p>

        <div class="space-y-4">
            <a href="<?= htmlspecialchars($link_wa) ?>" 
               class="flex items-center justify-center gap-3 w-full bg-green-500 hover:bg-green-600 text-white font-bold py-4 px-6 rounded-2xl transition-all shadow-lg shadow-green-100 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                </svg>
                Konfirmasi ke WhatsApp
            </a>

            <a href="index.php" class="block w-full py-4 text-sm font-bold text-gray-400 hover:text-blue-600 transition-colors">
                Kembali ke Katalog
            </a>
        </div>

        <div class="mt-8 pt-8 border-t border-gray-50">
            <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Langkah Selanjutnya</p>
            <div class="flex justify-between mt-4">
                <div class="flex flex-col items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</div>
                    <span class="text-[9px] text-gray-500 font-medium">Kirim Pesan WA</span>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</div>
                    <span class="text-[9px] text-gray-500 font-medium">Admin Verifikasi</span>
                </div>
                <div class="flex flex-col items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">3</div>
                    <span class="text-[9px] text-gray-500 font-medium">Barang Dikirim</span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>