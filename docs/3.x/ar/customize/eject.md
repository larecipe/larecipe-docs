---
title: الإخراج (eject)
description: إخراج واجهة القراءة مخطط له، ولم يُطرح بعد. ما الذي سيفعله، والأدوات التي تغطي اليوم معظم الحاجات نفسها.
sources:
  - vendor/larecipe/larecipe/packages/ui/src/slots.tsx
  - vendor/larecipe/larecipe/packages/ui/src/registry.ts
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ThemeCss.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/DocsStylesheets.php
  - vendor/larecipe/larecipe/src/LaRecipe.php
translated_from: sha256:561e06d9aa41eab55b8d77582ea697540787151a5126cc74a47a518ae0bc859f
---

# الإخراج (eject)

<Badge tone="neutral">قريبًا</Badge>

سينسخ الإخراج كود واجهة LaRecipe إلى تطبيقك فتملكه بالكامل. وهو مخطط له، وليس في هذا الإصدار: فلا يوجد بعد أمر `larecipe:ui:eject` ولا `larecipe:ui:diff`. أما الأدوات أدناه فتعمل اليوم، وكلٌّ منها يُبقيك على تحديثات LaRecipe.

## ما تستخدمه اليوم {#what-to-use-today}

| تريد أن | استخدم |
| --- | --- |
| تغيّر اللون المميِّز أو الخطوط أو الزوايا أو وضع الألوان الافتراضي | [قيم المظهر](theme.md) في `larecipe.json` |
| تعيد تنسيق أي شيء لا تغطيه القيم | ملف `theme.css` مذكورًا تحت `assets.css` في `larecipe.json`، أو `LaRecipe::head()` (راجع [المظهر](theme.md#add-your-own-stylesheet)) |
| تضيف نموذجًا أو شريط إعلان أو شارة في كل صفحة | [نقطة إدراج](insert-points.md) |
| تغيّر منطقة أو تغلّفها، مثل التذييل أو الشريط الجانبي | [استبدال فتحة](insert-points.md#replace-a-built-in-region) مع `useSlotDefault()` |
| تستخدم مكوّنك الخاص داخل صفحة | [مكوّن React مخصّص](custom-components.md) |

استبدال الفتحة هو أقرب شيء اليوم إلى إخراج مكوّن واحد. فهو يستبدل منطقة واحدة باسمها، ويتيح لك `useSlotDefault()` أن تُبقي الأصلية داخل نسختك بدلًا من نسخ كودها:

```tsx title="resources/larecipe/extension.tsx"
import { defineExtension, useSlotDefault } from '@larecipe/ui'

function PageFooter() {
  const Default = useSlotDefault('PageFooter')

  return (
    <>
      <Default />
      <p className="acme-page-help">Something missing? Ask in the community forum.</p>
    </>
  )
}

export default defineExtension({ slots: { PageFooter } })
```

## ما المخطط أن يفعله الإخراج {#what-eject-is-planned-to-do}

حجمان، فلا تتولى ملكية أكثر مما تحتاج إليه:

- **مكوّن واحد.** سينسخ `larecipe:ui:eject Sidebar` كود ذلك المكوّن وحده إلى امتدادك على شكل استبدال فتحة. فتملك الشريط الجانبي، ويبقى كل ما عداه يتحدّث مع LaRecipe.
- **الواجهة كلها.** سينسخ `larecipe:ui:eject` من دون وسيط كود الواجهة كاملًا إلى `resources/larecipe-ui/` ويربطه ببناء Vite لديك. ومن تلك اللحظة، تصبح ترقيات الواجهة عملًا تجريه بيدك.

وفي الحالتين، سيعرض `larecipe:ui:diff` ما تغيّر في المصدر الأصلي منذ الإخراج، فتنقل الإصلاحات إلى نسختك.

## التالي {#next}

- [نقاط الإدراج والفتحات](insert-points.md): كل نقاط الإدراج وكل أسماء الفتحات.
- [مكوّنات React المخصّصة](custom-components.md): الامتداد الذي تعيش فيه هذه الاستبدالات.
