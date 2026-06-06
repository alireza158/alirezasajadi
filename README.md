# راه‌اندازی سایت اصلی و سرویس Node.js چت‌بات

این پروژه برای هاست اشتراکی‌ای آماده شده که اجازه نمی‌دهد `Application Root` برنامه Node.js روی `public_html` قرار بگیرد. بنابراین سایت اصلی داخل `public_html` می‌ماند و سرویس چت‌بات به‌صورت مستقل داخل `ai-consultant` اجرا می‌شود.

## ساختار نهایی پوشه‌ها

روی هاست ساختار را به شکل زیر نگه دارید:

```text
/home/USER/
├── public_html/
│   ├── index.html
│   ├── assets/
│   │   ├── landing.js
│   │   └── landing.css
│   ├── admin/
│   ├── data/
│   │   ├── orders.json
│   │   ├── chats.json
│   │   ├── leads.json
│   │   └── settings.json
│   ├── payment.php
│   ├── verify.php
│   ├── request.php
│   ├── track.php
│   ├── save-chat-message.php
│   ├── style.css یا فایل‌های CSS داخل assets/
│   └── سایر فایل‌های استاتیک سایت
└── ai-consultant/
    ├── server.js
    ├── package.json
    ├── package-lock.json
    ├── .env
    ├── .env.example
    └── data/
        ├── chats.json
        └── leads.json
```

> نکته: فایل `.env` واقعی را روی هاست بسازید و کلید واقعی GapGPT را فقط همان‌جا قرار دهید. داخل مخزن فقط `.env.example` قرار دارد.

## تنظیمات Node.js App در پنل هاست

در پنل Node.js هاست این مقادیر را وارد کنید:

```text
Application root: ai-consultant
Application URL: alirezasajadi.ir/ai-consultant
Application startup file: server.js
Application mode: Production
Node.js version: 18 یا 20 در صورت وجود
```

اگر هاست فقط Node.js 10 داشت، داخل پوشه `ai-consultant` این وابستگی را اضافه کنید:

```bash
npm install node-fetch@2
```

کد `server.js` ابتدا از `fetch` داخلی Node.js 18/20 استفاده می‌کند و اگر موجود نبود، `node-fetch` را `require` می‌کند.

## فایل‌های سرویس چت‌بات

### `ai-consultant/package.json`

```json
{
  "name": "ai-consultant-service",
  "version": "1.0.0",
  "private": true,
  "description": "Independent Express API service for the GapGPT AI consultant chatbot.",
  "main": "server.js",
  "scripts": {
    "start": "node server.js",
    "check": "node --check server.js"
  },
  "engines": {
    "node": ">=18"
  },
  "dependencies": {
    "cors": "^2.8.5",
    "dotenv": "^16.4.7",
    "express": "^4.21.2"
  }
}
```

برای Node.js 10، بعد از نصب معمولی، `node-fetch` نسخه 2 را هم اضافه کنید:

```bash
cd ai-consultant
npm install node-fetch@2
```

### `ai-consultant/.env.example`

```env
PORT=3000
GAPGPT_BASE_URL=https://api.gapgpt.app/v1
GAPGPT_MODEL=gapgpt-qwen-3.6
GAPGPT_API_KEY=YOUR_API_KEY_HERE
CORS_ORIGIN=https://alirezasajadi.ir
```

روی هاست یک فایل `ai-consultant/.env` با همین محتوا بسازید و مقدار `GAPGPT_API_KEY` را با کلید واقعی جایگزین کنید.

### APIهای سرویس Node.js

سرویس Node.js فقط API چت‌بات را سرو می‌کند و فایل‌های سایت اصلی را `static` سرو نمی‌کند، چون سایت اصلی داخل `public_html` است.

Endpointها:

```text
POST /api/ai-consultant
POST /api/save-chat-message
```

وقتی `Application URL` برابر `alirezasajadi.ir/ai-consultant` باشد، آدرس بیرونی endpointها این‌ها هستند:

```text
https://alirezasajadi.ir/ai-consultant/api/ai-consultant
https://alirezasajadi.ir/ai-consultant/api/save-chat-message
```

ذخیره چت‌ها و لیدهایی که Node.js ثبت می‌کند با مسیر امن و مستقل زیر انجام می‌شود:

```js
path.join(__dirname, "data", "chats.json")
path.join(__dirname, "data", "leads.json")
```

## مسیرهای درست `fetch` در فرانت‌اند

داخل فایل JavaScript سایت، مسیرهای قبلی:

```js
fetch("/api/ai-consultant", ...)
fetch("/api/save-chat-message", ...)
```

به مسیرهای جدید تغییر کرده‌اند:

```js
fetch("/ai-consultant/api/ai-consultant", ...)
fetch("/ai-consultant/api/save-chat-message", ...)
```

## نصب و اجرا روی هاست

1. فایل‌های سایت را داخل `public_html` آپلود کنید.
2. پوشه `ai-consultant` را کنار `public_html` آپلود کنید، نه داخل آن.
3. در `ai-consultant` فایل `.env` را از روی `.env.example` بسازید و کلید واقعی را قرار دهید.
4. در پنل Node.js هاست، App را با تنظیمات بالا بسازید.
5. گزینه **Run NPM Install** را بزنید.
6. گزینه **Restart App** را بزنید.
7. API را تست کنید:

```bash
curl -X POST https://alirezasajadi.ir/ai-consultant/api/ai-consultant \
  -H "Content-Type: application/json" \
  -d '{"message":"سلام","history":[]}'
```

برای اجرای محلی سرویس Node.js:

```bash
cd ai-consultant
cp .env.example .env
# مقدار GAPGPT_API_KEY را در .env تنظیم کنید
npm install
npm start
```

## تست نهایی و رفع خطا

### خروجی موفق

اگر API درست باشد، پاسخ JSON برمی‌گردد، مثلاً:

```json
{
  "answer": "سلام، خوش اومدی..."
}
```

### خطای 404

معمولاً یعنی یکی از این موارد اشتباه است:

- `Application URL` در پنل هاست دقیقاً `alirezasajadi.ir/ai-consultant` نیست.
- در فرانت‌اند هنوز به `/api/ai-consultant` درخواست زده می‌شود.
- سرویس Node.js ری‌استارت نشده است.

### خطای 500 یا 502

معمولاً یعنی یکی از این موارد مشکل دارد:

- مقدارهای `.env` درست بارگذاری نشده‌اند.
- `GAPGPT_API_KEY` خالی یا placeholder است.
- روی Node.js قدیمی، `node-fetch@2` نصب نشده است.
- ارتباط سرور هاست با `https://api.gapgpt.app/v1` برقرار نمی‌شود.

### خطای CORS

- مقدار `CORS_ORIGIN` باید `https://alirezasajadi.ir` باشد.
- بهتر است فرانت‌اند با مسیر relative زیر درخواست بزند تا مبدأ همان دامنه سایت باشد:

```js
fetch("/ai-consultant/api/ai-consultant", ...)
```

### خطای API Key

- مقدار `GAPGPT_API_KEY` را داخل `ai-consultant/.env` بررسی کنید.
- مقدار نباید `YOUR_API_KEY_HERE` یا `PUT_API_KEY_HERE` باشد.
- بعد از تغییر `.env` حتماً از پنل Node.js گزینه **Restart App** را بزنید.
