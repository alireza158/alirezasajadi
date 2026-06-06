<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();
$statuses = ['new','contacted','interested','not_interested','converted'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $leads = read_leads();
    foreach ($leads as &$lead) {
        if ((int) ($lead['id'] ?? 0) === $id) {
            $newStatus = clean_text($_POST['follow_status'] ?? 'new', 40);
            $lead['follow_status'] = in_array($newStatus, $statuses, true) ? $newStatus : 'new';
            $lead['admin_note'] = clean_text($_POST['admin_note'] ?? '', 2000);
            $lead['updated_at'] = gmdate('c');
        }
    }
    unset($lead);
    write_leads($leads);
    $_SESSION['flash'] = 'Lead بروزرسانی شد.';
    redirect('leads.php');
}
$leads = read_leads();
$q = mb_strtolower(clean_text($_GET['q'] ?? '', 120), 'UTF-8');
$status = clean_text($_GET['status'] ?? '', 40);
$filtered = array_filter($leads, fn($lead) => ($status === '' || ($lead['follow_status'] ?? '') === $status) && item_matches_query($lead, $q, ['name','phone','goal','level','source']));
usort($filtered, fn($a,$b) => strcmp((string)($b['created_at'] ?? ''), (string)($a['created_at'] ?? '')));
if (isset($_GET['export'])) {
    send_csv('leads.csv', ['name','phone','level','goal','source','created_at','follow_status','admin_note'], array_map(fn($l) => [$l['name'] ?? '',$l['phone'] ?? '',$l['level'] ?? '',$l['goal'] ?? '',$l['source'] ?? '',$l['created_at'] ?? '',$l['follow_status'] ?? '',$l['admin_note'] ?? ''], $filtered));
}
admin_header('مدیریت Leadها'); flash_message();
?>
<section class="panel"><form class="filters" method="get"><input name="q" value="<?= e($q) ?>" placeholder="جستجو"><select name="status"><option value="">همه وضعیت‌ها</option><?php foreach($statuses as $s): ?><option value="<?= e($s) ?>" <?= $status===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select><span></span><button class="btn" type="submit">اعمال</button></form><div class="panel-head"><h2>Leadها (<?= count($filtered) ?>)</h2><a class="btn small secondary" href="?<?= e(http_build_query(array_merge($_GET, ['export'=>1]))) ?>">خروجی CSV</a></div><div class="table-wrap"><table><thead><tr><th>نام</th><th>موبایل</th><th>سطح</th><th>هدف</th><th>منبع</th><th>تاریخ</th><th>وضعیت و یادداشت</th></tr></thead><tbody><?php foreach($filtered as $lead): ?><tr><td><?= e($lead['name'] ?? '-') ?></td><td><?= e($lead['phone'] ?? '-') ?></td><td><?= e($lead['level'] ?? '-') ?></td><td><?= e($lead['goal'] ?? '-') ?></td><td><?= e($lead['source'] ?? '-') ?></td><td><?= e(format_datetime($lead['created_at'] ?? '')) ?></td><td><form method="post" class="actions"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= e($lead['id'] ?? 0) ?>"><select name="follow_status"><?php foreach($statuses as $s): ?><option value="<?= e($s) ?>" <?= ($lead['follow_status'] ?? 'new')===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select><textarea name="admin_note" rows="2" placeholder="یادداشت ادمین"><?= e($lead['admin_note'] ?? '') ?></textarea><button class="btn small" type="submit">ذخیره</button></form></td></tr><?php endforeach; ?><?php if(!$filtered): ?><tr><td colspan="7" class="empty">موردی پیدا نشد.</td></tr><?php endif; ?></tbody></table></div></section>
<?php admin_footer(); ?>
