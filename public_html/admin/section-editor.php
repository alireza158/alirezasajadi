<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();

$configs = landing_section_configs();
$section = clean_text($_GET['section'] ?? 'features', 40);
if (!isset($configs[$section])) {
    http_response_code(404);
    exit('سکشن پیدا نشد.');
}
$config = $configs[$section];
$content = read_landing_content();
$items = $content[$section] ?? [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $action = clean_text($_POST['action'] ?? 'save', 20);
    $id = clean_text($_POST['id'] ?? '', 80);
    if ($action === 'delete') {
        $content[$section] = array_values(array_filter($items, fn($item) => ($item['id'] ?? '') !== $id));
        write_landing_content($content);
        $_SESSION['flash'] = 'آیتم حذف شد.';
        redirect('section-editor.php?section=' . urlencode($section));
    }

    $image = clean_text($_POST['existing_image'] ?? '', 500);
    if (!empty($_FILES['image_file'])) {
        $uploaded = save_uploaded_media($_FILES['image_file'], $errors);
        if ($uploaded !== '') $image = $uploaded;
    }
    $item = [
        'id' => $id ?: ('item-' . bin2hex(random_bytes(5))),
        'title' => clean_text($_POST['title'] ?? '', 240),
        'description' => clean_text($_POST['description'] ?? '', 2000),
        'icon' => clean_text($_POST['icon'] ?? '', 80),
        'image' => $image,
        'link' => clean_text($_POST['link'] ?? '', 500),
        'button_text' => clean_text($_POST['button_text'] ?? '', 120),
        'tags' => array_values(array_filter(array_map(fn($v) => clean_text($v, 60), preg_split('/[,،\n]+/u', (string)($_POST['tags'] ?? '')) ?: []))),
        'lessons' => array_values(array_filter(array_map(fn($v) => clean_text($v, 220), preg_split('/\R/u', (string)($_POST['lessons'] ?? '')) ?: []))),
        'duration' => clean_text($_POST['duration'] ?? '', 80),
        'subtitle' => clean_text($_POST['subtitle'] ?? '', 180),
        'rating' => clean_text($_POST['rating'] ?? '', 20),
        'category' => clean_text($_POST['category'] ?? '', 120),
        'sort_order' => (int) clean_text($_POST['sort_order'] ?? '0', 20),
        'status' => in_array(($_POST['status'] ?? 'active'), ['active', 'inactive'], true) ? $_POST['status'] : 'active',
    ];
    if ($item['title'] === '') $errors[] = 'عنوان اجباری است.';
    if (!$errors) {
        $found = false;
        foreach ($items as $i => $old) {
            if (($old['id'] ?? '') === $item['id']) { $items[$i] = $item; $found = true; break; }
        }
        if (!$found) $items[] = $item;
        $content[$section] = $items;
        write_landing_content($content);
        $_SESSION['flash'] = 'آیتم با موفقیت ذخیره شد.';
        redirect('section-editor.php?section=' . urlencode($section));
    } else {
        $_SESSION['flash_error'] = implode(' ', $errors);
    }
}

$editId = clean_text($_GET['edit'] ?? '', 80);
$editing = null;
foreach ($items as $item) if (($item['id'] ?? '') === $editId) $editing = $item;
$empty = ['id'=>'','title'=>'','description'=>'','icon'=>'','image'=>'','link'=>'','button_text'=>'','tags'=>[],'lessons'=>[],'duration'=>'','subtitle'=>'','rating'=>'','category'=>'','sort_order'=>count($items)+1,'status'=>'active'];
$form = array_merge($empty, $editing ?: []);
admin_header('مدیریت ' . $config['label']);
?>
<div class="row g-4">
  <div class="col-12 col-xl-5">
    <section class="admin-card h-100">
      <div class="section-head"><h2><?= e($editing ? 'ویرایش آیتم' : 'افزودن آیتم جدید') ?></h2><a href="section-editor.php?section=<?= e($section) ?>">فرم جدید</a></div>
      <form method="post" enctype="multipart/form-data" class="row g-3 settings-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= e($form['id']) ?>">
        <input type="hidden" name="existing_image" value="<?= e($form['image']) ?>">
        <div class="col-12"><label class="form-label">عنوان</label><input class="form-control" name="title" value="<?= e($form['title']) ?>" required></div>
        <div class="col-12"><label class="form-label">توضیح / پاسخ</label><textarea class="form-control" name="description" rows="4"><?= e($form['description']) ?></textarea></div>
        <div class="col-12 col-md-6"><label class="form-label">آیکون / شماره</label><input class="form-control" name="icon" value="<?= e($form['icon']) ?>"></div>
        <div class="col-12 col-md-6"><label class="form-label">ترتیب نمایش</label><input class="form-control ltr" name="sort_order" inputmode="numeric" value="<?= e($form['sort_order']) ?>"></div>
        <div class="col-12"><label class="form-label">تصویر</label><input class="form-control" type="file" name="image_file" accept="image/*"><?php if ($form['image']): ?><img class="admin-thumb mt-2" src="../<?= e(ltrim((string)$form['image'], './')) ?>" alt=""><?php endif; ?></div>
        <div class="col-12 col-md-6"><label class="form-label">متن دکمه</label><input class="form-control" name="button_text" value="<?= e($form['button_text']) ?>"></div>
        <div class="col-12 col-md-6"><label class="form-label">لینک</label><input class="form-control ltr" name="link" value="<?= e($form['link']) ?>"></div>
        <div class="col-12"><label class="form-label">تگ‌ها (با کاما یا خط جدید)</label><textarea class="form-control" name="tags" rows="2"><?= e(implode("\n", (array)$form['tags'])) ?></textarea></div>
        <div class="col-12"><label class="form-label">لیست درس‌ها (هر درس یک خط)</label><textarea class="form-control" name="lessons" rows="3"><?= e(implode("\n", (array)$form['lessons'])) ?></textarea></div>
        <div class="col-12 col-md-4"><label class="form-label">مدت/زیرعنوان</label><input class="form-control" name="duration" value="<?= e($form['duration']) ?>"></div>
        <div class="col-12 col-md-4"><label class="form-label">سمت هنرجو</label><input class="form-control" name="subtitle" value="<?= e($form['subtitle']) ?>"></div>
        <div class="col-12 col-md-4"><label class="form-label">امتیاز/دسته</label><input class="form-control" name="rating" value="<?= e($form['rating']) ?>"></div>
        <div class="col-12 col-md-6"><label class="form-label">دسته‌بندی FAQ</label><input class="form-control" name="category" value="<?= e($form['category']) ?>"></div>
        <div class="col-12 col-md-6"><label class="form-label">وضعیت</label><select class="form-select" name="status"><option value="active" <?= $form['status']==='active'?'selected':'' ?>>فعال</option><option value="inactive" <?= $form['status']==='inactive'?'selected':'' ?>>غیرفعال</option></select></div>
        <div class="col-12"><button class="btn btn-primary btn-lg" type="submit"><i class="bi bi-save"></i> ذخیره</button></div>
      </form>
    </section>
  </div>
  <div class="col-12 col-xl-7">
    <section class="admin-card h-100">
      <div class="section-head"><h2><?= e($config['label']) ?> <span><?= e(count($items)) ?></span></h2><a href="../index.php" target="_blank">مشاهده سایت</a></div>
      <div class="table-responsive"><table class="table align-middle admin-table"><thead><tr><th>ترتیب</th><th>تصویر</th><th>عنوان</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
        <?php foreach ($items as $item): ?>
          <tr><td><?= e($item['sort_order'] ?? '') ?></td><td><?php if (!empty($item['image'])): ?><img class="admin-thumb" src="../<?= e(ltrim((string)$item['image'], './')) ?>" alt=""><?php else: ?>- <?php endif; ?></td><td><strong><?= e($item['title'] ?? '-') ?></strong><small class="d-block text-muted"><?= e(mb_substr((string)($item['description'] ?? ''), 0, 90)) ?></small></td><td><?= render_status_badge((string)($item['status'] ?? 'active')) ?></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="?section=<?= e($section) ?>&edit=<?= e($item['id']) ?>">ویرایش</a><form method="post" class="d-inline" onsubmit="return confirm('حذف شود؟')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($item['id']) ?>"><button class="btn btn-sm btn-outline-danger">حذف</button></form></td></tr>
        <?php endforeach; ?>
        <?php if (!$items): ?><tr><td colspan="5" class="empty-state">هنوز آیتمی ثبت نشده است.</td></tr><?php endif; ?>
      </tbody></table></div>
    </section>
  </div>
</div>
<?php admin_footer(); ?>
