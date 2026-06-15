<?php
declare(strict_types=1);
require_once __DIR__ . '/admin-config.php';

function admin_header(string $title): void
{
    $current = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    $settings = read_settings();
    $items = [
        'index.php' => ['label' => 'داشبورد', 'icon' => 'bi-speedometer2'],
        'orders.php' => ['label' => 'سفارش‌ها', 'icon' => 'bi-receipt-cutoff'],
        'leads.php' => ['label' => 'لیدها', 'icon' => 'bi-person-lines-fill'],
        'chats.php' => ['label' => 'چت‌ها', 'icon' => 'bi-chat-dots'],
        'settings.php' => ['label' => 'تنظیمات', 'icon' => 'bi-gear'],
        'content.php' => ['label' => 'محتوای صفحه', 'icon' => 'bi-layout-text-window'],
    ];
    ?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> | پنل ادمین</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
  <div class="admin-shell">
    <aside class="admin-sidebar offcanvas-lg offcanvas-end" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
      <div class="offcanvas-header d-lg-none">
        <h5 class="offcanvas-title" id="adminSidebarLabel">پنل مدیریت</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="بستن"></button>
      </div>
      <div class="offcanvas-body d-flex flex-column p-0">
        <div class="brand-box">
          <span class="brand-mark">AI</span>
          <div>
            <strong>پنل مدیریت دوره</strong>
            <small><?= e($settings['course_title']) ?></small>
          </div>
        </div>
        <nav class="admin-nav" aria-label="منوی پنل">
          <?php foreach ($items as $href => $item): ?>
            <a class="<?= $current === $href ? 'active' : '' ?>" href="<?= e($href) ?>">
              <i class="bi <?= e($item['icon']) ?>"></i><span><?= e($item['label']) ?></span>
            </a>
          <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">
          <a class="logout-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> خروج امن</a>
        </div>
      </div>
    </aside>

    <main class="admin-main">
      <header class="topbar">
        <button class="btn btn-light d-lg-none menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
          <i class="bi bi-list"></i>
        </button>
        <div class="min-w-0">
          <p class="page-kicker">مدیریت <?= e($settings['course_title']) ?></p>
          <h1><?= e($title) ?></h1>
        </div>
        <a class="btn btn-outline-primary site-link" href="../index.php"><i class="bi bi-house"></i> بازگشت به سایت</a>
      </header>
      <?php flash_message(); ?>
<?php }

function admin_footer(): void
{ ?>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/admin.js"></script>
</body>
</html>
<?php }

function flash_message(): void
{
    admin_start_session();
    if (!empty($_SESSION['flash'])) {
        echo '<div class="alert alert-success border-0 shadow-sm rounded-4">' . e($_SESSION['flash']) . '</div>';
        unset($_SESSION['flash']);
    }
    if (!empty($_SESSION['flash_error'])) {
        echo '<div class="alert alert-danger border-0 shadow-sm rounded-4">' . e($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
}

function render_status_badge(string $status): string
{
    return '<span class="status-badge ' . e($status) . '">' . e(status_label($status)) . '</span>';
}
