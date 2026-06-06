<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();
$statuses = ['open', 'closed', 'converted'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $sessionId = clean_text($_POST['session_id'] ?? '', 140);
    $chats = read_chats();
    foreach ($chats as &$chat) {
        if (($chat['session_id'] ?? '') === $sessionId) {
            $nextStatus = clean_text($_POST['status'] ?? 'open', 40);
            $chat['status'] = in_array($nextStatus, $statuses, true) ? $nextStatus : 'open';
            $chat['admin_note'] = clean_text($_POST['admin_note'] ?? '', 2000);
            $chat['updated_at'] = gmdate('c');
            if ($chat['status'] === 'converted' && !empty($chat['user_phone'])) {
                mark_lead_converted((string) $chat['user_phone']);
            }
            break;
        }
    }
    unset($chat);
    write_chats($chats);
    $_SESSION['flash'] = 'گفتگو بروزرسانی شد.';
    redirect('chats.php');
}
$chats = read_chats();
$q = mb_strtolower(clean_text($_GET['q'] ?? '', 160), 'UTF-8');
$status = clean_text($_GET['status'] ?? '', 40);
$filtered = array_filter($chats, function ($chat) use ($q, $status) {
    if ($status !== '' && ($chat['status'] ?? '') !== $status) return false;
    if ($q === '') return true;
    $haystack = mb_strtolower(($chat['user_phone'] ?? '') . ' ' . ($chat['user_name'] ?? '') . ' ' . json_encode($chat['messages'] ?? [], JSON_UNESCAPED_UNICODE), 'UTF-8');
    return mb_strpos($haystack, $q, 0, 'UTF-8') !== false;
});
usort($filtered, fn($a, $b) => strcmp((string) ($b['last_message_at'] ?? ''), (string) ($a['last_message_at'] ?? '')));
admin_header('مدیریت چت‌های کاربران');
?>
<section class="admin-card">
  <form class="row g-3 align-items-end mb-4" method="get">
    <div class="col-12 col-lg-7"><label class="form-label">جستجو</label><input class="form-control" name="q" value="<?= e($q) ?>" placeholder="موبایل، نام یا متن پیام"></div>
    <div class="col-12 col-md-6 col-lg-3"><label class="form-label">وضعیت گفتگو</label><select class="form-select" name="status"><option value="">همه وضعیت‌ها</option><?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option><?php endforeach; ?></select></div>
    <div class="col-12 col-md-6 col-lg-2"><button class="btn btn-primary w-100" type="submit">اعمال</button></div>
  </form>
  <div class="section-head"><h2>گفتگوها <span><?= e(count($filtered)) ?></span></h2></div>
  <div class="table-responsive"><table class="table align-middle admin-table"><thead><tr><th>session_id</th><th>نام کاربر</th><th>موبایل</th><th>آخرین پیام گفتگو</th><th>تعداد پیام‌ها</th><th>شروع</th><th>آخرین پیام</th><th>وضعیت</th><th>مشاهده</th></tr></thead><tbody>
  <?php foreach ($filtered as $i => $chat): $modalId = 'chatModal' . $i; $messages = is_array($chat['messages'] ?? null) ? $chat['messages'] : []; $lastMessage = extract_last_message($chat); ?>
    <tr><td class="ltr small text-nowrap"><?= e($chat['session_id'] ?? '-') ?></td><td><?= e($chat['user_name'] ?? '-') ?></td><td class="ltr text-nowrap"><?= e($chat['user_phone'] ?? '-') ?></td><td class="wide-cell"><?= e(($lastMessage['role'] ?? '') === 'user' ? 'کاربر: ' : 'مشاور: ') ?><?= e(mb_substr((string) ($lastMessage['content'] ?? ''), 0, 90, 'UTF-8')) ?></td><td><?= e(count($messages)) ?></td><td class="ltr text-nowrap"><?= e(format_datetime($chat['first_message_at'] ?? ($chat['started_at'] ?? ''))) ?></td><td class="ltr text-nowrap"><?= e(format_datetime($chat['last_message_at'] ?? '')) ?></td><td><?= render_status_badge((string)($chat['status'] ?? 'open')) ?></td><td><button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#<?= e($modalId) ?>">مشاهده گفتگو</button></td></tr>
    <div class="modal fade" id="<?= e($modalId) ?>" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content rounded-4 border-0"><div class="modal-header"><h5 class="modal-title">گفتگوی <?= e($chat['session_id'] ?? '') ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button></div><div class="modal-body">
      <div class="chat-thread">
        <?php foreach ($messages as $message): $role = ($message['role'] ?? '') === 'user' ? 'user' : 'assistant'; ?>
          <div class="chat-bubble <?= e($role) ?>"><strong><?= $role === 'user' ? 'کاربر' : 'مشاور هوشمند' ?></strong><p><?= e($message['content'] ?? '') ?></p><small><?= e(format_datetime($message['created_at'] ?? '')) ?> · intent: <?= e($message['intent'] ?? '-') ?></small></div>
        <?php endforeach; ?>
        <?php if (!$messages): ?><div class="empty-state">پیامی در این session ثبت نشده است.</div><?php endif; ?>
      </div>
      <form method="post" class="chat-admin-form mt-4"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="session_id" value="<?= e($chat['session_id'] ?? '') ?>"><div class="row g-3"><div class="col-12 col-md-4"><label class="form-label">وضعیت</label><select class="form-select" name="status"><?php foreach ($statuses as $s): ?><option value="<?= e($s) ?>" <?= ($chat['status'] ?? 'open') === $s ? 'selected' : '' ?>><?= e(status_label($s)) ?></option><?php endforeach; ?></select></div><div class="col-12 col-md-8"><label class="form-label">یادداشت ادمین</label><textarea class="form-control" name="admin_note" rows="3"><?= e($chat['admin_note'] ?? '') ?></textarea></div><div class="col-12"><button class="btn btn-primary" type="submit">ذخیره تغییرات</button></div></div></form>
    </div></div></div></div>
  <?php endforeach; ?>
  <?php if (!$filtered): ?><tr><td colspan="9" class="empty-state">گفتگویی مطابق فیلترها پیدا نشد.</td></tr><?php endif; ?>
  </tbody></table></div>
</section>
<?php admin_footer(); ?>
