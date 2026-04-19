-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 11 Apr 2026 pada 16.50
-- Versi server: 10.4.27-MariaDB
-- Versi PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_hp`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', 'admin123', '2026-04-11 14:14:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `metode` varchar(20) DEFAULT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `no_wa` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `nama`, `alamat`, `total`, `created_at`, `metode`, `bukti`, `status`, `no_wa`) VALUES
(13, 'FAISAL DWIKI NURDIANSYAH', 'asaass', 4500000, '2026-04-09 15:00:07', 'bank', '69d7bef73ace1.jpg', 'lunas', '6285862030566'),
(14, 'FAISAL DWIKI NURDIANSYAH', 'yyyy', 2400000, '2026-04-09 15:04:34', 'bank', '69d7c002c3cff.jpg', 'ditolak', '6285862030566'),
(15, 'FAISAL DWIKI NURDIANSYAH', 'ssss', 3200000, '2026-04-09 15:09:15', 'bank', '69d7c11b2aa34.jpg', 'pending', '6285862030566'),
(16, 'INDOMIE GORENG', 'jajajajaajjajaa', 5600000, '2026-04-11 13:37:21', 'bank', '69da4e91ce8ab.jpg', 'pending', '6285862030566');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `phone_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `phone_id`, `qty`, `harga`) VALUES
(21, 13, 16, 1, 800000),
(22, 13, 14, 1, 3700000),
(23, 14, 15, 1, 2400000),
(24, 15, 16, 1, 800000),
(25, 15, 15, 1, 2400000),
(26, 16, 13, 1, 3200000),
(27, 16, 15, 1, 2400000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `phones`
--

CREATE TABLE `phones` (
  `id` int(11) NOT NULL,
  `nama_hp` varchar(255) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `harga` decimal(10,2) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `spesifikasi` text DEFAULT NULL,
  `kategori` enum('Android','iPhone') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `phones`
--

INSERT INTO `phones` (`id`, `nama_hp`, `brand`, `harga`, `stok`, `deskripsi`, `spesifikasi`, `kategori`, `created_at`) VALUES
(13, 'Iphone 12 basic', 'Apple', '3200000.00', 1, 'second inter', 'ram 6 / 128', 'iPhone', '2026-04-09 07:11:07'),
(14, 'Motorola Edge 60 Fussion', 'Motorola', '3700000.00', 1, 'Bekas - Seperti Baru', '12/256 HP pemakaian pribadi. No minus. Fullset Original. Masih garansi sampai Desember 2026', 'Android', '2026-04-09 13:55:09'),
(15, 'Poco F4 ', 'Xiaomi', '2400000.00', 1, 'Poco F4 \r\nCas + Dos ada lengkap\r\nLecet pemakaian', '8/256', 'Android', '2026-04-09 14:10:25'),
(16, 'Vivo V15', 'Vivo', '800000.00', 1, 'WTS Vivo V15 6/64 - Normal (Nego)\r\nDijual santai pemakaian pribadi.\r\nModel: Vivo V15\r\nKondisi Fisik: Sesuai Foto\r\nFungsi: 100% Normal. Layar jernih, kamera bening, baterai awet, Fingerprint lancar jaya.\r\nKelengkapan: Fullset (Unit, Dus, Kabel, Kitab-kitab).\r\nMinus: LCD retak dan lecet pemakaian\r\nHarga: Rp 800.000 nego\r\nLokasi: Semarang Timur\r\nSistem Transaksi: Lebih diutamakan [COD di rumah saya / di Area Semarang Timur] agar bisa cek sepuasnya.', 'Kapasitas: 6/64gb', 'Android', '2026-04-09 14:27:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `phone_images`
--

CREATE TABLE `phone_images` (
  `id` int(11) NOT NULL,
  `phone_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `phone_images`
--

INSERT INTO `phone_images` (`id`, `phone_id`, `image`) VALUES
(48, 13, '69d7510b043e9.jpg'),
(49, 13, '69d7510b04bb2.jpg'),
(50, 13, '69d7510b052f0.jpg'),
(52, 14, '69d7afbd82a89.jpg'),
(53, 14, '69d7afbd83229.jpg'),
(54, 14, '69d7afbd8374e.jpg'),
(55, 14, '69d7afbd83d8f.jpg'),
(56, 15, '69d7b3515aec5.jpg'),
(57, 15, '69d7b3515b74f.jpg'),
(58, 16, '69d7b75c427c7.jpg'),
(59, 16, '69d7b75c435d3.jpg'),
(60, 16, '69d7b75c443b0.jpg');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `phones`
--
ALTER TABLE `phones`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `phone_images`
--
ALTER TABLE `phone_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_id` (`phone_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `phones`
--
ALTER TABLE `phones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `phone_images`
--
ALTER TABLE `phone_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `phone_images`
--
ALTER TABLE `phone_images`
  ADD CONSTRAINT `phone_images_ibfk_1` FOREIGN KEY (`phone_id`) REFERENCES `phones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
