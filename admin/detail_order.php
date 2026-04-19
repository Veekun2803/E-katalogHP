<?php
include 'config.php';

$id = intval($_GET['id']);

$items = $conn->query("
    SELECT oi.*, p.nama_hp 
    FROM order_items oi
    JOIN phones p ON p.id = oi.phone_id
    WHERE oi.order_id = $id
");
?>

<table class="w-full border">
    <tr class="bg-gray-200">
        <th>Produk</th>
        <th>Harga</th>
        <th>Qty</th>
        <th>Total</th>
    </tr>

    <?php while ($i = $items->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($i['nama_hp']) ?></td>
        <td>Rp <?= number_format($i['harga'],0,',','.') ?></td>
        <td><?= $i['qty'] ?></td>
        <td>Rp <?= number_format($i['harga']*$i['qty'],0,',','.') ?></td>
    </tr>
    <?php endwhile; ?>
</table>