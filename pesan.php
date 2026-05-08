<?php
require 'koneksi.php';

$tiket_id = $_GET['tiket'] ?? 0;

$stmt = $pdo->prepare("
  SELECT tt.*, e.title as nama_event, e.event_date, e.location
  FROM ticket_types tt
  JOIN events e ON tt.event_id = e.id
  WHERE tt.id = ?
");
$stmt->execute([$tiket_id]);
$tiket = $stmt->fetch();

if (!$tiket) {
  die("Tiket tidak ditemukan.");
}

$sisa = $tiket->quota - $tiket->sold_count;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Form Pemesanan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4">
  <a class="navbar-brand fw-bold" href="index.php">🎟️ Ticketing Event</a>
</nav>

<div class="container my-5">
  <div class="card shadow-sm p-4" style="max-width:500px;margin:auto;">
    <h5 class="mb-1">Form Pemesanan</h5>
    <p class="text-muted small mb-3"><?= htmlspecialchars($tiket->nama_event) ?> — <?= htmlspecialchars($tiket->name) ?></p>

    <div class="alert alert-secondary small">
      💰 Harga: <strong>Rp <?= number_format($tiket->price, 0, ',', '.') ?></strong> per tiket &nbsp;|&nbsp;
      Sisa: <strong><?= $sisa ?></strong> tiket
    </div>

    <form method="POST" action="proses_pesan.php">
      <input type="hidden" name="ticket_type_id" value="<?= $tiket->id ?>">

      <div class="mb-3">
        <label class="form-label">Nama Lengkap</label>
        <input type="text" name="buyer_name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="buyer_email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Jumlah Tiket</label>
        <input type="number" name="quantity" class="form-control" min="1" max="<?= $sisa ?>" value="1" required>
      </div>

      <button type="submit" class="btn btn-dark w-100">Pesan Sekarang</button>
    </form>
  </div>
</div>

</body>
</html>