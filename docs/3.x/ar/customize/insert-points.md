---
title: نقاط الإدراج والفتحات
description: أضف مكوّنًا إلى كل صفحة في أحد سبعة مواضع مسمّاة، واستثنِ صفحة منها، واستبدل منطقة مدمجة مثل التذييل مع الإبقاء على الأصلية داخلها.
sources:
  - vendor/larecipe/larecipe/resources/schemas/larecipe.schema.json
  - vendor/larecipe/larecipe/resources/views/page.blade.php
  - vendor/larecipe/larecipe/resources/views/prerender/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/InsertRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Frontmatter.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/ui/src/app/Insert.tsx
  - vendor/larecipe/larecipe/ui/src/app/App.tsx
  - vendor/larecipe/larecipe/ui/src/regions/**
  - vendor/larecipe/larecipe/packages/ui/src/slots.tsx
  - vendor/larecipe/larecipe/packages/ui/src/registry.ts
  - vendor/larecipe/larecipe/packages/ui/src/types.ts
  - docs/larecipe.json
  - docs/3.x/index.md
translated_from: sha256:bc76358d677f6c4a268deedccbfe2c1ed407b2abff144ea0a24a71d5eecc9a4f
---

# نقاط الإدراج والفتحات

**نقطة الإدراج** تضيف أحد مكوّناتك في موضع مسمّى في كل صفحة. ويضع هذا الموقع نموذج نشرته البريدية بعد كل مقال بسطر واحد في `larecipe.json`:

```json title="docs/larecipe.json"
{
  "inserts": { "article.after": ["Subscribe"] }
}
```

تحمل كل نقطة قائمة بأسماء مكوّنات وتُصيّرها بالترتيب:

| نقطة الإدراج | أين تُصيَّر |
| --- | --- |
| `header.actions` | في الشريط العلوي، قبل روابط التنقل وزر السؤال. اجعلها صغيرة. |
| `sidebar.before` | في أعلى الشريط الجانبي، فوق التنقل. |
| `sidebar.after` | في الشريط الجانبي، تحت التنقل. |
| `article.before` | بين رأس الصفحة (العنوان، والوصف، وسطر «حُدّث») والمقال. |
| `article.after` | بعد المقال مباشرة، قبل تذييل الصفحة (الملاحظات، والسابق والتالي، ورابط التحرير). |
| `toc.after` | في العمود الجانبي للصفحة، تحت جدول المحتويات وإجراءات الصفحة وآخر تغيير. |
| `footer` | في تذييل الموقع، قبل روابطه. |

المكوّن الموضوع هنا يُصيَّر على الخادم أولًا، فيحصل القرّاء بلا JavaScript ومحركات الزحف على بديله، ثم يتولاه React في مكانه. ولا يحصل الوكلاء على شيء من نقاط الإدراج: فهي جزء من إطار الصفحة، مثل التنقل، لذا تبقى خارج نسخة Markdown و `llms.txt` و MCP.

يجب أن يكون المكوّن واحدًا سجّلته أنت. وإن لم تكتب واحدًا بعد، فابدأ بصفحة [مكوّنات React المخصّصة](custom-components.md).

> [!NOTE]
> لا تستطيع نقطة الإدراج أن تمرّر خصائص (props)، فيحصل المكوّن على خصائصه الافتراضية. أعطِ كل خاصية قيمة افتراضية في تعريفه في PHP، كما يفعل `Subscribe` بـ `'list' => ['type' => 'string', 'default' => 'larecipe']`.

## استثنِ صفحة {#opt-a-page-out}

يمكن للصفحة أن توقف نقطة واحدة، أو كل النقاط، في ترويستها:

```yaml
inserts: { article.after: false }
```

```yaml
inserts: false
```

تفعل [المقدمة](../index.md) الأولى. فهي تعرض نموذج النشرة البريدية داخل نصها أصلًا، فلا تحصل على نسخة ثانية. ويُطبَّق الاستثناء على الخادم وفي المتصفح، وأثناء التنقل داخل المتصفح أيضًا، وعلى المكوّنات التي وضعها الامتداد.

## استبدل منطقة مدمجة {#replace-a-built-in-region}

**استبدال الفتحة** (slot override) يستبدل منطقة مدمجة في واجهة القراءة بمكوّنك الخاص، الذي يستطيع أن يُصيّر المنطقة الأصلية داخله. نقاط الإدراج تضيف، والفتحات تستبدل: فابدأ بنقطة إدراج، لأنها تعمل من دون JavaScript ولا تحذف شيئًا أبدًا.

هذه المناطق فتحات مسمّاة:

| الفتحة | المنطقة |
| --- | --- |
| `Header` | الشريط العلوي، بكل ما فيه |
| `Logo` | الشعار واسم الموقع في الشريط العلوي |
| `VersionSwitcher` | قائمة الإصدارات |
| `Search` | زر البحث الذي يفتح لوحة الأوامر |
| `LocaleSwitcher` | قائمة اللغات |
| `Sidebar` | الشريط الجانبي للتنقل |
| `PageHeader` | مسار التنقل، والعنوان، والوصف، وسطر «حُدّث» |
| `PageFooter` | الملاحظات، والسابق والتالي، ورابط التحرير |
| `Feedback` | عنصر «هل كانت هذه الصفحة مفيدة؟» |
| `Toc` | جدول المحتويات |
| `PageActions` | إجراءات «للوكلاء» في العمود الجانبي |
| `LastChange` | بطاقة التغيير الأخير الذي مسّ الصفحة |
| `Footer` | تذييل الموقع |

استبدل أيًّا منها باسمه في امتدادك. ويعيد `useSlotDefault('Footer')` التذييل المدمج على شكل مكوّن تستطيع تصييره أينما شئت:

```tsx title="resources/larecipe/extension.tsx"
import { defineExtension, useSlotDefault } from '@larecipe/ui'
import { Subscribe } from './Subscribe'

function Footer() {
  const Default = useSlotDefault('Footer')

  return (
    <>
      <Default />
      <p className="acme-footer-note">These docs cover Acme 4. Older releases live in the version menu.</p>
    </>
  )
}

export default defineExtension({ components: { Subscribe }, slots: { Footer } })
```

استبدالات الفتحات تعمل في المتصفح فقط. فالقرّاء بلا JavaScript ومحركات الزحف يرون المنطقة الأصلية، لذا اقصر الاستبدالات على العرض، وضع المحتوى المهم في الصفحة أو في نقطة إدراج.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="ضع المكوّنات من الامتداد">

يستطيع الامتداد أيضًا أن يضع مكوّنات، بجوار المكوّنات التي يسجّلها:

```tsx title="resources/larecipe/extension.tsx"
import { defineExtension } from '@larecipe/ui'
import { Subscribe } from './Subscribe'

export default defineExtension({
  components: { Subscribe },
  inserts: { 'article.after': ['Subscribe'] },
})
```

لا يعرف الخادم شيئًا عن المواضع المضبوطة هنا. فصفحة HTML تحمل عنصرًا نائبًا فارغًا، ولا يظهر المكوّن إلا بعد أن يحمّل متصفح القارئ الامتداد. والقرّاء بلا JavaScript ومحركات الزحف لا يرون شيئًا هناك، لذا فضّل `larecipe.json` لكل ما يهمّ.

وحين يضبط كلاهما نقطة ما، يغلب `larecipe.json`، نقطةً نقطة. فإن ذكر ملف التعريف `article.after`، تُتجاهَل قائمة الامتداد لـ `article.after`، ولا تُدمج معها. أما النقاط التي لا يضبطها إلا الامتداد فتبقى سارية.

</Accordion>

<Accordion title="قواعد نقاط الإدراج في larecipe.json">

يجب أن تكون المفاتيح أسماء النقاط السبع، وأن يكون كل اسم مكوّن بصيغة PascalCase؛ ويبلّغ `larecipe:validate` عن أي شيء آخر بخطأ `manifest.schema`.

تحمل صفحة HTML بديل المكوّن أصلًا، ويتولاه React في مكانه من دون أن يزيح التخطيط. ويأتي ذلك البديل من تعريف المكوّن في PHP (`LaRecipe::component('Subscribe')->fallback(...)`)، ويستقبل الصفحة التي يُصيَّر فيها، فيستطيع أن يربط إليها.

</Accordion>

<Accordion title="ما تحمله صفحة HTML">

في هذا الموقع، تحمل كل صفحة ما عدا المقدمة هذا في HTML الخاص بها، بعد المقال مباشرة (مختصرًا):

```html
<div class="lr-insert" data-lr-insert="article.after"><div data-lr-component="Subscribe" data-lr-props='{"list":"larecipe"}'><form class="lr-subscribe" …>…</form></div></div>
```

- **القرّاء مع JavaScript** يحصلون على مكوّن React، مركَّبًا في ذلك العنصر النائب كأي island.
- **القرّاء بلا JavaScript ومحركات الزحف** يحصلون على صيغة HTML البديلة.
- **الوكلاء** لا يحصلون على شيء. اقرأ [هذه الصفحة بصيغة Markdown](https://larecipe.saleem.dev/3.x/customize/insert-points.md) ولن تجد النموذج في آخرها.

لتنسيق نقطة ما، استهدف غلافها: `.lr-insert[data-lr-insert="article.after"]`.

</Accordion>

<Accordion title="قواعد استبدال الفتحات">

- استدعِ `useSlotDefault()` داخل الاستبدال الخاص بتلك الفتحة، مع اسم الفتحة. وفي أي مكان آخر يرمي خطأً.
- إن تركت `<Default />` جانبًا، يستبدل مكوّنك المنطقة كلها. وإن أعدت `null`، تختفي المنطقة.
- لإزالة عنصر الملاحظات، أوقف `feedback.enabled` في `config/larecipe.php` بدلًا من استبدال `Feedback`: فهذا يزيل نقطة النهاية الخاصة به أيضًا.
- تبقى المنطقة المدمجة في صفحة HTML القادمة من الخادم، ويضع React استبدالك مكانها حين يُركَّب. ولا يحصل الوكلاء على أي شيء من إطار الصفحة.

</Accordion>

</AccordionGroup>

## التالي {#next}

- [مكوّنات React المخصّصة](custom-components.md): اكتب المكوّن الذي تضعه.
- [المظهر](theme.md): غيّر الألوان والخطوط والزوايا من دون أي كود.
- [الإخراج (eject)](eject.md): ما هو مخطط لامتلاك كود الواجهة، وما تستخدمه اليوم.
