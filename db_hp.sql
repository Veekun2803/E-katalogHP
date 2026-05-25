-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 25 Bulan Mei 2026 pada 14.50
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
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama_lengkap`) VALUES
(2, 'faisal', '$2y$10$J3flKQYOlnAN.9bv68KmCetrvJ9995BkYEsER3ByIyif6mk4Rvnrq', 'FAISAL DWIKI');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `phone_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cart`
--

INSERT INTO `cart` (`id`, `customer_id`, `phone_id`, `qty`, `created_at`) VALUES
(38, 2, 15, 1, '2026-04-26 12:56:33'),
(39, 2, 14, 1, '2026-04-26 12:56:33'),
(46, 4, 14, 1, '2026-05-14 12:13:14');

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `no_wa` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`id`, `nama_lengkap`, `email`, `password`, `created_at`, `no_wa`, `alamat`) VALUES
(1, 'FAISAL DWIKI NURDIANSYAH', 'faisal@gmail.com', '$2y$10$J3flKQYOlnAN.9bv68KmCetrvJ9995BkYEsER3ByIyif6mk4Rvnrq', '2026-04-25 14:14:51', '0899999999999', 'jalan oiiiiiii'),
(2, 'jaka kentir', 'jaka123@gmail.com', '$2y$10$RxFsuL3olVB1QDF1zI1Gdeex5V47lnF0IO5FKHFqV9NFdoyIWOQrK', '2026-04-25 14:33:40', '085555555555555', 'jalan duluuuuuuuuuuuuuuuu'),
(4, 'ABDUL ROHMAN ', 'abdul@gmail.com', '$2y$10$3P9zNxepH3kbtr7SYyFbb.vdJjtA7YuDRwrgcytgNAwGiD6lLeUfm', '2026-05-14 11:48:33', '0859999999', 'jalannnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn');

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
  `no_resi` varchar(50) DEFAULT NULL,
  `no_wa` varchar(20) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `nama`, `alamat`, `total`, `created_at`, `metode`, `bukti`, `status`, `no_resi`, `no_wa`, `customer_id`) VALUES
(17, 'FAISAL DWIKI NURDIANSYAH', 'jalan dolog', 6900000, '2026-04-25 15:03:40', 'bank', '69ecd7cc0d9f6.jpeg', 'lunas', NULL, '6285862030566', 1),
(18, 'FAISAL DWIKI NURDIANSYAH', 'aaaaaa', 3700000, '2026-04-25 15:20:15', 'bank', '69ecdbaf2e207.jpeg', 'ditolak', NULL, '6285862030566', 1),
(20, 'FAISAL DWIKI NURDIANSYAH', 'jalan jalan', 6400000, '2026-04-25 15:48:41', 'bank', '69ece2595413b.png', 'lunas', 'JNT 1233333333', '6285862030566', 1),
(21, 'ABDUL ROHMAN ', 'jalan sehat', 2400000, '2026-05-14 12:07:57', 'qris', '6a05bb1dabc1c.jpg', 'lunas', 'SPX 1212121212121212', '0859999999', 4),
(22, 'FAISAL DWIKI NURDIANSYAH', 'jalan oiiiiiii', 800000, '2026-05-18 10:45:20', 'bank', '6a0aedc059a7c.png', 'pending', NULL, '0899999999999', 1),
(23, 'FAISAL DWIKI NURDIANSYAH', 'jalan oiiiiiii', 3200000, '2026-05-20 10:57:39', 'bank', '6a0d93a315106.png', 'lunas', 'SPX 9999999', '0899999999999', 1);

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
(28, 17, 13, 1, 3200000),
(29, 17, 14, 1, 3700000),
(30, 18, 14, 1, 3700000),
(32, 20, 16, 1, 800000),
(33, 20, 15, 1, 2400000),
(34, 20, 13, 1, 3200000),
(35, 21, 15, 1, 2400000),
(36, 22, 16, 1, 800000),
(37, 23, 16, 1, 800000),
(38, 23, 15, 1, 2400000);

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
(13, 'Iphone 12 basic', 'Apple', '3200000.00', 2, 'second inter', 'ram 6 / 128', 'iPhone', '2026-04-09 07:11:07'),
(14, 'Motorola Edge 60 Fussion', 'Motorola', '3700000.00', 1, 'Bekas - Seperti Baru', '12/256 HP pemakaian pribadi. No minus. Fullset Original. Masih garansi sampai Desember 2026', 'Android', '2026-04-09 13:55:09'),
(15, 'Poco F4 ', 'Xiaomi', '2400000.00', 0, 'Poco F4 \r\nCas + Dos ada lengkap\r\nLecet pemakaian', '8/256', 'Android', '2026-04-09 14:10:25'),
(16, 'Vivo V15', 'Vivo', '800000.00', 0, 'WTS Vivo V15 6/64 - Normal (Nego)\r\nDijual santai pemakaian pribadi.\r\nModel: Vivo V15\r\nKondisi Fisik: Sesuai Foto\r\nFungsi: 100% Normal. Layar jernih, kamera bening, baterai awet, Fingerprint lancar jaya.\r\nKelengkapan: Fullset (Unit, Dus, Kabel, Kitab-kitab).\r\nMinus: LCD retak dan lecet pemakaian\r\nHarga: Rp 800.000 nego\r\nLokasi: Semarang Timur\r\nSistem Transaksi: Lebih diutamakan [COD di rumah saya / di Area Semarang Timur] agar bisa cek sepuasnya.', 'Kapasitas: 6/64gb', 'Android', '2026-04-09 14:27:40');

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
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT untuk tabel `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT untuk tabel `phones`
--
ALTER TABLE `phones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `phone_images`
--
ALTER TABLE `phone_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `phone_images`
--
ALTER TABLE `phone_images`
  ADD CONSTRAINT `phone_images_ibfk_1` FOREIGN KEY (`phone_id`) REFERENCES `phones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
