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
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="login-page">
  <main class="card login-card">
    <h1>ورود به پنل ادمین</h1>
    <p>برای مدیریت سفارش‌ها، Leadها و گفتگوها وارد شوید.</p>
    <?php if ($error): ?><div class="notice" style="background:#fee2e2;border-color:#fecaca;color:#991b1b"><?= e($error) ?></div><?php endif; ?>
    <form method="post" autocomplete="off">
      <label>نام کاربری<input name="username" required autofocus></label>
      <label>رمز عبور<input name="password" type="password" required></label>
      <button class="btn" type="submit">ورود</button>
    </form>
  </main>
</body>
</html>
