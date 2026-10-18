<?php

// The InstallTabs component on the Introduction (resources/larecipe/InstallTabs.tsx holds the same words for React).
return [
    'label' => 'طريقة التثبيت',
    'agent_tab' => 'عبر وكيل البرمجة',
    'manual_tab' => 'يدويًا',
    'agent_lead' => 'الصق هذا الطلب في Claude Code أو Codex أو Cursor أو أي وكيل برمجة، داخل تطبيق Laravel الخاص بك.',
    'prompt_label' => 'الطلب',
    'copy_prompt' => 'انسخ الطلب',
    'prompt' => [
        'ثبّت LaRecipe في تطبيق Laravel هذا:',
        '',
        '1. شغّل `composer require larecipe/larecipe`.',
        '2. شغّل `php artisan larecipe:install --no-interaction` لتبقى القيم الافتراضية.',
        '3. شغّل `php artisan larecipe:doctor` وأصلح كل ما يذكره.',
        '4. اقرأ التطبيق واكتب توثيقًا أوليًا بمهارة larecipe-write-docs.',
        '',
        'ثم أخبرني برابط موقع التوثيق.',
    ],
    'manual_lead' => 'شغّل هذا في تطبيق Laravel الخاص بك.',
    'manual_markdown' => 'يدويًا: شغّل هذا في تطبيق Laravel الخاص بك. الصقه في الطرفية، اضغط Enter للقيم الافتراضية، ثم افتح `/docs`:',
    'steps' => ['الصقه في الطرفية', 'اضغط Enter للقيم الافتراضية', 'افتح `/docs`'],
];
