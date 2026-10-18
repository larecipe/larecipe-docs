<?php

// The DocsFlow diagram on the Introduction, in Arabic: its server fallback and
// its Markdown form for agents. Keep these in step with the strings in
// resources/larecipe/DocsFlow.tsx. Commits, paths and commands stay Latin.

return [
    'code' => 'كودك',
    'agent' => 'وكيلك يحدّث التوثيق',
    'agents_read' => 'يقرأ الوكلاء',
    'people_read' => 'يقرأ الناس',
    'code_and_docs' => 'كود وتوثيق',
    'new_command' => 'جديد',
    'title' => 'الاسترداد',
    'guide' => 'الدليل',
    'api' => 'API',
    'text' => 'استرد مبلغ فاتورة مدفوعة كاملًا أو جزءًا منه، من التطبيق أو بأمر واحد.',
    'updated_ago' => 'حُدّث قبل دقيقتين',
    'updated_by' => 'حدّثه Claude Code',
    'connected' => 'متصلون عبر MCP',
    'ago' => 'قبل دقيقتين',
    'ask' => 'اسأل الذكاء الاصطناعي…',
    'ask_short' => 'اسأل التوثيق…',
    'summary' => 'يتغيّر كودك، فيحدّث وكيل البرمجة صفحات التوثيق التي يمسّها التغيير في الـ pull request نفسه. يقرأ الناس موقع التوثيق، ويقرأ الوكلاء الصفحات نفسها بصيغة Markdown، وعبر llms.txt وخادم MCP.',
    'markdown' => "كيف يُبقي LaRecipe التوثيق محدّثًا:\n\n1. يتغيّر كودك في commit أو pull request.\n2. يحدّث وكيل البرمجة صفحات التوثيق التي يمسّها التغيير في الـ pull request نفسه، وتراجع الاثنين معًا.\n3. يقرأ الناس موقع التوثيق، ويقرأ الوكلاء الصفحات نفسها بصيغة Markdown، وعبر `llms.txt` وخادم MCP.",
];
