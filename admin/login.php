<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-config.php';
admin_start_session();

if (admin_is_authenticated()) {
    redirect('index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean_text($_POST['username'] ?? '', 80);
    $password = (string) ($_POST['password'] ?? '');

    if (verify_admin_credentials($username, $password)) {
        session_regenerate_id(true);
        $_SESSION[ADMIN_SESSION_KEY] = true;
        $_SESSION['admin_username'] = $username;
        redirect('index.php');
    }

    $error = 'نام کاربری یا رمز عبور صحیح نیست.';
}
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ورود ادمین</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-page">
  <main class="login-card card border-0 shadow-lg">
    <div class="login-icon"><i class="bi bi-shield-lock"></i></div>
    <h1>ورود به پنل ادمین</h1>
    <p>مدیریت سفارش‌ها، پرداخت‌ها، لیدها، چت‌های مشاور هوشمند و تنظیمات دوره.</p>
    <?php if ($error): ?>
      <div class="alert alert-danger rounded-4"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" autocomplete="off" novalidate>
      <div class="mb-3">
        <label class="form-label" for="username">نام کاربری</label>
        <input class="form-control form-control-lg" id="username" name="username" required autofocus>
      </div>
      <div class="mb-4">
        <label class="form-label" for="password">رمز عبور</label>
        <input class="form-control form-control-lg" id="password" name="password" type="password" required>
      </div>
      <button class="btn btn-primary btn-lg w-100" type="submit">ورود امن</button>
      <a class="back-home-link" href="../index.html">بازگشت به سایت</a>
    </form>
  </main>
</body>
</html>
