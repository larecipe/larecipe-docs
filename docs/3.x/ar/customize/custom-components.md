---
title: مكوّنات React المخصّصة
description: اكتب مكوّناتك الخاصة بـ React، واستخدمها في Markdown كالمكوّنات المدمجة، وأعطِ كلًّا منها بديلًا يعمل من دون JavaScript وصيغة Markdown للوكلاء.
sources:
  - vendor/larecipe/larecipe/src/LaRecipe.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/**
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/ComponentRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/Assets.php
  - vendor/larecipe/larecipe/packages/ui/src/**
  - vendor/larecipe/larecipe/packages/vite/src/**
  - resources/larecipe/**
  - resources/views/docs/subscribe.blade.php
  - app/Providers/AppServiceProvider.php
  - app/Http/Controllers/SubscribeController.php
  - lang/*/newsletter.php
  - routes/web.php
  - vite.config.js
translated_from: sha256:919442ababc212328a5cfd533821a883d16023676de8fb2c19d299b0ce1c2005
---

# مكوّنات React المخصّصة

حين لا تكفي المكوّنات المدمجة (نموذج اشتراك في نشرة بريدية، أو جدول أسعار، أو شارة حالة حيّة)، اكتب مكوّنك الخاص بـ React واستخدمه في Markdown كأي مكوّن مدمج: `<Subscribe list="larecipe" />`. للمكوّن ثلاثة أوجه، وتكتبها كلها:

- **React**، للقرّاء الذين يشغّل متصفحهم JavaScript.
- **HTML**، بديل (fallback) من الخادم للقرّاء بلا JavaScript، ولمحركات الزحف، وللرسم الأول.
- **Markdown**، ما يحصل عليه الوكلاء حين يقرؤون الصفحة بصيغة `.md` أو عبر MCP.

نموذج النشرة البريدية في آخر [المقدمة](../index.md) واحد منها. وهذه الصفحة تبنيه في أربع خطوات، مباشرةً من كود هذا الموقع:

1. [اكتب مكوّن React](#the-react-component).
2. [سجّله في ملف دخول الامتداد](#the-extension-entry).
3. [عرّفه في PHP](#the-php-definition)، مع بديله وصيغة Markdown الخاصة به.
4. [ابنِه](#build-it) باستخدام Vite.

ثم [استخدمه](#use-it) في صفحة، أو في كل صفحة.

<Accordion title="كل ملف يحتاج إليه نموذج Subscribe">

<FileTree>

- app/
  - Http/Controllers/SubscribeController.php
  - Providers/AppServiceProvider.php
- resources/
  - larecipe/
    - extension.tsx
    - Subscribe.tsx
    - subscribe.css
  - views/
    - docs/subscribe.blade.php
    - newsletter.blade.php
- routes/web.php
- vite.config.js

</FileTree>

</Accordion>

## مكوّن React {#the-react-component}

المكوّن المخصّص مكوّن React عادي. خصائصه (props) هي سمات الوسم، و Markdown الواقع بين وسم فتح ووسم إغلاق يصل على شكل `children`. وكل ما يستورده من `@larecipe/ui` يوفّره LaRecipe وقت التشغيل.

<<< @/resources/larecipe/Subscribe.tsx

<AccordionGroup>

<Accordion title="ما الذي يعطيه @larecipe/ui لمكوّنك">

| التصدير | ما يفعله |
| --- | --- |
| `useLaRecipe()` | الصفحة الحالية `page` (المسار والعنوان والروابط والإصدار واللغة و `meta`)، والمجموعة `set` (المفتاح والاسم والإصدارات واللغات)، واللغة `locale` (الرمز والاتجاه)، والمظهر `theme` (الوضع وتفضيل القارئ)، والمستخدم المسجّل `user` حين تشاركه، و `t` لنصوص واجهة LaRecipe بلغة الصفحة، ومساعد `navigate(url)`. |
| `request(path, options)` | طلب fetch إلى مسارات تطبيقك أنت. يرفض المصادر الأخرى، ويرسل ملف تعريف الارتباط الخاص بالجلسة، و `XSRF-TOKEN` الخاص بـ Laravel على شكل `X-XSRF-TOKEN`، و `Accept: application/json`، ويرمّز `body` بصيغة JSON، ويعيد `{ ok: true, status, data }` أو `{ ok: false, status, data, errors, message }`، مع أخطاء التحقق 422 من Laravel في `errors`. |
| `Button`، `LinkButton`، `Input`، `Textarea`، `Field`، `Callout`، `Badge`، `Card`، `Kbd`، `Loader`، `Ticket`، `Perforation` | الأجزاء التي بُنيت منها الواجهة المدمجة، في المظهرين ومن اليمين إلى اليسار. ويأخذ `Button` الخاصية `variant` (`primary` و `secondary` و `outline` و `ghost` و `ink`)، و `size`، و `loading`. |
| `Icons`، `Icon` | مجموعة الأيقونات الخاصة بالواجهة. |
| `Slot`، `useSlotDefault()` | تستبدل منطقة مدمجة في الصفحة مع إمكانية تصيير الأصلية. راجع [نقاط الإدراج والفتحات](insert-points.md). |
| `defineExtension()` | تسجّل مكوّناتك ونقاط الإدراج واستبدالات الفتحات في الصفحة. |

</Accordion>

<Accordion title="شارك المستخدم المسجّل وقيم الصفحة">

لا يُشارَك المستخدم المسجّل إلا حين تطلب ذلك، حقلًا حقلًا:

```php title="app/Providers/AppServiceProvider.php"
LaRecipe::shareUser(fn ($user) => ['name' => $user->name]);
```

والقيم التي يحتاج إليها مكوّن من صفحة بعينها توضع تحت `meta:` في ترويسة تلك الصفحة، وتصل على شكل `page.meta`:

```yaml
---
title: Pricing
meta:
  plan: enterprise
---
```

`meta` هي الترويسة الوحيدة خارج مفاتيح LaRecipe التي تصل إلى المتصفح. أما المفاتيح المجهولة الأخرى فتبقى على الخادم، لأنها غالبًا تحمل ملاحظات للفريق، مثل المسؤول عن الصفحة أو رقم تذكرة. راجع [ترويسة الصفحة](../reference/frontmatter.md#unknown-keys).

</Accordion>

</AccordionGroup>

## ملف دخول الامتداد {#the-extension-entry}

ملف دخول واحد يسجّل كل المكوّنات المخصّصة في تطبيقك:

<<< @/resources/larecipe/extension.tsx

يقرأ CSS الخاص به [متغيرات المظهر](theme.md) في LaRecipe، فيتبع النموذج لون الفريق المميِّز والوضع الداكن والصفحات من اليمين إلى اليسار من دون قواعد إضافية. والقواعد نفسها تنسّق نموذج React والبديل بصيغة HTML، لأن كليهما يستخدم أسماء الأصناف نفسها.

<Accordion title="resources/larecipe/subscribe.css">

<<< @/resources/larecipe/subscribe.css

</Accordion>

## التعريف في PHP {#the-php-definition}

كل مكوّن مخصّص يحتاج إلى تعريف في PHP، عادةً في `AppServiceProvider::boot()`. يسمّي التعريف المكوّن، ويصف خصائصه، ويعطي بديله بصيغة HTML وصيغة Markdown الخاصة به:

<<< @/app/Providers/AppServiceProvider.php#subscribe

تصف `props()` كل خاصية، وتعيد `fallback()` صيغة HTML من الخادم، وتعيد `markdown()` ما يقرؤه الوكلاء، وتسمح `island()` لـ React بأن يُركَّب فوق البديل. والوسم الذي لا تعريف له في PHP مكوّن مجهول: يُصيَّر نصًا مُهرَّبًا، ويبلّغ عنه `php artisan larecipe:validate` بالقاعدة `components.unknown`. فلا تستطيع الصفحة أبدًا أن تطلب من المتصفح تشغيل كود لم يسجّله تطبيقك.

<Accordion title="كل دوال التعريف">

| الدالة | ما تضبطه |
| --- | --- |
| `props([...])` | `type` لكل خاصية، واختياريًا `default` و `required` و `enum` و `description` (أو الصيغة المختصرة `'list' => 'string'`). ويفحصها `larecipe:validate` وكل عملية تصيير: فالخاصية المطلوبة الناقصة أو القيمة الواقعة خارج `enum` خطأ من نوع `components.props`. وفي مجموعة المستودع، تصل إلى React الخصائص التي لا تصفها أيضًا. أما في مجموعة قاعدة البيانات، التي قد يكون كتّابها أقل موثوقية من قاعدة الكود لديك، فتُحذف، لذا صِف كل خاصية تقرؤها. |
| `fallback(fn (array $props, string $children, RenderedComponent $context) => ...)` | صيغة HTML من الخادم. أعد نصًا، أو view، أو أي شيء `Htmlable`. و `$children` هو Markdown المُصيَّر داخل الوسم، و `$context` يحمل المجموعة والإصدار واللغة ومسار الصفحة. |
| `markdown(fn (array $props, string $children, $context) => ...)` | السطر أو الكتلة التي يقرؤها الوكلاء. ومن دونها، يحصل الوكلاء على المحتوى الداخلي. |
| `island()` | تغلّف البديل في عنصر نائب يُركَّب فيه React. ومن دونها، يبقى الوسم المضمَّن في النص على صيغة HTML البديلة ولا يعمل React أبدًا. وفي نقاط الإدراج، تُغلَّف المكوّنات دائمًا. |
| `raw()` | تُبقي المحتوى الواقع بين الوسمين نصًا حرفيًا بدلًا من Markdown. |
| `description('...')` | جملة واحدة لمرجع المكوّنات المولَّد الذي يقرؤه الوكلاء. |

</Accordion>

### البديل {#the-fallback}

البديل هو ما يحصل عليه القارئ بلا JavaScript، فاجعله يعمل وحده. وبديل Subscribe نموذج عادي يرسل إلى المسار نفسه:

<<< @/resources/views/docs/subscribe.blade.php

المقالات المُصيَّرة تُخزَّن مؤقتًا ويتشاركها القرّاء، لذا لا يجوز أن يثبّت البديل داخله أي شيء يخصّ طلبًا واحدًا. ويطبع `LaRecipe::csrfField()` عنصرًا نائبًا يملؤه LaRecipe برمز CSRF الخاص بالقارئ في كل استجابة. ومن دون JavaScript، يرسل النموذج وينتهي في [صفحة النشرة البريدية](/newsletter)، وهي صفحة عادية تُظهر هل نجح الاشتراك.

### صيغة Markdown {#the-markdown-form}

لا ينبغي للوكلاء أن يملؤوا النماذج، لذا فصيغة Markdown الخاصة بـ Subscribe سطر واحد فيه رابط، ومعه السطر الباهت حين تُضبط الخاصية `showcase`. اقرأ [المقدمة بصيغة Markdown](https://larecipe.saleem.dev/3.x/index.md) وستجده قرب النهاية:

```markdown
Subscribe to the LaRecipe newsletter, release notes by email to the address you give (unsubscribe any time): https://larecipe.saleem.dev/newsletter

This form is a custom React component this site adds to LaRecipe. See how it is built: https://larecipe.saleem.dev/3.x/customize/custom-components
```

<AccordionGroup>

<Accordion title="بلغة القارئ">

يستطيع المكوّن أن يتبع لغة الصفحة كما تفعل الواجهة المدمجة. فـ Subscribe يقرأ `locale` من `useLaRecipe()` في React، و `$context->locale` في بديله، ثم يختار كلماته من `lang/en/newsletter.php` أو `lang/ar/newsletter.php`. وفي [المقدمة العربية](/ar/3.x) يُقرأ النموذج نفسه من اليمين إلى اليسار، بالعربية، ويجيب المسار بالعربية أيضًا.

</Accordion>

<Accordion title="المسار الذي يرسل إليه النموذج">

يرسل نموذج React والبديل كلاهما إلى مسار عادي في تطبيقك، مع الـ middleware الخاص بك. وهذا هو ذلك المسار، مقروءًا من التطبيق العامل:

<Route name="newsletter.subscribe" />

يتحقق الـ controller من العنوان ويرسله إلى Nashra. ولا يفشل أبدًا بخطأ 500: فمفتاح API الناقص أو الخدمة التي يتعذّر الوصول إليها يعودان بجملة واحدة لطيفة، بصيغة JSON لـ `request()`، وعلى شكل إعادة توجيه للنموذج العادي.

<<< @/app/Http/Controllers/SubscribeController.php

</Accordion>

</AccordionGroup>

## ابنِه {#build-it}

المكوّنات المخصّصة هي الجزء الوحيد من LaRecipe الذي يحتاج إلى Node، ووقت البناء فقط، على جهازك أو في CI. أضف `@larecipe/vite` و `@larecipe/ui` اعتمادياتٍ للتطوير، ثم أضف الإضافة إلى إعدادات Vite لديك. ويعمل `@larecipe/vite` مع Vite 6 و 7 و 8. وهذه إعدادات هذا الموقع نفسه:

<<< @/vite.config.js

ثم يكتب `npm run build` الامتداد في `public/build/larecipe/`. ولا يشغّل خادمك Node أبدًا: فالملفات المبنية ثابتة. ويبحث LaRecipe عن `public/build/larecipe/manifest.json` في كل صفحة توثيق، وحين يجد مدخل الامتداد فيه، يضيف ملف أنماطه ووحدته بعد بيئة التشغيل الخاصة به مباشرة. فلا وسم script تضيفه بيدك.

<Accordion title="نسخة واحدة من React في الصفحة">

البناء وحدة ES مع ملف manifest. وتُستبعد `react` و `react-dom` و `@larecipe/ui` من الحزمة عن قصد. فكل صفحة توثيق تطبع import map قبل سكربتات LaRecipe، يوجّه الأسماء المجرّدة `react` و `react/jsx-runtime` و `react-dom` و `react-dom/client` و `@larecipe/ui` إلى الملفات المبنية الخاصة بـ LaRecipe:

```html
<script type="importmap">{"imports":{"react":"/larecipe-assets/{build}/assets/react-….js","@larecipe/ui":"/larecipe-assets/{build}/assets/larecipe-ui-….js", "…": "…"}}</script>
```

يُبقي امتدادك هذه الاستيرادات مجرّدة، فيحلّها المتصفح إلى الوحدات نفسها التي يستخدمها LaRecipe. فتوجد نسخة واحدة من React وسجلّ واحد للمكوّنات، وتعمل الـ hooks مثل `useLaRecipe()` داخل مكوّناتك. وتعمل `defineExtension()` سواء حُمّلت وحدتك قبل بيئة تشغيل LaRecipe أو بعدها.

</Accordion>

## استخدمه {#use-it}

**داخل النص، في Markdown.** اكتب الوسم في سطر مستقل، مع أسطر فارغة حوله، كأي مكوّن مدمج. والخصائص نصوص:

```markdown
<Subscribe list="larecipe" />
```

**في كل صفحة.** ضعه في نقطة إدراج في `larecipe.json`، كما يفعل هذا الموقع بـ `"inserts": { "article.after": ["Subscribe"] }`. راجع [نقاط الإدراج والفتحات](insert-points.md).

| القارئ | يحصل على |
| --- | --- |
| متصفح مع JavaScript | نموذج React، الذي يرسل بـ `request()` ويجيب في مكانه |
| متصفح بلا JavaScript، أو محرك زحف | النموذج البديل، الذي يرسل وينتهي في `/newsletter` |
| وكيل يقرأ `.md` أو MCP | سطر واحد: رابط النشرة البريدية |

## التالي {#next}

- [نقاط الإدراج والفتحات](insert-points.md): ضع مكوّنًا في كل صفحة.
- [المظهر](theme.md): القيم ومتغيرات CSS التي تستطيع مكوّناتك قراءتها.
- [مرجع المكوّنات](../reference/components.md): المكوّنات المدمجة و HTML الذي تُصيّره.
