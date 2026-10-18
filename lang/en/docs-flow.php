<?php

// The DocsFlow diagram on the Introduction: its server fallback and its
// Markdown form for agents. Keep these in step with the strings in
// resources/larecipe/DocsFlow.tsx.

return [
    'code' => 'Your code',
    'agent' => 'Your agent updates the docs',
    'agents_read' => 'Agents read',
    'people_read' => 'People read',
    'code_and_docs' => 'code + docs',
    'new_command' => 'new',
    'title' => 'Refunds',
    'guide' => 'Guide',
    'api' => 'API',
    'text' => 'Refund a paid invoice in full or in part, from the app or with one command.',
    'updated_ago' => 'Updated 2m ago',
    'updated_by' => 'Updated by Claude Code',
    'connected' => 'Connected via MCP',
    'ago' => '2m ago',
    'ask' => 'Ask AI about the docs…',
    'ask_short' => 'Ask the docs…',
    'summary' => 'Your code changes. Your coding agent updates the docs pages the change affects, in the same pull request. People read the docs site, and agents read the same pages as Markdown, through llms.txt and over MCP.',
    'markdown' => "How LaRecipe keeps docs current:\n\n1. Your code changes, in a commit or a pull request.\n2. Your coding agent updates the docs pages the change affects, in the same pull request, and you review both together.\n3. People read the docs site. Agents read the same pages as Markdown, through `llms.txt` and over MCP.\n\nThe diagram follows one example. Your code: commit `a1f3c9e` (`feat: refund invoices`, +42 -7), pull request #412 \"Add invoice refunds\" (code + docs) and a new `artisan invoices:refund` command. Your agent updates the docs: the page `docs/billing/refunds.md`, titled Refunds (Guide and API tabs), now reads \"Refund a paid invoice in full or in part, from the app or with one command.\" with `php artisan invoices:refund 1042`, and shows \"Updated by Claude Code\" for PR #412. Agents read it connected via MCP or through `llms.txt`, and people read it on the site, where they can ask AI about the docs.",
];
