<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();

$orders = read_orders();
$leads = read_leads();
$chats = read_chats();
$paidOrders = array_filter($orders, fn($order) => ($order['status'] ?? '') === 'paid');
$failedOrders = array_filter($orders, fn($order) => ($order['status'] ?? '') === 'failed');
$pendingOrders = array_filter($orders, fn($order) => ($order['status'] ?? '') === 'pending');
$revenue = array_sum(array_map(fn($order) => (int) ($order['amount'] ?? 0), $paidOrders));
$latestOrders = latest_items($orders, 6, 'created_at');
$latestLeads = latest_items($leads, 6, 'created_at');

admin_header('داشبورد');
?>
<section class="stats-grid mb-4">
  <div class="stat-card"><span>کل سفارش‌ها</span><strong><?= e(count($orders)) ?></strong><i class="bi bi-receipt"></i></div>
  <div class="stat-card success"><span>پرداخت موفق</span><strong><?= e(count($paidOrders)) ?></strong><i class="bi bi-check2-circle"></i></div>
  <div class="stat-card danger"><span>پرداخت ناموفق</span><strong><?= e(count($failedOrders)) ?></strong><i class="bi bi-x-circle"></i></div>
  <div class="stat-card warning"><span>در انتظار پرداخت</span><strong><?= e(count($pendingOrders)) ?></strong><i class="bi bi-hourglass-split"></i></div>
  <div class="stat-card primary"><span>مجموع درآمد پرداخت‌شده</span><strong><?= e(format_toman($revenue)) ?></strong><i class="bi bi-cash-stack"></i></div>
  <div class="stat-card"><span>تعداد لیدها</span><strong><?= e(count($leads)) ?></strong><i class="bi bi-people"></i></div>
  <div class="stat-card"><span>گفتگوهای چت‌بات</span><strong><?= e(count($chats)) ?></strong><i class="bi bi-chat-dots"></i></div>
  <div class="stat-card"><span>میانگین پیام هر گفتگو</span><strong><?= e(count($chats) ? round(array_sum(array_map('chat_message_count', $chats)) / count($chats), 1) : 0) ?></strong><i class="bi bi-bar-chart"></i></div>
</section>

<div class="row g-4">
  <div class="col-12 col-xl-7">
    <section class="admin-card h-100">
      <div class="section-head">
        <h2>آخرین سفارش‌ها</h2>
        <a href="orders.php">مشاهده همه</a>
      </div>
      <div class="table-responsive">
        <table class="table align-middle admin-table">
          <thead><tr><th>کد پیگیری</th><th>نام</th><th>مبلغ</th><th>وضعیت</th><th>تاریخ</th></tr></thead>
          <tbody>
          <?php foreach ($latestOrders as $order): ?>
            <tr>
              <td class="ltr text-nowrap"><?= e($order['tracking_code'] ?? '-') ?></td>
              <td><?= e($order['name'] ?? '-') ?></td>
              <td><?= e(format_toman($order['amount'] ?? 0)) ?></td>
              <td><?= render_status_badge((string) ($order['status'] ?? 'pending')) ?></td>
              <td class="ltr text-nowrap"><?= e(format_datetime($order['created_at'] ?? '')) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$latestOrders): ?><tr><td colspan="5" class="empty-state">هنوز سفارشی ثبت نشده است.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
  <div class="col-12 col-xl-5">
    <section class="admin-card h-100">
      <div class="section-head">
        <h2>آخرین لیدها</h2>
        <a href="leads.php">مشاهده همه</a>
      </div>
      <div class="table-responsive">
        <table class="table align-middle admin-table">
          <thead><tr><th>نام</th><th>موبایل</th><th>منبع</th><th>وضعیت</th></tr></thead>
          <tbody>
          <?php foreach ($latestLeads as $lead): ?>
            <tr>
              <td><?= e($lead['name'] ?? '-') ?></td>
              <td class="ltr text-nowrap"><?= e($lead['phone'] ?? '-') ?></td>
              <td><?= e($lead['source'] ?? '-') ?></td>
              <td><?= render_status_badge((string) ($lead['follow_status'] ?? 'new')) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$latestLeads): ?><tr><td colspan="4" class="empty-state">هنوز لیدی ثبت نشده است.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</div>
<?php admin_footer(); ?>
