---
title: الإصدارات
description: قدّم عدة إصدارات من توثيقك جنبًا إلى جنب، كلٌّ في مجلده، وابدأ الإصدار التالي بنسخ الحالي بأمر واحد.
sources:
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Version.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/src/Domains/Content/Enums/VersionStatus.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/MakeVersionCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/MakeVersionAction.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RequestResolver.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/DocsUrls.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Services/LlmsTxtBuilder.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
translated_from: sha256:374fc06663333536a3e4d673fbc9743c380fa4e0a0e048fb9a954ec8c3ed06b4
---

# الإصدارات

يمكن لمجموعة التوثيق أن تقدّم توثيق الإصدار الذي يستخدمه الناس اليوم بجوار توثيق الإصدار الذي سبقه. اسرد الإصدارات في ملف `larecipe.json` الخاص بالمجموعة، وأعطِ كل إصدار مجلدًا يحمل اسم معرّفه:

```json title="docs/larecipe.json"
{
  "versions": [
    { "id": "3.x", "default": true },
    { "id": "2.x", "status": "maintained" }
  ]
}
```

<FileTree>

- docs/
  - larecipe.json
  - 3.x/
    - navigation.json
    - index.md
  - 2.x/
    - navigation.json
    - index.md

</FileTree>

لكل إصدار روابطه (`/docs/3.x/installation`)، وتنقله، وفهرس بحثه، وملف `llms.txt` الخاص به، ويتنقّل القرّاء بينها من الشريط العلوي. والمجموعة التي لا إصدارات لها تعمل أيضًا: تبقى صفحاتها مباشرة في `docs/` حتى تضيف أول إصدار.

وهذا الموقع مثال على ذلك: يقدّم `3.x`، التوثيق الحالي، و `2.x`، توثيق LaRecipe 2 الذي حوّلته [ترقية الإصدار 2](../upgrade.md).

## أضف إصدارًا {#add-a-version}

انسخ التوثيق الحالي إلى إصدار جديد، واجعله الافتراضي:

```bash
php artisan larecipe:make:version 4.x --default
```

ينسخ الأمر مجلد الإصدار الافتراضي ويضيف الإصدار الجديد إلى `larecipe.json`. مرّر `--from=3.x` لتنسخ إصدارًا آخر، و `--dry-run` لترى الملفات التي سيكتبها. ثم عدّل الصفحات التي تغيّرت، وتحقّق من ذلك الإصدار وحده:

```bash
php artisan larecipe:validate --docs-version=4.x
```

وحين لا تكون للمجموعة إصدارات بعد، سمِّ الإصدار الذي تنتمي إليه صفحاتها الحالية بالخيار `--current-as`. ينقلها الأمر إلى ذلك المجلد قبل أن ينسخ:

```bash
php artisan larecipe:make:version 2.x --current-as=1.x --default
```

يبقى `larecipe.json` و `theme.css` ومجلد `skills/` في الجذر: فهي تخص المجموعة لا إصدارًا بعينه. ويعمل الأمر على مجموعات المستودع، أما مجموعة قاعدة البيانات فتضيف الإصدارات عبر ملف التعريف الخاص بها، بأداة MCP `update_manifest`.

## مفاتيح الإصدار {#version-keys}

| المفتاح | القيمة الافتراضية | المعنى |
| --- | --- | --- |
| `id` | مطلوب | اسم المجلد ومقطع الرابط، مثل `3.x`. |
| `label` | قيمة `id` | ما يعرضه مبدّل الإصدارات. |
| `status` | `current` | `current` أو `maintained` أو `deprecated` أو `archived`. ويصف `llms.txt` كل إصدار بها، فيعرف الوكلاء أيّها يفضّلون. |
| `default` | `false` | الإصدار الذي يصل إليه القرّاء. وحين لا يقول أي إصدار `true`، يكون الأول هو الافتراضي. |
| `visibility` | `public` | الإصدارات `private` تحتاج إلى البوابة (gate) `viewLarecipe` في كل واجهة. راجع [كتل الصلاحيات والتوثيق الخاص](gate-blocks.md). |

ويمكن أن يكون الإصدار نصًا مجرّدًا (`"versions": ["3.x", "2.x"]`) حين لا يحتاج إلى أكثر من معرّف.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="الروابط، والروابط القديمة، والمبدّل">

تعيش الصفحة ذات الإصدار على `/{prefix}/{version}/{path}`. وفي هذا الموقع، المجموعة مركّبة على جذر النطاق، فصفحة التثبيت هي `/3.x/get-started/installation`، ونسخة Markdown الخاصة بها هي الرابط نفسه مع `.md`.

الرابط الذي لا يحمل إصدارًا يُعاد توجيهه إلى تلك الصفحة في الإصدار الافتراضي، فلا تنكسر أبدًا الروابط المكتوبة قبل أن يوجد إصدارك الثاني. والاستثناء الوحيد هو جذر مجموعة مركّبة على جذر النطاق: يُصيّر `/` الصفحة الرئيسية للإصدار الافتراضي مباشرة، وهكذا يفتح هذا الموقع على المقدمة.

و[إعادة التوجيه](navigation.md#redirects) التي لا يحمل `from` فيها إصدارًا تنطبق على كل إصدار، و `to` الذي لا يحمل إصدارًا يُبقي القارئ في إصداره. اكتب الإصدار، كما في `/3.x/setup`، لتعيد توجيه إصدار واحد فقط.

الرابط الأساسي (canonical) لكل صفحة هو رابطها هي في إصدارها هي: فصفحة 2.x رابطها الأساسي عنوانها في 2.x.

يسرد المبدّل في الشريط العلوي كل الإصدارات بتسمية `label` الخاصة بكل منها. واختيار أحدها يُبقيك على الصفحة نفسها حين تكون موجودة في الإصدار الآخر، ويأخذك إلى الصفحة الرئيسية لذلك الإصدار حين لا تكون موجودة.

</Accordion>

<Accordion title="ما الذي يحصل عليه الوكلاء لكل إصدار">

- يسرد `/llms.txt` الإصدارات مع حالاتها، ثم صفحات الإصدار الافتراضي، فيصل الوكيل إلى التوثيق الحالي في طلب واحد.
- يسرد `/{version}/llms.txt` صفحات ذلك الإصدار، ويحوي `/{version}/llms-full.txt` نصّها الكامل.
- تأخذ أدوات MCP `search_docs` و `read_page` و `list_pages` الوسيط `version`، وتستخدم الإصدار الافتراضي من دونه.

وفي هذا الموقع، تجدها في [الفهرس الجذري](/llms.txt)، و[فهرس 3.x](/3.x/llms.txt)، و[فهرس 2.x](/2.x/llms.txt).

</Accordion>

<Accordion title="كل خيارات larecipe:make:version">

يقرأ `larecipe:make:version` كلا شكلي قائمة `versions`، ويكتبها بالطريقة التي كتبتها بها. مقروءة مباشرة من هذا التطبيق:

<Artisan command="larecipe:make:version" />

</Accordion>

</AccordionGroup>

## التالي {#next}

- [اللغات](languages.md): الصفحة نفسها بأكثر من لغة، داخل كل إصدار.
- [التنقل](navigation.md): ملف `navigation.json` لكل إصدار.
- [مرجع larecipe.json](../reference/manifest.md): كل مفتاح في ملف التعريف.
