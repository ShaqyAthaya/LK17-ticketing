<?php require 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Ticketing Event</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4">
  <span class="navbar-brand fw-bold">🎟️ Ticketing Event</span>
</nav>

<div class="container my-5">
  <h4 class="mb-4">Event Tersedia</h4>
  <div class="row g-4">
    <?php
    $events = $pdo->query("SELECT * FROM events WHERE status = 'active' ORDER BY event_date ASC");
    foreach ($events as $e):
    ?>
    <div class="col-md-4">
      <div class="card h-100 shadow-sm">
        <?php if ($e->banner_image): ?>
          <img src="<?= $e->banner_image ?>" class="card-img-top" style="height:180px;object-fit:cover;">
        <?php else: ?>
          <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:180px;">
            <span>No Image</span>
          </div>
        <?php endif; ?>
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($e->title) ?></h5>
          <p class="text-muted small">📅 <?= date('d M Y, H:i', strtotime($e->event_date)) ?></p>
          <p class="text-muted small">📍 <?= htmlspecialchars($e->location) ?></p>
          <a href="detail.php?id=<?= $e->id ?>" class="btn btn-dark w-100">Lihat Tiket</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

</body>
</html>