<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PhoneStore - Temukan Gadget Impianmu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <nav class="fixed w-full z-50 glass border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-3xl">📱</span>
                <h1 class="text-2xl font-black text-blue-600 tracking-tighter">Phone<span class="text-slate-800">Store</span></h1>
            </div>
            
            <div class="hidden md:flex items-center gap-8 font-semibold text-slate-600">
                <a href="#" class="hover:text-blue-600 transition">Beranda</a>
                <a href="#katalog" class="hover:text-blue-600 transition">Katalog</a>
                <a href="#" class="hover:text-blue-600 transition">Promo</a>
                <a href="#" class="hover:text-blue-600 transition">Tentang Kami</a>
            </div>

            <div class="flex items-center gap-4">
                <a href="cart.php" class="relative p-3 bg-white rounded-2xl shadow-sm border border-gray-100 hover:bg-blue-50 transition group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-600 group-hover:text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white">2</span>
                </a>
                <button class="md:hidden p-2 text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                </button>
            </div>
        </div>
    </nav>

    <section class="pt-32 pb-20 px-4">
        <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-block px-4 py-2 bg-blue-50 text-blue-600 rounded-full text-sm font-bold mb-6">
                    🚀 Gadget Terbaru Tahun 2026
                </div>
                <h2 class="text-5xl md:text-7xl font-extrabold leading-tight mb-6">
                    Bawa Pulang <br> <span class="text-blue-600 underline decoration-blue-200">Teknologi</span> Tercanggih.
                </h2>
                <p class="text-lg text-slate-500 mb-10 max-w-lg leading-relaxed">
                    Nikmati pengalaman belanja smartphone terbaik dengan harga transparan, cicilan 0%, dan garansi resmi Indonesia.
                </p>
                
                <div class="relative max-w-md shadow-2xl shadow-blue-100 rounded-[2rem]">
                    <input type="text" placeholder="Cari iPhone 15, Samsung S24..." 
                           class="w-full py-5 pl-8 pr-32 rounded-[2rem] border-none focus:ring-4 focus:ring-blue-100 outline-none text-slate-700">
                    <button class="absolute right-2 top-2 bottom-2 bg-blue-600 text-white px-8 rounded-[1.8rem] font-bold hover:bg-blue-700 transition active:scale-95">
                        Cari
                    </button>
                </div>
            </div>

            <div class="relative flex justify-center">
                <div class="absolute w-80 h-80 bg-blue-400 rounded-full filter blur-[100px] opacity-20 animate-pulse"></div>
                <img src="https://images.unsplash.com/photo-1616348436168-de43ad0db179?auto=format&fit=crop&q=80&w=800" 
                     alt="Smartphone" class="relative z-10 w-[400px] drop-shadow-2xl hover:-translate-y-4 transition duration-500 cursor-pointer">
            </div>
        </div>
    </section>

    <section class="bg-white py-12 border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 flex flex-wrap justify-between gap-8 opacity-50 grayscale hover:grayscale-0 transition duration-500">
            <span class="text-2xl font-black uppercase tracking-widest italic text-slate-400">Apple</span>
            <span class="text-2xl font-black uppercase tracking-widest italic text-slate-400">Samsung</span>
            <span class="text-2xl font-black uppercase tracking-widest italic text-slate-400">Xiaomi</span>
            <span class="text-2xl font-black uppercase tracking-widest italic text-slate-400">Google</span>
            <span class="text-2xl font-black uppercase tracking-widest italic text-slate-400">Oppo</span>
        </div>
    </section>

    <section id="katalog" class="py-24 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
                <div>
                    <h2 class="text-4xl font-black mb-4">Koleksi Terpopuler</h2>
                    <p class="text-slate-500">Dapatkan penawaran terbaik untuk gadget pilihan minggu ini.</p>
                </div>
                <div class="flex gap-2">
                    <button class="px-6 py-3 bg-white border border-gray-200 rounded-2xl font-bold hover:bg-slate-50 transition">Semua</button>
                    <button class="px-6 py-3 bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-blue-100">Smartphone</button>
                    <button class="px-6 py-3 bg-white border border-gray-200 rounded-2xl font-bold hover:bg-slate-50 transition">Tablet</button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="group bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-2xl hover:shadow-blue-100 transition duration-500">
                    <div class="bg-slate-50 rounded-3xl p-6 mb-6 overflow-hidden relative aspect-square flex items-center justify-center">
                        <img src="https://via.placeholder.com/300x400" alt="HP" class="h-48 group-hover:scale-110 transition duration-500">
                        <div class="absolute top-4 left-4 bg-white/80 backdrop-blur px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest text-blue-600 shadow-sm">
                            Apple
                        </div>
                    </div>
                    <h3 class="text-xl font-bold mb-2 leading-tight">iPhone 15 Pro Max Natural Titanium</h3>
                    <p class="text-2xl font-black text-blue-600 mb-6 italic">Rp 23.499.000</p>
                    <a href="#" class="block w-full text-center py-4 bg-slate-900 text-white font-bold rounded-2xl hover:bg-blue-600 transition shadow-lg shadow-slate-200 active:scale-95">
                        Lihat Detail
                    </a>
                </div>
                </div>
        </div>
    </section>

    <section class="px-4 pb-24">
        <div class="max-w-7xl mx-auto bg-blue-600 rounded-[3rem] p-12 md:p-20 text-center text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative z-10">
                <h2 class="text-4xl md:text-5xl font-black mb-6">Butuh Rekomendasi Gadget?</h2>
                <p class="text-blue-100 mb-10 max-w-xl mx-auto text-lg">Konsultasikan kebutuhanmu dengan admin spesialis kami secara gratis via WhatsApp.</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="#" class="px-10 py-5 bg-white text-blue-600 font-bold rounded-3xl hover:bg-blue-50 transition shadow-xl active:scale-95">
                        Chat WhatsApp
                    </a>
                    <a href="#" class="px-10 py-5 bg-blue-700 text-white font-bold rounded-3xl hover:bg-blue-800 transition border border-blue-500">
                        Lihat Testimoni
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-white pt-20 pb-10 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 grid md:grid-cols-4 gap-12 mb-20">
            <div class="col-span-2">
                <h2 class="text-3xl font-black text-blue-600 mb-6 tracking-tighter italic">PhoneStore</h2>
                <p class="text-slate-500 max-w-xs mb-6">Pusat belanja smartphone dan gadget original terpercaya sejak tahun 2020. Melayani dengan sepenuh hati.</p>
                <div class="flex gap-4">
                    <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-600 hover:text-white transition">IG</div>
                    <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-600 hover:text-white transition">FB</div>
                    <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center cursor-pointer hover:bg-blue-600 hover:text-white transition">TT</div>
                </div>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-6">Menu Cepat</h4>
                <ul class="space-y-4 text-slate-500 font-medium">
                    <li><a href="#" class="hover:text-blue-600 transition">Beranda</a></li>
                    <li><a href="#" class="hover:text-blue-600 transition">Katalog</a></li>
                    <li><a href="#" class="hover:text-blue-600 transition">Syarat & Ketentuan</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-6">Layanan Pelanggan</h4>
                <ul class="space-y-4 text-slate-500 font-medium">
                    <li><a href="#" class="hover:text-blue-600 transition">Pusat Bantuan</a></li>
                    <li><a href="#" class="hover:text-blue-600 transition">Lacak Pesanan</a></li>
                    <li><a href="#" class="hover:text-blue-600 transition">Kebijakan Retur</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 text-center text-slate-400 text-sm border-t border-gray-100 pt-10">
            &copy; 2026 PhoneStore Indonesia. All rights reserved. Design by AI Assistant.
        </div>
    </footer>

</body>
</html>