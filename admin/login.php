<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center" style="min-height:100vh;">

<div class="card p-4 shadow" style="width:350px;">
  <h5 class="text-center fw-bold mb-3">🔐 Login Admin</h5>

  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger small">Username atau password salah.</div>
  <?php endif; ?>

  <form method="POST" action="proses_login.php">
    <div class="mb-3">
      <label class="form-label">Username</label>
      <input type="text" name="username" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-dark w-100">Masuk</button>
  </form>
</div>

</body>
</html>