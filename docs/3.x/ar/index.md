---
title: توثيقٌ يواكب الكود
description: LaRecipe موقع توثيق يعيش داخل تطبيق Laravel الخاص بك. يكتب وكيل البرمجة الصفحات ويحدّثها مع كل تغيير، فيقرأ الناس والوكلاء دائمًا الإجابة الحالية.
sources:
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/InstallCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Services/IntroductionPage.php
  - vendor/larecipe/larecipe/src/Domains/Sync/**
  - vendor/larecipe/larecipe/src/Domains/Agents/**
  - resources/larecipe/**
  - lang/ar/featured-on.php
  - lang/ar/features.php
inserts: { article.after: false }
translated_from: sha256:1e1eab6645aa50b622f4c9da49d1d06912ec228d841dae0bfb963ceaa54d1878
---

# توثيقٌ يواكب الكود

تكتب الصفحات بصيغة Markdown في مستودعك، بجوار الكود الذي تشرحه. يقرؤها الناس موقعًا سريعًا، ويقرأ الوكلاء الصفحات نفسها بصيغة Markdown، وعبر `llms.txt` وخادم MCP.

تذكر كل صفحة الكود الذي تشرحه. وحين يتغيّر ذلك الكود، يحدّث وكيل البرمجة الذي أجرى التغيير الصفحةَ قبل أن يرفع عمله، وإن نسي يوقف خطّافٌ عملية الـ push مرة واحدة. فيصل التوثيق في الـ pull request نفسه مع الكود، وتراجع الاثنين معًا.

<DocsFlow />

<FeaturedOn />

## التثبيت بأمرين

<InstallTabs />

لا حاجة إلى Node ولا إلى خطوة بناء. ويُعِدّ المثبّت أيضًا وكلاء البرمجة الذين تستخدمهم أصلًا، مثل Claude Code و Codex و Cursor، فلا شيء عليك أن تتذكره في عملك اليومي.

## ما تحصل عليه

<Features />

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

<Subscribe list="larecipe" showcase />
