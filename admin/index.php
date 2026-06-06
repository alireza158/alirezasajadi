<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();
$orders = read_orders();
$leads = read_leads();
$chats = read_chats();
$paidOrders = array_filter($orders, fn($o) => ($o['status'] ?? '') === 'paid');
$failedOrders = array_filter($orders, fn($o) => ($o['status'] ?? '') === 'failed');
$pendingOrders = array_filter($orders, fn($o) => ($o['status'] ?? '') === 'pending');
$revenue = array_sum(array_map(fn($o) => (int) ($o['amount'] ?? 0), $paidOrders));
usort($orders, fn($a,$b) => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
usort($leads, fn($a,$b) => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
admin_header('داشبورد اصلی');
?>
<section class="grid stats">
  <div class="card stat"><span>کل سفارش‌ها</span><strong><?= count($orders) ?></strong></div>
  <div class="card stat"><span>پرداخت موفق</span><strong><?= count($paidOrders) ?></strong></div>
  <div class="card stat"><span>پرداخت ناموفق</span><strong><?= count($failedOrders) ?></strong></div>
  <div class="card stat"><span>سفارش pending</span><strong><?= count($pendingOrders) ?></strong></div>
  <div class="card stat"><span>درآمد پرداخت‌شده</span><strong><?= e(format_toman($revenue)) ?></strong></div>
  <div class="card stat"><span>تعداد Leadها</span><strong><?= count($leads) ?></strong></div>
  <div class="card stat"><span>گفتگوهای چت‌بات</span><strong><?= count($chats) ?></strong></div>
  <div class="card stat"><span>آخرین بروزرسانی</span><strong><?= e(gmdate('Y-m-d')) ?></strong></div>
</section>
<section class="panel">
  <div class="panel-head"><h2>آخرین ثبت‌نام‌ها / Leadها</h2><a class="btn small ghost" href="leads.php">مشاهده همه</a></div>
  <div class="table-wrap"><table><thead><tr><th>نام</th><th>موبایل</th><th>هدف</th><th>منبع</th><th>وضعیت</th><th>تاریخ</th></tr></thead><tbody>
  <?php foreach (array_slice($leads, 0, 8) as $lead): ?><tr><td><?= e($lead['name'] ?? '-') ?></td><td><?= e($lead['phone'] ?? '-') ?></td><td><?= e($lead['goal'] ?? '-') ?></td><td><?= e($lead['source'] ?? '-') ?></td><td><span class="badge <?= e($lead['follow_status'] ?? 'new') ?>"><?= e($lead['follow_status'] ?? 'new') ?></span></td><td><?= e(format_datetime($lead['created_at'] ?? '')) ?></td></tr><?php endforeach; ?>
  <?php if (!$leads): ?><tr><td colspan="6" class="empty">هنوز lead ثبت نشده است.</td></tr><?php endif; ?>
  </tbody></table></div>
</section>
<section class="panel">
  <div class="panel-head"><h2>آخرین پرداخت‌ها</h2><a class="btn small ghost" href="orders.php">مشاهده همه</a></div>
  <div class="table-wrap"><table><thead><tr><th>کد پیگیری</th><th>نام</th><th>موبایل</th><th>مبلغ</th><th>وضعیت</th><th>ref_id</th><th>تاریخ پرداخت</th></tr></thead><tbody>
  <?php foreach (array_slice($orders, 0, 8) as $order): ?><tr><td><?= e($order['tracking_code'] ?? '-') ?></td><td><?= e($order['name'] ?? '-') ?></td><td><?= e($order['phone'] ?? '-') ?></td><td><?= e(format_toman($order['amount'] ?? 0)) ?></td><td><span class="badge <?= e($order['status'] ?? 'pending') ?>"><?= e($order['status'] ?? 'pending') ?></span></td><td><?= e($order['ref_id'] ?? '-') ?></td><td><?= e(format_datetime($order['paid_at'] ?? '')) ?></td></tr><?php endforeach; ?>
  <?php if (!$orders): ?><tr><td colspan="7" class="empty">هنوز سفارشی ثبت نشده است.</td></tr><?php endif; ?>
  </tbody></table></div>
</section>
<?php admin_footer(); ?>
