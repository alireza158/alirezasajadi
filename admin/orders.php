<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();
$orders = read_orders();
$q = mb_strtolower(clean_text($_GET['q'] ?? '', 120), 'UTF-8');
$status = clean_text($_GET['status'] ?? '', 40);
$sort = clean_text($_GET['sort'] ?? 'desc', 10);
$filtered = array_filter($orders, function ($order) use ($q, $status) {
    if ($status !== '' && ($order['status'] ?? '') !== $status) return false;
    return item_matches_query($order, $q, ['name', 'phone', 'tracking_code']);
});
usort($filtered, fn($a,$b) => $sort === 'asc' ? strcmp((string)($a['created_at'] ?? ''), (string)($b['created_at'] ?? '')) : strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
if (isset($_GET['export'])) {
    send_csv('orders.csv', ['tracking_code','name','phone','email','level','goal','amount','status','ref_id','created_at','paid_at'], array_map(fn($o) => [$o['tracking_code'] ?? '',$o['name'] ?? '',$o['phone'] ?? '',$o['email'] ?? '',$o['level'] ?? '',$o['goal'] ?? '',$o['amount'] ?? '',$o['status'] ?? '',$o['ref_id'] ?? '',$o['created_at'] ?? '',$o['paid_at'] ?? ''], $filtered));
}
admin_header('مدیریت سفارش‌ها');
?>
<section class="panel">
  <form class="filters" method="get">
    <input name="q" value="<?= e($q) ?>" placeholder="جستجو بر اساس نام، موبایل، کد پیگیری">
    <select name="status"><option value="">همه وضعیت‌ها</option><?php foreach (['pending','paid','failed','cancelled'] as $s): ?><option value="<?= e($s) ?>" <?= $status===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select>
    <select name="sort"><option value="desc" <?= $sort==='desc'?'selected':'' ?>>جدیدترین</option><option value="asc" <?= $sort==='asc'?'selected':'' ?>>قدیمی‌ترین</option></select>
    <button class="btn" type="submit">اعمال</button>
  </form>
  <div class="panel-head"><h2>سفارش‌ها (<?= count($filtered) ?>)</h2><a class="btn small secondary" href="?<?= e(http_build_query(array_merge($_GET, ['export' => 1]))) ?>">خروجی CSV</a></div>
  <div class="table-wrap"><table><thead><tr><th>کد پیگیری</th><th>نام</th><th>موبایل</th><th>ایمیل</th><th>سطح</th><th>هدف</th><th>مبلغ</th><th>وضعیت</th><th>ref_id</th><th>ثبت</th><th>پرداخت</th><th>جزئیات</th></tr></thead><tbody>
  <?php foreach ($filtered as $i => $order): $rowId='order-'.$i; ?><tr><td><?= e($order['tracking_code'] ?? '-') ?></td><td><?= e($order['name'] ?? '-') ?></td><td><?= e($order['phone'] ?? '-') ?></td><td><?= e($order['email'] ?? '-') ?></td><td><?= e($order['level'] ?? '-') ?></td><td><?= e($order['goal'] ?? '-') ?></td><td><?= e(format_toman($order['amount'] ?? 0)) ?></td><td><span class="badge <?= e($order['status'] ?? 'pending') ?>"><?= e($order['status'] ?? 'pending') ?></span></td><td><?= e($order['ref_id'] ?? '-') ?></td><td><?= e(format_datetime($order['created_at'] ?? '')) ?></td><td><?= e(format_datetime($order['paid_at'] ?? '')) ?></td><td><button class="btn small ghost" data-toggle-details="<?= e($rowId) ?>" type="button">مشاهده</button></td></tr><tr id="<?= e($rowId) ?>" class="details"><td colspan="12"><pre><?= e(json_encode($order, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) ?></pre></td></tr><?php endforeach; ?>
  <?php if (!$filtered): ?><tr><td colspan="12" class="empty">موردی پیدا نشد.</td></tr><?php endif; ?>
  </tbody></table></div>
</section>
<?php admin_footer(); ?>
