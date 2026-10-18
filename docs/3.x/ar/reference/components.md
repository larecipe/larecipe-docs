---
title: المكوّنات
description: خصائص كل مكوّن مدمج، والمراجع الحية، وكتل الصلاحيات، ومكوّنات هذا الموقع الخاصة، مع ما يُصيّره كل منها في HTML الخادم وفي Markdown الوكلاء.
sources:
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/BuiltIn/**
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/BuiltInComponents.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/ComponentDefinition.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/COMPONENTS.md
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/ComponentRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/UndeclaredPropsFilter.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/DocumentTransformer.php
  - vendor/larecipe/larecipe/ui/src/islands/Video.tsx
  - vendor/larecipe/larecipe/src/Domains/References/Markdown/ReferenceComponents.php
  - vendor/larecipe/larecipe/src/Domains/References/Services/ReferenceHtml.php
  - vendor/larecipe/larecipe/src/Domains/References/Services/ReferenceMarkdown.php
  - vendor/larecipe/larecipe/src/Domains/Access/Blocks/**
  - app/Providers/AppServiceProvider.php
  - resources/views/docs/subscribe.blade.php
  - resources/views/docs/install-tabs.blade.php
  - resources/views/docs/docs-flow.blade.php
  - resources/views/docs/featured-on.blade.php
  - resources/views/docs/features.blade.php
translated_from: sha256:93418f92556b1e40c6a085d16e822655753bc7810eedc2dfe72014cc9074d0a3
---

# المكوّنات

كل مكوّن يمكن لصفحة في هذا الموقع أن تستخدمه: خصائصه (props)، و HTML الذي يُصيّره الخادم، و Markdown الذي يقرؤه الوكلاء. للأمثلة الحية، راجع [المكوّنات](../writing/components.md).

## كيف تُفحص الخصائص

- غياب خاصية مطلوبة، أو قيمة خارج القيم المسموح بها، خطأ `components.props`. والوسم غير المعروف خطأ `components.unknown`، ويُصيَّر نصًّا كما هو (escaped). يبلّغ `larecipe:validate` عن الاثنين، والصفحة التي تُصيَّر مع أخطاء لا تُخزَّن مؤقتًا.
- الخصائص التي لا يعلنها المكوّن تُحذف قبل أي تصيير، في كل مكوّن مدمج وفي كل مكوّن في مجموعة قاعدة بيانات. ولا يحتفظ بها إلا المكوّن المخصّص في مجموعة مستودع، ليقبل بيانات لا يصفها LaRecipe. والخصائص التي يستنتجها LaRecipe، مثل `embed` في الفيديو أو `labels` في مجموعة التبويبات، تأتي دائمًا من LaRecipe، لا من الصفحة.
- الخصائص من نوع `boolean` تقرأ `"false"` و `"0"` و `"no"` و `""` على أنها false، والخصائص من نوع `integer` تُحوَّل من نصّها.
- المكوّنات الموسومة بـ **island** تثبّتها واجهة React أيضًا. يُلفّ HTML الخادم الخاص بها في `<div data-lr-component="Name" data-lr-props='{...}'>`، حيث الخصائص JSON مع ملء القيم الافتراضية. و HTML الخادم كامل من دون JavaScript.
- في Markdown الوكلاء، كل رابط مطلق.

## المكوّنات المدمجة

### Callout

ملاحظة أو نصيحة أو تحذير بارز. ينتج تنبيه GitHub (`> [!WARNING]`) والتوجيه `:::warning` المكوّن نفسه.

| الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- |
| `type` | string | لا | `note` | `note`, `tip`, `important`, `warning`, `danger`, `caution`, `info`, `success` | نوع الملاحظة، وتسميتها. |
| `title` | string | لا | | | تسمية تظهر فوق المحتوى، بدلًا من النوع. |

- **HTML:** `<aside class="lr-callout" data-type="{type}" role="note">` مع `.lr-callout-label` و `.lr-callout-body`.
- **Markdown:** تنبيه GitHub، والعنوان سطر أول بخط عريض. `tip` و `success` يصبحان `[!TIP]`، و `important` يصبح `[!IMPORTANT]`، و `warning` يصبح `[!WARNING]`، و `danger` و `caution` يصبحان `[!CAUTION]`، والبقية `[!NOTE]`.

### Steps و Step

إجراء مرقّم: `<Step>` لكل خطوة داخل `<Steps>`. ليس لـ `Steps` خصائص.

| المكوّن | الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- | --- |
| `Step` | `title` | string | نعم | | | ما يفعله القارئ في هذه الخطوة. |

- **HTML:** `<ol class="lr-steps">`، وكل خطوة `<li class="lr-step">` مع `.lr-step-title` و `.lr-step-body`.
- **Markdown:** قائمة مرقّمة، كل عنصر فيها `1. **Title**` ومتنه مُزاح تحته.

### Tabs و Tab

محتوى في تبويبات: `<Tab>` لكل لوحة داخل `<Tabs>`. **Island:** `Tabs`، مع خاصية مستنتجة هي `labels`.

| المكوّن | الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- | --- |
| `Tabs` | `default` | string | لا | | | تسمية التبويب الذي يظهر أولًا. |
| `Tabs` | `sync` | string | لا | | | التبويبات التي تحمل المفتاح نفسه تتبدّل معًا في أنحاء الصفحة. |
| `Tab` | `label` | string | نعم | | | تسمية التبويب. |

- **HTML:** `<div class="lr-tabs">`، وكل لوحة `<section class="lr-tab" data-label="{label}">` مع `h4.lr-tab-label` و `.lr-tab-body`. ومن دون JavaScript تظهر كل اللوحات بالترتيب.
- **Markdown:** كل تبويب بصيغة `**Label**` يليه محتواه.

### CodeGroup

كتل كود متتالية تُعرض في تبويبات، يُسمّى كل منها بعنوان (`title`) الكتلة، وإلا بلغتها. بلا خصائص. **Island:** `CodeGroup`، مع خاصية مستنتجة هي `labels`.

- **HTML:** `<div class="lr-code-group">` حول `figure.lr-code` لكل كتلة.
- **Markdown:** كتل الكود، كل منها بعنوانها.

### Card

كتلة رابط بعنوان ووصف قصير.

| الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- |
| `title` | string | لا | | | عنوان البطاقة. ويمكن أن تكون البطاقة متنًا فقط. |
| `href` | string | لا | | | وجهة رابط البطاقة. والرابط النسبي إلى ملف `.md` يعمل. |
| `icon` | string | لا | | | أيقونة من مجموعة أيقونات المحتوى في LaRecipe، باسمها في Lucide، تُرسم فوق العنوان. والأسماء غير المعروفة لا ترسم شيئًا. |

- **HTML:** `<a class="lr-card" href="...">` مع `.lr-card-title` و `.lr-card-body`، أو `<div class="lr-card">` من دون `href`. ترسم `icon` الأيقونة في `.lr-card-icon` فوق العنوان (وتبقى على البطاقة بصيغة `data-icon`). والمتن المكوّن من فقرة واحدة يُزال عنه غلاف الفقرة.
- **Markdown:** `- [Title](url): description`، أو `- **Title**: description` من دون `href`.

<Accordion title="مجموعة أيقونات المحتوى">

`accessibility`, `activity`, `bell`, `blocks`, `book`, `book-open`, `bot`, `box`, `brain`, `bug`, `calendar`, `chart-column`, `circle-check`, `circle-question-mark`, `clipboard-list`, `clock`, `cloud`, `code`, `compass`, `cpu`, `credit-card`, `database`, `download`, `eye`, `file-code`, `file-text`, `flag`, `folder`, `gauge`, `gift`, `git-branch`, `git-merge`, `git-pull-request`, `globe`, `graduation-cap`, `hammer`, `heart`, `house`, `image`, `inbox`, `key`, `languages`, `layers`, `layout-grid`, `life-buoy`, `lightbulb`, `link`, `lock`, `mail`, `map`, `megaphone`, `message-circle`, `monitor`, `package`, `palette`, `pen-line`, `plug`, `puzzle`, `refresh-cw`, `rocket`, `scale`, `search`, `send`, `server`, `settings`, `shield-check`, `shopping-cart`, `smartphone`, `sparkles`, `star`, `terminal`, `timer`, `trophy`, `upload`, `user`, `users`, `wand-sparkles`, `workflow`, `wrench`, `zap`.

</Accordion>

### CardGroup و Columns

شبكة من البطاقات، ومحتوى متجاور.

| المكوّن | الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- | --- |
| `CardGroup` | `cols` | integer | لا | `2` | | عدد الأعمدة على الشاشات العريضة. |
| `Columns` | `cols` | integer | لا | `2` | | عدد الأعمدة على الشاشات العريضة. |

- **HTML:** `<div class="lr-card-group" data-cols="2">` و `<div class="lr-columns" data-cols="2">`.
- **Markdown:** العناصر الأبناء. وبطاقات مجموعة البطاقات تصبح قائمة واحدة.

### Accordion و AccordionGroup

قسم قابل للطي، ومجموعة متراصّة منها. ليس لـ `AccordionGroup` خصائص.

| المكوّن | الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- | --- |
| `Accordion` | `title` | string | نعم | | | الملخص الظاهر دائمًا. |
| `Accordion` | `open` | boolean | لا | `false` | | يبدأ مفتوحًا. |

- **HTML:** عنصر `<details class="lr-accordion">` الأصلي في المتصفح (مع `open` حين تُضبط)، و `<summary>` و `.lr-accordion-body`. والمجموعة `<div class="lr-accordion-group">`.
- **Markdown:** `**Title**` يليه المحتوى. والمجموعة هي أبناؤها.

### Badge

تسمية حالة قصيرة، تُكتب في سطر مستقل: `<Badge tone="success">Stable</Badge>`.

| الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- |
| `tone` | string | لا | `info` | `neutral`, `info`, `success`, `warning`, `danger` | اللون. |

- **HTML:** `<p><span class="lr-badge" data-tone="{tone}">text</span></p>`.
- **Markdown:** `**text**`.

### Button

رابط بشكل زر، أُبقي من أجل صفحات LaRecipe 2.

| الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- |
| `href` | string | لا | | | وجهة رابط الزر. ومن دونه يكون الزر تسمية ثابتة. |
| `variant` | string | لا | `primary` | `primary`, `secondary`, `outline`, `ghost` | النمط. |

- **HTML:** `<a class="lr-button lr-button-{variant}" href="...">`، أو `<span>` بأصناف CSS نفسها من دون `href`.
- **Markdown:** `[text](url)`، أو النص وحده.

### FileTree

بنية مجلدات، تُكتب قائمة Markdown متداخلة. بلا خصائص.

- **HTML:** `<div class="lr-file-tree">` حول القائمة.
- **Markdown:** القائمة.

### Frame

وسائط مع تعليق وقابلة للتكبير. **Island:** `Frame`.

| الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- |
| `caption` | string | لا | | | يظهر تحت الوسائط. |

- **HTML:** `<figure class="lr-frame">` حول الوسائط، مع `<figcaption>` حين تُضبط `caption`.
- **Markdown:** الوسائط، ثم `*caption*`.

### Video

فيديو لا يُحمَّل إلا حين يطلبه القارئ. ومن دون `src` يُظهر إطارًا مؤقتًا بتسمية. **Island:** `Video`.

| الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- |
| `src` | string | لا | | | ملف فيديو، أو رابط YouTube أو Vimeo أو Loom. |
| `poster` | string | لا | | | الصورة التي تظهر قبل تشغيل الفيديو. |
| `title` | string | لا | | | ما يعرضه الفيديو. |
| `duration` | string | لا | | | المدة، مثل `2:40`. |
| `placeholder` | string | لا | | | تسمية الإطار الذي يظهر حتى تُضبط `src`. |

- **HTML:** الملف يصبح `<figure class="lr-video"><video controls preload="none">`. ورابط YouTube أو Vimeo أو Loom يصبح رابطًا (`a.lr-video-link`، «شاهد على YouTube») ولا يُحمَّل شيء من المزوّد حتى يطلبه القارئ. يستنتج LaRecipe من `src` خاصيتي الـ island وهما `embed` (رابط `youtube-nocookie.com` أو `player.vimeo.com` أو `www.loom.com/embed`) و `provider`، ولا تستطيع الصفحة ضبطهما. ولا يعرض الـ island في إطار إلا `www.youtube-nocookie.com` و `player.vimeo.com` و `www.loom.com` عبر `https`. ومن دون `src`: يظهر `figure.lr-video.lr-video-placeholder` مع قيمة `placeholder`، وإلا فنص الواجهة `video.placeholder` بلغة الصفحة («العرض المصوّر قريبًا» بالعربية)، مع تحذير `media.placeholder`. والتعليق `title · duration`.
- **Markdown:** `[Video: Title · 2:40](src)`، أو `*Video coming soon: {placeholder}*` من دون `src`.

### Mermaid

مخطط Mermaid. وكتلة ```` ```mermaid ```` تُصيَّر بالطريقة نفسها. بلا خصائص. **Island:** `Mermaid`، مع خاصية مستنتجة هي `source`.

- **HTML:** `<pre class="lr-mermaid"><code>` مع المصدر نصًّا كما هو (escaped).
- **Markdown:** كتلة `mermaid`.

### Math

صيغة TeX. وكتلة `$$ ... $$` تُصيَّر بالطريقة نفسها. **Island:** `Math`، مع خاصية مستنتجة هي `source`.

| الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- |
| `display` | string | لا | `block` | `block`, `inline` | طريقة عرض الصيغة. |

- **HTML:** `<div class="lr-math" data-display="{display}"><code>` مع نص TeX كما هو (escaped).
- **Markdown:** كتلة `$$`.

## المراجع الحية

تقرأ `<Route>` و `<Artisan>` و `<Model>` و `<Event>` حقائق من التطبيق أثناء تشغيله. يأخذ كل منها خاصية واحدة مطلوبة. راجع [المراجع الحية](../writing/live-references.md).

| المكوّن | الخاصية | النوع | مطلوبة | الوصف |
| --- | --- | --- | --- | --- |
| `Route` | `name` | string | نعم | اسم المسار، مثل `orders.store`. |
| `Artisan` | `command` | string | نعم | اسم الأمر، مثل `orders:prune`. |
| `Model` | `name` | string | نعم | صنف النموذج، مثل `Order` أو `App\Models\Order`. |
| `Event` | `name` | string | نعم | صنف الحدث، مثل `OrderShipped` أو `App\Events\OrderShipped`. |

- **HTML:** `<div class="lr-reference" data-kind="{route|artisan|model|event}">` مع `.lr-reference-head` (النوع والهدف)، و `.lr-reference-description` اختياري، و `table.lr-reference-facts`، و `pre.lr-reference-usage` للأوامر، و `table.lr-reference-table` لكل جدول (الوسائط، والخيارات، والأعمدة، والعلاقات، والحمولة، والمستمعون). وكل قيمة تُعرض نصًّا كما هي (escaped).
- **Markdown:** سطر بخط عريض مثل `` **Command** `orders:prune`: description ``، ثم الحقائق في جدول، وطريقة استخدام الأمر في كتلة `bash`، ثم كل جدول تحت عنوانه.
- **ما الذي تعرضه:** يعرض `<Route>` الطريقة (method) والرابط (URI) وأسماء الـ middleware وحقول طلب النموذج (form request) مع أسماء قواعدها، ولا يعرض أبدًا وسائط القواعد ولا معاملات الـ middleware ولا النطاق، ولا يصف إلا المسارات التي يسمح بها `references.routes.allow`. ولا يصف `<Artisan>` أمرًا مخفيًا أبدًا، ولا يعرض قيمة افتراضية إلا حين يعلنها `$signature` الخاص بالأمر.
- **حين يختفي الهدف:** يكون HTML بالشكل `<div class="lr-reference" data-kind="..." data-missing="true">` مع إشعار مثل «The route `orders.store` is not defined in this app.»، ويكون Markdown الإشعار نفسه في تنبيه `> [!WARNING]`. ويبلّغ `larecipe:validate` عن `references.missing`.

## كتل الصلاحيات

تُظهر `<Can>` و `<Cannot>` المحتوى لبعض القرّاء فقط. والكتلة التي لا يحق للقارئ رؤيتها تُزال قبل أن تعمل أي أداة تصيير، فتتفق كل الواجهات. راجع [كتل الصلاحيات والتوثيق الخاص](../writing/gate-blocks.md).

| المكوّن | الخاصية | النوع | مطلوبة | الوصف |
| --- | --- | --- | --- | --- |
| `Can` | `ability` | string | نعم | صلاحية (ability) من بوابة (gate) أو سياسة (policy) يعرّفها تطبيقك، مثل `view-billing`. لا يظهر المحتوى إلا للقرّاء الذين تسمح لهم. |
| `Cannot` | `ability` | string | نعم | لا يظهر المحتوى إلا للقرّاء الذين لا تسمح لهم، ومنهم الزوار. |

- **HTML و Markdown:** المحتوى، بلا غلاف. والكتلة التي لا يحق للقارئ رؤيتها لا تكون في المخرجات أصلًا.
- الكتلة التي بلا `ability` تُخفى عن كل القرّاء ويُبلَّغ عنها خطأً. والصلاحية التي لم يعرّفها تطبيقك أبدًا ترفض كل القرّاء، ويحذّر `larecipe:validate` مع ذكر أقرب اسم معرَّف.

## مكوّنات هذا الموقع

### Subscribe

نموذج النشرة البريدية بعد كل مقال في هذا الموقع، مسجّل في `app/Providers/AppServiceProvider.php` بالاستدعاء `LaRecipe::component('Subscribe')`، وموضوع عبر `inserts` في [larecipe.json](manifest.md). **Island:** `Subscribe`، وهو نموذج React في `resources/larecipe/Subscribe.tsx`.

| الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- |
| `list` | string | لا | `larecipe` | | قائمة النشرة البريدية التي يُنضمّ إليها. |
| `showcase` | boolean | لا | `false` | | يضيف تحت النموذج سطرًا باهتًا يربط بطريقة بنائه. |

- **HTML:** نموذج عادي `<form class="lr-subscribe" method="post" action="/newsletter/subscribe">` من `resources/views/docs/subscribe.blade.php`، بلغة الصفحة (الإنجليزية أو العربية)، فيعمل النموذج من دون JavaScript.
- **Markdown:** سطر واحد يذكر ما ترسله النشرة ويربط صفحة `/newsletter`، بلغة الصفحة؛ ومع `showcase` يليه السطر الباهت ورابطه.

### InstallTabs

صندوق التثبيت في [المقدمة](../index.md)، مسجّل بالاستدعاء `LaRecipe::component('InstallTabs')`. يقدّم أحد تبويبيه طلبًا جاهزًا تلصقه في وكيل برمجة بالذكاء الاصطناعي، ويقدّم الآخر الأمرين لتشغيلهما بنفسك. لا يأخذ أي خاصية. **Island:** `InstallTabs`، في `resources/larecipe/InstallTabs.tsx`.

- **HTML:** اللوحتان معًا، كل واحدة تحت عنوانها، من `resources/views/docs/install-tabs.blade.php`، بلغة الصفحة. ومع JavaScript تصيران تبويبين في صندوق واحد.
- **Markdown:** الطلب في كتلة `text`، ثم الخطوات اليدوية والأمران في كتلة `bash`.

### DocsFlow

المخطط المتحرك قرب أعلى [المقدمة](../index.md)، مسجّل بالاستدعاء `LaRecipe::component('DocsFlow')`: commit يجعل صفحة التوثيق التي تذكر كوده قديمة، ووكيلك يحدّث تلك الصفحة، و pull request واحد يدمج الكود والتوثيق معًا، والوكلاء والناس يقرؤون النتيجة. **Island:** `DocsFlow`، في `resources/larecipe/DocsFlow.tsx`. لا يتحرك إلا وهو ظاهر على الشاشة، ويبقى ثابتًا للقرّاء الذين يفضّلون تقليل الحركة.

| الخاصية | النوع | مطلوبة | القيمة الافتراضية | القيم المسموح بها | الوصف |
| --- | --- | --- | --- | --- | --- |
| `caption` | string | لا | | | تعليق اختياري تحت المخطط. |

- **HTML:** المخطط نفسه ساكنًا، من `resources/views/docs/docs-flow.blade.php`، بلغة الصفحة.
- **Markdown:** التعليق، وقائمة مرقّمة قصيرة بخطوات الدورة، ثم المثال الذي يرسمه المخطط، بالكلمات.

### FeaturedOn

صف الشعارات تحت المخطط في [المقدمة](../index.md)، مسجّل بالاستدعاء `LaRecipe::component('FeaturedOn')`: عدد تنزيلات LaRecipe والمواقع التي ذكرته. لا يأخذ أي خاصية، وليس له Island: يُصيَّر على الخادم وحده.

- **HTML:** `<div class="lr-featured">` من `resources/views/docs/featured-on.blade.php`، بلغة الصفحة. كل شعار صورة بلون واحد من `public/images/featured/`، ويربط بمقاله، مع اسم الموقع لقارئات الشاشة.
- **Markdown:** سطر واحد بعدد التنزيلات والمواقع، مع روابطها.

### Features

شبكة أهم ميزات LaRecipe فوق «الخطوة التالية» في [المقدمة](../index.md)، مسجّلة بالاستدعاء `LaRecipe::component('Features')`. لكل ميزة أيقونة من مجموعة أيقونات المحتوى، وعنوان، وسطر واحد، وتربط بالصفحة التي تشرحها. لا تأخذ أي خاصية، وليس لها Island.

- **HTML:** `<div class="lr-features">` فيه `<ul class="lr-features-grid">` من الروابط، من `resources/views/docs/features.blade.php`، بلغة الصفحة. ثلاثة أعمدة بالعرض الكامل، وعمودان في عمود أضيق، وعمود واحد على الهواتف.
- **Markdown:** قائمة روابط، `- [Title](url): line`، سطر لكل ميزة.

راجع [المكوّنات المخصّصة](../customize/custom-components.md) لتضيف مكوّناتك.
