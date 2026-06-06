<?php
declare(strict_types=1);
require_once __DIR__ . '/layout.php';
require_admin();
$statuses = ['open','closed','converted'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
    $sessionId = clean_text($_POST['session_id'] ?? '', 120);
    $chats = read_chats();
    foreach ($chats as &$chat) {
        if (($chat['session_id'] ?? '') === $sessionId) {
            $newStatus = clean_text($_POST['status'] ?? 'open', 40);
            $chat['status'] = in_array($newStatus, $statuses, true) ? $newStatus : 'open';
            $chat['admin_note'] = clean_text($_POST['admin_note'] ?? '', 2000);
            $chat['updated_at'] = gmdate('c');
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
usort($filtered, fn($a,$b) => strcmp((string)($b['last_message_at'] ?? ''), (string)($a['last_message_at'] ?? '')));
admin_header('مدیریت چت‌های کاربران'); flash_message();
?>
<section class="panel"><form class="filters" method="get"><input name="q" value="<?= e($q) ?>" placeholder="جستجو بر اساس موبایل یا متن پیام"><select name="status"><option value="">همه وضعیت‌ها</option><?php foreach($statuses as $s): ?><option value="<?= e($s) ?>" <?= $status===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select><span></span><button class="btn" type="submit">اعمال</button></form><div class="panel-head"><h2>گفتگوها (<?= count($filtered) ?>)</h2></div><div class="table-wrap"><table><thead><tr><th>session_id</th><th>نام</th><th>موبایل</th><th>آخرین پیام کاربر</th><th>تعداد پیام</th><th>شروع</th><th>آخرین پیام</th><th>وضعیت</th><th>جزئیات</th></tr></thead><tbody><?php foreach($filtered as $i=>$chat): $messages=$chat['messages'] ?? []; $lastUser=''; foreach(array_reverse($messages) as $m){ if(($m['role'] ?? '')==='user'){ $lastUser=(string)($m['content'] ?? ''); break; }} $rowId='chat-'.$i; ?><tr><td><?= e($chat['session_id'] ?? '-') ?></td><td><?= e($chat['user_name'] ?? '-') ?></td><td><?= e($chat['user_phone'] ?? '-') ?></td><td><?= e(mb_substr($lastUser,0,80,'UTF-8')) ?></td><td><?= count($messages) ?></td><td><?= e(format_datetime($chat['started_at'] ?? '')) ?></td><td><?= e(format_datetime($chat['last_message_at'] ?? '')) ?></td><td><span class="badge <?= e($chat['status'] ?? 'open') ?>"><?= e($chat['status'] ?? 'open') ?></span></td><td><button class="btn small ghost" data-toggle-details="<?= e($rowId) ?>" type="button">مشاهده</button></td></tr><tr id="<?= e($rowId) ?>" class="details"><td colspan="9"><div class="chat-messages"><?php foreach($messages as $message): ?><div class="chat-message <?= e($message['role'] ?? 'assistant') ?>"><strong><?= e($message['role'] ?? '-') ?></strong><p><?= e($message['content'] ?? '') ?></p><small><?= e(format_datetime($message['created_at'] ?? '')) ?> | intent: <?= e($message['intent'] ?? '-') ?></small></div><?php endforeach; ?></div><form method="post" class="actions" style="margin-top:16px"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="session_id" value="<?= e($chat['session_id'] ?? '') ?>"><select name="status"><?php foreach($statuses as $s): ?><option value="<?= e($s) ?>" <?= ($chat['status'] ?? 'open')===$s?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select><textarea name="admin_note" rows="2" placeholder="یادداشت ادمین"><?= e($chat['admin_note'] ?? '') ?></textarea><button class="btn small" type="submit">ذخیره</button></form></td></tr><?php endforeach; ?><?php if(!$filtered): ?><tr><td colspan="9" class="empty">موردی پیدا نشد.</td></tr><?php endif; ?></tbody></table></div></section>
<?php admin_footer(); ?>
