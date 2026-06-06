# آموزش طراحی سایت با هوش مصنوعی

این پروژه یک لندینگ استاتیک برای دوره «آموزش طراحی سایت با هوش مصنوعی» است و برای چت مشاوره هوشمند، یک API proxy امن با Node.js/Express دارد تا کلید GapGPT در فرانت‌اند دیده نشود.

## راه‌اندازی

```bash
npm install
npm run dev
# یا مستقیم:
node server.js
```

سپس سایت را در آدرس زیر باز کنید:

```text
http://localhost:3000
```

## تنظیم کلید GapGPT

متغیرهای محیطی در `.env.local` تعریف شده‌اند. مقدار placeholder را با کلید واقعی سرور جایگزین کنید و کلید واقعی را داخل فایل‌های HTML، CSS یا JavaScript فرانت‌اند قرار ندهید.

```env
GAPGPT_API_KEY=PUT_API_KEY_HERE
GAPGPT_BASE_URL=https://api.gapgpt.app/v1
GAPGPT_MODEL=gpt-5.3-chat-latest
```

## Endpoint داخلی چت

فرانت‌اند فقط به endpoint داخلی زیر درخواست می‌زند:

```text
POST /api/ai-consultant
```

بدنه درخواست:

```json
{
  "message": "متن پیام کاربر",
  "history": []
}
```
