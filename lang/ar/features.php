<?php

// The Features grid on the Introduction, in Arabic: a title and one line per
// feature, for its HTML and its Markdown form for agents. Each key's icon and
// page live in AppServiceProvider::FEATURES. Product and file names stay Latin.

return [
    'current' => [
        'title' => 'توثيق يبقى محدّثًا',
        'line' => 'يحدّث وكيل البرمجة الصفحات التي يمسّها التغيير، في الـ pull request نفسه.',
    ],
    'agents' => [
        'title' => 'مصمَّم للوكلاء',
        'line' => 'كل صفحة بصيغة Markdown، مع llms.txt وخادم MCP.',
    ],
    'ask' => [
        'title' => 'اسأل الذكاء الاصطناعي',
        'line' => 'إجابات مكتوبة من توثيقك وحده، مع الصفحات التي تستشهد بها.',
    ],
    'references' => [
        'title' => 'المراجع الحية',
        'line' => 'المسارات والأوامر والنماذج والأحداث، تُقرأ من تطبيقك العامل.',
    ],
    'private' => [
        'title' => 'توثيق خاص',
        'line' => 'تحدّد المصادقة والبوابات في تطبيقك من يقرأ، حتى الفقرة الواحدة.',
    ],
    'languages' => [
        'title' => 'بكل لغة',
        'line' => 'مع الكتابة من اليمين إلى اليسار، وعرض الأصل للصفحات التي لم تُترجم بعد.',
    ],
];
