<?php
require 'koneksi.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND status = 'active'");
$stmt->execute([$id]);
$event = $stmt->fetch();

if (!$event) {
  die("Event tidak ditemukan.");
}

$stmt2 = $pdo->prepare("SELECT * FROM ticket_types WHERE event_id = ?");
$stmt2->execute([$id]);
$tikets = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($event->title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4">
  <a class="navbar-brand fw-bold" href="index.php">🎟️ Ticketing Event</a>
</nav>

<div class="container my-5">
  <div class="card shadow-sm p-4">
    <h3><?= htmlspecialchars($event->title) ?></h3>
    <p class="text-muted">📅 <?= date('d M Y, H:i', strtotime($event->event_date)) ?></p>
    <p class="text-muted">📍 <?= htmlspecialchars($event->location) ?></p>
    <p><?= nl2br(htmlspecialchars($event->description)) ?></p>

    <hr>
    <h5>Pilih Jenis Tiket</h5>
    <div class="row g-3 mt-1">
      <?php foreach ($tikets as $t): ?>
      <?php $sisa = $t->quota - $t->sold_count; ?>
      <div class="col-md-4">
        <div class="card border <?= $sisa <= 0 ? 'border-danger' : 'border-dark' ?>">
          <div class="card-body">
            <h6 class="fw-bold"><?= htmlspecialchars($t->name) ?></h6>
            <p class="mb-1">Rp <?= number_format($t->price, 0, ',', '.') ?></p>
            <p class="small text-muted">Sisa: <?= $sisa ?> tiket</p>
            <?php if ($sisa > 0): ?>
              <a href="pesan.php?tiket=<?= $t->id ?>" class="btn btn-dark btn-sm w-100">Beli</a>
            <?php else: ?>
              <button class="btn btn-danger btn-sm w-100" disabled>Habis</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

</body>
</html>