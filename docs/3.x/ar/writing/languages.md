---
title: اللغات
description: قدّم توثيقك بأكثر من لغة، مع دعم الكتابة من اليمين إلى اليسار، وعرض الأصل للصفحات التي لم تُترجم بعد، وفحص يجد الترجمات التي تأخرت عن أصلها.
sources:
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Locale.php
  - vendor/larecipe/larecipe/src/Domains/Content/Enums/Direction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RequestResolver.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PageCatalog.php
  - vendor/larecipe/larecipe/src/Domains/Search/Services/SearchIndexRepository.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Services/LlmsTxtBuilder.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Services/ScopeParser.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/FindAffectedPagesAction.php
  - vendor/larecipe/larecipe/resources/boost/skills/larecipe-update-docs/references/translations.md
translated_from: sha256:072a72aceca091cf2c92610d9a186a83a8515a42286a83aeff8fa9c8b6e9effe
---

# اللغات

يمكن لمجموعة توثيق واحدة أن تقدّم التوثيق نفسه بعدة لغات. اسرد اللغات في ملف `larecipe.json` الخاص بالمجموعة:

```json title="docs/larecipe.json"
{
  "locales": [
    { "code": "en", "label": "English", "default": true },
    { "code": "ar", "label": "العربية", "dir": "rtl" }
  ]
}
```

ثم ضع كل ترجمة في مجلد يحمل اسم رمز لغتها، داخل الإصدار، وبالمسار نفسه الذي لأصلها:

<FileTree>

- docs/3.x/
  - index.md
  - get-started/
    - installation.md
  - ar/
    - index.md
    - get-started/
      - installation.md

</FileTree>

تبقى اللغة الافتراضية في جذر الإصدار، وفي المجموعة التي لا إصدارات لها تقع مجلدات اللغات مباشرة في `docs/`. والصفحة التي لم تترجمها بعد تفتح مع ذلك في كل لغة: يحصل القرّاء على الأصل، مع ملاحظة تقول ذلك.

يقدّم هذا الموقع الإنجليزية والعربية، وهو مترجم بالكامل: لكل صفحة نسخة عربية، بدءًا من [المقدمة العربية](/ar/3.x). والصفحة التي تُضاف لاحقًا تعرض الأصل الإنجليزي على رابطها العربي حتى تُضاف ترجمتها.

## الروابط {#urls}

تأتي اللغة أولًا، ثم الإصدار، ثم الصفحة: `/{prefix}/{locale}/{version}/{path}`. واللغة الافتراضية لا مقطع لها. وفي هذا الموقع، دليل التثبيت هو `/3.x/get-started/installation` بالإنجليزية و `/ar/3.x/get-started/installation` بالعربية. وتتبع نسخة Markdown و `llms.txt` وفهرس البحث الشكل نفسه.

## مفاتيح اللغة {#locale-keys}

| المفتاح | القيمة الافتراضية | المعنى |
| --- | --- | --- |
| `code` | مطلوب | رمز اللغة، المستخدم في المجلدات والروابط: `ar` و `fr` و `pt-BR`. |
| `label` | اسم اللغة بلغتها نفسها للرموز الشائعة | ما يعرضه مبدّل اللغات. |
| `dir` | يُستنتج من الرمز | `ltr` أو `rtl`. تحصل العربية والعبرية والفارسية والأردية وغيرها من اللغات التي تُكتب من اليمين إلى اليسار على `rtl` من دون أن تحدّد ذلك. |
| `default` | `false` | اللغة الأصلية. وحين لا تقول أي لغة `true`، تكون الأولى هي الافتراضية. |

ويمكن أن تكون اللغة نصًا مجرّدًا (`"locales": ["en", "ar"]`) حين تناسبها القيم الافتراضية. والمجموعة التي لها لغة واحدة، أو لا لغة، ليست متعددة اللغات، ولا تحمل روابطها لغة أبدًا.

## من اليمين إلى اليسار {#right-to-left}

يأتي اتجاه الصفحة من اللغة، وتستخدم أوراق الأنماط خصائص منطقية، فينعكس التخطيط من دون أي إعداد إضافي. اكتب الصفحة بالعربية واترك الكود والأوامر ووسوم المكوّنات كما هي. ويأتي خط Thmanyah Sans في قائمة الخطوط الافتراضية بعد Inter مباشرة، فيُكتب به النص العربي، بينما تحتفظ الكلمات اللاتينية والأرقام والكود بخطوطها اللاتينية.

## أبقِ الترجمات محدّثة {#keep-translations-current}

تتأخر الترجمة حين يتغيّر أصلها ولا تتغيّر هي. ويجد `larecipe:affected` تلك الصفحات:

```bash
php artisan larecipe:affected --uncommitted --base=main --locale=ar
```

يسرد الصفحات العربية التي تغيّر أصلها في فرعك بينما لم تتغيّر ترجمتها، والصفحات التي تغيّر أصلها بعد آخر تحديث لترجمتها. والترجمة التي لا `sources:` خاصة بها تستخدم مصادر أصلها، فتغيير الكود الذي يجعل الصفحة الإنجليزية قديمة يعلّم الصفحة العربية أيضًا.

وتحمل [مهارة](../keeping-current/skills.md) `larecipe-update-docs` سير عمل للترجمة خاصًا بوكلاء البرمجة: اقرأ الـ diff الخاص بالأصل، وترجم ما تغيّر فقط، واترك الكود والأوامر والمسارات ووسوم المكوّنات كما هي تمامًا، وأبقِ العناوين بالترتيب نفسه كي تتطابق المراسي، وقل ذلك صراحة بدلًا من التخمين حين لا يستطيع الوكيل أن يترجم جيدًا.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="كيف تُصيَّر الصفحة التي لم تُترجم بعد">

كل صفحة في الأصل موجودة في كل لغة. وحين تكون الترجمة مفقودة، تُصيّر الصفحة المقالة الأصلية داخل لغة القارئ: تحتفظ الصفحة بـ `lang="ar"` و `dir="rtl"`، ويظل الشريط الجانبي يربط بالصفحات العربية، وتبقى المقالة بالإنجليزية مع `lang` و `dir` الخاصين بها، وتظهر فوقها ملاحظة نصّها "هذه الصفحة لم تُترجم بعد، وأنت تقرأ النسخة الأصلية." وتعود نسخة Markdown إلى الأصل بالطريقة نفسها.

ويعتمد مبدّل اللغات على هذا: تبديل اللغة يُبقيك على الصفحة نفسها، مترجمة كانت أم لا. وتسرد كل صفحة أيضًا بدائل `hreflang` الخاصة بها، واحدًا لكل لغة كُتبت بها، إضافة إلى `x-default` للأصل. والصفحة المعروضة بالأصل ليست بديلًا، ورابطها الأساسي (canonical) هو الصفحة الأصلية.

</Accordion>

<Accordion title="التنقل والروابط في كل لغة">

يسرد الشريط الجانبي للغة كل صفحات الأصل، بالترتيب نفسه، مع العنوان المترجم حيثما وُجدت ترجمة. ويقرأ ملف `navigation.json` الخاص بالإصدار ما لم يكن لمجلد اللغة ملفه الخاص (`docs/3.x/ar/navigation.json`)، الذي يمكن أن يعطي المجموعات تسميات مترجمة أو ترتيبًا مختلفًا. وملف اللغة الخاص يسرد الصفحات نفسها التي يسردها ملف الإصدار: فمداخل الصفحات التي لم تُترجم بعد تعرض الأصل. ويعمل الشريط الجانبي العربي في هذا الموقع بهذه الطريقة، بتسميات مجموعات عربية.

اربط بين الصفحات بالطريقة المعتادة، بروابط `.md` نسبية، من الترجمة أيضًا: فالرابط إلى صفحة لا توجد إلا في الأصل يصل إلى نسختها الأصلية المعروضة بلغة القارئ. ويتبع `larecipe:validate` القاعدة نفسها التي يتبعها القارئ، فلا يبلّغ عن مدخل تنقل ولا عن رابط يشير إلى صفحة غير مترجمة.

</Accordion>

<Accordion title="البحث والوكلاء في كل لغة">

يعمل البحث و `llms.txt` وخادم MCP كلٌّ داخل لغة واحدة، ويغطّي الصفحات المكتوبة بها:

- فهرس البحث العربي مبني من الصفحات العربية، فيبحث القرّاء باللغة التي يقرؤون بها.
- يسرد `/ar/llms.txt` و `/ar/3.x/llms.txt` الصفحات العربية، وينتهي الفهرسان الجذري والخاص بالإصدار بقسم Languages يربط باللغات الأخرى.
- تأخذ أدوات MCP `search_docs` و `read_page` و `list_pages` الوسيط `locale`، وتستخدم اللغة الافتراضية من دونه.

</Accordion>

</AccordionGroup>

## التالي {#next}

- [الإصدارات](versions.md): اللغات تعيش داخل كل إصدار.
- [التنقل](navigation.md): ملف `navigation.json`، لكل إصدار ولكل لغة.
- [مرجع larecipe.json](../reference/manifest.md): كل مفتاح في ملف التعريف.
