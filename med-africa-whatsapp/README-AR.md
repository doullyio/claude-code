# نظام إدارة شكاوي واتساب - Med Africa

نظام متكامل لاستقبال وإدارة شكاوي العملاء عبر واتساب لشركة **Med Africa** المتخصصة في توصيل منتجات Temu للمغرب.

## المكونات التقنية

| المكون | التقنية |
|--------|---------|
| أتمتة سير العمل | n8n (self-hosted) |
| الذكاء الاصطناعي | Claude 3.5 Sonnet عبر OpenRouter |
| واتساب | 360dialog WhatsApp Business API |
| قاعدة البيانات | Google Sheets API v4 |
| لوحة التحكم | WordPress 6+ Plugin |
| الرسوم البيانية | Chart.js |
| البنية التحتية | Docker + PostgreSQL |

---

## 1. إنشاء حساب 360dialog وربط رقم واتساب

1. ادخل على [360dialog.com](https://www.360dialog.com) وأنشئ حساب جديد
2. اتبع خطوات التحقق من حساب Facebook Business
3. أضف رقم واتساب جديد (يجب أن يكون رقم مغربي)
4. بعد التفعيل، احصل على **API Key** من لوحة تحكم 360dialog
5. احفظ المفتاح — ستحتاجه في الخطوة 5

---

## 2. إعداد Webhook URL

بعد تثبيت n8n (الخطوة 3)، قم بتكوين webhook في 360dialog:

1. ادخل للوحة تحكم 360dialog → **Settings** → **Webhooks**
2. أضف Webhook URL:
   ```
   https://your-n8n-domain.com/webhook/whatsapp-webhook
   ```
3. فعّل الأحداث:
   - `messages` — لاستقبال الرسائل
   - `statuses` — لتتبع حالة الرسائل

---

## 3. تثبيت n8n

### الطريقة 1: Docker (مستحسن)

```bash
# استنسخ المشروع
git clone <repo-url>
cd med-africa-whatsapp

# انسخ ملف المتغيرات
cp .env.example .env

# عدّل المتغيرات حسب بيئتك
nano .env

# شغّل n8n
docker compose up -d

# تحقق من التشغيل
docker compose ps
docker compose logs -f n8n
```

n8n سيكون متاح على: `http://localhost:5678`

### الطريقة 2: n8n Cloud

1. ادخل على [n8n.io](https://n8n.io) وأنشئ حساب
2. اختر خطة مناسبة
3. ستحصل على رابط مثل: `https://your-instance.app.n8n.cloud`

---

## 4. استيراد الـ 4 Workflows

1. افتح n8n في المتصفح
2. لكل ملف workflow:
   - اضغط على **+** → **Import from File**
   - اختر الملف من مجلد `n8n-workflows/`

الملفات المطلوبة:
| الملف | الوظيفة |
|-------|---------|
| `whatsapp-receiver.json` | استقبال رسائل واتساب |
| `conversation-manager.json` | إدارة حالة المحادثة |
| `claude-ai-processor.json` | معالجة الذكاء الاصطناعي عبر OpenRouter |
| `save-and-notify.json` | حفظ البيانات وإرسال التنبيهات |

3. بعد الاستيراد، فعّل كل workflow بالضغط على **Toggle** (تفعيل/إيقاف)
4. تأكد من ترتيب التفعيل:
   - أولاً: `claude-ai-processor`
   - ثانياً: `save-and-notify`
   - ثالثاً: `conversation-manager`
   - أخيراً: `whatsapp-receiver`

---

## 5. إعداد متغيرات البيئة

### في Docker (.env file):

```env
# OpenRouter API (Claude عبر OpenRouter)
OPENROUTER_API_KEY=sk-or-v1-xxxxxxxxxxxx

# Google Sheets
GOOGLE_SHEETS_ID=1xxxxxxxxxxxxxxxxxxxxxxxxxx
GOOGLE_CREDENTIALS_JSON={"type":"service_account",...}

# 360dialog WhatsApp
DIALOG360_API_KEY=your_api_key

# رقم واتساب المدير (للتنبيهات العاجلة)
MANAGER_WHATSAPP_NUMBER=212600000000

# بريد المدير (للتنبيهات المهمة)
MANAGER_EMAIL=manager@medafrica.ma
```

### في n8n Cloud:

1. اذهب إلى **Settings** → **Environment Variables**
2. أضف كل متغير من القائمة أعلاه

### الحصول على مفتاح OpenRouter:

1. ادخل على [openrouter.ai](https://openrouter.ai)
2. أنشئ حساب واشحن رصيد
3. اذهب إلى **Keys** → **Create Key**
4. انسخ المفتاح واحفظه في `OPENROUTER_API_KEY`

---

## 6. تثبيت WordPress Plugin

1. انسخ مجلد `wordpress/med-africa-dashboard/` إلى:
   ```
   /wp-content/plugins/med-africa-dashboard/
   ```

2. فعّل الإضافة من لوحة تحكم WordPress:
   - **Plugins** → **Installed Plugins** → **Med Africa Dashboard** → **Activate**

3. اذهب إلى **Med Africa** → **الإعدادات** وأدخل:
   - Google Sheets API Key
   - Google Sheets ID
   - 360dialog API Key
   - رقم واتساب المدير
   - فترة التحديث التلقائي

4. اضغط **اختبار الاتصال** للتأكد من صحة الإعدادات

---

## 7. إعداد Google Sheets وتشغيل Apps Script

### إنشاء Google Sheet:

1. ادخل على [Google Sheets](https://sheets.google.com) وأنشئ جدول جديد
2. سمّه: **"Med Africa - شكاوي العملاء"**
3. انسخ **Sheet ID** من الرابط:
   ```
   https://docs.google.com/spreadsheets/d/[SHEET_ID_HERE]/edit
   ```

### تشغيل Apps Script:

1. في Google Sheet، اذهب إلى **Extensions** → **Apps Script**
2. احذف الكود الافتراضي
3. انسخ محتوى ملف `google-scripts/setup.gs` والصقه
4. اضغط **Run** → اختر `runFullSetup`
5. وافق على الأذونات المطلوبة
6. انتظر حتى تظهر رسالة النجاح

### تفعيل Google Sheets API:

1. اذهب إلى [Google Cloud Console](https://console.cloud.google.com)
2. أنشئ مشروع جديد أو اختر مشروع موجود
3. فعّل **Google Sheets API**
4. أنشئ **Service Account** واحصل على ملف JSON
5. شارك Google Sheet مع البريد الإلكتروني للـ Service Account

---

## 8. اختبار المنظومة بمحادثة تجريبية

### اختبار سريع عبر curl:

```bash
curl -X POST https://your-n8n-domain.com/webhook/whatsapp-webhook \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "212612345678",
    "message": "السلام عليكم"
  }'
```

### سيناريو اختبار كامل:

1. أرسل رسالة من رقم واتساب إلى رقم Med Africa
2. تابع تدفق المحادثة:
   - **الخطوة 1**: ستصلك رسالة ترحيب وسؤال عن الاسم
   - **الخطوة 2**: أرسل اسمك → سيسأل عن اللقب
   - **الخطوة 3**: أرسل اللقب → سيسأل عن المدينة
   - **الخطوة 4**: أرسل المدينة → سيسأل عن رقم التتبع
   - **الخطوة 5**: أرسل رقم التتبع → سيسأل عن المشكل
   - **الخطوة 6**: اشرح مشكلتك → سيصنفها ويعطيك رقم تذكرة

3. تحقق من:
   - [ ] Google Sheets: هل ظهر صف جديد؟
   - [ ] WordPress Dashboard: هل تظهر الشكوى الجديدة؟
   - [ ] التنبيهات: إذا كانت الأولوية "عاجل"، هل وصل تنبيه للمدير؟

---

## هيكل المشروع

```
med-africa-whatsapp/
├── n8n-workflows/
│   ├── whatsapp-receiver.json      # استقبال رسائل واتساب
│   ├── conversation-manager.json   # إدارة حالة المحادثة
│   ├── claude-ai-processor.json    # معالجة AI عبر OpenRouter
│   └── save-and-notify.json        # حفظ وتنبيهات
├── google-scripts/
│   └── setup.gs                    # إعداد Google Sheets
├── wordpress/
│   └── med-africa-dashboard/       # WordPress Plugin
│       ├── med-africa-dashboard.php
│       ├── includes/
│       │   ├── class-google-sheets.php
│       │   ├── class-dashboard.php
│       │   ├── class-rest-api.php
│       │   └── class-notifications.php
│       └── assets/
│           ├── css/dashboard.css
│           └── js/dashboard.js
├── .env.example                    # متغيرات البيئة
├── docker-compose.yml              # تشغيل n8n بـ Docker
└── README-AR.md                    # دليل الإعداد (هذا الملف)
```

---

## حل المشاكل الشائعة

| المشكلة | الحل |
|---------|------|
| لا تصل الرسائل لـ n8n | تحقق من إعداد webhook في 360dialog وأن n8n يعمل |
| خطأ في Claude/OpenRouter | تحقق من `OPENROUTER_API_KEY` ومن رصيد الحساب |
| لا تُحفظ البيانات في Sheets | تحقق من مشاركة Sheet مع Service Account |
| لوحة التحكم فارغة | تحقق من إعدادات API في WordPress |
| الجلسة تنقطع | الجلسة تنتهي بعد 30 دقيقة من عدم النشاط — وهذا طبيعي |

---

## الدعم

للمساعدة التقنية، تواصل مع فريق التطوير عبر البريد الإلكتروني أو فتح issue في المستودع.
