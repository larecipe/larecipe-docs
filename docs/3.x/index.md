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

You write pages in Markdown, in your own repository, next to the code they describe. People read them as a fast site, and agents read the same pages as Markdown, through `llms.txt` and an MCP server.

Every page lists the code it describes. When that code changes, the coding agent that changed it updates the page before it pushes, and a hook holds the push once if it forgets. The docs land in the same pull request as the code, and you review them together.

<DocsFlow />

## Install in two commands

<InstallTabs />

There is no Node and no build step. The installer also sets up the coding agents you already use, such as Claude Code, Codex and Cursor, so there is nothing to remember day to day.

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

<Subscribe list="larecipe" showcase />
