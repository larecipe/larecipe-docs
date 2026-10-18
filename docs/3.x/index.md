---
title: Docs that keep up with your code
description: LaRecipe is a docs site that lives inside your Laravel app. Your coding agent writes the pages and updates them with every change, so people and agents always read the current answer.
sources:
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/InstallCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Services/IntroductionPage.php
  - vendor/larecipe/larecipe/src/Domains/Sync/**
  - vendor/larecipe/larecipe/src/Domains/Agents/**
  - resources/larecipe/**
inserts: { article.after: false }
---

# Docs that keep up with your code

<Video title="The maintainer explains LaRecipe" />

LaRecipe is a docs site that lives inside your Laravel app. You write pages in Markdown, in your own repository, next to the code they describe. People read them as a fast site that works without JavaScript. Agents read the same pages as Markdown, through `llms.txt` and an MCP server.

## Install in two commands

```bash
composer require larecipe/larecipe
php artisan larecipe:install
```

Open `/docs` in your browser. Your docs are already there, starting with an Introduction page written from facts about your app. There is no Node and no build step.

## Push code, the docs follow

Every page lists the code it describes. When you change that code, the coding agent that made the change also updates the page before it pushes. The docs land in the same pull request as the code, and you review them together.

The installer sets this up for the coding agents you already use, such as Claude Code, Codex and Cursor. There is nothing to remember day to day.

## Next

<CardGroup cols="2">

<Card title="Installation" href="get-started/installation.md" icon="rocket">
Requirements, the installer's questions and the files it writes.
</Card>

<Card title="Your first docs" href="get-started/your-first-docs.md" icon="pen-line">
Let your coding agent read your app and write starter pages.
</Card>

<Card title="Write a page" href="writing/markdown.md" icon="book-open">
Markdown, components and code samples from your app.
</Card>

<Card title="How it works" href="keeping-current/how-it-works.md" icon="refresh-cw">
How LaRecipe finds the pages a change left stale.
</Card>

</CardGroup>

Moving a site from LaRecipe 2? Read [Upgrade from v2](upgrade.md).

## Stay in the loop

The form below is a custom React component this site adds to LaRecipe. [See how it is built](customize/custom-components.md).

<Subscribe list="larecipe" />
