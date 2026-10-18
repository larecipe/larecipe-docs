---
title: النشر
description: انشر موقع LaRecipe كما تنشر أي تطبيق Laravel بسكربت من أربعة أسطر، مع الخطوات الإضافية لمجموعات قاعدة البيانات والمكوّنات المخصّصة وملفات الواجهة التي يقدّمها خادم الويب.
sources:
  - vendor/larecipe/larecipe/src/LaRecipeServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/DeliveryServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Commands/OptimizeCommand.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/DocsUrls.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/Assets.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PackageAssetController.php
  - vendor/larecipe/larecipe/src/Domains/Sources/SourcesServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/ProvenanceCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Commands/CacheWarmCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Listeners/WarmChangedSet.php
  - vendor/larecipe/larecipe/src/Domains/Export/Commands/ExportCommand.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - package.json
  - vite.config.js
translated_from: sha256:faf3c28e19a8b4223296147304fcab6a6993b94bb6de4f24f8d37962d44d58d5
---

# النشر

يُنشر موقع LaRecipe كما يُنشر أي تطبيق Laravel، لأنه تطبيق Laravel. يصل التوثيق في عملية النشر نفسها مع الكود، فلا مشروع توثيق منفصل ولا Node على الخادم. وحين يعيش التوثيق في المستودع، هذا هو السكربت كله:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan larecipe:provenance
php artisan optimize
```

أضف `php artisan larecipe:cache:warm` بعد `migrate` حين تستخدم مجموعة توثيق قاعدة البيانات، وأضف `npm ci && npm run build` حين تكون لديك مكوّنات مخصّصة.

## ما الذي تفعله كل خطوة

<Steps>

<Step title="ثبّت الاعتماديات">

LaRecipe اعتمادية Composer عادية. واجهة القراءة فيه تأتي مبنية مسبقًا داخل الحزمة، فلا شيء فيها يحتاج إلى بناء.

</Step>

<Step title="شغّل الترحيلات">

ينشئ `migrate` جداول [مجموعة قاعدة البيانات](../sources/database.md)، ويطبّق [ترحيلات التوثيق](../sources/docs-migrations.md) التي يحملها كل pull request. أما التطبيق الذي يعيش توثيقه كله في المستودع فلا يحصل على أي ترحيلات من LaRecipe.

</Step>

<Step title="اجمع بيانات التحديث (provenance)">

يأتي سطر «حُدّث» (Updated) تحت كل عنوان من git. يقرأ `larecipe:provenance` سجل git مرة واحدة ويكتبه في `bootstrap/cache/larecipe/`، فلا يشغّل أي طلب git أبدًا. شغّله حيث يكون السجل الكامل متاحًا. راجع [مصدر المستودع](../sources/repository.md) لتعرف ما الذي يسجّله.

</Step>

<Step title="شغّل optimize">

يسجّل LaRecipe الأمر `larecipe:optimize` ضمن `optimize` في Laravel، فيُصيّر هذا الأمر الواحد أيضًا كل صفحة من كل مجموعة مستودع في مخزن الذاكرة المؤقتة، مع قوائم الصفحات والتنقل وسجلات البحث. فيحصل القرّاء على صفحات جاهزة من أول طلب.

</Step>

<Step title="جهّز مجموعات قاعدة البيانات">

```bash
php artisan larecipe:cache:warm
```

لمجموعات قاعدة البيانات فقط، بعد أن يطبّق `migrate` ترحيلات التوثيق الجديدة. يجهّز كل مجموعات قاعدة البيانات، أو مجموعة واحدة مع `--set=handbook`. وإن لم تكن هناك مجموعات قاعدة بيانات، يقول ذلك ولا يفعل شيئًا.

</Step>

<Step title="ابنِ المكوّنات المخصّصة">

```bash
npm ci
npm run build
```

فقط حين يكون في تطبيقك [مكوّنات React مخصّصة](../customize/custom-components.md). يكتبها `@larecipe/vite` في `public/build/larecipe/`، ويحمّل LaRecipe هذا البناء في كل صفحة توثيق من تلقاء نفسه. لا تحتاج إلى Node إلا لهذا البناء، على جهازك أو في CI.

</Step>

</Steps>

## البيئة

- **مخزن ذاكرة مؤقتة دائم.** الصفحات المُصيَّرة والتنقل وسجلات البحث تعيش في مخزن الذاكرة المؤقتة (`larecipe.cache.store`، أو المخزن الافتراضي لديك). يصلح أي مخزن دائم: `redis` و `database` و `file` و `memcached`. ولا بد لمجموعة قاعدة البيانات من مخزن كهذا، ويبلّغ `larecipe:validate` عن خطأ حين تعمل على `array` أو `null`.
- **عامل طوابير لمجموعات قاعدة البيانات.** حين تُنشر صفحة، يضع LaRecipe في الطابور مهمة تجهّز المجموعة التي تغيّرت بعد أن تُثبَّت معاملة قاعدة البيانات. ومن دون عامل، يدفع القارئ التالي ثمن الصفحة غير الجاهزة.

## انشر ملفات الواجهة

افتراضيًا يقدّم LaRecipe ملفات واجهته من `vendor` عبر PHP، على المسار `/larecipe-assets/{build}/{file}`، بأسماء مشتقة من بصمة المحتوى وبترويسة تخزين مؤقت ثابتة لمدة عام. لتجعل خادم الويب أو شبكة CDN يقدّمها بدلًا من ذلك، انشرها بعد كل `composer install`:

```bash
php artisan vendor:publish --tag=larecipe-assets --force
```

توضع الملفات في `public/vendor/larecipe`. ولا يوجّه LaRecipe الصفحات إليها إلا ما دام البناء المنشور يطابق الحزمة المثبّتة، فتُتجاهل النسخة القديمة بعد `composer update` حتى تنشرها من جديد.

## بعد النشر

افحص ما يحصل عليه الوكلاء من التطبيق الحي:

```bash
php artisan larecipe:doctor
```

يطلب توثيقك عبر التطبيق ويقيّم نسخ Markdown و `llms.txt` وصفحات 404 والترويسات وعمليات إعادة التوجيه و `skill.md` ونقطة نهاية MCP، مع إصلاح لكل ملاحظة. راجع [التحقق و doctor](validation.md#larecipedoctor).

## تفاصيل

<AccordionGroup>

<Accordion title="بيانات التحديث في CI والنسخ السطحية">

النسخة السطحية (shallow clone) تعطي كل الصفحات آخر commit نفسه. ومن دون ملف مجمَّع، تعود الصفحة إلى وقت تعديل ملفها، وهو بعد نسخة جديدة وقتُ النشر. إن كنت تبني حزمة النشر في CI ثم تشحنها، فشغّل `larecipe:provenance` في CI واشحن `bootstrap/cache/larecipe/` معها.

لا يجمع `php artisan optimize` بيانات التحديث، فأبقِ له خطوته الخاصة في النشر. ولا يمسّ `php artisan optimize:clear` الملف المجمَّع.

</Accordion>

<Accordion title="optimize: المسارات ومخزن الذاكرة المؤقتة والنشر من دون توقف">

- **تخزين المسارات مؤقتًا** يشمل مسارات LaRecipe. شغّل `optimize` مرة أخرى بعد أن تغيّر `route.prefix` أو `route.domain` لمجموعة ما.
- **شغّله على مخزن الذاكرة المؤقتة في الإنتاج.** يكتب `larecipe:optimize` في المخزن الذي يستخدمه تطبيقك لـ LaRecipe. وعلى جهاز بناء بمخزن مختلف، تذهب الصفحات المجمَّعة إلى مكان لا يقرأ منه الإنتاج أبدًا.
- **النشر من دون توقف** آمن: مفاتيح الذاكرة المؤقتة مشتقة من محتوى الصفحات، فلا يزعج تجميعُ إصدار جديد الإصدارَ الذي ما زال يخدم القرّاء.
- **بعد ترقية LaRecipe** تبدأ الذاكرة المؤقتة فارغة: كل مفتاح يحمل رقم إصدار للصيغة، ولا يقرأ الإصدار الجديد أبدًا ما كتبه إصدار أقدم. وهذه الخطوة تجهّزها من جديد قبل أول قارئ.

تشرح صفحة [التخزين المؤقت](caching.md) ما الذي يُخزَّن ولماذا لا تحتاج المفاتيح إلى تفريغ أبدًا.

</Accordion>

<Accordion title="الخوادم الوسيطة و APP_URL والجلسات">

- **المضيف والبروتوكول الحقيقيان.** نسخ Markdown للوكلاء و `llms.txt` وخريطة الموقع والروابط الأساسية (canonical) تحمل عناوين URL مطلقة، تُبنى من الطلب كما يبنيها `url()` في Laravel. وخلف موازن أحمال أو خادم وسيط (proxy) ينهي TLS، اجعله موثوقًا (`$middleware->trustProxies(at: '*')` في `bootstrap/app.php`، أو الخوادم الوسيطة التي تشغّلها)، لتقول تلك العناوين `https` ونطاقك.
- **`APP_URL`** هو العنوان الذي تستخدمه أوامر الطرفية، لأنها بلا طلب: `larecipe:export` من دون `--base-url`، و `larecipe:doctor` الذي يطلب توثيقك عبر التطبيق.
- **الجلسات على مسارات التوثيق.** تعمل كل مجموعة افتراضيًا ضمن مجموعة الوسائط (middleware) `web` (`route.middleware`)، وعليها يعتمد تقييم الصفحات وأي نموذج في مكوّن مخصّص، من أجل الجلسة والحماية من CSRF.

</Accordion>

</AccordionGroup>

## التالي

- [التحقق و doctor](validation.md): افحص التوثيق قبل أن يُشحن، والموقع بعد ذلك.
- [التخزين المؤقت](caching.md): تعمّق في ما يُخزَّن ومتى يتغيّر.
- [الأمان](security.md): ما يفعله LaRecipe ليحافظ على سلامة المحتوى والتوثيق الخاص.
