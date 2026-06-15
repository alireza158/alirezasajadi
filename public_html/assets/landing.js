const LANDING_DATA = window.LANDING_DATA || {};
const activeItems = (key) => (Array.isArray(LANDING_DATA[key]) ? LANDING_DATA[key] : [])
  .filter((item) => !item || (item.status !== "inactive" && item.show_home !== false))
  .sort((a, b) => (Number(a?.sort_order || 0) - Number(b?.sort_order || 0)));
const normalizePair = (item) => Array.isArray(item) ? { title: item[0] || "", description: item[1] || "" } : (item || {});
const features = activeItems("features");
const projects = activeItems("projects");
const audience = activeItems("audience");
const curriculum = activeItems("curriculum");
const results = activeItems("results");
const testimonials = activeItems("testimonials");
const faqs = activeItems("faqs");

const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

function createFeatureCard(item) {
  const feature = Array.isArray(item) ? { icon: item[0], title: item[1] } : item;
  return `
    <article class="glass-card">
      <span class="feature-icon">${feature.icon || "✦"}</span>
      <h3>${feature.title || ""}</h3>
      <p>${feature.description || "این مهارت را هنگام ساخت و اصلاح خروجی تمرین می‌کنی، نه فقط با تماشای آموزش."}</p>
    </article>
  `;
}

$("[data-feature-grid]").innerHTML = features
  .map((feature) => createFeatureCard(feature))
  .join("");

$("[data-project-grid]").innerHTML = projects
  .map(
    (project) => {
      const item = Array.isArray(project) ? { title: project[0], description: project[1], tags: project[2] || [], link: "#curriculum", button_text: "مشاهده دوره ←" } : project;
      const imageMarkup = item.image
        ? `<img src="${item.image}" alt="${item.title || "نمونه‌کار"}" loading="lazy" />`
        : `<div class="project-card-placeholder" aria-hidden="true"><span></span><span></span><span></span></div>`;
      return `
      <article class="project-card">
        <a class="project-card-image" href="${item.link || "#curriculum"}" aria-label="${item.title || "مشاهده نمونه‌کار"}">
          ${imageMarkup}
        </a>
        <div class="project-card-body">
          <h3>${item.title || ""}</h3>
          <p>${item.description || ""}</p>
          <div class="project-tags">${(item.tags || []).slice(0, 5).map((tag) => `<span>${tag}</span>`).join("")}</div>
        </div>
        <a class="project-link" href="${item.link || "#curriculum"}" data-open-advisor data-advisor-intent="course">
          <span>${item.button_text || "مشاهده پروژه"}</span><i aria-hidden="true">←</i>
        </a>
      </article>
    `;
    },
  )
  .join("");

$("[data-audience-grid]").innerHTML = audience
  .map(
    (rawItem, index) => {
      const item = typeof rawItem === "string" ? { title: rawItem } : rawItem;
      return `
      <article class="glass-card">
        <span class="feature-icon">${item.icon || index + 1}</span>
        <h3>${item.title || ""}</h3>
        ${item.description ? `<p>${item.description}</p>` : ""}
      </article>
    `;
    },
  )
  .join("");

$("[data-results-grid]").innerHTML = results
  .map(
    (rawItem) => {
      const item = typeof rawItem === "string" ? { title: rawItem, icon: "✓" } : rawItem;
      return `
      <article class="glass-card">
        <span class="feature-icon">${item.icon || "✓"}</span>
        <h3>${item.title || ""}</h3>
        ${item.description ? `<p>${item.description}</p>` : ""}
      </article>
    `;
    },
  )
  .join("");

$("[data-testimonials]").innerHTML = testimonials
  .map(
    (rawItem, index) => {
      const item = typeof rawItem === "string" ? { description: rawItem, title: `هنرجوی دوره ${index + 1}` } : rawItem;
      const avatar = item.image ? `<img class="testimonial-avatar" src="${item.image}" alt="${item.title || "هنرجوی دوره"}" loading="lazy" />` : `<div class="testimonial-avatar" aria-hidden="true"></div>`;
      return `
      <article class="glass-card">
        ${avatar}
        <p>«${item.description || ""}»</p>
        <strong>${item.title || `هنرجوی دوره ${index + 1}`}</strong>
        ${item.subtitle ? `<small>${item.subtitle}</small>` : ""}
      </article>
    `;
    },
  )
  .join("");

function renderAccordion(items, root) {
  root.innerHTML = items
    .map(
      (item, index) => `
        <article class="accordion-item ${index === 0 ? "open" : ""}">
          <button class="accordion-button" type="button" aria-expanded="${index === 0}">
            ${normalizePair(item).title}<span aria-hidden="true">+</span>
          </button>
          <div class="accordion-panel"><div><p>${normalizePair(item).description}</p></div></div>
        </article>
      `,
    )
    .join("");
}

renderAccordion(curriculum, $("[data-accordion]"));
renderAccordion(faqs, $("[data-faq]"));

$$(".accordion-button").forEach((button) => {
  button.addEventListener("click", () => {
    const item = button.closest(".accordion-item");
    const isOpen = item.classList.toggle("open");
    button.setAttribute("aria-expanded", isOpen);
  });
});

const header = $("[data-header]");
const progress = $(".scroll-progress");
const menuToggle = $("[data-menu-toggle]");
const navLinks = $("[data-nav-links]");

function updateScrollState() {
  const maxScroll = document.documentElement.scrollHeight - innerHeight;
  progress.style.transform = `scaleX(${maxScroll ? scrollY / maxScroll : 0})`;
  header.classList.toggle("scrolled", scrollY > 20);
}

updateScrollState();
addEventListener("scroll", updateScrollState, { passive: true });

menuToggle.addEventListener("click", () => {
  const isOpen = navLinks.classList.toggle("open");
  menuToggle.setAttribute("aria-expanded", isOpen);
});

$$(".nav-links a").forEach((link) => {
  link.addEventListener("click", () => {
    navLinks.classList.remove("open");
    menuToggle.setAttribute("aria-expanded", "false");
  });
});

const revealObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
        revealObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.14, rootMargin: "0px 0px -40px 0px" },
);

$$(".reveal,.stagger").forEach((element) => revealObserver.observe(element));

const heroVisual = $("[data-tilt]");
if (heroVisual && !matchMedia("(prefers-reduced-motion: reduce)").matches) {
  heroVisual.addEventListener("mousemove", (event) => {
    const rect = heroVisual.getBoundingClientRect();
    const x = (event.clientX - rect.left) / rect.width - 0.5;
    const y = (event.clientY - rect.top) / rect.height - 0.5;
    heroVisual.style.transform = `rotateY(${x * -5}deg) rotateX(${y * 5}deg)`;
  });

  heroVisual.addEventListener("mouseleave", () => {
    heroVisual.style.transform = "";
  });
}

const ADVISOR_HINT_MESSAGES = {
  consultation: "سلام 👋 خوش اومدی. هر سوالی درباره دوره داری بپرس، من راهنمایی‌ات می‌کنم.",
  register: "سلام 👋 اگر آماده ثبت‌نامی، دکمه «الان ثبت‌نام کنید» پایین چت فرم ثبت‌نام رو باز می‌کنه. اگر سوالی داری همین‌جا بپرس.",
  start: "سلام 🌱 خوش اومدی. درباره شروع یادگیری طراحی سایت با هوش مصنوعی هر سوالی داری بپرس.",
  course: "سلام 👋 اگر می‌خوای ببینی این دوره برای هدفت مناسبه، سوالت رو بنویس یا یکی از سوالات پیشنهادی رو انتخاب کن.",
  general: "سلام 👋 خوش اومدی. هر سوالی درباره دوره داری بپرس، من راهنمایی‌ات می‌کنم.",
};
const DEFAULT_ADVISOR_INTENT = "general";
const ADVISOR_LEAD_STORAGE_KEY = "advisorLeadData";
const ADVISOR_SESSION_STORAGE_KEY = "ai_advisor_session_id";
const LEGACY_ADVISOR_SESSION_STORAGE_KEY = "advisorSessionId";

const quickQuestions = [
  "این دوره برای مبتدی‌ها مناسبه؟",
  "بعد از دوره چی می‌تونم بسازم؟",
  "می‌تونم سایت کامل بسازم؟",
  "قیمت و شرایط ثبت‌نام چیه؟",
];

function normalizeAdvisorIntent(intent) {
  if (!intent) {
    return DEFAULT_ADVISOR_INTENT;
  }

  const normalizedIntent = String(intent).trim().toLowerCase();
  return ADVISOR_HINT_MESSAGES[normalizedIntent]
    ? normalizedIntent
    : DEFAULT_ADVISOR_INTENT;
}

function readAdvisorLeadData() {
  try {
    return JSON.parse(localStorage.getItem(ADVISOR_LEAD_STORAGE_KEY) || "{}") || {};
  } catch (error) {
    return {};
  }
}

function saveAdvisorLeadData(data) {
  localStorage.setItem(ADVISOR_LEAD_STORAGE_KEY, JSON.stringify(data));
}

function getAdvisorSessionId() {
  let sessionId = localStorage.getItem(ADVISOR_SESSION_STORAGE_KEY) || localStorage.getItem(LEGACY_ADVISOR_SESSION_STORAGE_KEY);
  if (!sessionId) {
    sessionId = `chat-${Date.now()}-${Math.random().toString(16).slice(2)}`;
  }
  localStorage.setItem(ADVISOR_SESSION_STORAGE_KEY, sessionId);
  localStorage.removeItem(LEGACY_ADVISOR_SESSION_STORAGE_KEY);
  return sessionId;
}

function trackEvent(payload) {
  return fetch("track.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
    keepalive: true,
  }).catch(() => {});
}

function trackLead(source = "register-form", extra = {}) {
  const data = { ...readAdvisorLeadData(), ...extra };
  return trackEvent({
    type: "lead",
    session_id: getAdvisorSessionId(),
    source,
    name: data.name || "",
    phone: data.phone || "",
    email: data.email || "",
    level: data.level || "",
    goal: data.goal || "",
    intent: data.intent || "general",
    status: data.status || "new",
    follow_status: data.follow_status || data.status || "new",
  });
}

function saveChatMessage(role, content, extra = {}) {
  const data = { ...readAdvisorLeadData(), ...extra };
  const payload = {
    type: extra.messageType || "message",
    message_id: extra.messageId || `msg-${Date.now()}-${Math.random().toString(16).slice(2)}`,
    session_id: getAdvisorSessionId(),
    role,
    content,
    user_name: data.name || "",
    user_phone: data.phone || "",
    intent: data.intent || "general",
  };

  return fetch("/ai-consultant/api/save-chat-message", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
    keepalive: true,
  })
    .then((response) => {
      if (response.ok) return response;
      throw new Error("node-save-unavailable");
    })
    .catch(() =>
      fetch("save-chat-message.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
        keepalive: true,
      }),
    )
    .catch(() =>
      trackEvent({
        ...payload,
        type: "chat_message",
      }),
    )
    .catch(() => {});
}

function createAiConsultantChat() {
  const widget = document.createElement("section");
  widget.className = "ai-chat-widget";
  widget.setAttribute("aria-label", "مشاوره هوشمند خرید دوره");
  widget.innerHTML = `
    <button class="ai-chat-launcher" type="button" aria-expanded="false" aria-controls="ai-consultant-panel" data-open-advisor data-advisor-intent="consultation">
      <span class="ai-chat-launcher-icon" aria-hidden="true">✨</span>
      <span>مشاوره هوشمند خرید دوره</span>
    </button>
    <div class="ai-chat-backdrop" aria-hidden="true"></div>
    <div class="ai-chat-panel" id="ai-consultant-panel" role="dialog" aria-modal="true" aria-label="گفتگوی مشاوره هوشمند">
      <header class="ai-chat-header">
        <div>
          <span class="ai-chat-kicker">AI Course Advisor</span>
          <strong>مشاور هوشمند خرید دوره</strong>
          <p>آزادانه سوالت رو بپرس؛ اگر آماده بودی ثبت‌نام هم همین‌جاست.</p>
        </div>
        <button class="ai-chat-close" type="button" aria-label="بستن چت">×</button>
      </header>
      <div class="ai-chat-messages" aria-live="polite" tabindex="0"></div>
      <section class="ai-chat-questions" aria-label="سوالات پیشنهادی مشاوره">
        <span class="ai-chat-questions-title">سوالات پیشنهادی</span>
        <div class="ai-chat-questions-grid" data-ai-chat-question-list></div>
      </section>
      <div class="ai-chat-register-action">
        <button class="advisor-register-cta" type="button">الان ثبت‌نام کنید</button>
      </div>
      <form class="ai-chat-form">
        <input class="ai-chat-input" type="text" inputmode="text" autocomplete="off" maxlength="900" placeholder="هر سوالی داری بنویس..." aria-label="پیام چت" />
        <button class="ai-chat-send" type="submit">ارسال</button>
      </form>
    </div>
  `;

  document.body.append(widget);

  const launcher = $(".ai-chat-launcher", widget);
  const backdrop = $(".ai-chat-backdrop", widget);
  const closeButton = $(".ai-chat-close", widget);
  const panel = $(".ai-chat-panel", widget);
  const messagesRoot = $(".ai-chat-messages", widget);
  const questionsRoot = $("[data-ai-chat-question-list]", widget);
  const questionsSection = $(".ai-chat-questions", widget);
  const registerCta = $(".advisor-register-cta", widget);
  const form = $(".ai-chat-form", widget);
  const input = $(".ai-chat-input", widget);
  const sendButton = $(".ai-chat-send", widget);

  let currentIntent = DEFAULT_ADVISOR_INTENT;
  let leadData = readAdvisorLeadData();
  let lastFocusedElement = null;
  const shownHints = new Set();
  let isWaitingForApi = false;
  const chatHistory = [];

  function isRegisterModalOpen() {
    return document.body.classList.contains("register-modal-open") || document.body.classList.contains("register-open");
  }

  function focusAdvisorInput() {
    setTimeout(() => {
      if (!isRegisterModalOpen() && !input.disabled) {
        input.focus({ preventScroll: true });
      }
    }, 180);
  }

  function setChatDisabled(disabled) {
    input.disabled = disabled || isWaitingForApi;
    sendButton.disabled = disabled || isWaitingForApi;
  }

  window.setAdvisorChatDisabled = setChatDisabled;

  function setOpen(isOpen) {
    const alreadyOpen = widget.classList.contains("open");
    widget.classList.toggle("open", isOpen);
    launcher.setAttribute("aria-expanded", String(isOpen));
    document.documentElement.classList.toggle("advisor-open", isOpen);
    document.body.classList.toggle("advisor-open", isOpen);

    if (isOpen) {
      if (!alreadyOpen) {
        lastFocusedElement = document.activeElement;
      }
      focusAdvisorInput();
      return;
    }

    if (lastFocusedElement && typeof lastFocusedElement.focus === "function") {
      lastFocusedElement.focus({ preventScroll: true });
    } else {
      launcher.focus({ preventScroll: true });
    }
  }

  function scrollMessagesToEnd() {
    messagesRoot.scrollTo({ top: messagesRoot.scrollHeight, behavior: "smooth" });
  }

  function appendMessage(role, text, options = {}) {
    const message = document.createElement("div");
    message.className = `ai-chat-message ${role === "user" ? "user" : "assistant"}`;

    const bubble = document.createElement("p");
    bubble.textContent = text;
    message.append(bubble);
    messagesRoot.append(message);
    scrollMessagesToEnd();

    if (!options.skipHistory) {
      chatHistory.push({ role: role === "user" ? "user" : "assistant", content: text });
      if (chatHistory.length > 12) {
        chatHistory.splice(0, chatHistory.length - 12);
      }
    }

    if (options.persist !== false) {
      saveChatMessage(role, text, {
        intent: currentIntent,
        messageType: options.messageType || "message",
        messageId: options.messageId,
      });
    }
    return message;
  }

  function appendTyping() {
    const typing = document.createElement("div");
    typing.className = "ai-chat-message assistant typing";
    typing.innerHTML = `<p><span></span><span></span><span></span></p>`;
    messagesRoot.append(typing);
    scrollMessagesToEnd();
    return typing;
  }

  function renderQuickQuestions() {
    questionsRoot.innerHTML = "";
    questionsSection?.classList.toggle("is-empty", quickQuestions.length === 0);
    quickQuestions.forEach((question) => {
      const button = document.createElement("button");
      button.type = "button";
      button.className = "advisor-option-card";
      button.textContent = question;
      button.addEventListener("click", () => sendAdvisorMessage(question));
      questionsRoot.append(button);
    });
  }

  function setWaiting(waiting) {
    isWaitingForApi = waiting;
    setChatDisabled(isRegisterModalOpen());
    questionsRoot.querySelectorAll("button").forEach((button) => {
      button.disabled = waiting;
    });
  }

  function normalizePersianDigits(text = "") {
    return String(text).replace(/[۰-۹٠-٩]/g, (digit) => "۰۱۲۳۴۵۶۷۸۹٠١٢٣٤٥٦٧٨٩".indexOf(digit) % 10);
  }

  function updateLeadDataFromMessage(message) {
    const normalized = normalizePersianDigits(message);
    const phoneMatch = normalized.match(/(?:\+?98|0)?9\d{9}/);
    const updates = {};

    if (phoneMatch) {
      updates.phone = phoneMatch[0].startsWith("0") ? phoneMatch[0] : `0${phoneMatch[0].replace(/^(?:\+?98)/, "")}`;
    }

    const nameMatch = message.match(/(?:اسمم|نامم|من\s+)([آ-یA-Za-z][آ-یA-Za-z\s‌]{2,32})/);
    if (nameMatch && !/میخوام|می‌خوام|هستم|دارم|بلدم/.test(nameMatch[1])) {
      updates.name = nameMatch[1].trim();
    }

    if (/مبتدی|صفر|برنامه‌نویسی بلد نیستم|برنامه نویسی بلد نیستم/.test(message)) {
      updates.level = "کاملاً مبتدی هستم";
    }

    if (/کسب‌وکار|کسب و کار|بیزینس|فروشگاه|سایت خودم/.test(message)) {
      updates.goal = "ساخت سایت برای کسب‌وکار خودم";
    }

    if (Object.keys(updates).length) {
      leadData = { ...leadData, ...updates, intent: currentIntent };
      saveAdvisorLeadData(leadData);
      if (updates.phone) {
        trackLead("chat", leadData);
      }
    }
  }

  function shouldOfferRegister(message = "") {
    return /قیمت|هزینه|ثبت.?نام|خرید|پرداخت|شهریه|تومان|شرایط/.test(message);
  }

  async function fetchAdvisorAnswer(message) {
    const response = await fetch("/ai-consultant/api/ai-consultant", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        message,
        history: chatHistory.slice(0, -1),
        intent: currentIntent,
      }),
    });
    const result = await response.json().catch(() => ({}));

    if (!response.ok || result.error) {
      throw new Error(result.error || "ارتباط با مشاور هوشمند برقرار نشد.");
    }

    return result.answer;
  }

  async function sendAdvisorMessage(rawMessage) {
    if (isRegisterModalOpen()) {
      return;
    }

    const message = String(rawMessage || "").trim();
    if (!message || isWaitingForApi) {
      return;
    }

    appendMessage("user", message);
    updateLeadDataFromMessage(message);
    input.value = "";
    setWaiting(true);
    const typing = appendTyping();

    try {
      const answer = await fetchAdvisorAnswer(message);
      typing.remove();
      appendMessage("assistant", answer);

      if (shouldOfferRegister(message)) {
        appendMessage("assistant", "اگر تصمیم گرفتی ثبت‌نام رو شروع کنی، دکمه «الان ثبت‌نام کنید» همین پایین همیشه در دسترسه.");
      }
    } catch (error) {
      typing.remove();
      appendMessage("assistant", error.message || "الان ارتباط با مشاور هوشمند برقرار نشد. لطفاً چند لحظه بعد دوباره سوالت رو بپرس.");
    } finally {
      setWaiting(false);
      renderQuickQuestions();
      focusAdvisorInput();
    }
  }

  function showAdvisorHint(intent = DEFAULT_ADVISOR_INTENT) {
    const normalizedIntent = normalizeAdvisorIntent(intent);
    if (shownHints.has(normalizedIntent)) {
      return;
    }
    const hint = ADVISOR_HINT_MESSAGES[normalizedIntent] || ADVISOR_HINT_MESSAGES.general;
    appendMessage("assistant", hint, {
      skipHistory: true,
      messageType: "hint",
      messageId: `hint-${getAdvisorSessionId()}-${normalizedIntent}`,
    });
    shownHints.add(normalizedIntent);
  }

  window.openAdvisorPopup = function openAdvisorPopup(intent = DEFAULT_ADVISOR_INTENT) {
    currentIntent = normalizeAdvisorIntent(intent);
    leadData = { ...readAdvisorLeadData(), intent: currentIntent };
    saveAdvisorLeadData(leadData);
    renderQuickQuestions();
    setOpen(true);
    showAdvisorHint(currentIntent);
  };

  window.closeAdvisorPopup = function closeAdvisorPopup() {
    setOpen(false);
  };

  launcher.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();
    if (widget.classList.contains("open")) {
      closeAdvisorPopup();
      return;
    }
    openAdvisorPopup(launcher.dataset.advisorIntent);
  });

  closeButton.addEventListener("click", closeAdvisorPopup);
  backdrop.addEventListener("click", closeAdvisorPopup);
  registerCta.addEventListener("click", (event) => {
    event.preventDefault();
    event.stopPropagation();
    if (typeof window.openRegisterModal === "function") {
      window.openRegisterModal();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.target?.closest?.(".register-modal, .register-form") || isRegisterModalOpen()) {
      return;
    }

    if (event.key === "Escape" && widget.classList.contains("open")) {
      closeAdvisorPopup();
    }
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    event.stopPropagation();

    if (!event.target.classList.contains("ai-chat-form") || event.target !== form || isRegisterModalOpen()) {
      return;
    }

    sendAdvisorMessage(input.value);
  });

  document.addEventListener("click", (event) => {
    const trigger = event.target.closest("[data-open-advisor]");

    if (!trigger) {
      return;
    }

    event.preventDefault();
    openAdvisorPopup(trigger.dataset.advisorIntent);
  });

  panel.addEventListener("transitionend", () => {
    if (widget.classList.contains("open") && !isRegisterModalOpen()) {
      focusAdvisorInput();
    }
  });

  renderQuickQuestions();
}

createAiConsultantChat();

function initCourseRegistration() {
  const modal = $(`[data-register-modal]`);
  const form = $(`[data-register-form]`);

  if (!modal || !form) {
    return;
  }

  const REGISTER_STORAGE_KEY = "courseRegistrationData";
  const submitButton = $(`[data-submit-register]`, form);
  const messageBox = $(`[data-register-message]`, form);
  let lastFocusedElement = null;

  function normalizePersianDigits(value = "") {
    const persianDigits = "۰۱۲۳۴۵۶۷۸۹";
    const arabicDigits = "٠١٢٣٤٥٦٧٨٩";
    return String(value).replace(/[۰-۹٠-٩]/g, (digit) => {
      const persianIndex = persianDigits.indexOf(digit);
      return persianIndex > -1 ? String(persianIndex) : String(arabicDigits.indexOf(digit));
    });
  }

  function normalizePhone(value = "") {
    return normalizePersianDigits(value).replace(/[\s\-.()]/g, "");
  }

  function isValidIranPhone(value = "") {
    return /^(?:\+98|98|0)?9\d{9}$/.test(normalizePhone(value));
  }

  function readJsonStorage(key) {
    try {
      return JSON.parse(localStorage.getItem(key) || "{}") || {};
    } catch (error) {
      return {};
    }
  }

  function setMessage(text = "", type = "") {
    messageBox.textContent = text;
    messageBox.className = `form-alert${text ? " show" : ""}${type ? ` ${type}` : ""}`;
  }

  function setFieldError(name, text = "") {
    const field = form.elements[name];
    const wrapper = field?.closest(".form-field");
    const error = $(`[data-error-for="${name}"]`, form);

    wrapper?.classList.toggle("invalid", Boolean(text));
    if (error) {
      error.textContent = text;
    }
  }

  function clearErrors() {
    ["name", "phone", "email"].forEach((name) => setFieldError(name));
    setMessage();
  }

  function getFormValues() {
    return Object.fromEntries(new FormData(form).entries());
  }

  function saveRegistrationData() {
    const values = getFormValues();
    localStorage.setItem(REGISTER_STORAGE_KEY, JSON.stringify(values));
    saveAdvisorLeadData({ ...readAdvisorLeadData(), ...values, intent: "register" });
    trackLead("register-form", { ...values, intent: "register" });
  }

  function prefillForm() {
    const savedRegistration = readJsonStorage(REGISTER_STORAGE_KEY);
    const advisorLead = readAdvisorLeadData();
    const data = { ...savedRegistration, ...advisorLead };

    ["name", "phone", "email", "level", "goal", "note"].forEach((name) => {
      const field = form.elements[name];
      if (field && data[name]) {
        field.value = data[name];
      }
    });
  }

  function focusFirstEmptyField() {
    const emptyField = ["name", "phone", "email", "level", "goal", "note"]
      .map((name) => form.elements[name])
      .find((field) => field && !String(field.value || "").trim());
    const target = emptyField || form.elements.name;
    setTimeout(() => target?.focus({ preventScroll: true }), 80);
  }

  function setChatDisabled(disabled) {
    const chatInput = document.querySelector(".ai-chat-input");
    const chatSend = document.querySelector(".ai-chat-send");

    if (chatInput) {
      if (disabled) {
        chatInput.blur();
      }
      chatInput.disabled = disabled;
    }

    if (chatSend) {
      chatSend.disabled = disabled;
    }

    if (typeof window.setAdvisorChatDisabled === "function") {
      window.setAdvisorChatDisabled(disabled);
    }
  }

  function openRegisterModal() {
    lastFocusedElement = document.activeElement;
    document.querySelector(".ai-chat-input")?.blur();
    setChatDisabled(true);
    prefillForm();
    clearErrors();
    modal.classList.add("open");
    modal.setAttribute("aria-hidden", "false");
    document.documentElement.classList.add("register-open", "register-modal-open");
    document.body.classList.add("register-open", "register-modal-open");
    trackLead("register-form", { ...readAdvisorLeadData(), intent: "register" });
    focusFirstEmptyField();
  }

  function closeRegisterModal({ restoreFocus = true } = {}) {
    modal.classList.remove("open");
    modal.setAttribute("aria-hidden", "true");
    document.documentElement.classList.remove("register-open", "register-modal-open");
    document.body.classList.remove("register-open", "register-modal-open");
    setChatDisabled(false);

    if (!restoreFocus) {
      return;
    }

    const chatWidget = document.querySelector(".ai-chat-widget.open");
    const chatInput = document.querySelector(".ai-chat-input");
    if (chatWidget && chatInput) {
      setTimeout(() => chatInput.focus({ preventScroll: true }), 100);
      return;
    }

    if (lastFocusedElement && typeof lastFocusedElement.focus === "function") {
      lastFocusedElement.focus({ preventScroll: true });
    }
  }

  function validateForm() {
    clearErrors();
    let isValid = true;

    if (!form.elements.name.value.trim()) {
      setFieldError("name", "نام و نام خانوادگی را وارد کن.");
      isValid = false;
    }

    if (!form.elements.phone.value.trim()) {
      setFieldError("phone", "شماره موبایل را وارد کن.");
      isValid = false;
    } else if (!isValidIranPhone(form.elements.phone.value)) {
      setFieldError("phone", "شماره موبایل ایران را درست وارد کن.");
      isValid = false;
    }

    if (form.elements.email.value.trim() && !form.elements.email.validity.valid) {
      setFieldError("email", "ایمیل را درست وارد کن.");
      isValid = false;
    }

    if (!isValid) {
      setMessage("لطفاً خطاهای فرم را برطرف کن.", "error");
      const firstInvalid = $(".form-field.invalid input, .form-field.invalid select, .form-field.invalid textarea", form);
      firstInvalid?.focus({ preventScroll: true });
    }

    return isValid;
  }

  async function submitRegistration(event) {
    event.preventDefault();
    event.stopPropagation();

    if (event.target !== form || !event.target.classList.contains("register-form")) {
      return;
    }

    if (!validateForm()) {
      return;
    }

    saveRegistrationData();
    const originalButtonText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = "در حال انتقال به پرداخت...";
    setMessage("در حال ثبت اطلاعات و اتصال به درگاه پرداخت...");

    try {
      const formData = new FormData(form);
      formData.set("session_id", getAdvisorSessionId());
      const response = await fetch("payment.php", {
        method: "POST",
        body: formData,
      });
      const result = await response.json();

      if (result && result.error === false && result.url) {
        window.location.href = result.url;
        return;
      }

      setMessage(result?.message || "امکان انتقال به پرداخت فراهم نشد.", "error");
    } catch (error) {
      setMessage("ارتباط با پرداخت برقرار نشد. چند لحظه بعد دوباره تلاش کن.", "error");
    } finally {
      submitButton.disabled = false;
      submitButton.textContent = originalButtonText;
    }
  }

  window.openRegisterModal = openRegisterModal;
  window.closeRegisterModal = closeRegisterModal;

  document.addEventListener("click", (event) => {
    const registerTrigger = event.target.closest("[data-open-register], [data-register-course]");
    if (registerTrigger) {
      event.preventDefault();
      if (typeof window.openAdvisorPopup === "function") {
        window.openAdvisorPopup(registerTrigger.dataset.advisorIntent || "register");
      } else {
        openRegisterModal();
      }
    }
  });

  modal.addEventListener("click", (event) => {
    if (event.target.closest("[data-close-register]")) {
      event.preventDefault();
      closeRegisterModal();
    }
  });

  ["click", "keydown", "input", "submit"].forEach((eventName) => {
    modal.addEventListener(eventName, (event) => {
      event.stopPropagation();
    });
  });

  $(`[data-back-to-advisor]`, form)?.addEventListener("click", () => {
    closeRegisterModal({ restoreFocus: false });
    if (typeof window.openAdvisorPopup === "function") {
      window.openAdvisorPopup("consultation");
    }
  });

  modal.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modal.classList.contains("open")) {
      event.preventDefault();
      closeRegisterModal();
    }
  });

  form.addEventListener("input", () => {
    saveRegistrationData();
  });
  form.addEventListener("change", () => {
    saveRegistrationData();
  });
  form.addEventListener("submit", submitRegistration);
}

initCourseRegistration();
