<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    write_settings([
        'course_title' => clean_text($_POST['course_title'] ?? '', 200),
        'original_price' => (int) clean_text($_POST['original_price'] ?? '0', 30),
        'discount_price' => (int) clean_text($_POST['discount_price'] ?? '0', 30),
        'registration_enabled' => isset($_POST['registration_enabled']),
        'discount_badge' => clean_text($_POST['discount_badge'] ?? '', 200),
        'support_phone' => clean_text($_POST['support_phone'] ?? '', 60),
        'site_return_url' => clean_text($_POST['site_return_url'] ?? '', 300),
    ]);
    $_SESSION['flash'] = 'تنظیمات ذخیره شد.';
    redirect('settings.php');
}
$settings = read_settings();
admin_header('تنظیمات دوره'); flash_message();
?>
<section class="panel"><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><label class="full">عنوان دوره<input name="course_title" value="<?= e($settings['course_title']) ?>"></label><label>قیمت اصلی (تومان)<input name="original_price" value="<?= e($settings['original_price']) ?>" inputmode="numeric"></label><label>قیمت تخفیفی (تومان)<input name="discount_price" value="<?= e($settings['discount_price']) ?>" inputmode="numeric"></label><label>متن badge تخفیف<input name="discount_badge" value="<?= e($settings['discount_badge']) ?>"></label><label>شماره تماس پشتیبانی<input name="support_phone" value="<?= e($settings['support_phone']) ?>"></label><label class="full">لینک بازگشت به سایت<input name="site_return_url" value="<?= e($settings['site_return_url']) ?>"></label><label><span><input type="checkbox" name="registration_enabled" <?= !empty($settings['registration_enabled'])?'checked':'' ?>> ثبت‌نام فعال باشد</span></label><div class="full"><button class="btn" type="submit">ذخیره تنظیمات</button></div></form></section>
<?php admin_footer(); ?>
