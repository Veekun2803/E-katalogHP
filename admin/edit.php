<?php
session_start();
include 'config.php';

/*
|--------------------------------------------------------------------------
| CEK LOGIN ADMIN
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = intval($_GET['id']);

// =======================
// PROSES UPDATE (PINDAH KE ATAS)
// =======================
if (isset($_POST['update'])) {

    $nama  = $_POST['nama_hp'];
    $brand = $_POST['brand'];
    $harga = $_POST['harga'];
    $stok  = $_POST['stok'];
    $desk  = $_POST['deskripsi'];
    $spec  = $_POST['spesifikasi'];
    $kat   = $_POST['kategori'];

    $stmt = $conn->prepare("UPDATE phones SET 
        nama_hp=?, brand=?, harga=?, stok=?, deskripsi=?, spesifikasi=?, kategori=? 
        WHERE id=?");

    $stmt->bind_param("ssdisssi", 
        $nama, $brand, $harga, $stok, $desk, $spec, $kat, $id
    );
    $stmt->execute();

    // upload gambar
    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (!empty($_FILES['images']['name'][0])) {

        $allowed = ['jpg','jpeg','png'];

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {

            $file_name = $_FILES['images']['name'][$key];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) continue;

            $new_name = uniqid() . '.' . $ext;

            if (move_uploaded_file($tmp_name, $uploadDir . $new_name)) {

                $stmtImg = $conn->prepare("INSERT INTO phone_images (phone_id, image) VALUES (?, ?)");
                $stmtImg->bind_param("is", $id, $new_name);
                $stmtImg->execute();
            }
        }
    }

    header("Location: edit.php?id=$id&success=1");
    exit;
}

// =======================
// AMBIL DATA
// =======================
$stmt = $conn->prepare("SELECT * FROM phones WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    die("Data tidak ditemukan");
}

// gambar
$stmtImg = $conn->prepare("SELECT * FROM phone_images WHERE phone_id=?");
$stmtImg->bind_param("i", $id);
$stmtImg->execute();
$resultImages = $stmtImg->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit HP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 p-6">

<div class="max-w-4xl mx-auto bg-white p-6 rounded-xl shadow">

    <h2 class="text-2xl font-bold mb-6">Edit Data HP</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            ✅ Data berhasil diupdate!
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">

        <input type="text" name="nama_hp" required
            value="<?= htmlspecialchars($data['nama_hp']) ?>"
            class="w-full border p-2 rounded">

        <input type="text" name="brand"
            value="<?= htmlspecialchars($data['brand']) ?>"
            class="w-full border p-2 rounded">

        <div class="grid grid-cols-2 gap-4">
            <input type="number" name="harga" value="<?= $data['harga'] ?>" class="border p-2 rounded">
            <input type="number" name="stok" value="<?= $data['stok'] ?>" class="border p-2 rounded">
        </div>

        <textarea name="deskripsi" class="w-full border p-2 rounded"><?= htmlspecialchars($data['deskripsi']) ?></textarea>

        <textarea name="spesifikasi" class="w-full border p-2 rounded"><?= htmlspecialchars($data['spesifikasi']) ?></textarea>

        <select name="kategori" class="w-full border p-2 rounded">
            <option value="Android" <?= $data['kategori']=='Android'?'selected':'' ?>>Android</option>
            <option value="iPhone" <?= $data['kategori']=='iPhone'?'selected':'' ?>>iPhone</option>
        </select>

        <!-- GAMBAR -->
        <div>
            <p class="font-semibold mb-2">Gambar Lama</p>

            <div class="flex flex-wrap gap-4">
                <?php while ($img = $resultImages->fetch_assoc()): ?>
                    <div class="text-center">
                        <img src="uploads/<?= $img['image'] ?>" class="w-24 h-24 object-cover rounded mb-1">
                        <a href="hapus_gambar.php?id=<?= $img['id'] ?>&phone_id=<?= $id ?>"
                           class="text-red-500 text-sm">Hapus</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <input type="file" name="images[]" multiple class="w-full border p-2 rounded">

        <div class="flex gap-2">
            <button name="update" class="bg-blue-500 text-white px-4 py-2 rounded">
                Update
            </button>

            <a href="index.php" class="bg-gray-400 text-white px-4 py-2 rounded">
                Kembali
            </a>
        </div>

    </form>
</div>

</body>
</html>