<?php
require 'koneksi.php';

$ticket_type_id = $_POST['ticket_type_id'] ?? 0;
$buyer_name     = trim($_POST['buyer_name'] ?? '');
$buyer_email    = trim($_POST['buyer_email'] ?? '');
$quantity       = (int)($_POST['quantity'] ?? 0);

// Validasi input
if (!$ticket_type_id || !$buyer_name || !$buyer_email || $quantity < 1) {
  die("Data tidak lengkap.");
}

// Ambil data tiket
$stmt = $pdo->prepare("SELECT * FROM ticket_types WHERE id = ?");
$stmt->execute([$ticket_type_id]);
$tiket = $stmt->fetch();

if (!$tiket) die("Tiket tidak ditemukan.");

// Cek kuota
$sisa = $tiket->quota - $tiket->sold_count;
if ($quantity > $sisa) {
  die("Maaf, kuota tidak mencukupi. Sisa tiket: $sisa");
}

// Hitung total & buat kode booking
$total        = $tiket->price * $quantity;
$booking_code = 'TIX-' . strtoupper(substr(uniqid(), -6));

// Simpan ke database dengan transaction
try {
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("
    INSERT INTO orders (ticket_type_id, buyer_name, buyer_email, quantity, total_price, booking_code)
    VALUES (?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([$ticket_type_id, $buyer_name, $buyer_email, $quantity, $total, $booking_code]);

  $pdo->prepare("
    UPDATE ticket_types SET sold_count = sold_count + ? WHERE id = ?
  ")->execute([$quantity, $ticket_type_id]);

  $pdo->commit();

  header("Location: sukses.php?kode=$booking_code");
  exit;

} catch (Exception $e) {
  $pdo->rollBack();
  die("Terjadi kesalahan, silakan coba lagi.");
}
?>