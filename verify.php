<?php
$merchant_id = "0babc2f6-e2e7-43db-a75c-35ba6fb361a9"; // مرچنت کد واقعی خودت
$amount = 1990000; // مبلغ پرداختی (ریال)
$authority = $_GET['Authority'];

$data = [
    "merchant_id" => $merchant_id,
    "authority" => $authority,
    "amount" => $amount
];

$jsonData = json_encode($data);

$ch = curl_init('https://api.zarinpal.com/pg/v4/payment/verify.json');
curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v4');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($jsonData)
]);
$result = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);
$result = json_decode($result, true);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>نتیجه پرداخت</title>
<style>
  body {
    font-family: sans-serif;
    background-color: #f9fafb;
    text-align: center;
    padding: 40px;
  }
  .box {
    background: white;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    display: inline-block;
    max-width: 400px;
  }
  h2 {
    color: #10b981;
  }
  .fail h2 {
    color: #ef4444;
  }
  a.button {
    display: inline-block;
    background-color: #0088cc;
    color: white;
    text-decoration: none;
    padding: 12px 24px;
    border-radius: 10px;
    margin-top: 20px;
    font-weight: bold;
  }
  a.button:hover {
    background-color: #007ab8;
  }
  .ref {
    font-size: 0.9rem;
    color: #555;
    margin-top: 12px;
  }
</style>
</head>
<body>
<?php
if ($err) {
    echo '<div class="box fail"><h2>❌ خطا در ارتباط با زرین‌پال</h2><p>'.$err.'</p></div>';
} else {
    if (isset($result['data']['code']) && $result['data']['code'] == 100) {
        $ref_id = $result['data']['ref_id'];
        echo '
        <div class="box success">
          <h2>✅ پرداخت با موفقیت انجام شد</h2>
          <p class="ref">شماره پیگیری: <strong>'.$ref_id.'</strong></p>
          <p>از خرید شما سپاسگزاریم 🌸</p>
          <a href="https://t.me/YourTelegramChannel" class="button" target="_blank">مشاهده دوره در تلگرام</a>
        </div>';
    } else {
        echo '
        <div class="box fail">
          <h2>❌ پرداخت ناموفق بود</h2>
          <p>در صورت کسر وجه، مبلغ طی ۷۲ ساعت بازگشت داده می‌شود.</p>
        </div>';
    }
}
?>
</body>
</html>
