---
title: توثيقك الأول
description: دع وكيل البرمجة الذي تستخدمه أصلًا يقرأ تطبيقك ويكتب توثيقًا أوليًا حقيقيًا لفئات القرّاء التي تختارها، ثم راجعه قبل أن يُضاف أي شيء إلى git.
sources:
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/OnboardCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/InstallCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Enums/Audience.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Enums/Agent.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/DetectAgentsAction.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/BuildOnboardingCommandAction.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/RunOnboardingAction.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/ValidateWrittenPagesAction.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/DTOs/OnboardingRequest.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/DTOs/AgentInvocation.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Services/AgentOutput.php
  - vendor/larecipe/larecipe/resources/boost/skills/larecipe-write-docs/SKILL.md
  - vendor/larecipe/larecipe/resources/boost/skills/larecipe-write-docs/references/onboarding.md
translated_from: sha256:a15ffa489a361ba29fc036792e2c2ae726c02de7df9da3490320c42fa75f5625
---

# توثيقك الأول

يسلّم الإعداد الأولي (onboarding) تطبيقك إلى وكيل البرمجة الذي تستخدمه أصلًا. يقرأ الوكيل الكود ويكتب صفحات أولية لفئات القرّاء التي تختارها. وتصل الصفحات إلى شجرة العمل لديك من دون أن تُضاف إلى git، فتراجعها كأي تغيير آخر.

```bash
php artisan larecipe:onboard --audience=product,api
```

يجد LaRecipe وكيلك، ويعرض عليك الأمر الذي سيشغّله بالضبط، ويسأل "Run it now?". لا يعمل شيء حتى توافق. يظهر تقدّم الوكيل في الطرفية أولًا بأول، وحين ينتهي يسرد LaRecipe الصفحات التي كتبها ويشغّل عليها `php artisan larecipe:validate`.

الإعداد الأولي اختياري، ويعمل على جلسة وكيلك أنت، فلا يحتاج التطبيق إلى مفتاح API. شغّله بعد التثبيت مباشرة، أو في أي وقت لاحق، مثلًا حين تضيف فئة قرّاء جديدة. ويعرضه المثبّت سؤالًا أخيرًا حين يجد وكيلًا.

## اختر فئات القرّاء {#choose-the-audiences}

اختر فئة أو أكثر، مفصولة بفواصل:

| الفئة | لمن | الصفحات التي يخطّط لها |
| --- | --- | --- |
| `product` | من يستخدمون المنتج، لا الكود | ما الذي يفعله المنتج، والبدء، وصفحة لكل ميزة رئيسية، والأسئلة الشائعة |
| `api` | المطوّرون الذين يستدعون واجهة HTTP API لديك | المصادقة، والاصطلاحات، وصفحة لكل مورد مع مرجع `<Route>` لكل نقطة نهاية، و webhooks، وإدارة الإصدارات |
| `package` | المطوّرون الذين يثبّتون الكود الخاص بك عبر Composer | التثبيت، والإعدادات، والاستخدام حسب الميزة، ودليل الترقية، والواجهة البرمجية العامة |
| `internal` | من يبنون التطبيق ويشغّلونه | البنية، ومسرد مصطلحات المجال، والإعداد المحلي، والاصطلاحات، ونموذج البيانات، والطوابير والمهام، والنشر، وأدلة التشغيل |

يتخطّى الوكيل أي صفحة لا يملك تطبيقك ما يُكتب فيها: إن لم تكن فيه مسارات API، فلا صفحات API. وتطلب منه مهارته ألا يكتب أبدًا أسرارًا، ولا قيم إعدادات، ولا بيانات عملاء، ولا تخمينات.

> [!IMPORTANT]
> مكان التوثيق الداخلي مجموعة توثيق خاصة. يقترح الإعداد المسبق `internal` مجموعة باسم `handbook` لا يقرؤها إلا فريقك. وحين لا تكون تلك المجموعة موجودة بعد، يذكر الوكيل ذلك في تقريره بدلًا من أن يكتب صفحات داخلية في مجموعة عامة. راجع [كتل الصلاحيات والتوثيق الخاص](../writing/gate-blocks.md).

## راجع الصفحات {#review-the-pages}

تعامل مع التوثيق الأولي كأنه pull request من زميل جديد قرأ الكود كله في بضع دقائق: صحيح في معظمه، ويستحق التدقيق.

1. اطّلع على ما تغيّر بالأمرين `git status docs/` و `git diff docs/`.
2. اقرأ كل صفحة في المتصفح، كما سيقرؤها قرّاؤك.
3. تحقّق من بعض الادعاءات مقابل الكود الذي تذكره كل صفحة في `sources:`. واحذف أي صفحة لست مستعدًا لتحمّل مسؤوليتها.
4. ابحث في الصفحات الجديدة عن أسماء النطاقات لديك، وعن العلامة `@`، وعن أي شيء يشبه مفتاحًا.
5. شغّل التحقق، وحدّث فهرس التوثيق في `AGENTS.md`، ثم أضف التوثيق إلى git مع الكود:

```bash
php artisan larecipe:validate
php artisan larecipe:agents-md
```

يمكنك أن تطلب من الوكيل نفسه إجراء تعديلاتك في الجلسة نفسها. ومن الآن فصاعدًا، يُبقي [خطّاف pre-push](../keeping-current/pre-push-hook.md) هذه الصفحات محدّثة مع تغيّر الكود.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="ما الذي يحدث، خطوة بخطوة">

1. **يختار مجموعة التوثيق وفئات القرّاء.** تُكتب الصفحات في مجموعة التوثيق الافتراضية ما لم تمرّر `--set=handbook`. ومن دون `--audience` يسألك لمن هذا التوثيق.
2. **يجد وكيلك.** يبحث عن أداة سطر أوامر لوكيل في `PATH` ويختارها. وإن كان لديك أكثر من وكيل، يسألك أيّها يكتب. ويختار `--agent=codex` نيابة عنك.
3. **يجهّز الوكيل.** يتأكد من أن مهارات LaRecipe مثبّتة في `.claude/skills/` و `.agents/skills/` (ومع Laravel Boost، يقدّمها Boost بدلًا منه). وفي [مجموعة قاعدة البيانات](../sources/database.md)، يضيف أيضًا خادم MCP المحلي `larecipe` إلى إعدادات ذلك الوكيل، لأن الوكيل يكتب من خلاله.
4. **يعرض الأمر بالضبط ويسأل.** ترى الأمر كاملًا قبل أن يعمل أي شيء.
5. **يكتب الوكيل، وأنت تتابع.** في Claude Code، تظهر كل خطوة في سطر قصير، مثل `> Read app/Http/Controllers/OrderController.php` أو `> Write docs/api/orders.md`، يليه ما يقوله الوكيل. أما تقدّم Codex فيمرّ كما هو. لا توجد مهلة زمنية، ويمكنك إيقافه في أي وقت بالضغط على `Ctrl+C`. وإن توقف الوكيل بخطأ، يعرض LaRecipe رمز الخروج. ولم يُضف شيء إلى git.
6. **يسرد الصفحات ويتحقق منها.** يقارن LaRecipe مجلد التوثيق قبل التشغيل وبعده (وفي مجموعة قاعدة البيانات، مجلد ترحيلات التوثيق)، ويسرد كل ملف بأنه `created` أو `updated`. ثم يشغّل `php artisan larecipe:validate` على المجموعة. وحين تنجح الصفحات تظهر الرسالة "The new pages pass validation. Review them, then commit them with your code." وحين لا تنجح، يفشل الأمر ويسرد الأخطاء التي عليك إصلاحها أولًا.

صفحات مجموعة المستودع ملفات جديدة في شجرة العمل لديك. أما صفحات مجموعة قاعدة البيانات فتُطبَّق على قاعدة بياناتك المحلية وتُكتب في ترحيل التوثيق الخاص بالفرع داخل `database/migrations/docs/`، فتصل إلى الإنتاج عبر الـ pull request مثل بقية تغييرك.

</Accordion>

<Accordion title="ابدأه من المثبّت">

في الوضع التفاعلي، يسأل `larecipe:install` في آخر أسئلته "Generate starter docs from your app with your coding agent now?"، حين يجد وكيلًا مثبّتًا. أو أجب مسبقًا:

```bash
php artisan larecipe:install --onboard=api --onboard-agent=claude
```

</Accordion>

<Accordion title="ما الذي يقرؤه الوكيل لكل فئة وكيف يكتب">

كل فئة إعداد مسبق في المهارة `larecipe-write-docs`: مخطط للصفحات، ونبرة، وما يقرؤه من الكود، وما لا يكتبه أبدًا.

| الفئة | ما الذي يقرؤه |
| --- | --- |
| `product` | المسارات والمتحكّمات وراء الميزات التي يراها المستخدم، وصفحات Blade أو Inertia، ورسائل التحقق في نماذج الإدخال، ونصوص المساعدة الموجودة، وملف README |
| `api` | `routes/api.php` وملفات المسارات التي يحمّلها، والمتحكّمات، و FormRequests، و API Resources، وحرّاس المصادقة وإعداد Sanctum أو Passport، ومحدِّدات المعدّل، ومخرجات Scramble أو Scribe إن وُجدت |
| `package` | `composer.json`، ومزوّد الخدمة، وبنية ملف الإعدادات المنشور، والأصناف العامة وتعليقات docblock فيها، والاختبارات بوصفها أمثلة |
| `internal` | الكود كله: `app/`، والترحيلات، و `routes/`، وبنية `config/`، وإعدادات CI، و `AGENTS.md`، والملاحظات الموجودة |

صفحات المنتج تبدأ بالمهمة، ولا تحوي أسماء أصناف ولا أوامر artisan. وصفحات API دقيقة وسهلة التصفّح، مع أمثلة طلبات واستجابات مأخوذة من Resources والاختبارات لديك. وصفحات الحزمة سهلة النسخ واللصق، وتذكر أسماء مفاتيح الإعدادات في النص. والصفحات الداخلية مباشرة ومكثّفة، مكتوبة لمطوّر Laravel في يومه الأول، مع مراجع `<Model>` لنموذج البيانات.

وتصلح بعض الأسماء البديلة أيضًا: `users` بدلًا من `product`، و `api-consumers` بدلًا من `api`، و `package-users` بدلًا من `package`، و `team` أو `handbook` بدلًا من `internal`. والأسماء التي لا يعرفها تُتجاهل.

</Accordion>

<Accordion title="ما الذي لا يكتبه الوكيل أبدًا">

لكل فئة، تطلب المهارة من الوكيل أن يستبعد:

- **الأسرار والإعدادات.** لا قيم من `.env`، ولا مفاتيح، ولا رموز وصول، ولا كلمات مرور، ولا سلاسل اتصال، ولا أسماء مضيفين، ولا روابط داخلية. يجوز أن يظهر مفتاح الإعدادات باسمه، لا بقيمته أبدًا. وتستخدم أمثلة API قيمة وهمية مثل `YOUR_API_TOKEN`.
- **بيانات العملاء.** لا أسماء، ولا عناوين بريد، ولا معرّفات، ولا سجلات من seeders أو fixtures تبدو حقيقية.
- **التخمينات.** ما لا يقوله الكود لا تقوله الصفحة. ويترك الوكيل ملاحظة في تقريره بدلًا من ذلك.

ولكل فئة على حدة، يتخطّى أيضًا المسارات الداخلية وميزات الإدارة في صفحات المنتج، ونقاط النهاية الداخلية في صفحات API، والأصناف الموسومة `@internal` في صفحات الحزمة.

ويجب أن تكون قيمة `access.mode` في مجموعة `handbook` الخاصة بالإعداد المسبق `internal` هي `auth` أو `gate`. وتشرح [أساسيات الإعداد](configuration.md) كيف تضيف مجموعة ثانية.

</Accordion>

<Accordion title="كيف يعمل الوكيل">

سير عمل الإعداد الأولي في المهارة واحد لكل الوكلاء. يقرأ الوكيل أولًا `AGENTS.md` و `README.md` و `composer.json` والتوثيق الموجود لديك، ويحتفظ بصفحة المقدمة التي كتبها المثبّت، فيحسّنها ولا يستبدلها. ويخطّط لكل صفحة قبل كتابتها، مع الكود الذي ستقرؤه كل واحدة. ويجمع الصفحات الجديدة حسب الفئة في التنقل، ولا يكتب إلا داخل مجلد التوثيق، ويشغّل التحقق، وينهي عمله بقائمة بالصفحات التي كتبها وبكل ما استبعده.

</Accordion>

<Accordion title="أي الوكلاء يشغّلهم LaRecipe، والأمر الذي يعرضه">

يكتشف LaRecipe الوكيل بأداة سطر الأوامر الخاصة به في `PATH` (`claude` و `codex` و `cursor-agent` و `gemini` و `copilot`)، أو بملفاته داخل المشروع (`.claude` و `CLAUDE.md` و `.codex` و `.cursor` و `.gemini` و `GEMINI.md` و `.github/copilot-instructions.md` و `.github/skills`). ولا يختار الإعداد الأولي إلا وكيلًا أداته مثبّتة، لأنه يجب أن يشغّلها.

| الوكيل | ما الذي يفعله LaRecipe |
| --- | --- |
| Claude Code | يشغّله من دون واجهة (headless)، مع حصر الكتابة في مجلد التوثيق. |
| Codex | يشغّله من دون واجهة داخل بيئة العزل `workspace-write` الخاصة به. |
| Cursor و Gemini CLI و GitHub Copilot | يطبع الموجّه (prompt) لترسله أنت. لا يشغّل LaRecipe هؤلاء الوكلاء من دون واجهة بعد. |

ما تقرؤه هو ما يعمل: الأمر المعروض على الشاشة والعملية التي يبدؤها LaRecipe مبنيّان من الوسائط نفسها. لمجموعة مستودع في `docs/`:

<CodeGroup>

```bash title="Claude Code"
claude -p "Use the larecipe-write-docs skill to run onboarding for audiences: product, api (set: docs)" --allowedTools "Read,Grep,Glob,Edit(docs/**),Write(docs/**),Bash(php artisan larecipe:*)" --output-format stream-json --verbose
```

```bash title="Codex"
codex exec --sandbox workspace-write 'Use the $larecipe-write-docs skill to run onboarding for audiences: product, api (set: docs)'
```

</CodeGroup>

يمكن لـ Claude Code أن يقرأ مشروعك كله، لكنه لا يكتب إلا داخل `docs/`، ولا يشغّل إلا أوامر `php artisan larecipe:*`. وفي مجموعة قاعدة البيانات، تكون الأدوات المسموح بها `Read,Grep,Glob,mcp__larecipe,Bash(php artisan larecipe:*)`: فلا يكتب الوكيل إلا عبر أدوات التأليف في خادم MCP الخاص بـ LaRecipe، وهي تسجّل كل تغيير في ترحيل توثيق.

لا تستطيع بيئة العزل في Codex أن تحصر الكتابة في مجلد واحد، فينبّه LaRecipe إلى ذلك تحت الأمر: راجع الـ diff قبل أن تضيف التغييرات إلى git. ولكي يكتب Codex في مجموعة قاعدة بيانات، يحتاج أيضًا إلى خادم MCP `larecipe` في `.codex/config.toml`، داخل مشروع يثق به Codex.

أما Cursor و Gemini CLI و GitHub Copilot، فافتح الوكيل في مشروعك وأرسل إليه الموجّه الذي يطبعه LaRecipe:

```text
Use the larecipe-write-docs skill to run onboarding for audiences: product, api (set: docs)
```

</Accordion>

<Accordion title="الموافقة والسكربتات والتشغيل التجريبي">

لا يبدأ LaRecipe وكيلًا أبدًا من دون موافقتك:

- في الوضع التفاعلي، يسأل "Run it now?". وإن أجبت بالرفض، يطبع الأمر لتشغّله لاحقًا.
- يشغّله `--yes` من دون سؤال، للسكربتات.
- يكتفي `--print` بطباعة الأمر.
- من دون طرفية ومن دون `--yes`، يطبع الأمر ويتوقف. ويشمل ذلك `larecipe:install --no-interaction --onboard=api`: ينهي المثبّت عمله، وتشغّل أنت `php artisan larecipe:onboard --audience=api --yes` حين تكون مستعدًا.
- يطبع `larecipe:install --dry-run --onboard=api` الأمر ولا يشغّل شيئًا.

وحين تسمّي بالخيار `--agent` وكيلًا ليست أداته في `PATH`، يطبع LaRecipe الأمر ويطلب منك تشغيله حيث يكون الوكيل مثبّتًا.

</Accordion>

<Accordion title="من دون وكيل">

حين لا تكون أي أداة وكيل مثبّتة، يحتفظ توثيقك بصفحة المقدمة التي كتبها المثبّت، ويطبع LaRecipe الأمر الذي تشغّله حين يصبح لديك وكيل:

```bash
php artisan larecipe:onboard --audience=api --set=docs
```

يعيش سير العمل في المهارة `larecipe-write-docs` لا في الأمر، فيستطيع أي وكيل يقرأ المهارات أن يشغّله من محادثة: أرسل إليه الموجّه المعروض أعلاه. ويمكنك أيضًا أن تكتب الصفحات الأولى بنفسك، فصفحتا [Markdown](../writing/markdown.md) و[المكوّنات](../writing/components.md) تغطّيان كل ما يمكن أن تحويه الصفحة.

</Accordion>

<Accordion title="كل خيارات larecipe:onboard">

مقروءة مباشرة من سجلّ الأوامر في هذا التطبيق:

<Artisan command="larecipe:onboard" />

</Accordion>

</AccordionGroup>

## التالي {#next}

- [أساسيات الإعداد](configuration.md): الإعدادات القليلة التي تغيّرها معظم التطبيقات.
- [بنية المشروع](project-structure.md): أين تعيش الصفحات الجديدة وكيف تصبح مساراتها روابط.
- [كيف يعمل](../keeping-current/how-it-works.md): كيف يبقى التوثيق محدّثًا بعد هذه المسودة الأولى.
