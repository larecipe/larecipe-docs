---
title: خادم MCP
description: اربط أي عميل MCP بتوثيقك، عبر HTTP لوكلاء قرّائك أو محليًا لوكيل البرمجة الخاص بك، واعرف الأدوات التي يحصل عليها كلٌّ منهما.
sources:
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/BindMcpSet.php
  - vendor/larecipe/larecipe/src/Domains/Agents/AgentsServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Commands/McpCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Sync/Mcp/AffectedPagesTool.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Mcp/ValidateDocsTool.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/InstallMcpConfigAction.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Services/CodexToml.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Enums/Agent.php
  - vendor/larecipe/larecipe/src/Domains/Access/Laravel/GateAuthorizer.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - .mcp.json
translated_from: sha256:76eb6f9e480f4eac090dff967a4a8312d74a15981b59d42f1926ce1523cc09d0
---

# خادم MCP

لكل مجموعة توثيق خادم MCP، فيستطيع أي وكيل يتحدث بروتوكول Model Context Protocol أن يبحث في توثيقك ويقرأه بأدوات حقيقية. ويعمل بطريقتين، كلتاهما مفعّلة افتراضيًا:

- **نقطة نهاية عبر الويب لكل مجموعة توثيق**، في `/{prefix}/mcp`، لوكلاء قرّائك. تخدم مجموعة واحدة وتتبع قواعد الوصول نفسها التي تتبعها صفحات HTML.
- **خادم محلي لوكيل البرمجة الخاص بك**، `php artisan larecipe:mcp`. يعمل عبر stdio في مستودعك، ويخدم كل المجموعات، ويضيف الأدوات التي تكتب التوثيق.

لربط Claude Code بتوثيق هذا الموقع:

```bash
claude mcp add --transport http larecipe https://larecipe.saleem.dev/mcp
```

وأي عميل MCP يدعم النقل عبر HTTP يأخذ الرابط نفسه. ولا يحتاج القرّاء إلى تذكّره: ففي قائمة **للوكلاء** في كل صفحة إجراء **ربط MCP**، الذي ينسخ هذا الأمر للمجموعة التي يقرؤونها.

## وكيل البرمجة الخاص بك {#your-own-coding-agent}

نادرًا ما تشغّل الخادم المحلي بنفسك. فالأمر `php artisan larecipe:install` يسجّله باسم `larecipe` في ملف إعدادات المشروع لكل وكيل برمجة تستخدمه (`.mcp.json` لـ Claude Code، و `.codex/config.toml`، و `.cursor/mcp.json`، و `.gemini/settings.json`، و `.vscode/mcp.json`). أضف الملف إلى git ليحصل عليه وكيل كل زميل في الفريق، وشغّل وكيلك من جذر المشروع.

## الأدوات {#tools}

يشغّل الخادمان أصناف الأدوات نفسها، فتطابق الإجابة من MCP الصفحة التي يقرؤها الشخص.

| الأداة | ما تفعله | نقطة النهاية عبر الويب | الخادم المحلي |
| --- | --- | --- | --- |
| `search_docs` | صفحات مرتّبة لاستعلام ما، كلٌّ منها مع قسمها ومقتطف وروابطها | نعم | نعم |
| `read_page` | صفحة واحدة بصيغة Markdown، بالنص نفسه الذي في نسخة `.md` منها، أو قسم واحد منها (`section`)، مع `content_hash` | نعم | نعم |
| `list_pages` | التنقل: المجموعات، ثم الصفحات مع العنوان والمسار والوصف | نعم | نعم |
| `submit_feedback` | تخبر أصحاب التوثيق هل أفادت الصفحة، مع تعليق اختياري | حين يكون `feedback.enabled` | حين يكون `feedback.enabled` |
| `validate_docs` | تقرير `larecipe:validate --json` لمجموعة | للمستدعين الذين تسمح لهم البوابة (gate) `manageLarecipe` | نعم |
| `affected_pages` | الصفحات التي جعلها تغييرٌ ما قديمة، من `sources:` كل صفحة | أبدًا | نعم |
| `write_page`، `move_page`، `delete_page`، `update_navigation`، `update_manifest` | أدوات التأليف: تغيّر الصفحات والتنقل و `larecipe.json` | مجموعات قاعدة البيانات مع `mcp.web.author_tools` | نعم |

لا تعيد أدوات القراءة إلا ما يجوز للمستدعي رؤيته: تُستبعد الصفحات الخاصة والإصدارات الخاصة وكتل الصلاحيات تمامًا كما في صفحات HTML. أما `validate_docs` فتقرأ كل الصفحات، الخاصة منها أيضًا، ولهذا لا تقدّمها نقطة النهاية عبر الويب إلا للمديرين. ووسائط كل أداة ونتائجها موجودة في [مرجع أدوات MCP](../reference/mcp-tools.md).

## مجموعات التوثيق الخاصة {#private-doc-sets}

تنطبق قواعد الوصول الخاصة بالمجموعة على MCP في كل استدعاء: فالزائر الذي يستدعي أداة على مجموعة خاصة يحصل على "These docs are not available." وللسماح بدخول الوكلاء المسجّلين، أضف المصادقة إلى نقطة النهاية بالـ guard الذي يستخدمه تطبيقك أصلًا:

```php
// config/larecipe.php
'sets' => [
    'handbook' => [
        // route, source, access ...
        'mcp' => ['web' => ['middleware' => ['auth:sanctum']]],
    ],
],
```

مع `auth:sanctum`، يرسل الوكلاء رمز Sanctum على شكل bearer token:

```bash
claude mcp add --transport http handbook https://example.com/handbook/mcp \
  --header "Authorization: Bearer $HANDBOOK_TOKEN"
```

ولاستخدام تدفق OAuth في laravel/mcp بدلًا من ذلك، اضبط الـ middleware على `['auth:api']` وسجّل `Mcp::oauthRoutes()` في تطبيقك. وفي الحالتين، يُفحص الوصول من جديد في كل استدعاء أداة، صفحةً صفحة. وتشرح صفحة [كتل الصلاحيات والتوثيق الخاص](../writing/gate-blocks.md) القواعد.

## تفاصيل {#details}

<AccordionGroup>

<Accordion title="أين توجد نقطة النهاية عبر الويب">

كل مجموعة مع `mcp.web.enabled` تحصل على نقطة نهاية واحدة في بادئتها مضافًا إليها `mcp.web.path` (`mcp` افتراضيًا). فالمجموعة المركّبة على `/docs` تجيب في `/docs/mcp`. ومجموعة هذا الموقع مركّبة على جذر النطاق، لذا نقطة نهايتها هي `/mcp`. والمجموعة التي لها `route.domain` تحصل على نقطة نهايتها على ذلك النطاق.

يسجّل LaRecipe هذه المسارات بنفسه، من `AgentRoutes::global()`، فلا شيء تضيفه إلى `routes/ai.php`. مقروءة مباشرة من موجّه هذا التطبيق:

<Route name="larecipe.docs.mcp" />

لا تقبل نقطة النهاية إلا `POST`. وهي خارج مجموعة مسارات المجموعة عن قصد: فعملاء MCP يرسلون JSON من دون جلسة ولا رمز CSRF، فلا ينطبق `route.middleware` الخاص بالمجموعة (`web` افتراضيًا). وبعد الـ middleware الخاص بـ laravel/mcp، يضيف LaRecipe `mcp.web.middleware`، ثم محدِّد المعدّل `larecipe-mcp`، ثم الربط بمجموعتها. ولا ترى أدوات القراءة في نقطة نهاية ما إلا مجموعة نقطة النهاية تلك: فالوكيل المتصل بـ `/help/mcp` لا يستطيع قراءة `/handbook`.

</Accordion>

<Accordion title="جرّبه بـ curl">

MCP عبر HTTP هو JSON-RPC. هذا الأمر يسرد أدوات نقطة نهاية هذا الموقع، وهي تعمل محليًا:

```bash
curl -s -X POST http://127.0.0.1:8780/mcp \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
```

والاستجابة، مختصرة (حُذفت أوصاف الخصائص، ومخطط المُخرجات، وثلاثة من مخططات المدخلات الأربعة):

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "tools": [
      {
        "name": "search_docs",
        "title": "Search the docs",
        "description": "Search the documentation and get the best matching pages, with the section and a snippet for each. Search before you read or write a page. Pass a result's path to read_page to read it.",
        "inputSchema": {
          "type": "object",
          "properties": {
            "query": { "type": "string" },
            "version": { "type": "string" },
            "locale": { "type": "string" },
            "limit": { "type": "integer", "minimum": 1, "maximum": 20 }
          },
          "required": ["query"]
        },
        "annotations": { "readOnlyHint": true, "idempotentHint": true, "openWorldHint": false }
      },
      { "name": "read_page", "title": "Read a docs page" },
      { "name": "list_pages", "title": "List the docs pages" },
      { "name": "submit_feedback", "title": "Send feedback on a page" }
    ]
  }
}
```

أربع أدوات: فنقطة نهاية المجموعة العامة تقدّم أدوات القراءة و `submit_feedback` للجميع. واستدعِ أيًّا منها بالطريقة نفسها، مع `"method":"tools/call"` و `"params":{"name":"search_docs","arguments":{"query":"mcp endpoint"}}`.

</Accordion>

<Accordion title="الخادم المحلي، والأمر، وملفات الإعدادات">

<Artisan command="larecipe:mcp" />

هو اختصار للأمر `php artisan mcp:start larecipe`. ولفحصه بيدك، شغّل `php artisan mcp:inspector larecipe`.

يدمج المثبّت إدخال `larecipe` في ملف كل وكيل: تبقى الخوادم الأخرى، ويُترك إدخال `larecipe` الذي كتبته أنت كما هو. وهذا ملف `.mcp.json` الذي يضيفه هذا الموقع إلى git لـ Claude Code:

```json
{
  "mcpServers": {
    "larecipe": {
      "command": "php",
      "args": [
        "artisan",
        "larecipe:mcp"
      ]
    }
  }
}
```

يستخدم Cursor (`.cursor/mcp.json`) و Gemini CLI (`.gemini/settings.json`) شكل `mcpServers` نفسه. ويستخدم GitHub Copilot (`.vscode/mcp.json`) المفتاح `servers` بدلًا من `mcpServers`. أما Codex فيقرأ TOML، في `.codex/config.toml`:

```toml
[mcp_servers.larecipe]
command = "php"
args = ["artisan", "larecipe:mcp"]
```

يستخدم الأمر مسار `artisan` نسبيًا، لذا شغّل وكيلك من جذر المشروع. وفي الخادم المحلي، تأخذ الأدوات وسيطًا اختياريًا `set` لاختيار مجموعة توثيق، ومع أكثر من مجموعة، تسرد `list_pages` من دونه المجموعات أولًا.

</Accordion>

<Accordion title="كيف تكتب أدوات التأليف">

قبل أن يكتب الوكيل صفحة أو ينقلها أو يحذفها، يقرؤها بـ `read_page` ويمرّر `content_hash` من آخر النتيجة على شكل `based_on`، فلا يستبدل التغيير أبدًا تعديلًا أحدث. وفي `update_navigation` و `update_manifest`، تكون `based_on` هي `revision.content_hash` التي أعادها آخر استدعاء للأداة. راجع [أدوات MCP](../reference/mcp-tools.md#author-tools).

في الخادم المحلي، تكتب أدوات التأليف حيث توجد المجموعة. ففي مجموعة المستودع تغيّر الملفات في شجرة العمل لديك، فيصبح التغيير جزءًا من الـ commit التالي. وفي مجموعة قاعدة البيانات تطبّق التغيير على قاعدة بياناتك المحلية للمعاينة وتكتبه في [ترحيل التوثيق](../sources/docs-migrations.md) الخاص بالفرع، فيبقى ينتقل داخل الـ pull request. ويُنسب كل تغيير إلى الوكيل، باسم عميل MCP الخاص به.

</Accordion>

<Accordion title="أدوات التأليف عبر الويب">

افتراضيًا لا تكتب نقطة النهاية عبر الويب أبدًا. ويمكن لمجموعة قاعدة البيانات أن تفتح أدوات التأليف للوكلاء البعيدين، للتغييرات التي تجري خارج الـ pull request، مثل وكيل يصلح خطأً مطبعيًا في توثيق الإنتاج:

```php
'sets' => [
    'handbook' => [
        'source' => ['driver' => 'database'],
        'mcp' => [
            'web' => [
                'middleware' => ['auth:sanctum'],
                'author_tools' => true,
            ],
        ],
    ],
],
```

لا تظهر الأدوات إلا حين يتحقق كل ما يلي: تستخدم المجموعة مصدر قاعدة البيانات، و `mcp.web.author_tools` مفعّل، والمستدعي مسجّل الدخول، والبوابة `manageLarecipe` تسمح له في هذه المجموعة. وكل نقطة نهاية مربوطة بمجموعتها: فالاستدعاء الذي يسمّي مجموعة أخرى يُرفض، فلا يكتب رمزٌ خاص بنقطة نهاية مجموعة ما في مجموعة أخرى أبدًا. وتقرأ `affected_pages` شجرة العمل لديك، لذا لا تقدّمها أي نقطة نهاية عبر الويب. وتتبع تغييرات هذه الأدوات [وضع المراجعة](../keeping-current/review-modes.md) الخاص بالمجموعة: مع `review: required` تنتظر على شكل مسودات حتى ينشرها شخص، ومع `review: auto` تُنشر فورًا، مع بقائها محفوظة كمراجعات ومنسوبة إلى صاحبها.

مجموعة المستودع لا تعرض أدوات التأليف عبر الويب أبدًا، مهما قالت الإعدادات. فمكان تغييراتها هو git، داخل pull request.

</Accordion>

<Accordion title="الملاحظات والأحداث">

تطلق `submit_feedback` الحدث `FeedbackSubmitted` نفسه الذي يطلقه عنصر «هل كانت هذه الصفحة مفيدة؟» في الصفحة، مع ضبط مصدره على `agent`، فيستطيع الوكيل أن يبلّغ عن صفحة ضلّلته. وكل استدعاء أداة، على أي من الخادمين، يطلق أيضًا `McpToolCalled` مع المجموعة والأداة ونوع النقل والوسائط البسيطة. ولا يخزّن LaRecipe أيًّا منهما؛ راجع [التحليلات والأحداث](../operate/analytics.md).

</Accordion>

<Accordion title="الموارد">

كل صفحة هي أيضًا مورد MCP، ومحتواه هو Markdown الخاص بها:

```text
docs://{set}/{version}/{path}
```

مثل `docs://docs/3.x/agents/mcp` في هذا الموقع. والمجموعة بلا إصدارات تستخدم `current` إصدارًا لها، والصفحة بلغة أخرى تضع رمز اللغة قبل مسارها (`docs://docs/3.x/ar/index`). ولا تقدّم نقطة النهاية عبر الويب إلا موارد مجموعتها.

</Accordion>

<Accordion title="القوالب الجاهزة (prompts)">

| القالب | الوسائط | ما يعطيه للوكيل |
| --- | --- | --- |
| `write-docs-page` | `topic`، و `path` (اختياري)، و `set` (اختياري) | خطوات كتابة صفحة جديدة: ابحث أولًا، واكتب من الكود، والترويسة مع `sources`، والمكوّنات، والتنقل، والتحقق |
| `document-change` | `change`، و `set` (اختياري) | خطوات تحديث الصفحات التي جعلها تغيير في الكود قديمة، في الـ push نفسه مع الكود |

</Accordion>

<Accordion title="تعليمات الخادم">

يحصل كل عميل على سير العمل نفسه حين يتصل. وهذا ما ترسله نقطة نهاية هذا الموقع:

```text
The documentation for LaRecipe. Read it to answer questions, and keep it current when the code changes.

How to work with these docs:
1. Search first: call search_docs before you read or write anything.
2. Read before you write: read_page returns a page as Markdown (pass section to read one heading). list_pages shows the navigation.
3. Validate after you write: run validate_docs and fix every error it reports.

Cite the page URLs you used. If the docs do not cover something, say so, and use submit_feedback to tell the owners when a page was wrong or missing.
```

يسمّي الخادم نفسه باسم المجموعة (`LaRecipe Docs` هنا). وفي الخادم المحلي مع عدة مجموعات، تنتهي التعليمات بسطر يسردها ويسمّي الافتراضية منها.

</Accordion>

<Accordion title="حدّ المعدّل">

كل نقطة نهاية عبر الويب يحدّها محدِّد المعدّل `larecipe-mcp`، المضبوط بـ `mcp.web.rate_limit` بصيغة `"requests,minutes"`. والقيمة الافتراضية `"60,1"` تسمح بـ 60 طلبًا في الدقيقة، تُحسب لكل مجموعة ولكل مستخدم مسجّل، أو لكل عنوان IP للزوار. وتحمل الاستجابات `X-RateLimit-Limit` و `X-RateLimit-Remaining`، والعميل الذي يتجاوز الحدّ يحصل على `429`. ويمكن لأي مجموعة أن تضبط حدّها الخاص في `sets.{key}.mcp.web.rate_limit`. أما الخادم المحلي فلا حدّ له.

</Accordion>

<Accordion title="الإعدادات">

القيم الافتراضية، من `config/larecipe.php`. ويمكن لكل مجموعة أن تغيّر أي مفتاح من `mcp.web` في إدخالها الخاص تحت `sets`.

```php
'mcp' => [
    'web' => [
        'enabled' => true,
        'path' => 'mcp',
        'author_tools' => false,
        'middleware' => [],
        'rate_limit' => '60,1',
    ],
    'local' => true,
],
```

إيقاف `mcp.web.enabled` يزيل المسار وإجراء **ربط MCP**؛ وإيقاف `mcp.local` يلغي تسجيل `larecipe` من `mcp:start`. وكل مفتاح موجود في [مرجع الإعدادات](../reference/configuration.md#mcp).

</Accordion>

</AccordionGroup>

## التالي {#next}

- [واجهات الوكلاء](surfaces.md): نسخ Markdown و `llms.txt` و `skill.md`.
- [اسأل الذكاء الاصطناعي (Ask AI)](ask-ai.md): الأداتان نفسهما `search_docs` و `read_page`، تجيبان قرّاءك.
- [مرجع أدوات MCP](../reference/mcp-tools.md): وسائط كل أداة ونتائجها.
