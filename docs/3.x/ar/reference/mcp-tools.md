---
title: أدوات MCP
description: كل أداة ومورد وموجّه (prompt) يتيحه خادم MCP في LaRecipe، مع الأوصاف والتوصيفات (annotations) ومخططات المدخلات، والخادم الذي يقدّم كلًّا منها.
sources:
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Sync/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Validation/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/Domains/Agents/AgentsServiceProvider.php
  - vendor/larecipe/larecipe/config/larecipe.php
translated_from: sha256:d38c33e88658274975f17219c7d071981eb0457c3d74841944cb205ab92d8392
---

# أدوات MCP

كل أداة ومورد وموجّه (prompt) يتيحه خادم MCP في LaRecipe. وما يراه المستدعي يعتمد على الخادم والمجموعة وهوية المستدعي. لتوصيل وكيل، راجع [خادم MCP](../agents/mcp.md).

| الخادم | المكان | ما يقدّمه |
| --- | --- | --- |
| الويب | `POST /{prefix}/mcp` لكل مجموعة (`/mcp` في هذا الموقع) | تلك المجموعة فقط. أدوات القراءة لكل من تسمح له المجموعة بالدخول، وأدوات أكثر للمستدعين الذين تسمح لهم بواباتك (gates). |
| المحلي | `php artisan larecipe:mcp`، عبر stdio | كل المجموعات. تأخذ أدوات القراءة الوسيط `set`. كل الأدوات، ومنها أدوات التأليف. |

## الأدوات التي يتيحها كل خادم

| الأداة | المحلي | الويب |
| --- | --- | --- |
| `search_docs` و `read_page` و `list_pages` | نعم | نعم |
| `submit_feedback` | حين يكون `feedback.enabled` مفعّلًا | حين يكون `feedback.enabled` مفعّلًا |
| `validate_docs` | نعم | فقط للمستدعين الذين تسمح لهم البوابة `manageLarecipe` |
| `write_page` و `move_page` و `delete_page` و `update_navigation` و `update_manifest` | نعم | فقط في مجموعة قاعدة بيانات مع تفعيل `mcp.web.author_tools`، لمستدعٍ مسجّل الدخول تسمح له البوابة `manageLarecipe` |
| `affected_pages` | نعم | أبدًا: فهي تقرأ شجرة العمل لديك |

لا تتيح مجموعة المستودع أدوات التأليف على الويب أبدًا. وكل نقطة نهاية على الويب مرتبطة بمجموعتها: الاستدعاء الذي يسمّي مجموعة أخرى في `set` يُرفض. والزائر على نقطة النهاية في هذا الموقع يرى أربع أدوات: `search_docs` و `read_page` و `list_pages` و `submit_feedback`. والإعدادات تحت [`mcp` في مرجع الإعدادات](configuration.md#mcp).

## أدوات القراءة

تحمل الأدوات الثلاث التوصيفات `readOnlyHint: true` و `idempotentHint: true` و `openWorldHint: false`. وعلى الخادم المحلي تأخذ كل منها أيضًا `set` (string): مجموعة التوثيق المستخدمة، وقيمتها الافتراضية `larecipe.default`.

### search_docs

> Search the documentation and get the best matching pages, with the section and a snippet for each. Search before you read or write a page. Pass a result's path to read_page to read it.

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `query` | string | نعم | ما تبحث عنه: بضع كلمات، أو اسم API أو صنف، أو أمر، أو رسالة خطأ. |
| `version` | string | لا | إصدار التوثيق الذي يُبحث فيه. القيمة الافتراضية الإصدار الحالي. |
| `locale` | string | لا | اللغة التي يُبحث فيها، برمز اللغة. القيمة الافتراضية اللغة الرئيسية. |
| `limit` | integer, 1 to 20 | لا | عدد النتائج المُعادة. القيمة الافتراضية 8. |

تُعيد نتائج منظّمة: `set` و `version` و `query` و `results`، ولكل نتيجة `title` و `path` و `url` و `markdown_url` و `heading` و `snippet` و `description` و `score`.

### read_page

> Read one docs page as Markdown. Pass the path from search_docs or list_pages (for example "guides/webhooks"), or the page URL. Pass section (a heading or its #anchor) to read only that part of the page. The result ends with the page's content_hash (also in its _meta): pass it as based_on when you write, move or delete the page.

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `path` | string | نعم | مسار الصفحة (`guides/webhooks`)، أو رابطها، أو رابط `.md` الخاص بها. |
| `version` | string | لا | إصدار التوثيق. القيمة الافتراضية الإصدار الحالي. |
| `locale` | string | لا | اللغة، برمز اللغة. القيمة الافتراضية اللغة الرئيسية. |
| `section` | string | لا | إعادة هذا القسم فقط: نص عنوان أو معرّف الرابط (anchor) الخاص به. |

تُعيد نسخة Markdown للوكلاء من الصفحة، مع `title` و `path` و `version` و `locale` و `url` و `markdown_url` و `content_hash` في `_meta` من النتيجة. وينتهي النص بسطر يحمل `content_hash` نفسه، للعملاء الذين يعرضون النص فقط. ويحصل Ask AI على Markdown من دون ذلك السطر.

### list_pages

> List the docs as the navigation shows them: groups, then pages with their title, path and description. Pass group to list one group only. Read a page with read_page and its path.

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `version` | string | لا | إصدار التوثيق. القيمة الافتراضية الإصدار الحالي. |
| `locale` | string | لا | اللغة، برمز اللغة. القيمة الافتراضية اللغة الرئيسية. |
| `group` | string | لا | سرد القسم الذي يحمل هذه التسمية فقط. |

## الملاحظات

### submit_feedback

> Tell the docs owners whether a page helped. Use it when a page was wrong, outdated or missing something you needed, and say what in the comment.

التوصيفات: `readOnlyHint: false` و `destructiveHint: false` و `idempotentHint: false` و `openWorldHint: false`. وعلى الخادم المحلي تأخذ أيضًا `set`.

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `path` | string | نعم | مسار الصفحة أو رابطها الذي تخصه الملاحظة. |
| `helpful` | boolean | نعم | هل أفادت الصفحة. |
| `comment` | string, up to 2000 characters | لا | ما كان خاطئًا أو ناقصًا، في جملة أو اثنتين. |
| `version` | string | لا | إصدار التوثيق. القيمة الافتراضية الإصدار الحالي. |
| `locale` | string | لا | اللغة، برمز اللغة. القيمة الافتراضية اللغة الرئيسية. |

تطلق الحدث `FeedbackSubmitted` مع ضبط `source` على `agent`. ولا يخزّن LaRecipe شيئًا. راجع [الأحداث](events.md).

## التحقق

### validate_docs

> Check the docs for broken links and anchors, navigation entries without pages, schema errors in larecipe.json, navigation.json and frontmatter, unknown components, missing snippets and more. Run it after writing, and fix every error. Each issue has a stable rule id and often a fix.

التوصيفات: `readOnlyHint: true` و `idempotentHint: true` و `openWorldHint: false`.

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `version` | string | لا | فحص هذا الإصدار فقط. القيمة الافتراضية كل الإصدارات. |
| `set` | string | لا | للخادم المحلي فقط: مجموعة التوثيق التي تُفحص. القيمة الافتراضية `larecipe.default`. |

تُعيد التقرير نفسه الذي يُخرجه `php artisan larecipe:validate --json`.

## أدوات التأليف {#author-tools}

تغيّر أدوات التأليف التوثيق: الملفات في شجرة العمل في مجموعة المستودع، أما في مجموعة قاعدة البيانات فقاعدة البيانات المحلية مع ترحيل التوثيق الخاص بالفرع. وتُعيد كل منها `outcome` (`published` أو `drafted` أو `conflicted` أو `unchanged` أو `rejected`) و `message` و `revision` و `validation` و `docs_migration`.

كل أداة تأليف تحمل التوصيف `destructiveHint: true`، وتأخذ هذه الوسائط إلى جانب وسائطها الخاصة:

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `version` | string | لا | الإصدار، للمجموعات ذات الإصدارات. القيمة الافتراضية الإصدار الافتراضي. غير متاح في `update_manifest`. |
| `locale` | string | لا | رمز اللغة. القيمة الافتراضية اللغة الافتراضية. غير متاح في `update_manifest`. |
| `summary` | string | لا | سطر واحد يصف التغيير، لقائمة المراجعات (revisions) وللـ pull request. |
| `based_on` | string | لا | بصمة المحتوى التي كُتب التغيير على أساسها. ويُرفض التغيير إن تغيّر المحتوى منذئذ. انظر أدناه لمصدرها. |
| `set` | string | لا | مفتاح مجموعة التوثيق، حين يكون للتطبيق أكثر من مجموعة. القيمة الافتراضية المجموعة الافتراضية. وعلى نقطة نهاية الويب، مجموعتها فقط. |

من أين تأتي `based_on`:

- **`write_page` و `move_page` و `delete_page`:** قيمة `content_hash` التي أعادتها `read_page` للصفحة. احذفها لصفحة جديدة.
- **`update_navigation` و `update_manifest`:** قيمة `revision.content_hash` التي أعادها آخر استدعاء لك للأداة نفسها (وفي التنقل، لذلك الإصدار وتلك اللغة). احذفها لتغيّر التنقل أو ملف التعريف كما هو الآن.

تحمل `revision` التي تعيدها كل أداة تأليف القيمة `content_hash`، أي بصمة ما كتبته، فيستطيع الوكيل أن يسلسل عدة تغييرات على الصفحة نفسها.

### write_page

> Create or replace one docs page: frontmatter and Markdown body. Read the page first with read_page and pass the content_hash it returns as based_on, so you never overwrite a newer edit (leave based_on out for a new page). On a repository set the file changes in the working tree; on a database set the change is applied to the local database and written into the branch's docs migration (commit it with the code). Returns the revision and the validation result; fix every validation error it reports.

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `path` | string | نعم | مسار الصفحة داخل إصدارها، من دون `.md`، مثل `billing/exports`. و `index` هي صفحة البداية. |
| `frontmatter` | object | لا | ترويسة الصفحة: `title` و `description` و `sources` والبقية. ولا تضع فيها أبدًا قيم إعدادات أو بيئة. |
| `body` | string | نعم | متن Markdown، من دون كتلة الترويسة. |

### move_page

> Move one docs page to another path (or version, or locale) in the same set. Refused when the new path is taken. Update links and the navigation afterwards (update_navigation with a rename patch). Pass the content_hash read_page returned as based_on.

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `from` | string | نعم | مسار الصفحة الحالي، مثل `billing/exports`. |
| `to` | string | نعم | مسار الصفحة الجديد، مثل `billing/csv-exports`. |
| `to_version` | string | لا | النقل إلى إصدار آخر. القيمة الافتراضية الإصدار نفسه. |
| `to_locale` | string | لا | النقل إلى لغة أخرى. القيمة الافتراضية اللغة نفسها. |

### delete_page

> Delete one docs page. Remove it from the navigation too (update_navigation with a remove patch). On a database set the page stays in the revision history and can be restored. Pass the content_hash read_page returned as based_on.

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `path` | string | نعم | مسار الصفحة، مثل `billing/legacy`. |

### update_navigation

> Change the navigation (navigation.json) of one version: send a whole tree, or a patch, a list of operations such as {"add": "billing/exports", "group": "Billing", "after": "billing/overview"}, {"move": "a", "group": "B"}, {"remove": "a"} or {"rename": "a", "to": "b"}. A version without navigation.json starts from the navigation derived from its folders.

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `tree` | object | لا | ملف `navigation.json` كامل: `{"groups": [...]}` أو `{"tabs": [...]}`. |
| `patch` | list of objects | لا | عمليات تُطبَّق بالترتيب: `add` و `move` و `remove` و `rename`. |

### update_manifest

> Change the set's larecipe.json (name, description, theme, versions, locales, navbar, redirects, agent description) with a JSON merge patch: keys replace, null removes a key, objects merge, lists are replaced whole. Presentation and structure only: routes, drivers, AI settings and secrets live in config/larecipe.php and never here, and so do a database set's analytics integrations.

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `patch` | object | نعم | رقعة دمج JSON (JSON merge patch، وفق RFC 7386) لملف `larecipe.json`. |

### affected_pages

> List the docs pages whose `sources:` match files changed between `base` (default: the default branch) and `head`, plus uncommitted work. Each page says whether the change already updated it (status "updated") or left it stale (status "stale"). Update stale pages before you push.

للخادم المحلي فقط: تقرأ شجرة العمل لديك و git، فلا تقدّمها أي نقطة نهاية على الويب. تحمل التوصيف `readOnlyHint: true`، ولا تأخذ إلا وسائطها الخاصة:

| الوسيط | النوع | مطلوب | الوصف |
| --- | --- | --- | --- |
| `base` | string | لا | الفرع أو الـ commit الذي يُقارن به. الافتراضي: الفرع الافتراضي. |
| `head` | string | لا | نهاية النطاق. الافتراضي: `HEAD`. |
| `uncommitted` | boolean | لا | تضمين التغييرات المُدرجة في الـ staging وغير المُدرجة وغير المتتبَّعة. الافتراضي: true. |
| `set` | string | لا | هذه المجموعة فقط. الافتراضي: كل المجموعات. |
| `drift` | boolean | لا | سرد الصفحات التي صارت قديمة قبل هذا التغيير أيضًا. الافتراضي: false. |

هي الفحص نفسه الذي يجريه [`larecipe:affected`](commands.md#larecipeaffected).

## الموارد

| الاسم | قالب URI | النوع | الوصف |
| --- | --- | --- | --- |
| `docs-page` | `docs://{set}/{version}/{path}` | `text/markdown` | صفحة توثيق بصيغة Markdown للوكلاء، مثل `docs://docs/3.x/agents/surfaces`. والمجموعة التي بلا إصدارات تستخدم `current` إصدارًا، واللغة غير الرئيسية توضع قبل المسار (`ar/index`). |

يقدّم الخادم القالب فقط، و `resources/list` فارغة. ونقطة نهاية الويب لا تقرأ إلا صفحات مجموعتها.

## الموجّهات

| الاسم | الوسائط | ما يقدّمه للوكيل |
| --- | --- | --- |
| `write-docs-page` | `topic` (مطلوب)، `path`، `set` | خطوات كتابة صفحة توثيق جديدة: تحقّق مما هو موجود، واكتب من الكود، وأضفها إلى التنقل، وشغّل التحقق. |
| `document-change` | `change` (مطلوب: ملخص، أو diff، أو نطاق من الـ commits)، `set` | خطوات تحديث التوثيق الذي جعله تغيير في الكود قديمًا، في التغيير نفسه مع الكود. |

## التعليمات عند الاتصال

يحصل كل عميل على سير العمل نفسه عند الاتصال: ابحث أولًا بـ `search_docs`، واقرأ قبل الكتابة بـ `read_page`، وتحقّق بعد الكتابة بـ `validate_docs`، واستشهد بروابط الصفحات، واستخدم `submit_feedback` حين تكون صفحة خاطئة أو ناقصة. ومع وجود عدة مجموعات، يسردها الخادم المحلي أيضًا. ويعرض [خادم MCP](../agents/mcp.md) النص الكامل.

## التالي

- [خادم MCP](../agents/mcp.md): وصّل وكيلًا، وصادِق على مجموعة خاصة.
- [الأحداث](events.md): `McpToolCalled` و `SearchPerformed` و `FeedbackSubmitted`.
