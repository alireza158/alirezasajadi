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
        'session_id' => $input['session_id'] ?? '',
        'status' => $input['status'] ?? ($input['follow_status'] ?? 'new'),
        'follow_status' => $input['follow_status'] ?? ($input['status'] ?? 'new'),
    ]);
    json_response(['error' => false, 'lead_id' => $lead['id'] ?? null]);
}

if ($type === 'chat_message') {
    try {
        append_chat_message($input);
        json_response(['error' => false]);
    } catch (InvalidArgumentException $error) {
        json_response(['error' => true, 'message' => $error->getMessage()], 422);
    } catch (Throwable $error) {
        json_response(['error' => true, 'message' => 'امکان ذخیره پیام وجود ندارد.'], 500);
    }
}


json_response(['error' => true, 'message' => 'Unknown tracking type.'], 400);
