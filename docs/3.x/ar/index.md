---
title: توثيقٌ يواكب الكود
description: يعيش LaRecipe داخل تطبيق Laravel الخاص بك. يكتب وكلاء البرمجة التوثيق ويحدّثونه مع كل تغيير. يحصل القرّاء على موقع سريع وأنيق، ويحصل الوكلاء على Markdown و llms.txt و MCP.
sources:
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/InstallCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/**
  - vendor/larecipe/larecipe/src/Domains/Agents/**
  - vendor/larecipe/larecipe/src/Domains/Upgrade/**
  - resources/larecipe/**
inserts: { article.after: false }
---

# توثيقٌ يواكب الكود

<Video title="المشرف على LaRecipe يشرح المشروع" />

**ارفع الكود، ويلحق به التوثيق.** تذكر كل صفحة الكود الذي تصفه. وحين يجعل تغييرٌ ما إحدى الصفحات قديمة، يحدّثها وكيل البرمجة الذي أجرى التغيير [قبل أن يرفع التغييرات](keeping-current/pre-push-hook.md)، فيصل التوثيق في الـ pull request نفسه مع الكود. تراجع الاثنين معًا، أو تتركهما يمرّان في [الوضع التلقائي](keeping-current/review-modes.md).

## التثبيت بأمرين

```bash
composer require larecipe/larecipe
php artisan larecipe:install
```

افتح `/docs` وستجد التوثيق جاهزًا، بدءًا بصفحة مقدمة مبنية من معلومات حقيقية عن تطبيقك. لا حاجة إلى Node ولا إلى خطوة بناء. ويُعِدّ المثبّت أيضًا وكلاء البرمجة لديك: تعليمات في `AGENTS.md`، وأربع مهارات (skills)، وخادم MCP، وخطّاف pre-push، ليبقى التوثيق محدّثًا منذ أول push. تشرح صفحة [التثبيت](get-started/installation.md) كل خطوة وكل خيار.

ويستطيع المثبّت كذلك أن يسلّم تطبيقك لوكيل البرمجة الذي تستخدمه، فيكتب توثيقًا أوليًا حقيقيًا للقرّاء الذين تختارهم: مستخدمي المنتج، أو مستهلكي الـ API، أو مستخدمي الحزمة، أو فريقك نفسه. راجع [توثيقك الأول](get-started/your-first-docs.md).

## الحلقة

سأل ملف README الأول لـ LaRecipe إن كانت كتابة التوثيق مملّة. وفي الإصدار 3 جوابٌ أفضل: الوكيل الذي غيّر الكود يكتب التوثيق، وأنت تعتمده.

<CardGroup cols="2">

<Card title="الكتابة" href="get-started/your-first-docs.md">
يكتب وكيل البرمجة الصفحات من الكود الذي قرأه للتو، مستعينًا بأربع مهارات من LaRecipe وأدوات MCP ومدقّقٍ يكشف الروابط المعطّلة والأمثلة القديمة.
</Card>

<Card title="الإبقاء على الحداثة" href="keeping-current/how-it-works.md">
تذكر كل صفحة الكود الذي تصفه. وقبل كل push يحدّث وكيلك الصفحات التي أصبحت قديمة بسبب تغييرك، ويوقف خطّافٌ عملية الـ push مرة واحدة إن نسي.
</Card>

<Card title="التقديم" href="agents/surfaces.md">
يحصل الناس على موقع سريع يُقرأ جيدًا حتى من دون JavaScript. ويحصل الوكلاء على كل صفحة بصيغة Markdown، إلى جانب llms.txt و skill.md وخادم MCP، وبقواعد الوصول نفسها.
</Card>

<Card title="الملكية" href="sources/repository.md">
يعيش التوثيق في مستودعك أو في قاعدة بياناتك، خلف صلاحياتك (gates)، وضمن عملية النشر الخاصة بك. مفتوح المصدر، يعمل بجلسة وكيلك أو بمفاتيحك، ومن دون عدّادات.
</Card>

</CardGroup>

## كل قارئ

معظم قرّاء التوثيق اليوم وكلاء، لذا تُكتب كل صفحة هنا مرة واحدة وتُقدَّم مرتين: كهذه الصفحة للناس، وبصيغة Markdown للوكلاء. وهذه الصفحة نفسها متاحة أيضًا:

- **بصيغة Markdown**: [index.md](https://larecipe.saleem.dev/ar/3.x/index.md)، أو الرابط نفسه مع الترويسة `Accept: text/markdown`.
- **في llms.txt**: يدلّ [ملف llms.txt العربي](/ar/llms.txt) الوكلاءَ على صفحات هذه اللغة.
- **عبر MCP**: صِل وكيلك بالخادم، فيبحث في هذا التوثيق ويقرؤه أثناء عمله.
- **كمهارة**: يمنح [skill.md](https://larecipe.saleem.dev/skill.md) الوكيلَ خريطةً لهذا التوثيق وطرق الوصول إليه.

```bash
claude mcp add --transport http larecipe https://larecipe.saleem.dev/mcp
```

تشرح صفحتا [واجهات الوكلاء](agents/surfaces.md) و[خادم MCP](agents/mcp.md) كل واحدة منها، ويحصل توثيقك على جميعها لحظة التثبيت.

## الترقية من الإصدار 2

أمرٌ واحد يحوّل موقع LaRecipe 2 كاملًا: الإعدادات والقائمة الجانبية والصفحات. يبدأ بتشغيل تجريبي لا يغيّر شيئًا، ويكتب تقريرًا بما سيغيّره وما يبقى عليك.

```bash
php artisan larecipe:upgrade
php artisan larecipe:upgrade --apply
```

ثم تعمل مهارة `larecipe-upgrade-v2` مع وكيل البرمجة على ما ورد في التقرير: مكوّنات Vue المخصّصة، وCSS السمة، وBlade داخل Markdown. تجد التفاصيل في [دليل الترقية](upgrade.md)، وقد مرّ [توثيق الإصدار 2](/2.x) في هذا الموقع بالخطوات نفسها.

## ابقَ على اطلاع

النموذج أدناه مكوّن React مخصّص يضيفه هذا الموقع إلى LaRecipe، تمامًا كما ستضيف أنت مكوّنًا إلى موقعك. [شاهد كيف بُني](customize/custom-components.md).

<Subscribe list="larecipe" />
