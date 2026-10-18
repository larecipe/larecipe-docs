---
title: توثيقٌ يواكب الكود
description: LaRecipe موقع توثيق يعيش داخل تطبيق Laravel الخاص بك. يكتب وكيل البرمجة الصفحات ويحدّثها مع كل تغيير، فيقرأ الناس والوكلاء دائمًا الإجابة الحالية.
sources:
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/InstallCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Services/IntroductionPage.php
  - vendor/larecipe/larecipe/src/Domains/Sync/**
  - vendor/larecipe/larecipe/src/Domains/Agents/**
  - resources/larecipe/**
inserts: { article.after: false }
---

# توثيقٌ يواكب الكود

<Video title="المشرف على LaRecipe يشرح المشروع" />

LaRecipe موقع توثيق يعيش داخل تطبيق Laravel الخاص بك. تكتب الصفحات بصيغة Markdown في مستودعك، بجوار الكود الذي تشرحه. يقرؤها الناس موقعًا سريعًا يعمل حتى من دون JavaScript، ويقرأ الوكلاء الصفحات نفسها بصيغة Markdown، وعبر `llms.txt` وخادم MCP.

## التثبيت بأمرين

```bash
composer require larecipe/larecipe
php artisan larecipe:install
```

افتح `/docs` في متصفحك، وستجد التوثيق جاهزًا، بدءًا بصفحة مقدمة كُتبت من معلومات حقيقية عن تطبيقك. لا حاجة إلى Node ولا إلى خطوة بناء.

## ارفع الكود، ويلحق به التوثيق

تذكر كل صفحة الكود الذي تشرحه. وحين تغيّر ذلك الكود، يحدّث وكيل البرمجة الذي أجرى التغيير الصفحةَ أيضًا قبل أن يرفع عمله. فيصل التوثيق في الـ pull request نفسه مع الكود، وتراجع الاثنين معًا.

يُعِدّ المثبّت ذلك لوكلاء البرمجة الذين تستخدمهم أصلًا، مثل Claude Code و Codex و Cursor، فلا شيء عليك أن تتذكره في عملك اليومي.

## الخطوة التالية

<CardGroup cols="2">

<Card title="التثبيت" href="get-started/installation.md" icon="rocket">
المتطلبات، وأسئلة المثبّت، والملفات التي يكتبها.
</Card>

<Card title="توثيقك الأول" href="get-started/your-first-docs.md" icon="pen-line">
دع وكيل البرمجة يقرأ تطبيقك ويكتب صفحات أولية.
</Card>

<Card title="اكتب صفحة" href="writing/markdown.md" icon="book-open">
Markdown والمكوّنات وأمثلة الكود المأخوذة من تطبيقك.
</Card>

<Card title="كيف يعمل" href="keeping-current/how-it-works.md" icon="refresh-cw">
كيف يجد LaRecipe الصفحات التي جعلها تغييرٌ ما قديمة.
</Card>

</CardGroup>

تنقل موقعًا من LaRecipe 2؟ اقرأ [الترقية من الإصدار 2](upgrade.md).

## ابقَ على اطلاع

النموذج أدناه مكوّن React مخصّص يضيفه هذا الموقع إلى LaRecipe. [شاهد كيف بُني](customize/custom-components.md).

<Subscribe list="larecipe" />
