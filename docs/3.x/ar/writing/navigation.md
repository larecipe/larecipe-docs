---
title: التنقل
description: احصل على شريط جانبي من مجلداتك من دون أي إعداد، واكتب navigation.json حين تريد أن تختار الترتيب والمجموعات بنفسك، وأبقِ الروابط القديمة تعمل بإعادات التوجيه.
sources:
  - vendor/larecipe/larecipe/resources/schemas/navigation.schema.json
  - vendor/larecipe/larecipe/src/Domains/Content/Services/NavigationResolver.php
  - vendor/larecipe/larecipe/src/Domains/Content/Services/ZeroConfigNavigation.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/NavigationTree.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/NavigationTab.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/NavigationNode.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/NavigationRule.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/RedirectsRule.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/NavigationFreezeCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/FreezeNavigationAction.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/ui/src/regions/Sidebar.tsx
  - docs/3.x/navigation.json
translated_from: sha256:5dc53784a8b171892f01cd03374880cb74c3ba943be733d31e00edded008be02
---

# التنقل

لكل إصدار شريط جانبي. ومن دون أي إعداد، يبنيه LaRecipe من مجلداتك ومن ترويسة كل صفحة، فتظهر الصفحة الجديدة لحظة إضافة ملفها.

وحين تريد أن تختار الترتيب والمجموعات بنفسك، اكتب ملف `navigation.json` في مجلد الإصدار (`docs/3.x/navigation.json`):

```json
{
  "$schema": "https://larecipe.saleem.dev/schemas/3.x/navigation.json",
  "groups": [
    { "group": "Get started", "pages": ["index", "installation"] },
    { "group": "Billing", "pages": ["billing/invoices", "billing/refunds"] }
  ]
}
```

مداخل الصفحات مسارات من جذر الإصدار، من دون `.md`: فالمدخل `billing/refunds` هو `docs/3.x/billing/refunds.md`، و `index` هو الصفحة الرئيسية. وتظهر الصفحات بالترتيب الذي تسردها به، بالعناوين المأخوذة من ترويساتها. ويتيح السطر `$schema` لمحرّرك ولوكيلك التحقق من الملف أثناء الكتابة.

والشجرة نفسها تحدّد أيضًا رابطي الصفحة السابقة والتالية أسفل كل صفحة، وأقسام `llms.txt`، والمخطط في `skill.md`.

## التنقل من مجلداتك {#navigation-from-your-folders}

الإصدار الذي لا ملف `navigation.json` فيه يأخذ تنقله من ملفاته:

- تأتي صفحة `index.md` الخاصة بالإصدار أولًا، ثم الصفحات الأخرى في جذر الإصدار، ثم مجموعة لكل مجلد.
- تُسمّى مجموعة المجلد بعنوان صفحته الخاصة (`guides/index.md`، أو `guides.md` بجوار المجلد). ومن دونها يُستخدم اسم المجلد: فيصبح `getting-started` هو "Getting started".
- تُرتّب الصفحات حسب `order` في ترويستها، الأصغر أولًا، ثم حسب العنوان. وتُرتّب المجلدات بالطريقة نفسها، حسب صفحتها الخاصة.
- المجلدات داخل المجلدات تصبح مجموعات متداخلة، والصفحات التي فيها `hidden: true` تُستبعد.

<Accordion title="مثال، وقواعد الترتيب الأدق">

هذا المجلد:

<FileTree>

- docs/3.x/
  - index.md
  - configuration.md
  - installation.md
  - billing/
    - index.md
    - refunds.md
    - invoices.md

</FileTree>

مع `order: 1` في `installation.md` و `order: 2` في `configuration.md` يعطي هذا الشريط الجانبي: المقدمة، ثم Installation، ثم Configuration، ثم مجموعة تحمل عنوان `billing/index.md`، تبدأ بتلك الصفحة ثم Invoices و Refunds، مرتّبتين حسب العنوان.

- صفحة المجلد الخاصة تتصدّر مجموعته، وقيمتا `icon` و `order` فيها تمثّلان المجلد كله.
- الصفحات التي لا `order` لها تأتي بعد الصفحات التي لها.
- البادئة الرقمية في اسم المجلد، مثل `01-`، تُحذف من تسميته.
- إخفاء صفحة المجلد الخاصة يخفي المجلد كله.

</Accordion>

وحين يضيق بك هذا، يكتب `php artisan larecipe:navigation:freeze` الشجرة التي يستنتجها LaRecipe اليوم في ملف `navigation.json`، فتعدّل منها بدلًا من أن تبدأ من الصفر.

## المداخل في navigation.json {#entries-in-navigationjson}

كل قائمة في الملف تحوي الأنواع الأربعة نفسها من المداخل:

| المدخل | الشكل | ما يظهر |
| --- | --- | --- |
| صفحة | `"billing/refunds"` | الصفحة، بعنوانها الخاص |
| صفحة بتسمية خاصة | `{ "page": "index", "label": "Introduction" }` | الصفحة، بالتسمية `label` |
| مجموعة | `{ "group": "Billing", "collapsed": true, "pages": [...] }` | مجموعة لها تسمية، ويمكن أن تحوي `pages` فيها أي مدخل، بما في ذلك المجموعات |
| رابط | `{ "label": "Changelog", "href": "https://example.com/changelog" }` | رابط إلى أي مكان، موسوم بأنه خارجي |

لا تغيّر `label` الخاصة بالصفحة إلا مدخلها في الشريط الجانبي، وتحتفظ الصفحة بعنوانها الخاص في كل مكان آخر. ويجب أن تكون `href` الخاصة بالرابط رابطًا بالمخطط `http` أو `https` أو `mailto`، أو مسارًا في موقعك.

المجموعات العلوية مفتوحة دائمًا، ما لم تضف `"collapsed": true`. والمجموعات المتداخلة قابلة للطيّ، وتبدأ مفتوحة ما لم تقل `"collapsed": true`. والمجموعة المطويّة تنفتح مع ذلك حين تحوي الصفحة التي تقرؤها.

<AccordionGroup>

<Accordion title="ملف navigation.json الخاص بهذا الموقع">

<<< @/docs/3.x/navigation.json

</Accordion>

<Accordion title="التبويبات والقوائم المجرّدة والأشكال الأخرى">

يمكن أن يأخذ الملف ثلاثة أشكال. استخدم أصغر شكل يفي بالغرض.

**المجموعات**، الحالة الشائعة والشكل الذي يستخدمه هذا الموقع: قائمة واحدة من المجموعات والصفحات، كما في الأعلى.

**التبويبات**: عدة أقسام لها تسميات، ولكل منها مجموعاته. ويعرض الشريط الجانبي تسمية كل تبويب فوق مجموعاته.

```json
{
  "tabs": [
    {
      "label": "Guides",
      "groups": [
        { "group": "Get started", "pages": ["index", "installation"] }
      ]
    },
    {
      "label": "API",
      "groups": [
        { "group": "Resources", "pages": ["api/orders", "api/customers"] }
      ]
    }
  ]
}
```

**القائمة المجرّدة**، لمجموعة توثيق صغيرة: صفحات ومجموعات من دون أي شيء حولها.

```json
["index", "installation", "configuration", "faq"]
```

وداخل `{ "groups": [...] }` أو داخل تبويب، يمكنك أيضًا أن تكتب `"pages": [...]` لمداخل تقع خارج أي مجموعة. وحين يوجد الاثنان، تأتي مداخل `groups` أولًا.

ويقبل المخطط (schema) أيضًا تبويبًا فيه `href` بدلًا من المجموعات، رابطًا مكان القسم. لكن واجهة القراءة الحالية تعرض تسمية هذا التبويب من دون الرابط، فاستخدم مدخل رابط داخل مجموعة في الوقت الحالي.

</Accordion>

<Accordion title="المجموعات المتداخلة والمطويّة">

```json
{
  "groups": [
    {
      "group": "Billing",
      "pages": [
        "billing",
        "billing/invoices",
        { "group": "Webhooks", "collapsed": true, "pages": ["billing/webhooks/events", "billing/webhooks/retries"] }
      ]
    }
  ]
}
```

يمكن تسمية المجموعة بالمفتاح `label` بدلًا من `group`.

</Accordion>

<Accordion title="الأيقونات والشارات وما يراه كل قارئ">

يأخذ `icon` اسم أيقونة من [Lucide](https://lucide.dev/icons/) في الصفحات والمجموعات والروابط والتبويبات. ويمرّره LaRecipe إلى واجهة القراءة، لكن الشريط الجانبي الافتراضي لا يرسم أيقونات التنقل بعد.

يظهر `badge` من ترويسة الصفحة بجوار مدخلها. وتحمل الصفحات التي تغيّرت في اليوم الأخير نقطة صغيرة إلى أن يفتحها القارئ؛ وتضبط هذه المدة بالمفتاح `ui.fresh_days` في [الإعدادات](../reference/configuration.md#ui).

التنقل ملف واحد للجميع، لكن كل قارئ لا يرى إلا ما يحق له قراءته. فالصفحات الخاصة تُستبعد عن القرّاء الذين لا يستطيعون فتحها، والمجموعة التي لا يبقى فيها ما يُعرض تختفي. وتُستبعد أيضًا مداخل الصفحات غير الموجودة، ويبلّغ عنها `larecipe:validate`.

</Accordion>

</AccordionGroup>

## الصفحات المخفية {#hidden-pages}

تجعل `hidden: true` في ترويسة الصفحة الصفحةَ قابلة للوصول لكن غير مدرجة: لها رابط ونسخة Markdown، لكنها تبقى خارج التنقل المستنتج، والبحث، وخريطة الموقع، وفهرس التوثيق في `AGENTS.md`. استخدمها للصفحات التي تربط بها لكنك لا تريدها في الشريط الجانبي. والصفحة المخفية ليست خاصة: يستطيع أي أحد يملك رابطها أن يقرأها. أما الصفحات التي لا يراها إلا بعض القرّاء، فاستخدم لها `visibility: private` ([كتل الصلاحيات والتوثيق الخاص](gate-blocks.md)).

ومع وجود `navigation.json`، يقرّر الملف ما يُدرج. والصفحة التي تتركها خارجه تحصل على تحذير `pages.orphan` من `larecipe:validate`، لأن القرّاء لا يصلون إليها إلا بالرابط. اضبط فيها `hidden: true` لتقول إن ذلك مقصود.

## إعادات التوجيه {#redirects}

الصفحات تنتقل. وميزتان تُبقيان الروابط القديمة تعمل، وكلتاهما لا تعيد التوجيه إلا حين لا توجد صفحة على الرابط القديم.

اسرد المسارات القديمة للصفحة في ترويستها هي، فتنتقل إعادة التوجيه مع الصفحة:

```yaml
---
title: Webhooks
redirect_from:
  - hooks
  - guides/hooks
---
```

أما الروابط التي لم تعد تملكها أي صفحة، فاسردها في ملف `larecipe.json` الخاص بالمجموعة:

```json
{
  "redirects": [
    { "from": "/docs/3.x/setup", "to": "/docs/3.x/installation" }
  ]
}
```

<AccordionGroup>

<Accordion title="كيف يطابق redirect_from">

اكتب كل مسار قديم على شكل مسار الصفحة داخل الإصدار، كما تكتبه في `navigation.json`. فالطلب إلى المسار القديم يُعاد توجيهه بالرمز `301` إلى هذه الصفحة، في الإصدار واللغة نفسيهما، والطلب إلى نسخة Markdown القديمة (`hooks.md`) يُعاد توجيهه إلى النسخة الجديدة. ويطابق أيضًا مسار الرابط القديم كاملًا، مثل `/docs/3.x/hooks`.

يبحث LaRecipe عن `redirect_from` بين صفحات الإصدار واللغة المطلوبين، ولا يبحث إلا بين الصفحات التي يحق للقارئ رؤيتها، فلا يكشف رابط قديم أبدًا عن صفحة خاصة. والصفحة المنسوخة إلى إصدار جديد تحتفظ بـ `redirect_from` الخاص بها، أما الترجمة فتحتاج إلى `redirect_from` خاص بها لتعيد توجيه رابطها القديم.

</Accordion>

<Accordion title="كيف تطابق إعادات التوجيه في larecipe.json">

`from` هو المسار الكامل للرابط القديم: بادئة المجموعة، ثم الإصدار، ثم الصفحة (مع مقطع اللغة في الترجمة). و `to` أي مسار أو رابط، وقيمة `status` الافتراضية `301` (وتعمل `302` و `307` و `308` أيضًا).

في مجموعة لها إصدارات، ينطبق `from` الذي لا يحمل إصدارًا على كل إصدار. فالمدخل `{ "from": "/docs/setup", "to": "/docs/installation" }` يرسل `/docs/3.x/setup` إلى `/docs/3.x/installation` و `/docs/2.x/setup` إلى `/docs/2.x/installation`: فالوجهة `to` داخل المجموعة التي لا تسمّي إصدارًا تبقى في إصدار القارئ. واكتب الإصدار في `from` لتعيد توجيه إصدار واحد فقط.

يبلّغ `larecipe:validate` عن إعادات التوجيه التي تدور في حلقة (`redirects.loop`)، وعن إعادات التوجيه من رابط لا تزال صفحة تعيش عليه (`redirects.shadowed`). ولإعادة توجيه روابط خارج التوثيق، أو بادئة قديمة كاملة، استخدم مسارات تطبيقك. وتبيّن صفحة [الترقية من الإصدار 2](../upgrade.md) كيف يُبقي هذا الموقع روابط LaRecipe 2 تعمل.

</Accordion>

</AccordionGroup>

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="التنقل في كل لغة">

تستخدم كل لغة تنقل الإصدار، مع العنوان المترجم لكل صفحة لها ترجمة. ولتغيير الترتيب أو ترجمة تسميات المجموعات، أعطِ اللغة ملفها الخاص بجوار صفحاتها، مثل `docs/3.x/ar/navigation.json`. يبحث LaRecipe هناك أولًا، ثم يعود إلى ملف الإصدار. راجع [اللغات](languages.md).

</Accordion>

<Accordion title="جمّد التنقل المستنتج">

يكتب `larecipe:navigation:freeze` ملف الإصدار الافتراضي (`--docs-version=2.x` لإصدار آخر، و `--locale=ar` لملف خاص بلغة، و `--set=` لمجموعة توثيق أخرى)، مع مؤشّر `$schema` والمجموعات بالترتيب الذي يراها به القرّاء الآن. والإصدار الذي فيه `navigation.json` أصلًا يُترك كما هو. ويعمل على مجموعات المستودع، أما مجموعة قاعدة البيانات فتغيّر تنقلها بأداة MCP `update_navigation`. مقروءة مباشرة من هذا التطبيق:

<Artisan command="larecipe:navigation:freeze" />

</Accordion>

<Accordion title="ما الذي يتحقق منه larecipe:validate">

| القاعدة | المستوى | متى |
| --- | --- | --- |
| `navigation.schema` | خطأ | `navigation.json` ليس JSON صالحًا، أو ليس شكلًا يسمح به المخطط |
| `navigation.missing-page` | خطأ | مدخل يسمّي صفحة غير موجودة، مع أقرب مسار إصلاحًا |
| `pages.orphan` | تحذير | للإصدار ملف `navigation.json`، وصفحة ليست فيه وليست `hidden` |
| `links.unsafe` | خطأ | مدخل رابط ليست `href` فيه `http` أو `https` أو `mailto` أو مسارًا في موقعك |

التنقل المستنتج يسرد كل صفحة بطبيعة بنائه، فلا صفحات يتيمة فيه أبدًا.

</Accordion>

</AccordionGroup>

## التالي {#next}

- [الإصدارات](versions.md): ملف `navigation.json` لكل إصدار.
- [اللغات](languages.md): التنقل لكل لغة.
- [مرجع ترويسة الصفحة](../reference/frontmatter.md): `order` و `icon` و `badge` و `hidden` و `redirect_from`.
