---
title: كتل الصلاحيات والتوثيق الخاص
description: حدّد من يحق له قراءة مجموعة توثيق، أو إصدار، أو صفحة، أو فقرة واحدة، بنظام المصادقة والبوابات في تطبيقك نفسه، مطبّقًا بالطريقة نفسها على كل واجهة يقرؤها الناس والوكلاء.
sources:
  - vendor/larecipe/larecipe/src/Domains/Access/**
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/PageSummary.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ResponseHeaders.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/NotFoundResponder.php
  - vendor/larecipe/larecipe/src/Domains/Search/Laravel/Reader.php
  - vendor/larecipe/larecipe/src/Domains/Search/DTOs/SearchRecord.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/LaravelAgentDocs.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/ServerComposition.php
  - vendor/larecipe/larecipe/src/Domains/Ask/AskServiceProvider.php
  - vendor/larecipe/larecipe/config/larecipe.php
translated_from: sha256:93f79cc1d32a6dd557f624494f0bedee9eeb5c6a1078a00a49c067e773159d1e
---

# كتل الصلاحيات والتوثيق الخاص

ليس لدى LaRecipe مستخدمون ولا أدوار خاصة به. بل يسأل تطبيقك، عبر نظام المصادقة والبوابات (gates) في Laravel، على ثلاثة مستويات:

| لتجعل هذا خاصًا | اكتب |
| --- | --- |
| مجموعة توثيق كاملة | `access.mode` بالقيمة `auth` أو `gate` في مدخلها داخل `config/larecipe.php` |
| إصدارًا أو صفحة | `visibility: private`، على الإصدار في `larecipe.json` أو في ترويسة الصفحة |
| فقرة واحدة | `<Can ability="...">` حولها |

الإصدارات والصفحات الخاصة تسأل البوابة `viewLarecipe` لديك. عرّفها في مزوّد خدمة، كأي بوابة أخرى:

```php title="app/Providers/AppServiceProvider.php"
Gate::define('viewLarecipe', fn (?User $user) => $user !== null);
```

سياسة واحدة تجيب عن كل الواجهات: الصفحة أو الفقرة التي لا يحق للقارئ رؤيتها تغيب عن HTML، ونسخة Markdown، والبحث، و `llms.txt`، و MCP على حدّ سواء.

## من يحق له قراءة المجموعة {#who-may-read-the-set}

يحدّد `access.mode` في [config/larecipe.php](../get-started/configuration.md) باب الدخول إلى مجموعة توثيق كاملة:

```php title="config/larecipe.php"
'sets' => [
    'handbook' => [
        'route' => ['prefix' => 'handbook', 'middleware' => ['web', 'auth']],
        'source' => ['driver' => 'repository', 'path' => base_path('handbook')],
        'access' => ['mode' => 'gate'],
    ],
],
```

| الوضع | من يحق له القراءة | ما يحصل عليه الآخرون |
| --- | --- | --- |
| `public` | الجميع، بمن فيهم الزوّار. وهو الافتراضي. | لا يُردّ أحد |
| `auth` | كل من سجّل دخوله عبر الحارس (guard) الافتراضي لديك. | استجابة تطبيقك لغير المصادَق عليهم، وهي عادة إعادة توجيه إلى صفحة تسجيل الدخول |
| `gate` | القرّاء الذين تسمح لهم البوابة `viewLarecipe`، وتُسأل مرة للمجموعة ومرة أخرى لكل صفحة. | خطأ 404 |

في الوضع `gate` يحصل الزائر على 404 مثل أي أحد آخر ترفضه البوابة. ولترسل الزوّار إلى صفحة تسجيل الدخول أولًا، أضف `auth` إلى `route.middleware` الخاص بالمجموعة، كما في الأعلى.

## البوابات {#the-gates}

يسأل LaRecipe ثلاث بوابات:

| البوابة | الوسائط بعد المستخدم | متى تُسأل |
| --- | --- | --- |
| `viewLarecipe` | `?PageSummary $page` و `string $set` | في الوضع `gate`، للمجموعة (من دون صفحة) ولكل صفحة. وفي أي وضع، للإصدارات الخاصة (من دون صفحة) وللصفحات الخاصة. |
| `manageLarecipe` | `string $set` | قبل أن تفتح [لوحة الإدارة](../sources/database.md#the-admin-ui)، وقبل أن تعرض نقطة نهاية MCP على الويب الأداة `validate_docs`، وقبل أن تقبل أدوات التأليف عن بُعد في مجموعة قاعدة بيانات. |
| `askLarecipe` | `string $set` | قبل عرض لوحة Ask AI وقبل الإجابة عن سؤال، حين يكون Ask AI مفعّلًا. |

```php title="app/Providers/AppServiceProvider.php"
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use LaRecipe\Domains\Content\DTOs\PageSummary;

public function boot(): void
{
    Gate::define('viewLarecipe', function (?User $user, ?PageSummary $page, string $set) {
        if ($set === 'handbook') {
            return $user?->is_staff ?? false;
        }

        // Private pages in the public docs: any signed-in customer.
        return $user !== null;
    });

    Gate::define('manageLarecipe', fn (User $user, string $set) => $user->is_admin);

    Gate::define('askLarecipe', fn (?User $user, string $set) => $user !== null);
}
```

`$page` من النوع `PageSummary`، لا متن الصفحة: اقرأ `$page->ref->path` و `$page->ref->version` و `$page->ref->locale` و `$page->title` و `$page->private`. وقيمته `null` حين يسأل LaRecipe عن المجموعة أو عن إصدار خاص، فاجعل نوعه قابلًا للقيمة الفارغة.

> [!IMPORTANT]
> اجعل نوع المستخدم قابلًا للقيمة الفارغة (`?User $user`) حيثما ينبغي أن يمرّ الزوّار. فـ Laravel يرفض الزائر من دون تشغيل البوابة إن كان معاملها الأول لا يقبل `null`.

<Accordion title="حين لا تكون البوابة معرّفة">

| البوابة | من دون تعريف |
| --- | --- |
| `viewLarecipe` | يمرّ أي قارئ مسجّل الدخول في البيئة `local`. ويُرفض الجميع في أي مكان آخر. |
| `manageLarecipe` | مثل `viewLarecipe`. |
| `askLarecipe` | يحق للجميع السؤال، بمن فيهم الزوّار، ما دام Ask AI مفعّلًا. |

وهكذا تعمل الصفحة الخاصة مباشرة على جهازك لأي أحد سجّل دخوله، وتبقى مغلقة في الإنتاج حتى تعرّف `viewLarecipe`.

</Accordion>

## الإصدارات والصفحات الخاصة {#private-versions-and-pages}

تكون الصفحة خاصة حين تقول ترويستها ذلك، ويكون الإصدار خاصًا حين يقول مدخله في [larecipe.json](../reference/manifest.md) ذلك:

```yaml
---
title: Roadmap
description: What ships next quarter, for customers on the Enterprise plan.
visibility: private
---
```

```json
{
  "versions": [
    { "id": "4.x", "status": "current", "visibility": "private" },
    { "id": "3.x", "default": true }
  ]
}
```

كلاهما يحتاج إلى `viewLarecipe`، في كل أوضاع الوصول. والقارئ الذي ترفضه البوابة لا يعرف أبدًا أن أيًّا منهما موجود: فهما يُستبعدان من التنقل، ومن مبدّلَي الإصدارات واللغات، والبحث، و `llms.txt`، وخريطة الموقع، و MCP، والطلب المباشر يُجاب بـ 404.

<Accordion title="لماذا تكون الصفحة المخفية 404، لا 403 أبدًا">

يجيب LaRecipe عن الصفحة التي لا يحق للقارئ فتحها تمامًا كما يجيب عن صفحة غير موجودة: 404 حقيقي، في HTML وفي Markdown، مع اقتراحات مأخوذة فقط من الصفحات التي يستطيع القارئ رؤيتها. فالرمز 403 سيؤكد أن الصفحة موجودة. والاستثناء الوحيد هو باب الدخول نفسه: في الوضع `auth`، يحصل الزائر على استجابة تسجيل الدخول في تطبيقك للمجموعة كلها.

للإصدار الخاص تُسأل البوابة من دون صفحة، وللصفحة الخاصة تتلقى البوابة الصفحة. وتُقصّ المسافات من قيمة `visibility` وتُحوَّل إلى أحرف صغيرة، وأي قيمة غير `public` تُعدّ خاصة، فيُبقي الخطأ الإملائي الصفحة مغلقة.

</Accordion>

## كتل الصلاحيات: Can و Cannot {#gate-blocks-can-and-cannot}

أحيانًا يكون معظم الصفحة للجميع وفقرة واحدة ليست كذلك. أحِط ذلك الجزء بـ `<Can>` أو `<Cannot>` مع اسم بوابة يعرّفها تطبيقك:

```markdown
<Can ability="view-billing">
Enterprise plans can export invoices as CSV from **Billing**, then **Exports**.
</Can>

<Cannot ability="view-billing">
Ask your workspace owner for access to billing exports.
</Cannot>
```

```php title="app/Providers/AppServiceProvider.php"
Gate::define('view-billing', fn (?User $user) => $user?->plan === 'enterprise');
```

يُظهر `<Can>` محتواه للقرّاء الذين تسمح لهم الصلاحية (ability). ويُظهر `<Cannot>` محتواه لكل من عداهم، بمن فيهم الزوّار: وهو المكان المناسب لعبارة مثل "سجّل دخولك لترى المزيد" أو "اسأل المسؤول". والمحتوى Markdown عادي. ويحذف LaRecipe الكتل التي لا يحق للقارئ رؤيتها قبل أن يُصيَّر أي شيء، فلا شيء يمكن العثور عليه في مصدر الصفحة.

<Accordion title="قواعد كتل الصلاحيات">

- **تُفحص الصلاحية من دون أي وسائط أخرى**، كما يفعل `Gate::allows('view-billing')`. والسياسات (policies) التي تحتاج إلى نموذج لا تناسب هنا: استخدم بوابة تقرّر من المستخدم وحده.
- **الصلاحية التي لم تعرّفها قط ترفض الجميع.** يبقى `<Can>` مخفيًا ويظهر `<Cannot>`، فيُخفي الخطأ الإملائي المحتوى بدلًا من أن يسرّبه. ويحذّر منه `php artisan larecipe:validate` بالرمز `access.unknown-ability`، مع أقرب صلاحية يعرّفها تطبيقك.
- **الكتلة التي لا `ability` فيها مخفية عن كل القرّاء**، ويبلّغ `larecipe:validate` عن الخاصية (prop) الناقصة.
- **اكتب كل وسم في سطر مستقل**، بصيغة PascalCase، مع سطر فارغ قبله وبعده. ووسم كتلة الصلاحيات الذي لا يستطيع المحلّل قراءته (داخل جملة، أو جدول، أو عنوان، أو مقسومًا على عدة أسطر، أو بأحرف صغيرة مثل `<can>`) يفشل مغلقًا: تُخفى الكتلة المحيطة به، حتى وسم إغلاقها، عن كل القرّاء، ويبلّغ `larecipe:validate` عن خطأ `access.gate-syntax`.
- **كتلة الصلاحيات في صفحة خاصة** يُبلَّغ عنها بالرمز `access.redundant-gate`: فقرّاؤها قد مرّوا من `viewLarecipe` أصلًا، فتحقّق هل ما زالت الكتلة لازمة.
- **تُحذف، لا تُخفى.** لا شيء يُخفى بـ CSS. والعناوين داخل كتلة محذوفة تغادر جدول المحتويات أيضًا، والاقتباس الذي لم يكن يحوي إلا كتلة محذوفة يختفي معها.
- **تُخزَّن مؤقتًا لكل جمهور.** القرّاء الذين يملكون الصلاحيات نفسها في صفحة يتشاركون نسخة مُصيَّرة واحدة مخزّنة مؤقتًا، والقارئ الذي لا يملك أيًّا منها يحصل على نسخة الزائر.

</Accordion>

## كل الواجهات، القواعد نفسها {#every-surface-the-same-rules}

تتبع الصفحات الخاصة وكتل الصلاحيات القارئَ إلى البحث، ونسخة Markdown، و `llms.txt`، و MCP. أما الواجهات التي تسلّم الوكيل صفحات كاملة (`llms-full.txt`، وأداة MCP `read_page`، و Ask AI الذي يقرأ من خلالها) فتقدّم لكل مستدعٍ نسخة الزائر من كتل الصلاحيات، فقد تُظهر للوكيل أقل مما يُظهره HTML لقارئه، لا أكثر أبدًا.

<Accordion title="واجهة بعد واجهة">

| الواجهة | المجموعات والإصدارات والصفحات الخاصة | كتل الصلاحيات |
| --- | --- | --- |
| صفحة HTML | لا تظهر إلا للقرّاء الذين يحق لهم رؤيتها | تُحذف عن القرّاء الذين لا يحق لهم رؤيتها |
| نسخة Markdown (`.md`) | الشيء نفسه | العرض نفسه الذي في HTML، للقارئ نفسه |
| البحث | فهرس كل قارئ لا يحوي إلا الصفحات التي يحق له رؤيتها | تُقسم السجلات عند كتل الصلاحيات ولا تُحفظ إلا للقرّاء الذين تسمح لهم، في فهرس المتصفح ومرتِّب الخادم على السواء |
| `llms.txt` و `llms-full.txt` | تُبنى للمستدعي، فلا تسرد إلا ما يحق له رؤيته | يحوي `llms-full.txt` نسخة الزائر |
| `skill.md` و `sitemap.xml` | يُبنى `skill.md` للمستدعي، ولا تسرد خريطة الموقع إلا ما يحق للزائر رؤيته | لا يحوي أيٌّ منهما محتوى الصفحات |
| MCP | لا ترى `search_docs` و `read_page` و `list_pages` إلا ما يحق للمستدعي رؤيته | تتبع `search_docs` صلاحيات المستدعي، وتعيد `read_page` نسخة الزائر |
| Ask AI | يقرأ عبر أدوات MCP نفسها، بصفة القارئ | نسخة الزائر، عبر `read_page` |

لتسمح للوكلاء بقراءة مجموعة خاصة عبر MCP، ضع middleware للمصادقة أمام نقطة النهاية الخاصة بها عبر `mcp.web.middleware`، مثل `['auth:sanctum']`. ويُفحص الوصول من جديد عند كل استدعاء أداة. راجع [خادم MCP](../agents/mcp.md).

</Accordion>

## شارك صفحة خاصة واحدة {#share-one-private-page}

لتُظهر صفحة خاصة لشخص لا حساب له، مثل عميل أو متعاقد، أنشئ رابط مشاركة:

```bash
php artisan larecipe:share 3.x/billing/roadmap --expires=7d
```

يطبع الأمر رابط الصفحة مع رمز `share`. ويستطيع أي أحد يملك الرابط أن يقرأ تلك الصفحة وحدها حتى تنتهي صلاحيته، حتى حين تكون الصفحة، أو إصدارها، أو مجموعتها كلها خاصة. وتبقى كل صفحة أخرى مغلقة، وتظل كتل الصلاحيات تتبع القارئ.

<Accordion title="روابط المشاركة بالتفصيل">

- **الصفحة** تُكتب بالشكل `set/locale/version/path`، حيث المجموعة واللغة والإصدار اختيارية ويُتعرّف عليها بالاسم: `docs/ar/3.x/billing/roadmap`، أو `billing/roadmap` وحدها للمجموعة واللغة والإصدار الافتراضية.
- **مدة الصلاحية** هي `30m` أو `12h` أو `7d` أو `2w`، والقيمة الافتراضية `7d`.
- **الرابط يفتح الصفحة بصيغة HTML أو Markdown.** ولا يسرد الشريط الجانبي إلا ما كان القارئ يستطيع رؤيته أصلًا، والزائر الذي يحمل الرابط يحصل على نسخة الزائر من كتل الصلاحيات.
- **الإلغاء.** تُوقَّع الروابط بالمفتاح `APP_KEY` لديك، فتدوير المفتاح يلغي كل روابط المشاركة دفعة واحدة. ولا توجد طريقة لإلغاء رابط واحد قبل انتهاء صلاحيته.
- يوقّع الأمر أي مسار تعطيه إياه، فافتح الرابط مرة لتتأكد من أنه يصل إلى الصفحة التي قصدتها.
- رابط المشاركة يتجاوز فحوص LaRecipe، لا الـ middleware في مساراتك. ففي مجموعة يتضمن `route.middleware` فيها `auth`، يُرسَل الزائر إلى تسجيل الدخول قبل أن يُقرأ الرابط، لذا استخدم `access.mode` بالقيمة `auth` أو `gate` من دون ذلك الـ middleware في المجموعات التي تشارك منها.

مقروءة مباشرة من هذا التطبيق:

<Artisan command="larecipe:share" />

</Accordion>

## رؤوس الاستجابة {#response-headers}

الاستجابة التي لا يحق إلا لقارئ واحد رؤيتها (صفحة في مجموعة ليست `public`، أو صفحة أو إصدار خاص، أو صفحة فُتحت برابط مشاركة، أو صفحة منحت كتلُ الصلاحيات فيها القارئَ شيئًا لا يحصل عليه الزوّار) تخرج مع `Cache-Control: private, no-store` و `X-Robots-Tag: noindex`، فلا يخزّنها أي مخزن مؤقت مشترك ولا يفهرسها أي محرّك بحث. أما الصفحة العامة التي فيها كتل صلاحيات، حين تُقدَّم لزائر، فتبقى قابلة للتخزين المؤقت لكنها تتغيّر حسب `Accept` و `Cookie`. وتسرد صفحة [الأمان](../operate/security.md) رؤوس الاستجابة لكل حالة.

## التالي {#next}

- [أساسيات الإعداد](../get-started/configuration.md): `access.mode` وبقية إعدادات مجموعة التوثيق.
- [خادم MCP](../agents/mcp.md): مصادقة الوكلاء للمجموعات الخاصة.
- [الأمان](../operate/security.md): تعمّق في ما يفعله LaRecipe ليُبقي التوثيق الخاص خاصًا.
