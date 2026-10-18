---
title: التخزين المؤقت
description: كيف يخزّن LaRecipe مؤقتًا الصفحات المُصيَّرة وقوائم الصفحات والتنقل وسجلات البحث بحسب المحتوى، فلا يُفرَّغ شيء يدويًا أبدًا، وما الذي يفعله larecipe:optimize و larecipe:clear.
sources:
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RenderCache.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PageCatalog.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Commands/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/DeliveryServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/CodeHighlighter.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/References/Actions/ReferencesFingerprintAction.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/SitemapController.php
  - vendor/larecipe/larecipe/src/Support/CacheFormat.php
  - vendor/larecipe/larecipe/config/larecipe.php
translated_from: sha256:3116d4a58263f98f6de63557bb1ac8439ae3231bd003a94eb35dff655de52b8a
---

# التخزين المؤقت

يخزّن LaRecipe مؤقتًا بحسب المحتوى، لا بحسب الوقت. تُحفظ الصفحة المُصيَّرة تحت مفتاح مبني من محتوى الصفحة ومن كل ما يشكّل HTML الخاص بها، فتحصل الصفحة المتغيّرة على مفتاح جديد، ولا يُفرَّغ شيء يدويًا أبدًا. ولا تضبط شيئًا من ذلك: في صفحة [النشر](deployment.md) الأمران اللذان يجهّزان الذاكرة المؤقتة عند النشر.

يستخدم LaRecipe مخزن الذاكرة المؤقتة الافتراضي في تطبيقك ما لم تسمِّ مخزنًا آخر:

```php title="config/larecipe.php"
'cache' => [
    'store' => env('LARECIPE_CACHE_STORE'),
],
```

يصلح أي مخزن دائم: `database` و `file` و `redis` و `memcached`. مجموعة المستودع تقرأ ملفاتها من القرص، فالذاكرة المؤقتة الفارغة تكلّف وقت تصيير، لا صحة النتيجة. أما [مجموعة قاعدة البيانات](../sources/database.md) فتعمل دائمًا خلف هذا المخزن، ويبلّغ `larecipe:validate` عن خطأ حين تستخدم مجموعة قاعدة بيانات المخزن `array` أو `null` خارج الاختبارات. وكل ما يخزّنه LaRecipe بيانات بسيطة (نصوص ومصفوفات)، فيعمل مع الإعداد الافتراضي في Laravel 13 `cache.serializable_classes => false`.

## ما الذي يُخزَّن

| ما يُخزَّن | يتغيّر حين |
| --- | --- |
| المقالات المُصيَّرة (HTML وجدول المحتويات والمكوّنات) | تتغيّر الصفحة، أو LaRecipe، أو تعريف مكوّن، أو امتداد Markdown، أو مظهر الكود |
| كتل الكود الملوّنة، كل منها على حدة | يتغيّر كود الكتلة أو لغتها أو علامات أسطرها |
| قوائم الصفحات والتنقل | يتغيّر أي ملف في الإصدار |
| سجلات البحث | يتغيّر أي ملف في الإصدار |
| خريطة الموقع | يتغيّر أي ملف في المجموعة |

عمليات التصيير التي فيها أخطاء (مكوّن مجهول، أو مقتطف مفقود، أو مرجع لم يعد يُحَل) لا تُخزَّن أبدًا، فيظهر الإصلاح في الطلب التالي. أما التحذيرات، مثل العنصر النائب للفيديو، فلا تمنع التخزين.

<Accordion title="المفاتيح بالتفصيل">

| ما يُخزَّن | المفتاح |
| --- | --- |
| المقالات المُصيَّرة | إصدار أداة التصيير، والصفحة (المجموعة والإصدار واللغة والمسار)، وبصمة محتواها، وإصدار بيئة Markdown، والنسخة (مسار تركيب المجموعة، إضافة إلى الجمهور حين تنطبق كتل الصلاحيات) |
| الكود الملوّن | إصدار مظهر الكود، واللغة، وعلامات الفروق والتظليل، والكود |
| قوائم الصفحات والتنقل | المجموعة والإصدار واللغة، إضافة إلى بصمة المصدر |
| سجلات البحث | المجموعة والإصدار واللغة، إضافة إلى بصمة المصدر |
| خريطة الموقع | بصمات كل الإصدارات وعنوان المجموعة، لمضيف تطبيقك نفسه (`APP_URL`) أو لـ `route.domain` الخاص بالمجموعة فقط |

يشمل إصدار بيئة Markdown سجلّ المكوّنات، فتسجيل مكوّن أو تغييره عبر `LaRecipe::component()` يعيد تصيير كل صفحة في الطلب التالي. وتأتي بصمة مجموعة المستودع من ملفاتها؛ راجع [مصدر المستودع](../sources/repository.md). أما بصمة مجموعة قاعدة البيانات فتعيش في مخزن الذاكرة المؤقتة وتتغيّر مع كل نشر؛ راجع [مصدر قاعدة البيانات](../sources/database.md).

</Accordion>

## ما يقع خارج محتوى الصفحة

نوعان من الاعتماديات ليسا في نص الصفحة نفسه، ويتتبّعهما LaRecipe كليهما:

- **تضمينات المقتطفات.** تتذكر عملية التصيير المخزّنة بصمة كل ملف ضمّنته بـ `<<<`. وحين يتغيّر أحد تلك الملفات، تُصيَّر الصفحة من جديد.
- **المراجع الحية.** الصفحة التي تستخدم `<Route>` أو `<Artisan>` أو `<Model>` أو `<Event>` تُخزَّن تحت بصمة الجزء من التطبيق الذي تقرؤه. غيّر اسم مسار، فتُصيَّر من جديد الصفحات التي تعرضه بالضبط، لا أكثر. وحين تكون المسارات مخزّنة مؤقتًا بالأمر `php artisan route:cache`، تكون بصمة المسارات هي بصمة ملف ذاكرة المسارات المؤقتة. راجع [المراجع الحية](../writing/live-references.md).

## التجميع والتفريغ

يُصيّر `larecipe:optimize` كل صفحة من كل مجموعة مستودع في مخزن الذاكرة المؤقتة، ويبني قوائم الصفحات والتنقل وسجلات البحث، ويقيس كل فهرس بحث للزوار مقابل الميزانية. وهو مسجّل ضمن `optimize` في Laravel، فيشغّله `php artisan optimize`. وتُتخطّى مجموعات قاعدة البيانات: فهي تُخزَّن مؤقتًا عند النشر.

يمحو `larecipe:clear` ما جُمّع، ويشغّله `php artisan optimize:clear`.

<AccordionGroup>

<Accordion title="ما يكتبه optimize، مع تشغيله على هذا الموقع">

هذا تشغيل الموقع نفسه:

```text
docs 3.x en ................... 46 pages, 542 search records, 127 KB index
docs 3.x ar ...................... 2 pages, 17 search records, 6 KB index
docs 2.x en .................... 17 pages, 76 search records, 10 KB index

INFO LaRecipe docs compiled.
```

تُكتب قائمة ما جُمّع في `bootstrap/cache/larecipe/optimized.php`. ولا يجمع `larecipe:optimize` بيانات التحديث (provenance)؛ راجع [مصدر المستودع](../sources/repository.md#provenance-from-git).

<Artisan command="larecipe:optimize" />

</Accordion>

<Accordion title="ما يزيله clear، وما يبقيه">

يمحو `larecipe:clear` الصفحات المُصيَّرة وسجلات البحث المذكورة في `optimized.php`، ثم تلك القائمة نفسها. يحذف `bootstrap/cache/larecipe/optimized.php` ولا شيء غيره في ذلك المجلد، فيبقى بعد `optimize:clear` فهرس بيانات التحديث المجمَّع، ولقطة المراجع الحية، وأنماط الخطّافات المجمَّعة.

<Artisan command="larecipe:clear" />

</Accordion>

<Accordion title="بعد ترقية LaRecipe">

كل مفتاح يكتبه LaRecipe يبدأ برقم إصدار للصيغة (`larecipe:v2:`)، والصفحات المُصيَّرة تحمل أيضًا إصدار أداة التصيير. والإصدار الذي يغيّر ما يخزّنه LaRecipe، أو طريقة تصييره، يغيّر هذه الأرقام، فلا تُقرأ أبدًا المدخلات التي كتبها إصدار أقدم: بعد ترقية LaRecipe مباشرة، تبدأ الذاكرة المؤقتة فارغة وتُصيَّر الصفحات من جديد. شغّل `php artisan optimize` في عملية النشر تلك، كما تفعل أصلًا، فيحصل القرّاء على صفحات جاهزة من أول طلب. والمدخل الذي يأتي بشكل لا يتوقعه LaRecipe يُقرأ على أنه غير موجود، لا على أنه خطأ.

</Accordion>

<Accordion title="تتبّع عمليات التصيير بالحدث PageRendered">

يُطلق `PageRendered` كلما صُيّرت مقالة لأنها لم تكن في الذاكرة المؤقتة، ومع كل طلب لنسخة Markdown. راجع [مرجع الأحداث](../reference/events.md#pagerendered).

</Accordion>

</AccordionGroup>

## الاستجابات والذاكرات المؤقتة المشتركة

تذكر كل استجابة أيضًا كيف يجوز للمتصفح أو الخادم الوسيط أو شبكة CDN أن يحتفظ بها: الصفحة العامة المقدَّمة لزائر قابلة للتخزين المؤقت لدى الجميع، والاستجابة التي لا يحق إلا لقارئ واحد رؤيتها لا تصل أبدًا إلى ذاكرة مؤقتة مشتركة. وتجد في صفحة [الأمان](security.md#responses-and-shared-caches) الترويسة لكل حالة. وتُقدَّم ملفات واجهة LaRecipe نفسها بأسماء مشتقة من بصمة المحتوى وبترويسة ثابتة لمدة عام؛ وتبيّن صفحة [النشر](deployment.md) كيف تنشرها ليقدّمها خادم الويب لديك بدلًا من ذلك.

## التالي

- [النشر](deployment.md): `optimize` و `larecipe:provenance` و `larecipe:cache:warm` في سكربت النشر.
- [الأمان](security.md): ترويسات الاستجابة والوصول في كل الواجهات.
- [التحقق و doctor](validation.md): افحص التوثيق قبل أن يُشحن.
