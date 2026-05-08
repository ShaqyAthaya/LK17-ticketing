<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit;
}
require '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title       = trim($_POST['title']);
  $description = trim($_POST['description']);
  $event_date  = $_POST['event_date'];
  $location    = trim($_POST['location']);

  // Simpan event
  $stmt = $pdo->prepare("
    INSERT INTO events (title, description, event_date, location)
    VALUES (?, ?, ?, ?)
  ");
  $stmt->execute([$title, $description, $event_date, $location]);
  $event_id = $pdo->lastInsertId();

  // Simpan jenis tiket (minimal 1)
  $nama_tikets  = $_POST['tiket_nama'];
  $harga_tikets = $_POST['tiket_harga'];
  $quota_tikets = $_POST['tiket_quota'];

  $stmt2 = $pdo->prepare("
    INSERT INTO ticket_types (event_id, name, price, quota)
    VALUES (?, ?, ?, ?)
  ");
  foreach ($nama_tikets as $i => $nama) {
    if (!empty($nama)) {
      $stmt2->execute([$event_id, $nama, $harga_tikets[$i], $quota_tikets[$i]]);
    }
  }

  header("Location: dashboard.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Event</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-4">
  <a class="navbar-brand fw-bold" href="dashboard.php">⚙️ Admin Panel</a>
</nav>

<div class="container my-4" style="max-width:600px;">
  <h5 class="mb-4">Tambah Event Baru</h5>
  <div class="card shadow-sm p-4">
    <form method="POST">

      <div class="mb-3">
        <label class="form-label">Judul Event</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Tanggal & Waktu</label>
        <input type="datetime-local" name="event_date" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Lokasi</label>
        <input type="text" name="location" class="form-control" required>
      </div>

      <hr>
      <h6>Jenis Tiket</h6>

      <div id="tiket-wrapper">
        <div class="row g-2 mb-2 tiket-row">
          <div class="col-4">
            <input type="text" name="tiket_nama[]" class="form-control" placeholder="Nama (cth: VIP)" required>
          </div>
          <div class="col-4">
            <input type="number" name="tiket_harga[]" class="form-control" placeholder="Harga" required>
          </div>
          <div class="col-4">
            <input type="number" name="tiket_quota[]" class="form-control" placeholder="Kuota" required>
          </div>
        </div>
      </div>

      <button type="button" class="btn btn-outline-dark btn-sm mb-3" onclick="tambahTiket()">+ Tambah Jenis Tiket</button>

      <div class="d-grid">
        <button type="submit" class="btn btn-dark">Simpan Event</button>
      </div>
    </form>
  </div>
</div>

<script>
function tambahTiket() {
  const wrapper = document.getElementById('tiket-wrapper');
  const row = document.createElement('div');
  row.className = 'row g-2 mb-2 tiket-row';
  row.innerHTML = `
    <div class="col-4"><input type="text" name="tiket_nama[]" class="form-control" placeholder="Nama"></div>
    <div class="col-4"><input type="number" name="tiket_harga[]" class="form-control" placeholder="Harga"></div>
    <div class="col-4"><input type="number" name="tiket_quota[]" class="form-control" placeholder="Kuota"></div>
  `;
  wrapper.appendChild(row);
}
</script>
</body>
</html>