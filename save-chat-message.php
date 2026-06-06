<?php
declare(strict_types=1);
require_once __DIR__ . '/admin/admin-config.php';
header('Content-Type: application/json; charset=utf-8');

function save_chat_json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    save_chat_json_response(['error' => true, 'message' => 'Only POST is allowed.'], 405);
}

$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

try {
    $chat = append_chat_message($input);
    save_chat_json_response([
        'error' => false,
        'session_id' => $chat['session_id'] ?? ($input['session_id'] ?? ''),
        'message_count' => chat_message_count($chat),
    ]);
} catch (InvalidArgumentException $error) {
    save_chat_json_response(['error' => true, 'message' => $error->getMessage()], 422);
} catch (Throwable $error) {
    save_chat_json_response(['error' => true, 'message' => 'امکان ذخیره پیام وجود ندارد.'], 500);
}
