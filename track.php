<?php
declare(strict_types=1);
require_once __DIR__ . '/admin/admin-config.php';
header('Content-Type: application/json; charset=utf-8');

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => true, 'message' => 'Only POST is allowed.'], 405);
}

$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}
$type = clean_text($input['type'] ?? '', 40);

if ($type === 'lead') {
    $lead = upsert_lead([
        'name' => $input['name'] ?? '',
        'phone' => $input['phone'] ?? '',
        'email' => $input['email'] ?? '',
        'level' => $input['level'] ?? '',
        'goal' => $input['goal'] ?? '',
        'source' => $input['source'] ?? 'register-form',
        'intent' => $input['intent'] ?? '',
        'follow_status' => $input['follow_status'] ?? 'new',
    ]);
    json_response(['error' => false, 'lead_id' => $lead['id'] ?? null]);
}

if ($type === 'chat_message') {
    $sessionId = clean_text($input['session_id'] ?? '', 120);
    if ($sessionId === '') {
        json_response(['error' => true, 'message' => 'session_id is required.'], 422);
    }
    $role = clean_text($input['role'] ?? 'user', 20);
    $role = in_array($role, ['user', 'assistant'], true) ? $role : 'user';
    $content = clean_text($input['content'] ?? '', 1200);
    if ($content === '') {
        json_response(['error' => true, 'message' => 'content is required.'], 422);
    }
    $now = gmdate('c');
    $phone = normalize_mobile((string) ($input['user_phone'] ?? ''));
    $name = clean_text($input['user_name'] ?? '', 160);
    $chats = read_chats();
    $index = -1;
    foreach ($chats as $i => $chat) {
        if (($chat['session_id'] ?? '') === $sessionId) {
            $index = $i;
            break;
        }
    }
    $message = [
        'role' => $role,
        'content' => $content,
        'created_at' => $now,
        'user_name' => $name,
        'user_phone' => $phone,
        'intent' => clean_text($input['intent'] ?? 'general', 60),
    ];
    if ($index < 0) {
        $chats[] = [
            'session_id' => $sessionId,
            'user_name' => $name,
            'user_phone' => $phone,
            'status' => 'open',
            'admin_note' => '',
            'started_at' => $now,
            'last_message_at' => $now,
            'messages' => [$message],
        ];
    } else {
        if ($name !== '') $chats[$index]['user_name'] = $name;
        if ($phone !== '') $chats[$index]['user_phone'] = $phone;
        $chats[$index]['last_message_at'] = $now;
        $chats[$index]['messages'][] = $message;
    }
    write_chats($chats);
    if ($phone !== '' || $name !== '') {
        upsert_lead(['name' => $name, 'phone' => $phone, 'source' => 'chat', 'intent' => $input['intent'] ?? 'general']);
    }
    json_response(['error' => false]);
}

json_response(['error' => true, 'message' => 'Unknown tracking type.'], 400);
