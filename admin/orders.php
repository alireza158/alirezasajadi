<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();

$orders = read_orders();
$q = mb_strtolower(clean_text($_GET['q'] ?? '', 120), 'UTF-8');
$status = clean_text($_GET['status'] ?? '', 40);
$statuses = ['paid', 'pending', 'failed', 'cancelled'];
$filtered = array_filter($orders, function ($order) use ($q, $status) {
    if ($status !== '' && ($order['status'] ?? '') !== $status) return false;
    return item_matches_query($order, $q, ['name', 'phone', 'tracking_code']);
});
usort($filtered, fn($a, $b) => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));

if (isset($_GET['export'])) {
    send_csv('orders.csv', ['tracking_code','name','phone','email','level','goal','amount','status','ref_id','created_at','paid_at'], array_map(fn($o) => [
        $o['tracking_code'] ?? '', $o['name'] ?? '', $o['phone'] ?? '', $o['email'] ?? '', $o['level'] ?? '', $o['goal'] ?? '', $o['amount'] ?? '', $o['status'] ?? '', $o['ref_id'] ?? '', $o['created_at'] ?? '', $o['paid_at'] ?? ''
    ], $filtered));
}

admin_header('مدیریت سفارش‌ها');
?>
<section class="admin-card">
  <form class="row g-3 align-items-end mb-4" method="get">
    <div class="col-12 col-lg-6">
      <label class="form-label">جستجو</label>
      <input class="form-control" name="q" value="<?= e($q) ?>" placeholder="نام، موبایل یا کد پیگیری">
    </div>
    <div class="col-12 col-md-6 col-lg-3">
      <label class="form-label">وضعیت</label>
      <select class="form-select" name="status">
        <option value="">همه وضعیت‌ها</option>
        <?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-12 col-md-6 col-lg-3 d-flex gap-2">
      <button class="btn btn-primary flex-fill" type="submit"><i class="bi bi-filter"></i> اعمال</button>
      <a class="btn btn-outline-success" href="?<?= e(http_build_query(array_merge($_GET, ['export' => 1]))) ?>"><i class="bi bi-filetype-csv"></i> CSV</a>
    </div>
  </form>

  <div class="section-head"><h2>سفارش‌ها <span><?= e(count($filtered)) ?></span></h2></div>
  <div class="table-responsive">
    <table class="table align-middle admin-table">
      <thead><tr><th>کد پیگیری</th><th>نام و نام خانوادگی</th><th>موبایل</th><th>ایمیل</th><th>سطح آشنایی</th><th>هدف</th><th>مبلغ</th><th>وضعیت</th><th>ref_id</th><th>ثبت سفارش</th><th>پرداخت</th><th>جزئیات</th></tr></thead>
      <tbody>
      <?php foreach ($filtered as $i => $order): $modalId = 'orderModal' . $i; ?>
        <tr>
          <td class="ltr text-nowrap"><?= e($order['tracking_code'] ?? '-') ?></td>
          <td><?= e($order['name'] ?? '-') ?></td>
          <td class="ltr text-nowrap"><?= e($order['phone'] ?? '-') ?></td>
          <td><?= e($order['email'] ?? '-') ?></td>
          <td><?= e($order['level'] ?? '-') ?></td>
          <td class="wide-cell"><?= e($order['goal'] ?? '-') ?></td>
          <td><?= e(format_toman($order['amount'] ?? 0)) ?></td>
          <td><?= render_status_badge((string) ($order['status'] ?? 'pending')) ?></td>
          <td class="ltr text-nowrap"><?= e($order['ref_id'] ?? '-') ?></td>
          <td class="ltr text-nowrap"><?= e(format_datetime($order['created_at'] ?? '')) ?></td>
          <td class="ltr text-nowrap"><?= e(format_datetime($order['paid_at'] ?? '')) ?></td>
          <td><button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#<?= e($modalId) ?>">مشاهده</button></td>
        </tr>
        <div class="modal fade" id="<?= e($modalId) ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0">
              <div class="modal-header"><h5 class="modal-title">جزئیات سفارش <?= e($order['tracking_code'] ?? '') ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button></div>
              <div class="modal-body">
                <div class="row g-3 detail-grid">
                  <?php foreach ([
                    'نام' => $order['name'] ?? '-', 'موبایل' => $order['phone'] ?? '-', 'ایمیل' => $order['email'] ?? '-', 'سطح آشنایی' => $order['level'] ?? '-',
                    'هدف' => $order['goal'] ?? '-', 'یادداشت' => $order['note'] ?? '-', 'مبلغ پرداختی' => format_rial($order['amount'] ?? 0), 'وضعیت' => status_label((string)($order['status'] ?? 'pending')),
                    'Authority' => $order['authority'] ?? '-', 'Ref ID' => $order['ref_id'] ?? '-', 'IP' => $order['user_ip'] ?? '-', 'User Agent' => $order['user_agent'] ?? '-',
                  ] as $label => $value): ?>
                    <div class="col-12 col-md-6"><span><?= e($label) ?></span><strong><?= e($value) ?></strong></div>
                  <?php endforeach; ?>
                </div>
                <h6 class="mt-4">داده کامل سفارش</h6>
                <pre class="json-preview"><?= e(json_encode($order, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) ?></pre>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$filtered): ?><tr><td colspan="12" class="empty-state">سفارشی مطابق فیلترها پیدا نشد.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php admin_footer(); ?>
