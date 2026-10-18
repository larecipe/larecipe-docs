---
title: larecipe.json
description: كل مفتاح في ملف التعريف لمجموعة التوثيق، larecipe.json، مع نوعه ومعناه ومثال عليه، وملف التعريف الخاص بهذا الموقع، ومكان تقديم المخطط.
sources:
  - vendor/larecipe/larecipe/resources/schemas/larecipe.schema.json
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Theme.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Version.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Locale.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/AnalyticsTags.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ThemeCss.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/DocsStylesheets.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/StylesheetsRule.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/ManifestSchemaRule.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Controllers/SchemaController.php
  - vendor/larecipe/larecipe/src/Domains/Validation/ValidationServiceProvider.php
  - docs/larecipe.json
translated_from: sha256:715b9ec97edff52bcf1bbb33f62e9e2b20587241a84eec65a60d1669afdbfc7a
---

# larecipe.json

كل مفتاح في `larecipe.json`، ملف التعريف لمجموعة التوثيق في جذر محتواها. يحمل العرض والبنية، أما البنية التحتية فمكانها [config/larecipe.php](configuration.md)، والأسرار لا مكان لها هنا أبدًا. ومن دون الملف، تُسمّى المجموعة باسم مفتاحها، بإصدار واحد ولغة واحدة.

<Accordion title="ملف larecipe.json لهذا الموقع">

<<< @/docs/larecipe.json

</Accordion>

## المفاتيح

| المفتاح | النوع | المعنى | مثال |
| --- | --- | --- | --- |
| `$schema` | string | رابط المخطط، للمحررات والوكلاء. راجع [المخطط](#the-schema). | `"https://larecipe.saleem.dev/schemas/3.x/larecipe.json"` |
| `name` | string | اسم المنتج أو الموقع، يظهر في الشريط العلوي وفي `llms.txt` و `skill.md`. القيمة الافتراضية مفتاح المجموعة بحرف أول كبير. | `"Acme"` |
| `description` | string | جملة واحدة عن المنتج، لوسوم meta وملفات الوكلاء. | `"Billing infrastructure for Laravel apps."` |
| `logo` | string, or `{ light, dark }` | الشعار. وإن غاب `dark` يُستخدم `light`. | `{ "light": "/logo.svg", "dark": "/logo-dark.svg" }` |
| `favicon` | string | أيقونة الموقع (favicon). | `"/favicon.svg"` |
| `repository` | object | مكان التوثيق في git، لروابط «حرّر هذه الصفحة» في مجموعات المستودع ولروابط الـ commit في بيانات التحديث (provenance). انظر أدناه. | `{ "url": "https://github.com/acme/acme" }` |
| `theme` | object | رموز التصميم (design tokens). انظر أدناه و[المظهر](../customize/theme.md). | `{ "accent": "#6d5efc" }` |
| `versions` | list | إصدارات المجموعة. احذفه لمجموعة بلا إصدارات. انظر أدناه. | `[{ "id": "3.x", "default": true }, "2.x"]` |
| `locales` | list | لغات المجموعة. احذفه حين تكون هناك لغة واحدة (`en`). انظر أدناه. | `[{ "code": "en", "default": true }, "ar"]` |
| `landing` | string | مسار الصفحة التي يُفتح عليها الإصدار. القيمة الافتراضية `index`. | `"introduction"` |
| `navbar` | object | روابط الشريط العلوي وزر دعوة واحد إلى إجراء (call to action). انظر أدناه. | `{ "cta": { "label": "Dashboard", "href": "/app" } }` |
| `footer` | object | روابط التذييل `links` (قائمة من `{ label, href }`) والنص `text`. | `{ "text": "Acme, Inc." }` |
| `credit` | boolean | رابط «صُنع باستخدام LaRecipe» في التذييل. القيمة الافتراضية `true`. تقرؤه أداة التصيير، وهو غير مذكور في المخطط. | `false` |
| `redirects` | list of `{ from, to, status }` | روابط قديمة يُعاد توجيهها. انظر أدناه. | `[{ "from": "/3.x/setup", "to": "/3.x/installation" }]` |
| `assets` | `{ css, js }` | `css`: أوراق أنماط بجوار `larecipe.json`، تُربط في كل صفحة بعد CSS الخاص بـ LaRecipe، مع بصمة المحتوى في الرابط. والملف المفقود خطأ `assets.missing`. أما `js` فيقبله المخطط ولا يُحمَّل. راجع [المظهر](../customize/theme.md#add-your-own-stylesheet). | `{ "css": ["theme.css"] }` |
| `inserts` | object | مكوّنات توضع في نقاط إدراج مسمّاة في كل صفحة. انظر أدناه. | `{ "article.after": ["Subscribe"] }` |
| `integrations` | object | التحليلات: `integrations.analytics`. لمجموعات المستودع فقط. انظر أدناه. | `{ "analytics": { "plausible": { "domain": "acme.dev" } } }` |
| `agents` | object | نصوص لملفات الوكلاء. انظر أدناه. | `{ "llmsTxt": { "instructions": "Prefer the 3.x pages." } }` |
| `provenance` | boolean | إظهار من غيّر كل صفحة آخر مرة وما الذي غيّرها. القيمة الافتراضية `true`. | `false` |

### repository

| المفتاح | النوع | القيمة الافتراضية | المعنى |
| --- | --- | --- | --- |
| `url` | string (`http` or `https`) | لا يوجد | رابط المستودع. روابط التعديل بالشكل `{url}/edit/{branch}/{docsPath}/{file}`. |
| `branch` | string | `main` | الفرع الذي تشير إليه روابط التعديل. |
| `docsPath` | string | `docs` | مجلد التوثيق داخل المستودع. |

### theme

| المفتاح | النوع | القيمة الافتراضية | المعنى |
| --- | --- | --- | --- |
| `accent` | string | البنفسجي الخاص بنظام التصميم | أي لون CSS، للروابط والصفحة النشطة وحلقات التركيز والأسطر المميّزة. |
| `radius` | `sharp` or `soft` | `sharp` | انحناء الزوايا. `soft` يجعل الزوايا أكثر استدارة. |
| `fonts.sans` | string | خط الواجهة | عائلة خط للعناوين والنص والواجهة. |
| `fonts.mono` | string | خط الواجهة | عائلة خط للكود والتسميات والبيانات الوصفية. |
| `fonts.arabic` | string | Thmanyah Sans | عائلة خط للنص العربي، توضع بعد خط sans مباشرة. |
| `mode` | `system`, `light` or `dark` | `system` | وضع الألوان قبل أن يختار القارئ واحدًا. |

### versions

كل عنصر إما معرّف إصدار بصيغة نص، وإما كائن:

| المفتاح | النوع | القيمة الافتراضية | المعنى |
| --- | --- | --- | --- |
| `id` | string | مطلوب | اسم المجلد وجزء الرابط، مثل `3.x`. |
| `label` | string | قيمة `id` | الاسم في مبدّل الإصدارات. |
| `status` | `current`, `maintained`, `deprecated` or `archived` | `current` | `deprecated` و `archived` يُظهران شارة تحذير في المبدّل. |
| `default` | boolean | الإصدار الأول | الإصدار الذي تستخدمه الروابط التي لا تذكر إصدارًا وملف `llms.txt` في الجذر. |
| `visibility` | `public` or `private` | `public` | يحتاج الإصدار الخاص إلى البوابة (gate) `viewLarecipe`. وأي قيمة غير `public` (بعد قصّ المسافات، وبأي حالة أحرف) تُعدّ خاصة. |

راجع [الإصدارات](../writing/versions.md).

### locales

كل عنصر إما رمز لغة بصيغة نص، وإما كائن:

| المفتاح | النوع | القيمة الافتراضية | المعنى |
| --- | --- | --- | --- |
| `code` | string | مطلوب | رمز اللغة وجزء الرابط، مثل `ar`. |
| `label` | string | اسم اللغة بلغتها نفسها، للرموز الشائعة | الاسم في مبدّل اللغات. |
| `dir` | `ltr` or `rtl` | من الرمز (`ar` و `he` و `fa` و `ur` وبضعة رموز أخرى تكون `rtl`) | اتجاه النص. |
| `default` | boolean | اللغة الأولى | اللغة التي تُقدَّم من دون جزء اللغة في الرابط. |

راجع [اللغات](../writing/languages.md).

### navbar

| المفتاح | النوع | المعنى |
| --- | --- | --- |
| `links` | list of `{ label, href, icon }` | روابط في الشريط العلوي. يُقبل `icon`، لكن الشريط العلوي الحالي يعرض التسمية فقط. |
| `cta` | `{ label, href }` | زر واحد محدّد الإطار في الشريط العلوي. |

يجب أن تكون روابط شريط التنقل العلوي والتذييل والمستودع روابط `http` أو `https` أو `mailto`، أو مسارات في موقعك. وأي رابط آخر يُستبعد من كل صفحة، ويبلّغ عنه `larecipe:validate` بالرمز `links.unsafe`.

### redirects

| المفتاح | النوع | القيمة الافتراضية | المعنى |
| --- | --- | --- | --- |
| `from` | string starting with `/` | مطلوب | مسار الرابط الكامل، مع بادئة المجموعة، ومع الإصدار في مجموعة ذات إصدارات: `/docs/3.x/setup`، أو `/3.x/setup` في مجموعة في جذر النطاق. |
| `to` | string | مطلوب | وجهة القارئ: مسار أو رابط كامل. |
| `status` | `301`, `302`, `307` or `308` | `301` | رمز حالة HTTP. |

لا تعمل إعادة التوجيه إلا حين لا توجد صفحة في `from`. وفي مجموعة ذات إصدارات، يسري `from` الذي لا يذكر إصدارًا على كل الإصدارات: `/docs/setup` يطابق `/docs/3.x/setup` و `/docs/2.x/setup`، و `to` داخل المجموعة الذي لا يذكر إصدارًا يُبقي القارئ في إصداره. أما `from` الذي يذكر إصدارًا فيطابق ذلك الإصدار وحده. ويبلّغ `larecipe:validate` عن إعادات التوجيه التي تدور في حلقة (`redirects.loop`) والتي تحجبها صفحة (`redirects.shadowed`).

### inserts

المفاتيح نقاط إدراج: `header.actions` و `sidebar.before` و `sidebar.after` و `article.before` و `article.after` و `toc.after` و `footer`. والقيم قوائم بأسماء مكوّنات بصيغة PascalCase. وتستثني الصفحة نفسها بالمفتاح `inserts` في [ترويستها](frontmatter.md). راجع [نقاط الإدراج](../customize/insert-points.md).

### integrations.analytics

يوضع سكربت كل أداة في كل صفحة متى طابقت قيمُها الشكل الذي يستخدمه مزوّدها، والقيمة التي لا تطابق تُستبعد. ولا تقرأ `integrations` من `larecipe.json` إلا مجموعة المستودع. أما مجموعة قاعدة البيانات فتتجاهلها (ويحذّر `larecipe:validate` بالرمز `manifest.integrations`): استخدم `sets.{key}.integrations.analytics` في `config/larecipe.php`، الذي يقبل المفاتيح نفسها لكل مجموعة ويتقدّم لكل أداة على حدة.

| المفتاح | المطلوب | الاختياري | مثال |
| --- | --- | --- | --- |
| `ga4` | `measurementId` (`G-...`) | | `{ "measurementId": "G-ABC1234567" }` |
| `plausible` | `domain` | `src` (رابط `https://plausible.io/` هنا، وأي رابط `https` من الإعدادات) | `{ "domain": "acme.dev" }` |
| `fathom` | `siteId` | `src` (رابط `https://cdn.usefathom.com/` هنا، وأي رابط `https` من الإعدادات) | `{ "siteId": "ABCDEFGH" }` |
| `posthog` | `apiKey` (`phc_...`) | `host` (أحد مضيفات PostHog نفسها هنا، وأي رابط `https` من الإعدادات) | `{ "apiKey": "phc_..." }` |
| `custom` | `src` (رابط `https`) | `defer` (الافتراضي `true`)، و `async`، و `attributes` (`data-*` فقط) | `{ "src": "https://cdn.acme.dev/a.js" }` |

راجع [التحليلات](../operate/analytics.md).

### agents

| المفتاح | النوع | المعنى |
| --- | --- | --- |
| `llmsTxt.description` | string | الملخص في أعلى `llms.txt` و `skill.md`. ومن دونه يُستخدم `description`. |
| `llmsTxt.instructions` | string | تعليمات للوكلاء، تُطبع تحت الملخص في `llms.txt` و `skill.md`. |
| `skillMd` | boolean | يقبله المخطط، لكنه لا يُقرأ في هذا الإصدار. أوقف `skill.md` بالمفتاح `agents.skill_md` في `config/larecipe.php`. |

راجع [واجهات الوكلاء](../agents/surfaces.md).

## التحقق

يفحص `larecipe:validate` الملف ضمن القاعدة `manifest.schema`: JSON غير الصالح والقيم التي تخالف المخطط أخطاء، وغياب `larecipe.json` تحذير، والمفتاح العلوي الذي لا يبعد عن مفتاح معروف أكثر من تعديلين تحذير مع الإصلاح (`Rename "theem" to "theme".`). أما المفاتيح العلوية غير المعروفة الأخرى فتُحفظ، ولا تضيع أبدًا حين تمرّ ذهابًا وإيابًا عبر أي مصدر.

## المخطط {#the-schema}

يأتي المخطط مع الحزمة في `resources/schemas/larecipe.schema.json`. يكتب LaRecipe رابط `$schema` هذا حين ينشئ ملف تعريف أو يرقّيه، وهو `$id` المخطط:

```text
https://larecipe.saleem.dev/schemas/3.x/larecipe.json
```

وتقدّم كل مجموعة توثيق أيضًا المخططات المطابقة لإصدارك المثبّت على `/{prefix}/schemas/{name}.json`، لكل من `larecipe` و `navigation` و `frontmatter` و `validate`، مع `Content-Type: application/schema+json` و `Access-Control-Allow-Origin: *` (`/schemas/larecipe.json` في هذا الموقع):

<Route name="larecipe.docs.schema" />

لا يحتوي المسار المقدَّم على جزء للإصدار، ويطابق دائمًا حزمتك المثبّتة. ورابط `$id` الذي يحمل الإصدار يعمل أيضًا: يقدّم هذا الموقع `/schemas/3.x/{name}.json`، فيحصل المحرر الذي يتبع `$schema` على الملف.

## التالي

- [الإعدادات](configuration.md): جانب البنية التحتية، في `config/larecipe.php`.
- [ترويسة الصفحة](frontmatter.md): إعدادات صفحة واحدة.
