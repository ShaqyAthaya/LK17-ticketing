<?php
require 'koneksi.php';

$kode = $_GET['kode'] ?? '';
if (!$kode) die("Kode booking tidak ditemukan.");

$stmt = $pdo->prepare("
  SELECT o.*, tt.name as jenis_tiket, tt.price,
        e.title as nama_event, e.event_date, e.location
  FROM orders o
  JOIN ticket_types tt ON o.ticket_type_id = tt.id
  JOIN events e ON tt.event_id = e.id
  WHERE o.booking_code = ?
");
$stmt->execute([$kode]);
$order = $stmt->fetch();

if (!$order) die("Data pesanan tidak ditemukan.");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pesanan Berhasil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4">
  <a class="navbar-brand fw-bold" href="index.php">🎟️ Ticketing Event</a>
</nav>

<div class="container my-5">
  <div class="card shadow-sm p-4 text-center" style="max-width:500px;margin:auto;">
    <div class="display-4 mb-2">✅</div>
    <h4 class="fw-bold mb-1">Pesanan Berhasil!</h4>
    <p class="text-muted mb-4">Simpan kode booking kamu di bawah ini</p>

    <div class="alert alert-dark fs-5 fw-bold letter-spacing-1">
      <?= htmlspecialchars($order->booking_code) ?>
    </div>

    <table class="table table-bordered text-start mt-3">
      <tr><th>Event</th><td><?= htmlspecialchars($order->nama_event) ?></td></tr>
      <tr><th>Tanggal</th><td><?= date('d M Y, H:i', strtotime($order->event_date)) ?></td></tr>
      <tr><th>Lokasi</th><td><?= htmlspecialchars($order->location) ?></td></tr>
      <tr><th>Jenis Tiket</th><td><?= htmlspecialchars($order->jenis_tiket) ?></td></tr>
      <tr><th>Nama</th><td><?= htmlspecialchars($order->buyer_name) ?></td></tr>
      <tr><th>Email</th><td><?= htmlspecialchars($order->buyer_email) ?></td></tr>
      <tr><th>Jumlah</th><td><?= $order->quantity ?> tiket</td></tr>
      <tr><th>Total</th><td>Rp <?= number_format($order->total_price, 0, ',', '.') ?></td></tr>
    </table>

    <a href="index.php" class="btn btn-dark w-100 mt-2">Kembali ke Beranda</a>
  </div>
</div>

</body>
</html>