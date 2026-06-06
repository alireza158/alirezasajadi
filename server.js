const express = require("express");
const cors = require("cors");
const dotenv = require("dotenv");
const path = require("path");

const rootDir = __dirname;
dotenv.config({ path: path.join(rootDir, ".env") });
dotenv.config({ path: path.join(rootDir, ".env.local"), override: true });

const app = express();
const port = process.env.PORT || 3000;

const fallbackErrorMessage =
  "الان ارتباط با مشاور هوشمند برقرار نشد. لطفاً شماره‌ات رو بفرست تا مشاور انسانی باهات تماس بگیره.";

const systemPrompt = `تو یک مشاور فروش حرفه‌ای برای دوره «آموزش طراحی سایت با هوش مصنوعی» هستی.

وظیفه تو این است که به سوالات کاربران درباره دوره پاسخ بدهی، سطح کاربر را تشخیص بدهی، نگرانی‌های او را برطرف کنی و در زمان مناسب او را به ثبت‌نام یا ارسال شماره موبایل هدایت کنی.

قوانین:
- پاسخ‌ها کوتاه، فارسی، صمیمی و قابل فهم باشند.
- لحن حرفه‌ای ولی دوستانه باشد.
- وعده غیرواقعی نده.
- اگر کاربر مبتدی بود، اطمینان بده که آموزش پروژه‌محور و مناسب شروع است.
- اگر کاربر درباره ابزارهای دقیق پشت‌صحنه پرسید، وارد جزئیات ابزارهای خاص نشو.
- اگر سوال خارج از اطلاعات دوره بود، بگو برای پاسخ دقیق‌تر بهتر است با مشاور انسانی صحبت کند.
- در پایان اکثر پاسخ‌ها یک سوال کوتاه بپرس.
- اگر کاربر علاقه‌مند شد، او را به ارسال شماره موبایل یا ثبت‌نام هدایت کن.

اطلاعات دوره:
نام دوره: آموزش طراحی سایت با هوش مصنوعی
مدرس: علی‌رضا سجادی
نوع آموزش: آنلاین و پروژه‌محور
مخاطب: افراد مبتدی، صاحبان کسب‌وکار، فریلنسرهای تازه‌کار، تولیدکنندگان محتوا و کسانی که می‌خواهند سایت واقعی بسازند
هدف دوره: یادگیری ساخت سایت واقعی با کمک هوش مصنوعی
پیش‌نیاز: آشنایی اولیه با کامپیوتر و اینترنت
نتیجه دوره: توانایی ساخت صفحات سایت، لندینگ، سایت شخصی، سایت آموزشی و نمونه‌کار اولیه
لحن برند: حرفه‌ای، ساده، صمیمی و نتیجه‌محور`;

app.use(cors({ origin: process.env.CORS_ORIGIN || false }));
app.use(express.json({ limit: "24kb" }));

function cleanMessage(value, maxLength = 900) {
  if (typeof value !== "string") {
    return "";
  }

  return value.replace(/[\u0000-\u001f\u007f]/g, " ").trim().slice(0, maxLength);
}

function normalizeHistory(history) {
  if (!Array.isArray(history)) {
    return [];
  }

  return history
    .filter((item) => item && ["user", "assistant"].includes(item.role))
    .map((item) => ({
      role: item.role,
      content: cleanMessage(item.content, 700),
    }))
    .filter((item) => item.content)
    .slice(-8);
}

app.post("/api/ai-consultant", async (req, res) => {
  const message = cleanMessage(req.body?.message);
  const history = normalizeHistory(req.body?.history);

  if (!message) {
    return res.status(400).json({ error: "لطفاً پیام خودت رو بنویس." });
  }

  const apiKey = process.env.GAPGPT_API_KEY;
  const baseUrl = process.env.GAPGPT_BASE_URL || "https://api.gapgpt.app/v1";
  const model = process.env.GAPGPT_MODEL || "gpt-5.3-chat-latest";

  if (!apiKey || apiKey === "PUT_API_KEY_HERE") {
    return res.status(503).json({ error: fallbackErrorMessage });
  }

  const controller = new AbortController();
  const timeout = setTimeout(() => controller.abort(), 30000);

  try {
    const response = await fetch(`${baseUrl.replace(/\/$/, "")}/chat/completions`, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${apiKey}`,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        model,
        messages: [
          { role: "system", content: systemPrompt },
          ...history,
          { role: "user", content: message },
        ],
        temperature: 0.6,
        max_tokens: 500,
      }),
      signal: controller.signal,
    });

    if (!response.ok) {
      return res.status(502).json({ error: fallbackErrorMessage });
    }

    const data = await response.json();
    const answer = data?.choices?.[0]?.message?.content?.trim();

    if (!answer) {
      return res.status(502).json({ error: fallbackErrorMessage });
    }

    return res.json({ answer });
  } catch (error) {
    return res.status(502).json({ error: fallbackErrorMessage });
  } finally {
    clearTimeout(timeout);
  }
});

app.use(express.static(rootDir));

app.listen(port, () => {
  console.log(`AI consultant server is running on http://localhost:${port}`);
});
