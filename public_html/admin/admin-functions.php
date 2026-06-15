<?php
declare(strict_types=1);

function admin_start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name('ai_course_admin');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        session_start();
    }
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function normalize_digits(string $value): string
{
    return strtr($value, [
        '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
        '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ]);
}

function clean_text(mixed $value, int $maxLength = 1000): string
{
    if (is_array($value)) {
        return '';
    }
    $clean = trim(strip_tags(normalize_digits((string) $value)));
    return mb_substr($clean, 0, $maxLength, 'UTF-8');
}

function clean_url(mixed $value): string
{
    $url = clean_text($value, 500);
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : '';
}

function normalize_mobile(string $phone): string
{
    $phone = normalize_digits($phone);
    $phone = preg_replace('/[\s\-()]+/', '', $phone) ?? '';
    if (preg_match('/^(?:\+98|0098|98)?(9\d{9})$/', $phone, $matches)) {
        return '0' . $matches[1];
    }
    if (preg_match('/^09\d{9}$/', $phone)) {
        return $phone;
    }
    return '';
}

function data_defaults_for(string $file): array
{
    if ($file === SETTINGS_FILE) {
        return DEFAULT_SETTINGS;
    }
    return [];
}

function ensure_data_file(string $file, ?array $default = null): void
{
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0755, true);
    }
    $htaccess = DATA_DIR . '/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Require all denied\n<FilesMatch \"\\.(json)$\">\n  Require all denied\n</FilesMatch>\n", LOCK_EX);
    }
    if (!file_exists($file)) {
        write_json_file($file, $default ?? data_defaults_for($file));
    }
}

function read_json_file(string $file, array $default = []): array
{
    ensure_data_file($file, $default);
    $content = file_get_contents($file);
    if ($content === false || trim($content) === '') {
        return $default;
    }
    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : $default;
}

function write_json_file(string $file, array $data): bool
{
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }
    return file_put_contents($file, $json . PHP_EOL, LOCK_EX) !== false;
}

function read_orders(): array { return read_json_file(ORDERS_FILE, []); }
function write_orders(array $orders): bool { return write_json_file(ORDERS_FILE, array_values($orders)); }
function read_leads(): array { return read_json_file(LEADS_FILE, []); }
function write_leads(array $leads): bool { return write_json_file(LEADS_FILE, array_values($leads)); }
function read_chats(): array { return read_json_file(CHATS_FILE, []); }
function write_chats(array $chats): bool { return write_json_file(CHATS_FILE, array_values($chats)); }

function default_landing_content(): array
{
    return array_fill_keys(array_keys(landing_section_configs()), []);
}

function landing_section_configs(): array
{
    return [
        'features' => ['label' => 'مهارت‌های عملی', 'icon' => 'bi-stars', 'fields' => ['icon', 'title', 'description']],
        'projects' => ['label' => 'نمونه‌کارها', 'icon' => 'bi-kanban', 'fields' => ['title', 'description', 'full_description', 'thumbnail_image', 'image', 'full_image', 'gallery', 'tags', 'category', 'button_text', 'link', 'show_home']],
        'audience' => ['label' => 'مخاطبان دوره', 'icon' => 'bi-people', 'fields' => ['title', 'description', 'icon']],
        'curriculum' => ['label' => 'سرفصل‌ها', 'icon' => 'bi-journal-text', 'fields' => ['title', 'description', 'duration', 'lessons']],
        'results' => ['label' => 'نتایج دوره', 'icon' => 'bi-trophy', 'fields' => ['title', 'description', 'icon']],
        'testimonials' => ['label' => 'نظرات هنرجوها', 'icon' => 'bi-chat-heart', 'fields' => ['title', 'subtitle', 'description', 'image', 'rating']],
        'faqs' => ['label' => 'سوالات متداول', 'icon' => 'bi-question-circle', 'fields' => ['title', 'description', 'category']],
    ];
}

function normalize_section_item(mixed $item, string $section, int $index): array
{
    $base = ['id' => 'item-' . ($index + 1), 'title' => '', 'description' => '', 'full_description' => '', 'icon' => '', 'thumbnail_image' => '', 'image' => '', 'full_image' => '', 'gallery' => [], 'link' => '', 'button_text' => '', 'tags' => [], 'lessons' => [], 'duration' => '', 'subtitle' => '', 'rating' => '', 'category' => '', 'show_home' => true, 'sort_order' => $index + 1, 'status' => 'active'];
    if (is_array($item) && array_is_list($item)) {
        if ($section === 'features') return array_merge($base, ['icon' => (string)($item[0] ?? ''), 'title' => (string)($item[1] ?? '')]);
        if ($section === 'projects') return array_merge($base, ['title' => (string)($item[0] ?? ''), 'description' => (string)($item[1] ?? ''), 'tags' => array_values((array)($item[2] ?? [])), 'button_text' => 'مشاهده دوره ←', 'link' => '#curriculum']);
        if ($section === 'curriculum' || $section === 'faqs') return array_merge($base, ['title' => (string)($item[0] ?? ''), 'description' => (string)($item[1] ?? '')]);
    }
    if (is_string($item)) {
        if ($section === 'testimonials') {
            return array_merge($base, ['title' => 'هنرجوی دوره ' . ($index + 1), 'description' => $item]);
        }
        return array_merge($base, ['title' => $item, 'icon' => $section === 'results' ? '✓' : '']);
    }
    if (is_array($item)) {
        $merged = array_merge($base, $item);
        $merged['id'] = clean_text($merged['id'] ?: ('item-' . ($index + 1)), 80);
        $merged['tags'] = array_values(array_filter(array_map('strval', (array)($merged['tags'] ?? []))));
        $merged['gallery'] = array_values(array_filter(array_map('strval', (array)($merged['gallery'] ?? []))));
        $merged['show_home'] = filter_var($merged['show_home'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $merged['lessons'] = array_values(array_filter(array_map('strval', (array)($merged['lessons'] ?? []))));
        $merged['sort_order'] = (int)($merged['sort_order'] ?? ($index + 1));
        $merged['status'] = ($merged['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        return $merged;
    }
    return $base;
}

function normalize_landing_content(array $content): array
{
    $normalized = [];
    foreach (landing_section_configs() as $section => $config) {
        $items = (array)($content[$section] ?? []);
        $items = array_map(fn($item, $index) => normalize_section_item($item, $section, $index), $items, array_keys($items));
        usort($items, fn($a, $b) => ((int)($a['sort_order'] ?? 0)) <=> ((int)($b['sort_order'] ?? 0)));
        $normalized[$section] = array_values($items);
    }
    return $normalized;
}

function read_landing_content(): array
{
    return normalize_landing_content(read_json_file(LANDING_CONTENT_FILE, default_landing_content()));
}

function write_landing_content(array $content): bool
{
    return write_json_file(LANDING_CONTENT_FILE, normalize_landing_content($content));
}

function save_uploaded_media(array $file, array &$errors): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) { $errors[] = 'آپلود فایل با خطا روبه‌رو شد.'; return ''; }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', 'image/svg+xml' => 'svg'];
    $tmp = (string)($file['tmp_name'] ?? '');
    $mime = function_exists('mime_content_type') ? (mime_content_type($tmp) ?: '') : '';
    if (!isset($allowed[$mime])) { $errors[] = 'فرمت تصویر مجاز نیست. JPG، PNG، WEBP، GIF یا SVG بارگذاری کنید.'; return ''; }
    $dir = __DIR__ . '/../uploads';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = 'media-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($tmp, $dir . '/' . $name)) { $errors[] = 'ذخیره فایل آپلودی ممکن نشد.'; return ''; }
    return './uploads/' . $name;
}

function read_settings(): array
{
    return array_merge(DEFAULT_SETTINGS, read_json_file(SETTINGS_FILE, DEFAULT_SETTINGS));
}

function write_settings(array $settings): bool
{
    return write_json_file(SETTINGS_FILE, array_merge(DEFAULT_SETTINGS, $settings));
}

function admin_is_authenticated(): bool
{
    admin_start_session();
    return !empty($_SESSION[ADMIN_SESSION_KEY]);
}

function require_admin(): void
{
    if (!admin_is_authenticated()) {
        redirect('login.php');
    }
}

function verify_admin_credentials(string $username, string $password): bool
{
    return hash_equals(ADMIN_USERNAME, $username) && password_verify($password, ADMIN_PASSWORD_HASH);
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string
{
    admin_start_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function require_csrf(): void
{
    admin_start_session();
    $token = (string) ($_POST['csrf_token'] ?? '');
    if ($token === '' || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $token)) {
        http_response_code(403);
        exit('درخواست نامعتبر است. لطفاً صفحه را دوباره باز کنید.');
    }
}

function status_label(string $status): string
{
    return [
        'paid' => 'پرداخت موفق', 'pending' => 'در انتظار پرداخت', 'failed' => 'ناموفق', 'cancelled' => 'لغوشده',
        'new' => 'جدید', 'contacted' => 'تماس گرفته شد', 'interested' => 'علاقه‌مند', 'not_interested' => 'عدم علاقه', 'converted' => 'تبدیل‌شده',
        'open' => 'باز', 'closed' => 'بسته',
    ][$status] ?? $status;
}

function format_toman(mixed $amount): string
{
    $value = (int) $amount;
    if ($value > 10000000) {
        $value = (int) round($value / 10);
    }
    return number_format($value) . ' تومان';
}

function format_rial(mixed $amount): string
{
    return number_format((int) $amount) . ' ریال';
}

function format_datetime(mixed $date): string
{
    if (!$date) {
        return '-';
    }
    $timestamp = strtotime((string) $date);
    return $timestamp ? gmdate('Y-m-d H:i', $timestamp) : (string) $date;
}

function item_matches_query(array $item, string $query, array $fields): bool
{
    if ($query === '') {
        return true;
    }
    foreach ($fields as $field) {
        $value = mb_strtolower((string) ($item[$field] ?? ''), 'UTF-8');
        if (mb_strpos($value, $query, 0, 'UTF-8') !== false) {
            return true;
        }
    }
    return false;
}

function send_csv(string $filename, array $headers, array $rows): never
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, array_map(fn($value) => (string) $value, $row));
    }
    fclose($out);
    exit;
}

function next_id(array $items): int
{
    $ids = array_map(fn($item) => (int) ($item['id'] ?? 0), $items);
    return (empty($ids) ? 0 : max($ids)) + 1;
}

function upsert_lead(array $data): array
{
    $leads = read_leads();
    $phone = normalize_mobile((string) ($data['phone'] ?? ''));
    $email = clean_text($data['email'] ?? '', 180);
    $sessionId = clean_text($data['session_id'] ?? '', 140);
    $now = gmdate('c');
    $index = -1;
    foreach ($leads as $i => $lead) {
        if (
            ($phone !== '' && ($lead['phone'] ?? '') === $phone) ||
            ($phone === '' && $email !== '' && ($lead['email'] ?? '') === $email) ||
            ($phone === '' && $email === '' && $sessionId !== '' && ($lead['session_id'] ?? '') === $sessionId)
        ) {
            $index = $i;
            break;
        }
    }
    $status = clean_text($data['status'] ?? ($data['follow_status'] ?? 'new'), 50);
    $payload = [
        'session_id' => $sessionId,
        'name' => clean_text($data['name'] ?? '', 160),
        'phone' => $phone,
        'email' => $email,
        'level' => clean_text($data['level'] ?? '', 250),
        'goal' => clean_text($data['goal'] ?? '', 500),
        'source' => clean_text($data['source'] ?? 'register-form', 80),
        'intent' => clean_text($data['intent'] ?? 'general', 80),
        'status' => $status,
        'follow_status' => $status,
    ];
    if ($index < 0) {
        $lead = array_merge([
            'id' => next_id($leads),
            'created_at' => $now,
            'updated_at' => $now,
            'admin_note' => '',
        ], $payload);
        $leads[] = $lead;
    } else {
        $lead = $leads[$index];
        foreach ($payload as $key => $value) {
            if ($value !== '' || !isset($lead[$key])) {
                $lead[$key] = $value;
            }
        }
        $lead['status'] = $lead['follow_status'] ?? ($lead['status'] ?? 'new');
        $lead['updated_at'] = $now;
        $leads[$index] = $lead;
    }
    write_leads($leads);
    return $lead;
}

function extract_mobile_from_text(string $content): string
{
    $normalized = normalize_digits($content);
    if (preg_match('/(?:\+98|0098|98|0)?9\d{9}/', $normalized, $matches)) {
        return normalize_mobile($matches[0]);
    }
    return '';
}

function append_chat_message(array $data): array
{
    $sessionId = clean_text($data['session_id'] ?? '', 140);
    if ($sessionId === '') {
        throw new InvalidArgumentException('session_id is required.');
    }
    $role = clean_text($data['role'] ?? 'user', 20);
    $role = in_array($role, ['user', 'assistant'], true) ? $role : 'user';
    $content = clean_text($data['content'] ?? '', 1600);
    if ($content === '') {
        throw new InvalidArgumentException('content is required.');
    }
    $intent = clean_text($data['intent'] ?? 'general', 80);
    $messageId = clean_text($data['message_id'] ?? '', 160);
    $type = clean_text($data['type'] ?? 'message', 40);
    $now = gmdate('c');
    $name = clean_text($data['user_name'] ?? '', 160);
    $phone = normalize_mobile((string) ($data['user_phone'] ?? '')) ?: extract_mobile_from_text($content);

    $chats = read_chats();
    $index = -1;
    foreach ($chats as $i => $chat) {
        if (($chat['session_id'] ?? '') === $sessionId) {
            $index = $i;
            break;
        }
    }

    $message = [
        'id' => $messageId !== '' ? $messageId : 'msg-' . bin2hex(random_bytes(8)),
        'role' => $role,
        'type' => $type,
        'content' => $content,
        'created_at' => $now,
        'intent' => $intent,
    ];

    if ($index < 0) {
        $chat = [
            'session_id' => $sessionId,
            'intent' => $intent,
            'status' => 'open',
            'user_name' => $name,
            'user_phone' => $phone,
            'messages' => [$message],
            'first_message_at' => $now,
            'started_at' => $now,
            'last_message_at' => $now,
            'admin_note' => '',
        ];
        $chats[] = $chat;
    } else {
        $chat = $chats[$index];
        $messages = is_array($chat['messages'] ?? null) ? $chat['messages'] : [];
        foreach ($messages as $existing) {
            if (($existing['id'] ?? '') !== '' && ($existing['id'] ?? '') === $message['id']) {
                return $chat;
            }
        }
        $last = end($messages);
        if (is_array($last) && ($last['role'] ?? '') === $role && ($last['content'] ?? '') === $content && ($last['type'] ?? 'message') === $type) {
            return $chat;
        }
        $messages[] = $message;
        $chat['messages'] = $messages;
        $chat['intent'] = clean_text($chat['intent'] ?? $intent, 80) ?: $intent;
        if (empty($chat['first_message_at'])) $chat['first_message_at'] = $now;
        if (empty($chat['started_at'])) $chat['started_at'] = $chat['first_message_at'];
        $chat['last_message_at'] = $now;
        if ($name !== '') $chat['user_name'] = $name;
        if ($phone !== '') $chat['user_phone'] = $phone;
        $chat['status'] = $chat['status'] ?? 'open';
        $chat['admin_note'] = $chat['admin_note'] ?? '';
        $chats[$index] = $chat;
    }

    write_chats($chats);
    if ($phone !== '' || $name !== '') {
        upsert_lead([
            'session_id' => $sessionId,
            'name' => $name,
            'phone' => $phone,
            'source' => 'chat',
            'intent' => $intent,
            'status' => 'new',
        ]);
    }
    return $index < 0 ? end($chats) : $chats[$index];
}

function mark_lead_converted(string $phone): void
{
    $phone = normalize_mobile($phone);
    if ($phone === '') {
        return;
    }
    $leads = read_leads();
    foreach ($leads as &$lead) {
        if (($lead['phone'] ?? '') === $phone) {
            $lead['follow_status'] = 'converted';
            $lead['status'] = 'converted';
            $lead['updated_at'] = gmdate('c');
        }
    }
    unset($lead);
    write_leads($leads);
}

function latest_items(array $items, int $limit, string $dateField = 'created_at'): array
{
    usort($items, fn($a, $b) => strcmp((string) ($b[$dateField] ?? ''), (string) ($a[$dateField] ?? '')));
    return array_slice($items, 0, $limit);
}

function extract_last_user_message(array $chat): string
{
    $messages = $chat['messages'] ?? [];
    if (!is_array($messages)) {
        return '';
    }
    foreach (array_reverse($messages) as $message) {
        if (($message['role'] ?? '') === 'user') {
            return (string) ($message['content'] ?? '');
        }
    }
    return '';
}

function extract_last_message(array $chat): array
{
    $messages = $chat['messages'] ?? [];
    if (!is_array($messages) || !$messages) {
        return [];
    }
    $last = end($messages);
    return is_array($last) ? $last : [];
}

function chat_message_count(array $chat): int
{
    return is_array($chat['messages'] ?? null) ? count($chat['messages']) : 0;
}
