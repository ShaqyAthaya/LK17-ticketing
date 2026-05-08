<?php
session_start();
require '../koneksi.php';

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if ($admin && $password === $admin->password) {
  $_SESSION['admin'] = $admin->username;
  header("Location: dashboard.php");
  exit;
} else {
  header("Location: login.php?error=1");
  exit;
}
?>