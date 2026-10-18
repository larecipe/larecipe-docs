---
title: أوامر Artisan
description: كل أوامر artisan من نوع larecipe:* مجمّعة حسب المهمة، مع طريقة استخدام كل أمر وخياراته مقروءة من التطبيق أثناء تشغيله.
sources:
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Upgrade/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Validation/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Agents/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Export/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Access/Commands/**
  - vendor/larecipe/larecipe/src/Domains/References/Commands/**
  - vendor/larecipe/larecipe/src/LaRecipeServiceProvider.php
translated_from: sha256:f19e309d5fa3b10ac130cf9586fbe86f5eea9acfaddb6b906cc923ac8aad4ea2
---

# أوامر Artisan

كل أوامر `larecipe:*`، مجمّعة حسب المهمة. تُقرأ كتلة الاستخدام وجدول الخيارات لكل أمر من التطبيق أثناء تشغيله عبر [مرجع حي `<Artisan>`](../writing/live-references.md)، فتطابق ما يعرضه `php artisan help`.

الأوامر التي تأخذ إصدارًا من التوثيق تستخدم `--docs-version`، لأن Symfony يحجز `--version` للتطبيق نفسه.

## الإعداد والكتابة

### larecipe:install

يُعِدّ مجموعة توثيق بصفحة أولى، ويكتب تعليمات `AGENTS.md` والمهارات وإعداد MCP والخطّافات للوكلاء الذين يجدهم. يعرض `--dry-run` كل خطوة مسبقًا، ولكل سؤال خيار في سطر الأوامر. راجع [التثبيت](../get-started/installation.md).

<Artisan command="larecipe:install" />

### larecipe:onboard

يكتب توثيقًا أوليًا بوكيل البرمجة الذي تستخدمه ومهارة `larecipe-write-docs`. يعرض أمر الوكيل ويسأل قبل أن يشغّله. ولا يُنشئ أي commit. راجع [توثيقك الأول](../get-started/your-first-docs.md).

<Artisan command="larecipe:onboard" />

### larecipe:upgrade

ينقل تطبيقًا من LaRecipe 2 إلى LaRecipe 3: الإعدادات والتنقل والمحتوى. ومن دون `--apply` يكتب التقرير فقط. راجع [الترقية من الإصدار 2](../upgrade.md).

<Artisan command="larecipe:upgrade" />

### larecipe:make:page

ينشئ صفحة فيها `title` و `description` و `sources` في ترويستها (frontmatter). لمجموعات المستودع فقط. لا يعدّل `navigation.json`، لكنه ينبّهك حين يكون للإصدار ملف منه.

<Artisan command="larecipe:make:page" />

### larecipe:make:version

يضيف إصدارًا إلى مجموعة مستودع بنسخ إصدار موجود. وفي مجموعة بلا إصدارات، يسمّي `--current-as` الإصدار الذي تنتمي إليه صفحاتها الحالية. راجع [الإصدارات](../writing/versions.md).

<Artisan command="larecipe:make:version" />

### larecipe:navigation:freeze

يكتب التنقل الذي يستنتجه LaRecipe من المجلدات وترويسات الصفحات في `navigation.json`، لتعدّله بيدك. لمجموعات المستودع فقط.

<Artisan command="larecipe:navigation:freeze" />

## إبقاء التوثيق محدّثًا

### larecipe:affected

يسرد الصفحات التي تطابق `sources:` فيها الملفات التي لمسها تغييرٌ ما، وأيّها تركها التغيير قديمة. يشغّله خطّاف pre-push وفحص CI. راجع [كيف يعمل](../keeping-current/how-it-works.md) لنطاقات المقارنة وصيغ الإخراج.

<Artisan command="larecipe:affected" />

### larecipe:sources:suggest

يقترح `sources:` للصفحات التي لا تذكر أيًّا منها، انطلاقًا من الأصناف والأوامر والمسارات التي تذكرها. ويكتبها `--apply` في تلك الصفحات.

<Artisan command="larecipe:sources:suggest" />

### larecipe:validate

يفحص الروابط والتنقل والمخططات (schemas) والمكوّنات والمقتطفات والمراجع الحية وحدود الحجم والمهارات. يخرج برمز غير الصفر عند وجود خطأ، ويطبع `--json` التقرير الذي يقرؤه الوكلاء. راجع [التحقق](../operate/validation.md).

<Artisan command="larecipe:validate" />

### larecipe:hook

تستدعيه خطّافات الوكلاء (`pre-push` و `session-start`) وخطّاف git (`git-pre-push`). لا تشغّله بنفسك.

<Artisan command="larecipe:hook" />

### larecipe:hooks:git

يثبّت فحص pre-push في git أو يزيله، لمن يرفع عمله من دون وكيل. يكتفي بالتحذير افتراضيًا، ويوقف `--strict` عملية الـ push. ولا يستبدل خطّافًا موجودًا أبدًا.

<Artisan command="larecipe:hooks:git" />

### larecipe:sync

يشغّل وكيل توثيق داخل تطبيقك بمفتاح مزوّد `laravel/ai` الخاص بك: للصفحات التي تركها تغييرٌ قديمة، أو للصفحات التي تسمّيها، أو للمجموعة كلها مع `--audit`. يعرض خطته ويسأل أولًا. راجع [larecipe:sync](../keeping-current/sync.md).

<Artisan command="larecipe:sync" />

## الوكلاء

### larecipe:agents

يعيد توليد كتلة `AGENTS.md` والمهارات وأنماط الخطّافات المُجمَّعة. شغّله بعد تحديث LaRecipe أو تغيير إعداداته.

<Artisan command="larecipe:agents" />

### larecipe:agents-md

يكتب فهرس التوثيق في كتلة LaRecipe داخل `AGENTS.md`. شغّله بعد إضافة صفحات أو حذفها أو إعادة تسميتها.

<Artisan command="larecipe:agents-md" />

### larecipe:mcp

يشغّل خادم MCP المحلي عبر stdio. يشغّله إعداد MCP لدى وكيلك. راجع [أدوات MCP](mcp-tools.md).

<Artisan command="larecipe:mcp" />

### larecipe:doctor

يقيّم ما يحصل عليه الوكلاء من كل مجموعة توثيق، مع إصلاح لكل ملاحظة. يخرج برمز غير الصفر حين يفشل أحد الفحوص. راجع [التحقق و doctor](../operate/validation.md).

<Artisan command="larecipe:doctor" />

## المصادر

### larecipe:source:export

يكتب مجموعة توثيق في صورة حزمة (bundle) من الملفات المعيارية، إلى مجلد أو ملف `.tar.gz`، لاستخدامها مع `source:import` و `pull`.

<Artisan command="larecipe:source:export" />

### larecipe:source:import

يحمّل حزمة إلى المصدر المضبوط لمجموعة ما، بعد التحقق منها أولًا. ويقارن `--verify` كل بصمة محتوى (content hash) بعد ذلك.

<Artisan command="larecipe:source:import" />

### larecipe:source:move

ينسخ مجموعة بين المستودع وقاعدة البيانات، ويطبع تغيير الإعدادات الذي يجعل الموقع يُقدَّم من المصدر الجديد. ولا يعدّل `config/larecipe.php` أبدًا. راجع [تبديل المصادر](../sources/switching.md).

<Artisan command="larecipe:source:move" />

### larecipe:make:docs-migration

ينشئ هيكل ترحيل توثيق يدويًا. يُملأ كل `--page` بمحتواه الحالي وبصمة محتواه. راجع [ترحيلات التوثيق](../sources/docs-migrations.md).

<Artisan command="larecipe:make:docs-migration" />

### larecipe:pull

يحدّث مجموعة قاعدة بيانات محلية أو في CI من حزمة صُنعت في الإنتاج بالأمر `larecipe:source:export`.

<Artisan command="larecipe:pull" />

### larecipe:cache:warm

يملأ الذاكرة المؤقتة لمجموعات قاعدة البيانات مسبقًا. شغّله في عملية النشر بعد `php artisan migrate`.

<Artisan command="larecipe:cache:warm" />

## التشغيل

### larecipe:optimize

يُصيّر كل صفحة في مجموعات المستودع في الذاكرة المؤقتة، مع قوائم الصفحات والتنقل وسجلات البحث. يشغّله `php artisan optimize`.

<Artisan command="larecipe:optimize" />

### larecipe:clear

يمحو ما جمّعه `larecipe:optimize`، ويحذف `bootstrap/cache/larecipe/optimized.php`. ويبقى فهرس بيانات التحديث (provenance) ولقطة المراجع وأنماط الخطّافات. يشغّله `php artisan optimize:clear`.

<Artisan command="larecipe:clear" />

### larecipe:provenance

يجمع آخر تغيير لكل صفحة من git، لمجموعات المستودع. شغّله حيث يتوفر السجل الكامل، فالأمر `larecipe:optimize` لا يشغّله. راجع [مصدر المستودع](../sources/repository.md).

<Artisan command="larecipe:provenance" />

### larecipe:references:snapshot

يكتب ما تستطيع المراجع الحية قراءته في `bootstrap/cache/larecipe/references.json`، لبيئات CI التي لا قاعدة بيانات فيها. ولا يُستخدم إلا حين لا يستطيع التطبيق الإجابة.

<Artisan command="larecipe:references:snapshot" />

### larecipe:export

يكتب مجموعات التوثيق العامة موقعًا ثابتًا، ومعه البحث. وما يحتاج إلى التطبيق أثناء تشغيله يُستبعد ويُذكر في قائمة. راجع [التصدير الثابت](../operate/static-export.md).

<Artisan command="larecipe:export" />

### larecipe:share

يطبع رابطًا موقّعًا مؤقتًا يتيح لأي شخص قراءة صفحة واحدة، خاصة كانت أم لا. راجع [كتل الصلاحيات والتوثيق الخاص](../writing/gate-blocks.md).

<Artisan command="larecipe:share" />

## لاحقًا

ليست في هذا الإصدار. لا تكتب سكربتات تعتمد عليها بعد.

| الأمر | الحالة | ما الذي سيفعله |
| --- | --- | --- |
| `larecipe:ui:eject` | قريبًا | ينسخ مصدر واجهة القراءة، أو مكوّنًا واحدًا منها، إلى تطبيقك. |
| `larecipe:ui:diff` | قريبًا | يعرض تغييرات الواجهة في المصدر الأصلي منذ الإخراج (eject). |

لا يوجد أمر `larecipe:publish`. ليقدّم خادم الويب ملفات الواجهة المبنية مسبقًا، استخدم أمر Laravel نفسه (راجع [النشر](../operate/deployment.md)):

```bash
php artisan vendor:publish --tag=larecipe-assets --force
```
