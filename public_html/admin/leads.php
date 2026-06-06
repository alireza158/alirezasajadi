<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();
$statuses = ['new', 'contacted', 'interested', 'not_interested', 'converted'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $leads = read_leads();
    foreach ($leads as &$lead) {
        if ((int) ($lead['id'] ?? 0) === $id) {
            $nextStatus = clean_text($_POST['follow_status'] ?? 'new', 50);
            $lead['follow_status'] = in_array($nextStatus, $statuses, true) ? $nextStatus : 'new';
            $lead['admin_note'] = clean_text($_POST['admin_note'] ?? '', 2000);
            $lead['updated_at'] = gmdate('c');
            break;
        }
    }
    unset($lead);
    write_leads($leads);
    $_SESSION['flash'] = 'وضعیت و یادداشت لید ذخیره شد.';
    redirect('leads.php');
}
$leads = read_leads();
$q = mb_strtolower(clean_text($_GET['q'] ?? '', 160), 'UTF-8');
$status = clean_text($_GET['status'] ?? '', 50);
$filtered = array_filter($leads, function ($lead) use ($q, $status) {
    if ($status !== '' && ($lead['follow_status'] ?? '') !== $status) return false;
    return item_matches_query($lead, $q, ['name', 'phone', 'email', 'goal', 'source']);
});
usort($filtered, fn($a, $b) => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));
if (isset($_GET['export'])) {
    send_csv('leads.csv', ['name','phone','level','goal','source','created_at','follow_status','admin_note'], array_map(fn($l) => [$l['name'] ?? '', $l['phone'] ?? '', $l['level'] ?? '', $l['goal'] ?? '', $l['source'] ?? '', $l['created_at'] ?? '', $l['follow_status'] ?? '', $l['admin_note'] ?? ''], $filtered));
}
admin_header('مدیریت لیدها');
?>
<section class="admin-card">
  <form class="row g-3 align-items-end mb-4" method="get">
    <div class="col-12 col-lg-7"><label class="form-label">جستجو</label><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="نام، موبایل، ایمیل، هدف یا منبع"></div>
    <div class="col-12 col-md-6 col-lg-3"><label class="form-label">وضعیت پیگیری</label><select class="form-select" name="status"><option value="">همه وضعیت‌ها</option><?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option><?php endforeach; ?></select></div>
    <div class="col-12 col-md-6 col-lg-2 d-flex gap-2"><button class="btn btn-primary flex-fill" type="submit">اعمال</button><a class="btn btn-outline-success" href="?<?= e(http_build_query(array_merge($_GET, ['export' => 1]))) ?>">CSV</a></div>
  </form>
  <div class="section-head"><h2>لیدها <span><?= e(count($filtered)) ?></span></h2></div>
  <div class="table-responsive"><table class="table align-middle admin-table"><thead><tr><th>نام</th><th>موبایل</th><th>سطح آشنایی</th><th>هدف</th><th>منبع ورود</th><th>تاریخ ثبت</th><th>وضعیت پیگیری</th><th>یادداشت ادمین</th><th>ذخیره</th></tr></thead><tbody>
  <?php foreach ($filtered as $lead): ?>
    <tr>
      <td><?= e($lead['name'] ?? '-') ?></td><td class="ltr text-nowrap"><?= e($lead['phone'] ?? '-') ?></td><td><?= e($lead['level'] ?? '-') ?></td><td class="wide-cell"><?= e($lead['goal'] ?? '-') ?></td><td><?= e($lead['source'] ?? '-') ?></td><td class="ltr text-nowrap"><?= e(format_datetime($lead['created_at'] ?? '')) ?></td><td><?= render_status_badge((string)($lead['follow_status'] ?? 'new')) ?></td>
      <td colspan="2">
        <form class="lead-inline-form" method="post">
          <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($lead['id'] ?? 0) ?>">
          <select class="form-select form-select-sm" name="follow_status"><?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>" <?= ($lead['follow_status'] ?? 'new') === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option><?php endforeach; ?></select>
          <textarea class="form-control form-control-sm" name="admin_note" rows="2" placeholder="یادداشت پیگیری... "><?= e($lead['admin_note'] ?? '') ?></textarea>
          <button class="btn btn-sm btn-primary" type="submit">ذخیره</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$filtered): ?><tr><td colspan="9" class="empty-state">لیدی مطابق فیلترها پیدا نشد.</td></tr><?php endif; ?>
  </tbody></table></div>
</section>
<?php admin_footer(); ?>
