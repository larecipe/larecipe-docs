<?php

// The Features grid on the Introduction: a title and one line per feature, for
// its HTML and its Markdown form for agents. Each key's icon and page live in
// AppServiceProvider::FEATURES. Check a line against its page before you change it.

return [
    'current' => [
        'title' => 'Docs that stay current',
        'line' => 'Your coding agent updates the pages a change affects, in the same pull request.',
    ],
    'agents' => [
        'title' => 'Made for agents',
        'line' => 'Every page as Markdown, plus llms.txt and an MCP server.',
    ],
    'ask' => [
        'title' => 'Ask AI',
        'line' => 'Answers written only from your docs, with the pages they cite.',
    ],
    'references' => [
        'title' => 'Live references',
        'line' => 'Routes, commands, models and events, read from your running app.',
    ],
    'private' => [
        'title' => 'Private docs',
        'line' => 'Your app\'s own auth and gates decide who reads, down to one paragraph.',
    ],
    'languages' => [
        'title' => 'Every language',
        'line' => 'Right to left included, with a fallback for pages not translated yet.',
    ],
];
