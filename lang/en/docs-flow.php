<?php

// The DocsFlow diagram on the Introduction: its server fallback and its
// Markdown form for agents. Keep these in step with the strings in
// resources/larecipe/DocsFlow.tsx.

return [
    'code' => 'Your code changes',
    'agent' => 'Your agent updates the docs',
    'agents_read' => 'Agents read',
    'people_read' => 'People read',
    'stale' => 'stale',
    'stale_why' => 'Its source changed',
    'title' => 'Refunds',
    'guide' => 'Guide',
    'api' => 'API',
    'text' => 'Refund a paid invoice in full or in part, from the app or with one command.',
    'updated_ago' => 'Updated 2m ago',
    'updated_by' => 'Updated by Claude Code',
    'pr_code' => 'code',
    'pr_docs' => 'docs',
    'merged' => 'Merged',
    'connected' => 'Connected via MCP',
    'ago' => '2m ago',
    'ask' => 'Ask AI about the docs…',
    'ask_short' => 'Ask the docs…',
    'summary' => 'Your code changes, and LaRecipe finds the docs pages that list that code as their source. Your coding agent updates them before it pushes, so the code and the docs land in the same pull request and merge together. Then people read the docs site, and agents read the same pages as Markdown, through llms.txt and over MCP.',
    'markdown' => "How LaRecipe keeps docs current:\n\n1. Your code changes, in a commit.\n2. LaRecipe finds the docs pages that list that code as their source, and your coding agent updates them before it pushes.\n3. The code and the docs land in the same pull request. You review them together, and they merge together.\n4. Then people read the docs site. Agents read the same pages as Markdown, through `llms.txt` and over MCP.\n\nThe diagram follows one example. Your code changes: commit `a1f3c9e` (`feat: refund invoices`, +42 -7), so `refunds.md` is stale because its source changed. Your agent updates the docs: the page `docs/billing/refunds.md`, titled Refunds (Guide and API tabs), now reads \"Refund a paid invoice in full or in part, from the app or with one command.\" with `php artisan invoices:refund 1042`, and shows \"Updated 2m ago\" and \"Updated by Claude Code\" for PR #412. Pull request #412 \"Add invoice refunds\" holds both, code + docs, and they are merged together. Agents read the page connected via MCP or through `llms.txt`, and people read it on the site, where they can ask AI about the docs.",
];
