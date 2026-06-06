<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $saved = write_settings([
        'course_title' => clean_text($_POST['course_title'] ?? '', 200),
        'instructor' => clean_text($_POST['instructor'] ?? '', 160),
        'original_price' => (int) clean_text($_POST['original_price'] ?? '0', 30),
        'discount_price' => (int) clean_text($_POST['discount_price'] ?? '0', 30),
        'payment_amount' => (int) clean_text($_POST['payment_amount'] ?? '0', 30),
        'registration_enabled' => isset($_POST['registration_enabled']),
        'discount_badge' => clean_text($_POST['discount_badge'] ?? '', 200),
        'support_phone' => clean_text($_POST['support_phone'] ?? '', 80),
        'site_return_url' => clean_url($_POST['site_return_url'] ?? '') ?: DEFAULT_SETTINGS['site_return_url'],
        'merchant_id' => clean_text($_POST['merchant_id'] ?? '', 120),
        'callback_url' => clean_url($_POST['callback_url'] ?? '') ?: DEFAULT_SETTINGS['callback_url'],
    ]);
    $_SESSION[$saved ? 'flash' : 'flash_error'] = $saved ? 'تنظیمات دوره با موفقیت ذخیره شد.' : 'ذخیره تنظیمات با خطا روبه‌رو شد.';
    redirect('settings.php');
}
$settings = read_settings();
admin_header('تنظیمات دوره');
?>
<section class="admin-card">
  <div class="section-head"><h2>ویرایش تنظیمات اصلی دوره</h2></div>
  <form method="post" class="row g-4 settings-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <div class="col-12 col-lg-8"><label class="form-label">عنوان دوره</label><input class="form-control" name="course_title" value="<?= e($settings['course_title']) ?>" required></div>
    <div class="col-12 col-lg-4"><label class="form-label">مدرس</label><input class="form-control" name="instructor" value="<?= e($settings['instructor']) ?>"></div>
    <div class="col-12 col-md-4"><label class="form-label">قیمت اصلی (تومان)</label><input class="form-control ltr" name="original_price" value="<?= e($settings['original_price']) ?>" inputmode="numeric"></div>
    <div class="col-12 col-md-4"><label class="form-label">قیمت تخفیفی (تومان)</label><input class="form-control ltr" name="discount_price" value="<?= e($settings['discount_price']) ?>" inputmode="numeric"></div>
    <div class="col-12 col-md-4"><label class="form-label">مبلغ پرداخت به ریال</label><input class="form-control ltr" name="payment_amount" value="<?= e($settings['payment_amount']) ?>" inputmode="numeric"></div>
    <div class="col-12 col-md-6"><label class="form-label">متن badge تخفیف</label><input class="form-control" name="discount_badge" value="<?= e($settings['discount_badge']) ?>"></div>
    <div class="col-12 col-md-6"><label class="form-label">شماره تماس پشتیبانی</label><input class="form-control ltr" name="support_phone" value="<?= e($settings['support_phone']) ?>"></div>
    <div class="col-12 col-md-6"><label class="form-label">لینک بازگشت به سایت</label><input class="form-control ltr" name="site_return_url" value="<?= e($settings['site_return_url']) ?>"></div>
    <div class="col-12 col-md-6"><label class="form-label">Callback URL</label><input class="form-control ltr" name="callback_url" value="<?= e($settings['callback_url']) ?>"></div>
    <div class="col-12"><label class="form-label">Merchant ID زرین‌پال</label><input class="form-control ltr" name="merchant_id" value="<?= e($settings['merchant_id']) ?>"></div>
    <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="registration_enabled" name="registration_enabled" <?= !empty($settings['registration_enabled']) ? 'checked' : '' ?>><label class="form-check-label" for="registration_enabled">ثبت‌نام دوره فعال باشد</label></div></div>
    <div class="col-12"><button class="btn btn-primary btn-lg" type="submit"><i class="bi bi-save"></i> ذخیره تنظیمات</button></div>
  </form>
</section>
<?php admin_footer(); ?>
