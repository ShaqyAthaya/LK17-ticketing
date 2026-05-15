<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit;
}
require '../koneksi.php';

$events = $pdo->query("SELECT * FROM events ORDER BY event_date DESC");
$orders = $pdo->query("
  SELECT o.*, tt.name as jenis_tiket, e.title as nama_event
  FROM orders o
  JOIN ticket_types tt ON o.ticket_type_id = tt.id
  JOIN events e ON tt.event_id = e.id
  ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4 d-flex justify-content-between">
  <span class="navbar-brand fw-bold">⚙️ Admin Panel</span>
  <div>
    <a href="../index.php" class="btn btn-outline-light btn-sm me-2">Lihat Website</a>
    <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
  </div>
</nav>

<div class="container my-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Daftar Event</h5>
    <a href="event_tambah.php" class="btn btn-dark btn-sm">+ Tambah Event</a>
  </div>
  <div class="card shadow-sm mb-5">
    <table class="table table-hover mb-0">
      <thead class="table-dark">
        <tr>
          <th>#</th><th>Judul</th><th>Tanggal</th><th>Lokasi</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no=1; foreach ($events as $e): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($e->title) ?></td>
          <td><?= date('d M Y', strtotime($e->event_date)) ?></td>
          <td><?= htmlspecialchars($e->location) ?></td>
          <td>
            <span class="badge <?= $e->status === 'active' ? 'bg-success' : 'bg-secondary' ?>">
              <?= $e->status ?>
            </span>
          </td>
          <td>
            <a href="event_hapus.php?id=<?= $e->id ?>" class="btn btn-danger btn-sm"
              onclick="return confirm('Hapus event ini?')">Hapus</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <h5 class="mb-3">Daftar Pemesan</h5>
  <div class="card shadow-sm">
    <table class="table table-hover mb-0">
      <thead class="table-dark">
        <tr>
          <th>#</th><th>Kode Booking</th><th>Nama</th><th>Email</th>
          <th>Event</th><th>Tiket</th><th>Qty</th><th>Total</th><th>Tanggal</th>
        </tr>
      </thead>
      <tbody>
        <?php $no=1; foreach ($orders as $o): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><strong><?= $o->booking_code ?></strong></td>
          <td><?= htmlspecialchars($o->buyer_name) ?></td>
          <td><?= htmlspecialchars($o->buyer_email) ?></td>
          <td><?= htmlspecialchars($o->nama_event) ?></td>
          <td><?= htmlspecialchars($o->jenis_tiket) ?></td>
          <td><?= $o->quantity ?></td>
          <td>Rp <?= number_format($o->total_price, 0, ',', '.') ?></td>
          <td><?= date('d M Y', strtotime($o->created_at)) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>