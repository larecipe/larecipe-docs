---
title: المظهر
description: اجعل مظهر LaRecipe الافتراضي خاصًا بك ببضع قيم في larecipe.json، وشعارك، وملف أنماط خاص بك حين تحتاج إليه.
sources:
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Theme.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ThemeCss.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/DocsStylesheets.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/DocsAssetController.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/StylesheetsRule.php
  - vendor/larecipe/larecipe/src/LaRecipe.php
  - vendor/larecipe/larecipe/resources/views/partials/head.blade.php
  - vendor/larecipe/larecipe/resources/views/prerender/header.blade.php
  - vendor/larecipe/larecipe/resources/schemas/larecipe.schema.json
  - vendor/larecipe/larecipe/ui/src/styles/tokens.css
  - docs/larecipe.json
translated_from: sha256:47217651250e48a94e700348171e6396061115f9c386faf5af42eb724da0785b
---

# المظهر

اجعل المظهر الافتراضي خاصًا بك ببضع قيم (tokens) تحت `theme` في `larecipe.json`. وهي تعيش بجوار التوثيق، فينتقل المظهر مع المحتوى ولا يحتاج إلى خطوة بناء. اضبط فقط ما تريد تغييره:

```json title="docs/larecipe.json"
{
  "theme": {
    "accent": "#0f766e",
    "radius": "soft",
    "fonts": { "sans": "IBM Plex Sans", "mono": "IBM Plex Mono" },
    "mode": "system"
  }
}
```

| المفتاح | القيم | ما يغيّره |
| --- | --- | --- |
| `accent` | أي لون CSS | الروابط، والصفحة الحالية، وحلقات التركيز، وأسطر الكود المميّزة. ويُشتق منه لون الوضع الداكن، فيبقى مقروءًا في الوضعين. |
| `radius` | `sharp` (الافتراضي) أو `soft` | يُبقي `sharp` الزوايا عند 4 و 6 و 10 px. وينقلها `soft` إلى 6 و 10 و 14 px. |
| `fonts.sans` | اسم عائلة خطوط | العناوين والنصوص والواجهة. |
| `fonts.mono` | اسم عائلة خطوط | الكود والتسميات والبيانات الوصفية. |
| `fonts.arabic` | اسم عائلة خطوط | النص العربي. والافتراضي هو Thmanyah Sans. |
| `mode` | `system` (الافتراضي) أو `light` أو `dark` | وضع الألوان الذي يحصل عليه القارئ في زيارته الأولى. ويتبع `system` نظام تشغيل القارئ. |

قيم الخطوط تسمّي العائلة، ولا تنزّلها. حمّل ملفات الخطوط بنفسك، مثلًا من [ملف أنماط خاص بك](#add-your-own-stylesheet).

## الشعار وأيقونة الموقع {#logo-and-favicon}

يظهر الشعار في الشريط العلوي، بجوار اسم الموقع المأخوذ من `larecipe.json`. أعطِ ملفًا واحدًا للوضعين، أو ملفًا لكل وضع:

```json title="docs/larecipe.json"
{
  "name": "Acme",
  "logo": { "light": "/brand/acme.svg", "dark": "/brand/acme-dark.svg" },
  "favicon": "/favicon.svg"
}
```

- المسارات روابط، فضع الملفات في مجلد `public/` في تطبيقك.
- يُطبع الاسم دائمًا بجوار الشعار، لذا فالرمز وحده يعمل أفضل من شعار نصي كامل.
- من دون `logo`، يعرض LaRecipe شعاره الخاص، تذكرة الوصفة البنفسجية. وهذا الموقع لا يضبط شعارًا، فذلك هو الشعار الذي تراه هنا.
- يصبح `favicon` هو `<link rel="icon">` الخاص بالصفحة. ويمكن لأيقونة SVG أن تبدّل ألوانها باستعلام وسائط `prefers-color-scheme` داخل الملف.

## أضف ملف أنماط خاصًا بك {#add-your-own-stylesheet}

لكل ما لا تغطيه القيم، أضف ملف `theme.css` بجوار `larecipe.json` واذكره تحت `assets.css`:

```json title="docs/larecipe.json"
{
  "assets": { "css": ["theme.css"] }
}
```

<FileTree>

- docs/
  - larecipe.json
  - theme.css
  - 3.x/

</FileTree>

يربط LaRecipe كل ملف أنماط مذكور في كل صفحة من المجموعة، بعد CSS الخاص به مباشرة، فتغلب قواعدك حيث تتداخل. وينتقل الملف مع التوثيق: يخدم كل الإصدارات، ويدخل في [التصدير الثابت](../operate/static-export.md)، ولا يحتاج إلى خطوة بناء. والملف المذكور غير الموجود لا يحصل على وسم، ويبلّغ عنه `php artisan larecipe:validate` بالقاعدة `assets.missing`.

اكتب CSS الخاص بك على الأصناف التي يُصيّرها LaRecipe (`.lr-callout` و `.lr-card` و `.lr-code` وغيرها؛ ويسردها [مرجع المكوّنات](../reference/components.md)) وعلى متغيرات المظهر أدناه، فيتبع الوضع الداكن والصفحات من اليمين إلى اليسار. أما CSS الذي يأتي مع [مكوّن مخصّص](custom-components.md) فمكانه امتداد ذلك المكوّن.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="المظهر الافتراضي">

يُسمّى المظهر الافتراضي The Pass، نسبةً إلى طاولة التسليم في مطبخ المطعم حيث يُفحص كل طبق قبل أن يخرج. وهو مصمّم للقراءة الطويلة والتصفّح السريع:

- **ورق وحبر.** صفحة بيضاء دافئة (`#FAFAF8`)، أو شبه سوداء في الوضع الداكن (`#111016`)، مع نص بثلاث درجات ثابتة.
- **لون واحد للحالة.** البنفسجي يميّز ما هو نشط أو مركَّز عليه أو مرتبط أو جديد، ولا شيء غير ذلك، فيستطيع القارئ أن يجد كل ما هو حيّ باللون وحده. ويحلّ لونك `accent` محل البنفسجي في كل موضع يعني فيه حالة.
- **خط أحادي المسافة لما تصنعه الآلات.** التسميات والمسارات والطوابع الزمنية والكود مكتوبة بخط JetBrains Mono. والنصوص بخط Inter، والعربية بخط Thmanyah Sans.
- **التذكرة.** بطاقة صغيرة برأس مثقّب وحافة سفلية مقضومة هي الشكل المميّز للنظام. وشعار LaRecipe نفسه مرسوم منها.

الوضعان الفاتح والداكن كلاهما أساسي، والصفحات من اليمين إلى اليسار هي التصميم نفسه معكوسًا، لا نسخة مختلفة. وتأتي الخطوط مع بناء واجهة LaRecipe، فيبدو الموقع صحيحًا من دون أي إعداد.

</Accordion>

<Accordion title="كيف تصل القيم إلى الصفحة">

لا يكتب LaRecipe إلا القيم التي تضبطها، على شكل متغيرات CSS في وسم `<style>` داخل رأس الصفحة. وتُفحص كل قيمة أولًا، لأنها تأتي من ملف محتوى: فالقيمة التي قد تغلق الوسم أو تحقن قاعدة تُحذف. ويُشتق لون الوضع الداكن من لونك في فضاء OKLCH (أفتح، وأقل تشبعًا بقليل). ويأتي `fonts.arabic` بعد خط sans مباشرة في سلسلة الخطوط، فتحتفظ الكلمات اللاتينية والأرقام والكود داخل النص العربي بخط sans.

</Accordion>

<Accordion title="متغيرات المظهر">

تحمل متغيرات CSS هذه نظام التصميم. وتتبدّل قيمها في الوضع الداكن، فيتبع CSS الذي يقرؤها وضع القارئ من دون قواعد إضافية.

| المتغير | يحمل |
| --- | --- |
| `--lr-paper` | خلفية الصفحة |
| `--lr-ink`، `--lr-ink-muted`، `--lr-ink-faint` | النص بدرجاته الثلاث. لا تضع معلومات أبدًا في الدرجة الباهتة. |
| `--lr-surface`، `--lr-surface-2` | التنبيهات وحقول الإدخال والبطاقات، وحالة المرور فوقها |
| `--lr-hairline`، `--lr-hairline-strong` | الحدود والفواصل |
| `--lr-accent-current` | اللون المميِّز للوضع الحالي. فضّله على `--lr-accent`، الذي يحمل قيمة الوضع الفاتح. |
| `--lr-accent-soft` | خلفيات العناصر المحدّدة |
| `--lr-success`، `--lr-warning`، `--lr-danger` | ألوان الإشعار، للتسميات والأيقونات |
| `--lr-radius-xs`، `--lr-radius-sm`، `--lr-radius-md`، `--lr-radius-lg` | أنصاف أقطار الزوايا (يغيّرها `radius: soft`) |
| `--lr-font-sans`، `--lr-font-mono` | سلاسل الخطوط، بما فيها قيم `fonts` الخاصة بك |

```css title="docs/theme.css"
.status-pill {
  border: 1px solid var(--lr-hairline);
  border-radius: var(--lr-radius-sm);
  color: var(--lr-ink-muted);
}

.status-pill[data-live] {
  color: var(--lr-accent-current);
}
```

</Accordion>

<Accordion title="كيف تُقدَّم ملفات الأنماط المذكورة">

ملف الأنماط المذكور تحت `assets.css` يقدّمه مسار ملفات التوثيق مع بصمة محتواه في الرابط (`?v=...`)، فتحتفظ به المتصفحات وشبكات CDN لمدة سنة، ويحصل الملف المعدَّل على رابط جديد.

</Accordion>

<Accordion title="CSS من مكان آخر، مع LaRecipe::head()">

لـ CSS الموجود خارج التوثيق، أو لملف أنماط لا تحصل عليه إلا بعض المجموعات، سجّله في service provider باستخدام `LaRecipe::head()`، الذي يطبع ما تعيده داخل `<head>` في كل صفحة توثيق:

```php title="app/Providers/AppServiceProvider.php"
use LaRecipe\Facades\LaRecipe;

public function boot(): void
{
    LaRecipe::head(fn () => '<link rel="stylesheet" href="/css/docs.css">');
}
```

تستقبل الدالة مجموعة التوثيق، فيستطيع تطبيق واحد أن ينسّق مجموعاته بطرق مختلفة (`fn ($set) => $set->key === 'handbook' ? '...' : ''`). ويجوز أن تعيد HTML، أو view، أو أي شيء `Htmlable`. وهي أيضًا مكان مناسب لملفات أنماط الخطوط من خدمة خطوط.

</Accordion>

<Accordion title="الوضع الداكن والكتابة من اليمين إلى اليسار">

- **الوضع الداكن.** يحمل عنصر `<html>` في الصفحة `data-theme="light"` أو `data-theme="dark"`. وحين تحتاج قاعدة إلى قيمة مختلفة في الوضع الداكن ولا يناسبها أي متغير من متغيرات المظهر، فاحصرها بـ `:root[data-theme='dark']`.
- **من اليمين إلى اليسار.** اللغة التي لها `"dir": "rtl"` تضبط `dir="rtl"` على الصفحة، وينعكس CSS الخاص بـ LaRecipe عبر الخصائص المنطقية. اكتب CSS الخاص بك بالطريقة نفسها (`margin-inline-start` و `padding-inline` و `inset-inline-end`) فينعكس هو أيضًا. وتبقى كتل الكود والأوامر وأرقام الإصدارات من اليسار إلى اليمين. راجع [اللغات](../writing/languages.md).

</Accordion>

<Accordion title="اختيارات القارئ نفسه">

اختيار القارئ نفسه للوضع الفاتح أو الداكن يغلب `mode`. ويختاره بزر المظهر في آخر الشريط العلوي، الذي يبدّل بين الفاتح والداكن بنقرة واحدة، ويُحفظ اختياره في متصفحه تحت `larecipe:theme`. ويُضبط وضع الألوان قبل الرسم الأول، فلا تومض الصفحات أبدًا بالمظهر الخاطئ.

والمفتاح المجاور للشعار يُخفي التنقل على الشاشات العريضة ويُظهره من جديد، كما كان يفعل مفتاح LaRecipe 2. ومع إخفاء التنقل، يتسع المقال لقراءة أسهل. ويُحفظ هذا الاختيار تحت `larecipe:sidebar` ويُطبَّق قبل الرسم الأول أيضًا. وعلى الهواتف يُفتح التنقل لوحةً من زر القائمة بدلًا من ذلك.

</Accordion>

</AccordionGroup>

## التالي {#next}

- [مكوّنات React المخصّصة](custom-components.md): أضف مكوّناتك الخاصة، مع CSS الخاص بها.
- [نقاط الإدراج والفتحات](insert-points.md): ضع مكوّنًا في كل صفحة.
- [larecipe.json](../reference/manifest.md): كل مفاتيح ملف التعريف.
