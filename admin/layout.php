<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-config.php';

function admin_header(string $title): void
{
    $current = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    $items = [
        'index.php' => 'داشبورد',
        'orders.php' => 'سفارش‌ها',
        'leads.php' => 'Leadها',
        'chats.php' => 'چت‌ها',
        'settings.php' => 'تنظیمات',
        'logout.php' => 'خروج',
    ];
    ?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> | پنل ادمین</title>
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
  <div class="admin-shell">
    <aside class="sidebar">
      <div class="brand">
        <span>AI</span>
        <strong>پنل مدیریت دوره</strong>
      </div>
      <nav>
        <?php foreach ($items as $href => $label): ?>
          <a class="<?= $current === $href ? 'active' : '' ?>" href="<?= e($href) ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>
    </aside>
    <main class="main">
      <header class="topbar">
        <div>
          <p>آموزش طراحی سایت با هوش مصنوعی</p>
          <h1><?= e($title) ?></h1>
        </div>
        <a class="site-link" href="../index.html">بازگشت به سایت</a>
      </header>
<?php }

function admin_footer(): void
{ ?>
    </main>
  </div>
  <script src="assets/admin.js"></script>
</body>
</html>
<?php }

function flash_message(): void
{
    admin_start_session();
    if (!empty($_SESSION['flash'])) {
        echo '<div class="notice">' . e($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
}
