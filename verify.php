<?php
declare(strict_types=1);

const MERCHANT_ID = '0babc2f6-e2e7-43db-a75c-35ba6fb361a9';
const ZARINPAL_VERIFY_URL = 'https://api.zarinpal.com/pg/v4/payment/verify.json';
const ORDERS_FILE = __DIR__ . '/orders.json';
const SITE_URL = 'https://alirezasajadi.ir/';
const COURSE_PRICE_TOMAN_LABEL = '۵,۵۰۰,۰۰۰ تومان';

function ensure_orders_file(): void
{
    if (!file_exists(ORDERS_FILE)) {
        file_put_contents(ORDERS_FILE, json_encode([], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX);
    }
}

function read_orders(): array
{
    ensure_orders_file();
    $content = file_get_contents(ORDERS_FILE);
    if ($content === false || trim($content) === '') {
        return [];
    }

    $orders = json_decode($content, true);
    return is_array($orders) ? $orders : [];
}

function write_orders(array $orders): bool
{
    ensure_orders_file();
    $json = json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if ($json === false) {
        return false;
    }

    return file_put_contents(ORDERS_FILE, $json . PHP_EOL, LOCK_EX) !== false;
}

function find_order_index_by_authority(array $orders, string $authority): int
{
    foreach ($orders as $index => $order) {
        if (($order['authority'] ?? '') === $authority) {
            return (int) $index;
        }
    }

    return -1;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function send_zarinpal_verify(array $payload): array
{
    $jsonData = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($jsonData === false) {
        return ['curl_error' => 'خطا در آماده‌سازی اطلاعات تأیید پرداخت'];
    }

    $ch = curl_init(ZARINPAL_VERIFY_URL);
    curl_setopt_array($ch, [
        CURLOPT_USERAGENT => 'ZarinPal Rest Api v4',
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData),
        ],
    ]);

    $result = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        return ['curl_error' => $curlError];
    }

    $decoded = json_decode((string) $result, true);
    return is_array($decoded) ? $decoded : ['errors' => ['message' => 'پاسخ تأیید پرداخت قابل خواندن نیست']];
}

function render_result(string $type, string $title, array $order = [], ?string $message = null): never
{
    $isSuccess = $type === 'success';
    $boxClass = $isSuccess ? 'success' : 'fail';
    $icon = $isSuccess ? '✅' : '❌';
    $trackingCode = $order['tracking_code'] ?? '';
    $refId = $order['ref_id'] ?? '';
    $courseTitle = $order['course_title'] ?? 'آموزش طراحی سایت با هوش مصنوعی';
    $buyerName = $order['name'] ?? '';
    ?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($title) ?></title>
  <style>
    body { margin: 0; font-family: Tahoma, Arial, sans-serif; background: #f6f7fb; color: #1f2937; text-align: center; padding: 40px 16px; }
    .box { background: #fff; border-radius: 20px; padding: 32px; box-shadow: 0 16px 40px rgba(15, 23, 42, .10); display: inline-block; width: min(100%, 520px); text-align: right; }
    h1 { margin: 0 0 16px; font-size: 1.6rem; text-align: center; }
    .success h1 { color: #059669; }
    .fail h1 { color: #dc2626; }
    .message { text-align: center; color: #4b5563; line-height: 1.9; }
    .details { margin: 24px 0; padding: 0; list-style: none; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; }
    .details li { display: flex; justify-content: space-between; gap: 16px; padding: 14px 16px; border-bottom: 1px solid #e5e7eb; }
    .details li:last-child { border-bottom: 0; }
    .details span { color: #6b7280; }
    .details strong { direction: ltr; text-align: left; }
    .actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; margin-top: 24px; }
    .button { display: inline-block; background: #2563eb; color: #fff; text-decoration: none; padding: 12px 22px; border-radius: 12px; font-weight: 700; }
    .button.secondary { background: #111827; }
  </style>
</head>
<body>
  <main class="box <?= e($boxClass) ?>">
    <h1><?= $icon ?> <?= e($title) ?></h1>
    <?php if ($message): ?>
      <p class="message"><?= e($message) ?></p>
    <?php endif; ?>

    <?php if ($isSuccess): ?>
      <ul class="details">
        <li><span>نام دوره</span><strong><?= e($courseTitle) ?></strong></li>
        <li><span>نام خریدار</span><strong><?= e($buyerName) ?></strong></li>
        <li><span>مبلغ پرداخت‌شده</span><strong><?= COURSE_PRICE_TOMAN_LABEL ?></strong></li>
        <li><span>کد پیگیری سفارش</span><strong><?= e($trackingCode) ?></strong></li>
        <li><span>شماره پیگیری پرداخت</span><strong><?= e((string) $refId) ?></strong></li>
      </ul>
    <?php else: ?>
      <p class="message">پرداخت انجام نشد یا توسط کاربر لغو شد.</p>
      <?php if ($trackingCode !== ''): ?>
        <ul class="details">
          <li><span>کد پیگیری سفارش</span><strong><?= e($trackingCode) ?></strong></li>
        </ul>
      <?php endif; ?>
    <?php endif; ?>

    <div class="actions">
      <?php if (!$isSuccess): ?>
        <a class="button" href="<?= e(SITE_URL) ?>#start">تلاش دوباره</a>
      <?php endif; ?>
      <a class="button secondary" href="<?= e(SITE_URL) ?>">بازگشت به سایت</a>
    </div>
  </main>
</body>
</html>
    <?php
    exit;
}

$status = trim((string) ($_GET['Status'] ?? ''));
$authority = trim((string) ($_GET['Authority'] ?? ''));

if ($authority === '') {
    render_result('fail', 'خطا در بازگشت از درگاه', [], 'شناسه پرداخت از زرین‌پال دریافت نشد.');
}

$orders = read_orders();
$orderIndex = find_order_index_by_authority($orders, $authority);

if ($orderIndex < 0) {
    render_result('fail', 'سفارش پیدا نشد', [], 'سفارشی با این شناسه پرداخت در سیستم ثبت نشده است.');
}

$order = $orders[$orderIndex];

if (($order['status'] ?? '') === 'paid') {
    render_result('success', 'این سفارش قبلاً پرداخت شده است', $order, 'پرداخت این سفارش پیش‌تر با موفقیت ثبت شده است.');
}

if (strtoupper($status) !== 'OK') {
    $orders[$orderIndex]['status'] = 'failed';
    $orders[$orderIndex]['zarinpal_response'] = ['status' => $status, 'message' => 'پرداخت توسط کاربر لغو شد یا تأیید نشد.'];
    write_orders($orders);
    render_result('fail', 'پرداخت ناموفق بود', $orders[$orderIndex]);
}

$verifyPayload = [
    'merchant_id' => MERCHANT_ID,
    'amount' => (int) ($order['amount'] ?? 0),
    'authority' => $authority,
];
$verifyResponse = send_zarinpal_verify($verifyPayload);
$code = (int) ($verifyResponse['data']['code'] ?? 0);

$orders[$orderIndex]['zarinpal_response'] = $verifyResponse;

if (!empty($verifyResponse['curl_error'])) {
    $orders[$orderIndex]['status'] = 'failed';
    write_orders($orders);
    render_result('fail', 'خطا در ارتباط با زرین‌پال', $orders[$orderIndex], 'امکان تأیید پرداخت در این لحظه وجود ندارد. اگر مبلغی کسر شده باشد، وضعیت را با پشتیبانی پیگیری کنید.');
}

if ($code === 100 || $code === 101) {
    $orders[$orderIndex]['status'] = 'paid';
    $orders[$orderIndex]['ref_id'] = $verifyResponse['data']['ref_id'] ?? null;
    $orders[$orderIndex]['paid_at'] = gmdate('c');
    write_orders($orders);
    render_result('success', 'پرداخت با موفقیت انجام شد', $orders[$orderIndex], 'از خرید شما سپاسگزاریم. اطلاعات سفارش شما با موفقیت ثبت شد.');
}

$orders[$orderIndex]['status'] = 'failed';
write_orders($orders);
render_result('fail', 'پرداخت ناموفق بود', $orders[$orderIndex], 'تأیید پرداخت توسط زرین‌پال ناموفق بود. در صورت کسر وجه، مبلغ طبق روال بانکی بازگردانده می‌شود.');
