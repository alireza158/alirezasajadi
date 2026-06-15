<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();

$labels = [
    'features' => 'مهارت‌های عملی',
    'projects' => 'پروژه‌ها',
    'audience' => 'مخاطبان دوره',
    'curriculum' => 'سرفصل‌ها',
    'results' => 'نتایج دوره',
    'testimonials' => 'نظرات هنرجوها',
    'faqs' => 'سوالات متداول',
];
$content = read_landing_content();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $next = [];
    foreach ($labels as $key => $label) {
        $next[$key] = decode_admin_json_field((string) ($_POST[$key] ?? '[]'), $label, $errors);
    }
    if (!$errors && write_landing_content($next)) {
        $_SESSION['flash'] = 'محتوای داینامیک صفحه اصلی با موفقیت ذخیره شد.';
        redirect('content.php');
    }
    $_SESSION['flash_error'] = $errors ? implode(' ', $errors) : 'ذخیره محتوا با خطا روبه‌رو شد.';
    $content = $next ?: $content;
}
admin_header('محتوای صفحه اصلی');
?>
<section class="admin-card">
  <div class="section-head"><h2>مدیریت داده‌های داینامیک سکشن‌ها</h2><a href="../index.php" target="_blank">مشاهده سایت</a></div>
  <p class="text-muted">هر بخش به‌صورت JSON ذخیره می‌شود و در فرانت از <code>window.LANDING_DATA</code> به فایل JavaScript تزریق می‌شود؛ بنابراین آرایه‌های ثابت از JS حذف شده‌اند.</p>
  <form method="post" class="row g-4 settings-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <?php foreach ($labels as $key => $label): ?>
      <div class="col-12">
        <label class="form-label"><?= e($label) ?></label>
        <textarea class="form-control ltr" dir="ltr" name="<?= e($key) ?>" rows="8"><?= e(json_encode($content[$key] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) ?></textarea>
      </div>
    <?php endforeach; ?>
    <div class="col-12"><button class="btn btn-primary btn-lg" type="submit"><i class="bi bi-save"></i> ذخیره محتوا</button></div>
  </form>
</section>
<?php admin_footer(); ?>
