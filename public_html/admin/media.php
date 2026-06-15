<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    if (($_POST['action'] ?? '') === 'delete') {
        $file = basename(clean_text($_POST['file'] ?? '', 200));
        $path = __DIR__ . '/../uploads/' . $file;
        if ($file && is_file($path)) unlink($path);
        $_SESSION['flash'] = 'فایل حذف شد.';
        redirect('media.php');
    }
    $uploaded = !empty($_FILES['media_file']) ? save_uploaded_media($_FILES['media_file'], $errors) : '';
    if ($uploaded) { $_SESSION['flash'] = 'فایل با موفقیت آپلود شد.'; redirect('media.php'); }
    $_SESSION['flash_error'] = $errors ? implode(' ', $errors) : 'فایلی برای آپلود انتخاب نشده است.';
}
$uploadDir = __DIR__ . '/../uploads';
$files = is_dir($uploadDir) ? array_values(array_filter(scandir($uploadDir) ?: [], fn($file) => !in_array($file, ['.', '..'], true) && is_file($uploadDir . '/' . $file))) : [];
rsort($files);
admin_header('مدیریت رسانه‌ها');
?>
<section class="admin-card mb-4">
  <div class="section-head"><h2>آپلود تصویر جدید</h2></div>
  <form method="post" enctype="multipart/form-data" class="row g-3 align-items-end">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="col-12 col-lg-8"><label class="form-label">انتخاب تصویر</label><input class="form-control" type="file" name="media_file" accept="image/*" required></div>
    <div class="col-12 col-lg-4"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-cloud-upload"></i> آپلود</button></div>
  </form>
</section>
<section class="admin-card">
  <div class="section-head"><h2>کتابخانه رسانه <span><?= e(count($files)) ?></span></h2></div>
  <div class="row g-3">
    <?php foreach ($files as $file): $url = '../uploads/' . $file; $public = './uploads/' . $file; ?>
      <div class="col-12 col-md-6 col-xl-3"><div class="detail-grid"><div><img class="w-100 admin-media-preview" src="<?= e($url) ?>" alt=""><strong class="mt-2 d-block ltr"><?= e($public) ?></strong><form method="post" class="mt-2" onsubmit="return confirm('حذف شود؟')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="file" value="<?= e($file) ?>"><button class="btn btn-sm btn-outline-danger w-100">حذف</button></form></div></div></div>
    <?php endforeach; ?>
    <?php if (!$files): ?><div class="col-12 empty-state">هنوز تصویری آپلود نشده است.</div><?php endif; ?>
  </div>
</section>
<?php admin_footer(); ?>
