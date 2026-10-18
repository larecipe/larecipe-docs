<?php

// The InstallTabs component on the Introduction (resources/larecipe/InstallTabs.tsx holds the same words for React).
return [
    'label' => 'How to install',
    'agent_tab' => 'With your AI agent',
    'manual_tab' => 'Manual',
    'agent_lead' => 'Paste this prompt into Claude Code, Codex, Cursor or any coding agent, inside your Laravel app.',
    'prompt_label' => 'Prompt',
    'copy_prompt' => 'Copy prompt',
    'prompt' => [
        'Install LaRecipe in this Laravel app:',
        '',
        '1. Run `composer require larecipe/larecipe`.',
        '2. Run `php artisan larecipe:install --no-interaction` to keep the defaults.',
        '3. Run `php artisan larecipe:doctor` and fix anything it reports.',
        '4. Read the app and write starter docs with the larecipe-write-docs skill.',
        '',
        'Then tell me the URL of the docs site.',
    ],
    'manual_lead' => 'Run this in your Laravel app.',
    'manual_markdown' => 'Manual: run this in your Laravel app. Paste it in your terminal, press Enter for the defaults, then open `/docs`:',
    'steps' => ['Paste it in your terminal', 'Press Enter for the defaults', 'Open `/docs`'],
];
