<?php

// The DocsFlow diagram on the Introduction, in Arabic: its server fallback and
// its Markdown form for agents. Keep these in step with the strings in
// resources/larecipe/DocsFlow.tsx. Commits, paths and commands stay Latin.

return [
    'code' => 'يتغيّر كودك',
    'agent' => 'وكيلك يحدّث التوثيق',
    'agents_read' => 'يقرأ الوكلاء',
    'people_read' => 'يقرأ الناس',
    'stale' => 'قديمة',
    'stale_why' => 'تغيّر مصدرها',
    'title' => 'الاسترداد',
    'guide' => 'الدليل',
    'api' => 'API',
    'text' => 'استرد مبلغ فاتورة مدفوعة كاملًا أو جزءًا منه، من التطبيق أو بأمر واحد.',
    'updated_ago' => 'حُدّث قبل دقيقتين',
    'updated_by' => 'حدّثه Claude Code',
    'pr_code' => 'كود',
    'pr_docs' => 'توثيق',
    'merged' => 'دُمج',
    'connected' => 'متصلون عبر MCP',
    'ago' => 'قبل دقيقتين',
    'ask' => 'اسأل الذكاء الاصطناعي…',
    'ask_short' => 'اسأل التوثيق…',
    'summary' => 'يتغيّر كودك، فيجد LaRecipe صفحات التوثيق التي تذكر ذلك الكود مصدرًا لها. ويحدّثها وكيل البرمجة قبل أن يرفع عمله، فيصل الكود والتوثيق في الـ pull request نفسه ويُدمجان معًا. ثم يقرأ الناس موقع التوثيق، ويقرأ الوكلاء الصفحات نفسها بصيغة Markdown، وعبر llms.txt وخادم MCP.',
    'markdown' => "كيف يُبقي LaRecipe التوثيق محدّثًا:\n\n1. يتغيّر كودك في commit.\n2. يجد LaRecipe صفحات التوثيق التي تذكر ذلك الكود مصدرًا لها، ويحدّثها وكيل البرمجة قبل أن يرفع عمله.\n3. يصل الكود والتوثيق في الـ pull request نفسه، فتراجعهما معًا ويُدمجان معًا.\n4. ثم يقرأ الناس موقع التوثيق، ويقرأ الوكلاء الصفحات نفسها بصيغة Markdown، وعبر `llms.txt` وخادم MCP.\n\nيتتبّع المخطط مثالًا واحدًا. «يتغيّر كودك»: الـ commit ‏`a1f3c9e` (`feat: refund invoices`)، فتصبح `refunds.md` «قديمة» لأنه «تغيّر مصدرها». ثم «وكيلك يحدّث التوثيق»: صفحة `docs/billing/refunds.md` بعنوان «الاسترداد» (تبويبا «الدليل» و API) تقول: «استرد مبلغ فاتورة مدفوعة كاملًا أو جزءًا منه، من التطبيق أو بأمر واحد.» مع `php artisan invoices:refund 1042`، وعليها «حُدّث قبل دقيقتين» و«حدّثه Claude Code» في PR #412. ويضمّ الـ pull request ‏#412 «Add invoice refunds» الاثنين معًا، «كود» + «توثيق»، وقد «دُمج» بهما معًا. ثم «يقرأ الوكلاء» الصفحة وهم «متصلون عبر MCP» أو عبر `llms.txt`، و«يقرأ الناس» الموقع، حيث يكتبون «اسأل الذكاء الاصطناعي» أو «اسأل التوثيق».",
];
