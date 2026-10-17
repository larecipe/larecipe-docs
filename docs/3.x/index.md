---
title: Docs that keep up with your code
description: LaRecipe lives inside your Laravel app. Your coding agents write the docs and update them with every change. People get a fast, beautiful site; agents get Markdown, llms.txt and MCP.
sources:
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/InstallCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/**
  - vendor/larecipe/larecipe/src/Domains/Agents/**
  - vendor/larecipe/larecipe/src/Domains/Upgrade/**
  - resources/larecipe/**
inserts: { article.after: false }
---

# Docs that keep up with your code

<Video title="The maintainer explains LaRecipe" />

**Push code. The docs follow.** Every page lists the code it describes. When a change leaves a page stale, the coding agent that made the change updates it [before it pushes](keeping-current/pre-push-hook.md), so the docs land in the same pull request as the code. You review both together, or let them through in [auto mode](keeping-current/review-modes.md).

## Install in two commands

```bash
composer require larecipe/larecipe
php artisan larecipe:install
```

Open `/docs` and your docs are there, starting with an Introduction page built from facts about your app. No Node, no build step. The installer also sets up your coding agents: instructions in `AGENTS.md`, four skills, the MCP server and a pre-push hook, so the docs stay current from the first push. [Installation](get-started/installation.md) walks through every step and flag.

The installer can also hand your app to your own coding agent, which writes real starter docs for the readers you pick: product users, API consumers, package users or your own team. See [Your first docs](get-started/your-first-docs.md).

## The loop

LaRecipe's first README asked whether writing documentation was boring. Version 3 has a better answer: the agent that changed the code writes the docs, and you sign them off.

<CardGroup cols="2">

<Card title="Write" href="get-started/your-first-docs.md">
Your coding agent writes pages from the code it read a moment ago, guided by four LaRecipe skills, MCP tools and a validator that catches broken links and stale samples.
</Card>

<Card title="Keep current" href="keeping-current/how-it-works.md">
Each page lists the code it describes. Before every push, your agent updates the pages your change left stale, and a hook holds the push once if it forgot.
</Card>

<Card title="Serve" href="agents/surfaces.md">
People get a fast site that reads fine without JavaScript. Agents get every page as Markdown, plus llms.txt, skill.md and an MCP server, under the same access rules.
</Card>

<Card title="Own" href="sources/repository.md">
Docs live in your repository or your database, behind your own gates, on your own deploy. Open source, your agent session or your keys, no meters.
</Card>

</CardGroup>

## Every reader

Most docs readers are now agents, so every page here is written once and served twice: as this page for people, and as Markdown for agents. This very page is also:

- **Markdown**: [index.md](https://larecipe.saleem.dev/3.x/index.md), or this URL requested with `Accept: text/markdown`.
- **In llms.txt**: [the root llms.txt](/llms.txt) points agents at each version, and [the 3.x llms.txt](/3.x/llms.txt) lists every page of this one.
- **On MCP**: connect your agent and it can search and read these docs while it works.
- **A skill**: [skill.md](https://larecipe.saleem.dev/skill.md) gives an agent a map of these docs and how to reach them.

```bash
claude mcp add --transport http larecipe https://larecipe.saleem.dev/mcp
```

[Agent surfaces](agents/surfaces.md) and [the MCP server](agents/mcp.md) explain each one, and your own docs get all of them the moment you install.

## Upgrading from v2

One command converts a LaRecipe 2 site: config, sidebar and pages. It starts as a dry run and writes a report of what it changed and what is left for you.

```bash
php artisan larecipe:upgrade
php artisan larecipe:upgrade --apply
```

Then the `larecipe-upgrade-v2` skill works through the report with your coding agent: custom Vue components, theme CSS, Blade inside Markdown. The [upgrade guide](upgrade.md) has the details. The [2.x docs](/2.x) on this site went through exactly this.

## Stay in the loop

The form below is a custom React component this site adds to LaRecipe, the same way you would add one to yours. [See how it is built](customize/custom-components.md).

<Subscribe list="larecipe" />
