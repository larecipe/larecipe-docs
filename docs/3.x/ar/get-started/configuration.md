---
title: أساسيات الإعداد
description: أين تعيش إعدادات LaRecipe، والقليل منها الذي تغيّره معظم التطبيقات (الرابط، ومن يحق له القراءة، وأين تُحفظ الصفحات)، وكيف تضيف مجموعة توثيق ثانية.
sources:
  - vendor/larecipe/larecipe/config/larecipe.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/SetConfig.php
  - vendor/larecipe/larecipe/src/Domains/Content/ContentServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Laravel/RouteRegistrar.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RequestResolver.php
  - vendor/larecipe/larecipe/src/Domains/Access/Services/PageAccess.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/LaRecipeServiceProvider.php
  - config/larecipe.php
translated_from: sha256:5e828779ded5e3f372af28bb2057c5d53d4e9487df15bd17ab896808048b1047
---

# أساسيات الإعداد

معظم التطبيقات لا تغيّر شيئًا في البداية: يعيش التوثيق في `docs/`، ويُقدَّم على `/docs`، ويحق لأي أحد قراءته. وحين تريد غير ذلك، انشر ملف الإعدادات:

```bash
php artisan vendor:publish --tag=larecipe-config
```

ثم عدّل مدخل مجموعة التوثيق في `config/larecipe.php`. وهذه هي الإعدادات الثلاثة التي تمسّها معظم التطبيقات:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        'route' => ['prefix' => 'docs', 'domain' => null, 'middleware' => ['web']],
        'source' => ['driver' => 'repository', 'path' => base_path('docs')],
        'access' => ['mode' => 'public'],
    ],
],
```

| الإعداد | ما الذي يحدّده | القيم |
| --- | --- | --- |
| `route.prefix` | الرابط الذي يعيش تحته التوثيق | `docs` افتراضيًا (`LARECIPE_PREFIX`). والقيمة `''` تضع التوثيق في جذر النطاق. |
| `source.driver` | أين تُحفظ الصفحات | `repository` (ملفات Markdown في مستودعك، وهو الموصى به) أو `database` |
| `access.mode` | من يحق له القراءة | `public` (الجميع)، أو `auth` (كل من سجّل دخوله)، أو `gate` (البوابة `viewLarecipe` لديك) |

يدمج Laravel ملفك فوق القيم الافتراضية للحزمة، مفتاحًا علويًا بعد آخر، فيحتفظ أي مفتاح لا تكتبه بقيمته الافتراضية.

## الإعدادات والمحتوى منفصلان {#settings-and-content-live-apart}

يحدّد `config/larecipe.php` كيف يقدّم تطبيقك التوثيق. أما ما يقوله التوثيق وشكله فينتقلان مع المحتوى، بجوار الصفحات:

| الملف | ما يحويه |
| --- | --- |
| `config/larecipe.php` | مجموعات التوثيق، والمسارات، والمصدر، والوصول، ووضع المراجعة، والتخزين المؤقت، والبحث، و MCP، وواجهات الوكلاء، والذكاء الاصطناعي |
| `docs/larecipe.json` | الاسم، والشعار، والمظهر، والإصدارات، واللغات، وشريط التنقل العلوي، وإعادات التوجيه، والتحليلات. راجع [مرجع ملف التعريف](../reference/manifest.md). |
| `navigation.json` | الشريط الجانبي لإصدار واحد. راجع [التنقل](../writing/navigation.md). |
| ترويسة الصفحة (frontmatter) | عنوان صفحة واحدة، ووصفها، ومصادرها، وما شابه. راجع [بنية المشروع](project-structure.md). |

وبهذا يستطيع وكيل البرمجة أن يعدّل التوثيق من دون أن يمسّ إعداداتك. والأسرار لا مكان لها في أيٍّ منهما: مفتاح المزوّد مكانه `.env`، ويقرؤه ملف الإعدادات.

## أضف مجموعة توثيق ثانية {#add-a-second-doc-set}

مجموعة التوثيق موقع توثيق واحد داخل تطبيقك، له رابطه ومحتواه وواجهات الوكلاء الخاصة به. أضف مجموعة أخرى حين تحتاج فئة قرّاء مختلفة إلى موقع مختلف، مثل أدلة تشغيل داخلية بجوار التوثيق العام:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        // ...
    ],

    'handbook' => [
        'route' => ['prefix' => 'handbook', 'middleware' => ['web', 'auth']],
        'source' => ['driver' => 'repository', 'path' => base_path('handbook')],
        'access' => ['mode' => 'gate'],
        'mcp' => ['web' => ['enabled' => false]],
    ],
],
```

يسمّي المفتاح `default` المجموعة التي تستخدمها أوامر artisan وخادم MCP المحلي حين لا تمرّر `--set` (وهي `docs` ما لم يحدّد `LARECIPE_DEFAULT_SET` غيرها). وتشرح صفحة [كتل الصلاحيات والتوثيق الخاص](../writing/gate-blocks.md) أوضاع الوصول والبوابات.

## كل ما عدا ذلك {#everything-else}

لكل قسم آخر في الملف صفحته:

| المفتاح | ما يتحكّم فيه | اقرأ |
| --- | --- | --- |
| `review` | هل يسأل وكيلك قبل أن يضيف تغييرات التوثيق إلى git | [أوضاع المراجعة](../keeping-current/review-modes.md) |
| `search` | مشغّل البحث وميزانية الفهرس | [البحث](../operate/search.md) |
| `mcp` | نقطة نهاية MCP للوكلاء والخادم المحلي | [خادم MCP](../agents/mcp.md) |
| `agents` | `llms.txt` و `llms-full.txt` و `skill.md` وخريطة الموقع | [واجهات الوكلاء](../agents/surfaces.md) |
| `ai` | Ask AI و `larecipe:sync`، بمفتاح المزوّد الخاص بك | [اسأل الذكاء الاصطناعي (Ask AI)](../agents/ask-ai.md) |
| `cache` | مخزن الذاكرة المؤقتة للصفحات المُصيَّرة | [النشر](../operate/deployment.md) |
| `hooks` و `instructions` | فحص pre-push وكتلة `AGENTS.md` | [خطّاف pre-push](../keeping-current/pre-push-hook.md) |

ويسرد [مرجع الإعدادات](../reference/configuration.md) كل مفتاح، وقيمته الافتراضية، ومتغير البيئة الخاص به.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="ضع التوثيق في جذر النطاق">

البادئة `''` تجعل المجموعة تملك جذر النطاق. وهكذا يعمل هذا الموقع: يفتح [larecipe.saleem.dev](https://larecipe.saleem.dev) على صفحة المقدمة، من دون صفحة رئيسية منفصلة. وهذا مدخله، من ملف `config/larecipe.php` في هذا التطبيق نفسه:

```php title="config/larecipe.php"
'sets' => [
    'docs' => [
        'route' => [
            'prefix' => '',   // larecipe.saleem.dev opens on the docs: the set owns the domain root
            'domain' => null,
            'middleware' => ['web'],
        ],
        'source' => [
            'driver' => 'repository',
            'path' => base_path('docs'),
        ],
        'access' => [
            'mode' => 'public',
        ],
    ],
],
```

- يُصيّر `/` الصفحة الرئيسية للإصدار الافتراضي مباشرة. ويُعاد توجيه الروابط الأخرى التي لا تحمل إصدارًا إلى الإصدار الافتراضي.
- مساراتك أنت لها الأولوية. يسجّل LaRecipe مساراته بعد مسارات تطبيقك، ويسجّل المجموعة المركّبة على الجذر بعد كل المجموعات الأخرى، فيظل `/newsletter` في هذا الموقع يصل إلى مسار التطبيق نفسه. ولا يمكن الوصول إلى صفحة توثيق يطابق مسارها أحد مساراتك، فأبقِ المسارات منفصلة.
- احذف مسار الترحيب الافتراضي `/` في Laravel من `routes/web.php`، وإلا فسيجيب هو بدلًا من توثيقك.
- لا يمسّ المسار الشامل `/larecipe-assets/` ولا `/.well-known/`.

</Accordion>

<Accordion title="المسارات والنطاقات والـ middleware">

يثبّت `route.domain` المجموعة على نطاق واحد: المجموعة التي فيها `'domain' => 'docs.example.com'` لا تجيب إلا على ذلك المضيف، وكذلك نقطة نهاية MCP الخاصة بها. أما `route.middleware` فهو مجموعة الـ middleware التي تحيط بكل مسار توثيق (`['web']`).

كل روابط التوثيق تتفرّع من البادئة: الصفحات، ونسخ Markdown المقابلة لها بامتداد `.md`، و `llms.txt`، و `skill.md`، و `sitemap.xml`، والملفات المرفقة، ونقطة نهاية MCP. وكلها تمرّ عبر مجموعة الـ middleware ما عدا نقطة نهاية MCP، كي يستطيع عملاء MCP الإرسال من دون جلسة ولا رمز CSRF.

</Accordion>

<Accordion title="المصدران">

- `repository` (الافتراضي والموصى به): ملفات Markdown في مستودعك، في `source.path` (`base_path('docs')`). تنتقل التغييرات في الـ pull requests ملفاتٍ عادية. راجع [مصدر المستودع](../sources/repository.md).
- `database`: الصفحات في قاعدة بياناتك، خلف ذاكرة مؤقتة دائمًا. ويقبل أيضًا `source.connection`، و `source.cache` (مخزن دائم)، و `source.disk` (للملفات المرفقة). والتغييرات التي تجري في pull request تُشحن [ترحيلاتِ توثيق](../sources/docs-migrations.md). راجع [مصدر قاعدة البيانات](../sources/database.md).

للمجموعة مشغّل واحد لكل إصداراتها ولغاتها، ويمكنك أن [تنتقل](../sources/switching.md) إلى الآخر لاحقًا.

</Accordion>

<Accordion title="غيّر قيمة افتراضية لمجموعة واحدة">

معظم المفاتيح خارج `sets` قيم افتراضية لكل المجموعات. وتتجاوز المجموعة أحدها بتكراره في مدخلها الخاص. تُدمج قيمها فوق القيم العلوية مفتاحًا بعد مفتاح، فلا تكتب إلا ما يختلف:

```php title="config/larecipe.php"
'sets' => [
    'handbook' => [
        // route, source, access...
        'review' => 'auto',
        'search' => ['driver' => 'server'],
        'ai' => ['ask' => ['enabled' => true]],
        'agents' => ['llms_full' => false],
    ],
],
```

ويبيّن [مرجع الإعدادات](../reference/configuration.md)، مفتاحًا مفتاحًا، أيّها يجوز للمجموعة أن تتجاوزه.

</Accordion>

</AccordionGroup>

## التالي {#next}

- [بنية المشروع](project-structure.md): ما الذي يعيش في مجلد التوثيق.
- [مرجع الإعدادات](../reference/configuration.md): كل مفتاح، مع نوعه وقيمته الافتراضية.
- [كتل الصلاحيات والتوثيق الخاص](../writing/gate-blocks.md): أوضاع الوصول والبوابات بالتفصيل.
