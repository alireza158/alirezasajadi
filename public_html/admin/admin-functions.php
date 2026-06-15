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
    return [
        'features' => [],
        'projects' => [],
        'audience' => [],
        'curriculum' => [],
        'results' => [],
        'testimonials' => [],
        'faqs' => [],
    ];
}

function read_landing_content(): array
{
    return array_merge(default_landing_content(), read_json_file(LANDING_CONTENT_FILE, default_landing_content()));
}

function write_landing_content(array $content): bool
{
    return write_json_file(LANDING_CONTENT_FILE, array_merge(default_landing_content(), $content));
}

function decode_admin_json_field(string $value, string $field, array &$errors): array
{
    $decoded = json_decode($value, true);
    if (!is_array($decoded)) {
        $errors[] = 'ساختار JSON بخش ' . $field . ' معتبر نیست.';
        return [];
    }
    return $decoded;
}


function default_cms_content(): array
{
    return [
        'hero' => [[
            'id'=>1,'eyebrow'=>'مسیر عملی طراحی سایت با AI','title'=>'طراحی سایت را سریع‌تر، دقیق‌تر و حرفه‌ای‌تر یاد بگیر','description'=>'قدم‌به‌قدم از ایده خام به یک صفحه زیبا و قابل ارائه برس؛ با تمرکز روی چیدمان، متن، تجربه کاربری و اصلاح خروجی.','image'=>'./assets/instructor-hero-BtbWE4mA.jpg','mobile_image'=>'./assets/instructor-hero-BtbWE4mA.jpg','image_alt'=>'مدرس دوره طراحی سایت با هوش مصنوعی','button_1_text'=>'شروع آموزش','button_1_link'=>'#start','button_1_action'=>'consultation','button_2_text'=>'دیدن مسیر یادگیری','button_2_link'=>'#path','button_2_action'=>'scroll','button_3_text'=>'مشاوره رایگان','button_3_link'=>'#start','button_3_action'=>'consultation','trust_text'=>'بدون حفظ کردن کدهای پیچیده؛ با تمرین عملی و خروجی قابل نمایش','cards'=>'مسیر مرحله‌به‌مرحله\nتمرین‌های کاربردی\nمناسب شروع جدی\nآماده برای نمونه‌کار','float_cards'=>'Idea → Page\nنمونه‌کار آماده\nAI Assistant','sort_order'=>1,'status'=>'active'
        ]],
        'challenges' => [
            ['id'=>1,'number'=>'۰۱','title'=>'آموزش‌های پراکنده','description'=>'ویدئوهای زیادی می‌بینی، اما مسیر مشخصی برای شروع و پایان پروژه نداری.','icon'=>'','image'=>'','sort_order'=>1,'status'=>'active'],
            ['id'=>2,'number'=>'۰۲','title'=>'ترس از خطاها','description'=>'با اولین ارورها متوقف می‌شوی، چون روش تحلیل و اصلاح مرحله‌ای را تمرین نکرده‌ای.','icon'=>'','image'=>'','sort_order'=>2,'status'=>'active'],
            ['id'=>3,'number'=>'۰۳','title'=>'نبود نمونه‌کار','description'=>'دانش زمانی ارزشمند می‌شود که به یک خروجی قابل نمایش تبدیل شود.','icon'=>'','image'=>'','sort_order'=>3,'status'=>'active'],
            ['id'=>4,'number'=>'۰۴','title'=>'سردرگمی بین ابزارها','description'=>'وقتی معیار انتخاب نداری، هر ابزار تازه می‌تواند تمرکزت را از بین ببرد.','icon'=>'','image'=>'','sort_order'=>4,'status'=>'active'],
        ],
        'learning_path' => [
            ['id'=>1,'number'=>'۱','title'=>'تعریف ایده و هدف','description'=>'مخاطب، پیام اصلی و نتیجه مورد انتظار را مشخص می‌کنی.','icon'=>'','image'=>'','sort_order'=>1,'status'=>'active'],
            ['id'=>2,'number'=>'۲','title'=>'چیدن ساختار صفحه','description'=>'ایده را به بخش‌های خوانا، تیترهای مؤثر و مسیر تبدیل کاربر تبدیل می‌کنی.','icon'=>'','image'=>'','sort_order'=>2,'status'=>'active'],
            ['id'=>3,'number'=>'۳','title'=>'ساخت نسخه اولیه','description'=>'با دستیار هوشمند، چیدمان و ظاهر پایه را سریع‌تر آماده می‌کنی.','icon'=>'','image'=>'','sort_order'=>3,'status'=>'active'],
            ['id'=>4,'number'=>'۴','title'=>'بهبود متن و ظاهر','description'=>'فاصله‌ها، رنگ، تایپوگرافی و تجربه کاربری را مرحله‌به‌مرحله بهتر می‌کنی.','icon'=>'','image'=>'','sort_order'=>4,'status'=>'active'],
            ['id'=>5,'number'=>'۵','title'=>'تحویل خروجی قابل ارائه','description'=>'فایل‌ها را مرتب می‌کنی و پروژه را برای نمایش یا نمونه‌کار آماده می‌سازی.','icon'=>'','image'=>'','sort_order'=>5,'status'=>'active'],
        ],
        'course_features' => array_map(fn($x,$i)=>['id'=>$i+1,'course'=>'آموزش طراحی سایت با هوش مصنوعی','title'=>$x,'description'=>'','icon'=>'','sort_order'=>$i+1,'status'=>'active'], ['آموزش ساده و مرحله‌به‌مرحله','شروع از نصب ابزارها و ساخت اولین صفحه','ساخت صفحات حرفه‌ای با کمک هوش مصنوعی','طراحی سایت واکنش‌گرا برای موبایل و دسکتاپ','ساخت سایت کامل‌تر با فرانت‌اند و بک‌اند','ساخت فرم‌ها، ثبت اطلاعات و فرآیند خرید','آشنایی با ساخت وب‌اپ و نرم‌افزار تحت وب','آماده‌سازی خروجی قابل ارائه','مناسب برای ساخت نمونه‌کار اولیه و پروژه واقعی'], array_keys(['a','b','c','d','e','f','g','h','i'])),
        'instructor' => [['id'=>1,'name'=>'علی‌رضا سجادی','job_title'=>'مدرس طراحی سایت با ابزارهای هوشمند','short_bio'=>'تجربه ساخت صفحات وب را به زبان ساده و عملی یاد می‌گیری.','description'=>'هدف، دیدن چند دستور آماده نیست؛ هدف این است که بتوانی ایده را به ساختار، طراحی و خروجی تمیز تبدیل کنی.','image'=>'./assets/instructor-hero-BtbWE4mA.jpg','mobile_image'=>'./assets/instructor-hero-BtbWE4mA.jpg','trust_features'=>'تجربه طراحی وب\nتمرکز روی خروجی قابل ارائه\nآموزش ساده و کاربردی\nمناسب شروع حرفه‌ای','instagram'=>'','linkedin'=>'','telegram'=>'','sort_order'=>1,'status'=>'active']],
        'ctas' => [['id'=>1,'internal_title'=>'CTA شروع مسیر','title'=>'آماده‌ای اولین خروجی جدی‌ات را بسازی؟','description'=>'از همین‌جا طراحی سایت را با برنامه، تمرین و بازخورد مرحله‌ای شروع کن.','button_text'=>'شروع یادگیری','button_link'=>'#curriculum','action'=>'consultation','variant'=>'light','location'=>'start','sort_order'=>1,'status'=>'active']],
        'seo' => [['id'=>1,'page'=>'home','title'=>'آموزش طراحی سایت با هوش مصنوعی | علی‌رضا سجادی','meta_description'=>'یک مسیر عملی و مرحله‌به‌مرحله برای ساخت لندینگ، سایت شخصی و نمونه‌کار حرفه‌ای با کمک ابزارهای هوشمند.','meta_keywords'=>'آموزش طراحی سایت با هوش مصنوعی، طراحی سایت با AI','canonical'=>'','og_title'=>'آموزش طراحی سایت با هوش مصنوعی','og_description'=>'از ایده تا خروجی قابل ارائه؛ طراحی سایت را با مسیر عملی یاد بگیر.','og_image'=>'./assets/instructor-hero-BtbWE4mA.jpg','twitter_title'=>'آموزش طراحی سایت با هوش مصنوعی','twitter_description'=>'مسیر عملی ساخت سایت و نمونه‌کار با ابزارهای هوشمند.','twitter_image'=>'./assets/instructor-hero-BtbWE4mA.jpg','robots'=>'index,follow','sort_order'=>1,'status'=>'active']],
        'media'=>[], 'menus'=>[], 'sections'=>[], 'portfolio_categories'=>[], 'registrations'=>[], 'consultations'=>[], 'users'=>[]
    ];
}

function read_cms_content(): array { return array_merge(default_cms_content(), read_json_file(CMS_CONTENT_FILE, default_cms_content())); }
function write_cms_content(array $content): bool { return write_json_file(CMS_CONTENT_FILE, array_merge(default_cms_content(), $content)); }
function active_sorted(array $items): array { $items=array_values(array_filter($items, fn($i)=>($i['status']??'active')==='active')); usort($items, fn($a,$b)=>(int)($a['sort_order']??0)<=>(int)($b['sort_order']??0)); return $items; }
function lines_to_array(mixed $value): array { return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string)$value) ?: []), fn($v)=>$v!=='')); }
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
