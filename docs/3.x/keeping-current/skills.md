---
title: Skills and the Claude Code plugin
description: The four LaRecipe skills that teach any coding agent to write, update, audit and upgrade your docs, where the installer puts them, and the Claude Code plugin that ships them with the hooks.
sources:
  - vendor/larecipe/larecipe/resources/boost/skills/**
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/SkillReferences.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/InstallSkillsAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/AgentsCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/DetectBoostAction.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/ComponentRegistry.php
  - vendor/larecipe/larecipe/resources/schemas/**
  - vendor/larecipe/larecipe/.claude-plugin/marketplace.json
  - vendor/larecipe/larecipe/.claude-plugin/plugin.json
  - vendor/larecipe/larecipe/claude-plugin/**
---

# Skills and the Claude Code plugin

A skill is a folder of instructions an agent loads when a task calls for it. LaRecipe ships four, one for each docs job. They work in Claude Code, Codex, Cursor, Copilot, Gemini CLI and any other agent that reads the Agent Skills format.

| Skill | Its job | Agents reach for it when |
| --- | --- | --- |
| `larecipe-write-docs` | Write new pages from the app's code, and run the onboarding that writes your [first docs](../get-started/your-first-docs.md). | Asked to document a feature, an API, a command or part of the codebase. |
| `larecipe-update-docs` | Update what a change made stale, keep every unrelated sentence, and keep translations current. | Before every push, or when the [pre-push hook](pre-push-hook.md) held a push. |
| `larecipe-audit-docs` | Review a whole set and produce a prioritized list of fixes. | Asked to audit the docs, or before a release. |
| `larecipe-upgrade-v2` | Finish a LaRecipe 2 upgrade after `php artisan larecipe:upgrade`. | `larecipe-upgrade-report.md` exists, or the docs still carry v2 leftovers. |

`php artisan larecipe:install` copies them into `.claude/skills/`, where Claude Code looks, and `.agents/skills/`, the shared folder other agents read. Commit both, so every teammate's agent has them. After a package update, or after you add a component, re-sync them:

```bash
php artisan larecipe:agents
```

It updates the folders that already hold LaRecipe's skills, or both default folders when none do. The installer can add it to Composer's `post-update-cmd`, so the skills never fall behind; see [agent instructions](agent-instructions.md#regenerate-after-a-change).

## What every skill teaches

The same few rules run through all four: run `larecipe:affected` before pushing and update what it lists, check the existing docs before writing, set `sources:` on every page, write for people first, never write configuration or environment values into docs, run `larecipe:validate`, and follow the review mode in `AGENTS.md`. On a database set they write through the LaRecipe MCP author tools, which record the change in the branch's docs migration, and never edit rows or write SQL.

The [`AGENTS.md` block](agent-instructions.md) and the pre-push hook both point agents at `larecipe-update-docs`, so the skill does the work at the moment it matters.

## The Claude Code plugin

The LaRecipe package repository is also a Claude Code plugin marketplace. The plugin bundles the four skills, the pre-push and SessionStart hooks, a `/larecipe:onboard` command and the local `larecipe` MCP server. Install it from inside Claude Code:

```text
/plugin marketplace add saleem-hadad/larecipe
/plugin install larecipe@larecipe
```

The files `larecipe:install` writes are still the recommended setup: read the plugin's caveats below before you choose it.

## Details

<AccordionGroup>

<Accordion title="What each skill does, step by step">

- **`larecipe-write-docs`** reads the code first, searches the existing docs, sets `sources:`, prefers live references to pasted values, adds the page to the navigation and runs `larecipe:validate`. It also holds the onboarding workflow and its audience presets.
- **`larecipe-update-docs`** runs `larecipe:affected`, changes only what the code change made wrong, keeps every unrelated sentence, validates, and summarizes the docs change in one line. It also carries the translation workflow.
- **`larecipe-audit-docs`** looks for drifted pages, pages without `sources:`, weak descriptions, undocumented routes, commands, models and events, and broken samples. It sorts its findings as P1 (wrong or unsafe), P2 (missing) and P3 (weak), and fixes only what you ask.
- **`larecipe-upgrade-v2`** works through custom Vue components, theme CSS, Blade inside Markdown and everything the upgrade report flags as manual. See [Upgrade from v2](../upgrade.md).

</Accordion>

<Accordion title="Portable frontmatter">

Each `SKILL.md` uses only the portable Agent Skills keys: `name`, `description`, and at most `license`, `compatibility`, `metadata` and `allowed-tools`. No agent-specific keys, so every agent reads them the same way. This is `larecipe-write-docs`:

```yaml
---
name: larecipe-write-docs
description: Write new LaRecipe docs pages from this app's code. Use when asked to document a feature, an API, an artisan command or part of the codebase, to add a page or section to the docs, or to run LaRecipe onboarding (starter docs for product users, API consumers, package users or an internal team).
license: MIT
compatibility: A Laravel app with larecipe/larecipe installed, so php artisan larecipe:* works. The larecipe MCP server is recommended and required for database doc sets.
metadata:
  author: larecipe
  version: "3.0"
---
```

Each `SKILL.md` stays under 500 lines. The details live in a `references/` folder the agent opens only when it needs them.

</Accordion>

<Accordion title="References generated from your app">

A skill that describes a component or a key that does not exist teaches an agent to write broken pages. So the references that describe LaRecipe's dialect are generated from code by `larecipe:agents`, not written by hand:

| Reference | Generated from | In the skills |
| --- | --- | --- |
| `components.md` | The component registry: every built-in component, the live references, and the components your app registers with `LaRecipe::component()`, each with its props, allowed values and defaults | All four |
| `frontmatter.md` | The frontmatter JSON Schema | All four |
| `manifest.md` | The `larecipe.json` JSON Schema | Write, audit, upgrade |
| `navigation.md` | The `navigation.json` JSON Schema | Write, audit, upgrade |

Your own components appear in the catalog marked "(defined by this app)", so an agent writing your docs knows they exist and what their props are. Each generated file starts with a line saying it was generated by `php artisan larecipe:agents` and should not be edited.

The other references are written by hand and ship as they are: `writing-style.md`, `live-references.md` and `database-sets.md` (kept identical across the skills that carry them), the onboarding presets in `larecipe-write-docs`, the translation workflow in `larecipe-update-docs`, and the v2 mapping in `larecipe-upgrade-v2`.

</Accordion>

<Accordion title="With Laravel Boost">

When `laravel/boost` is installed, Boost distributes skills for the whole app, and LaRecipe writes no copies of its own. The skills ship from the package's `resources/boost/skills/` folder: run `php artisan boost:install` (or `boost:update`) and select `larecipe/larecipe`, and Boost copies them into each agent's folder.

`larecipe:agents` still matters with Boost. It finds the copies Boost wrote (in `.claude/skills`, `.agents/skills`, `.github/skills`, `.cursor/skills` and the other folders Boost uses) and regenerates their references, so your own components show up there too. See [agent instructions](agent-instructions.md#with-laravel-boost).

</Accordion>

<Accordion title="The plugin: what it ships, and its caveats">

The plugin bundles:

- the four skills, from `resources/boost/skills/`;
- the pre-push and SessionStart hooks, from `claude-plugin/hooks.json`;
- a `/larecipe:onboard` command, from `claude-plugin/commands/onboard.md`, which runs the onboarding workflow of `larecipe-write-docs` for the audiences you name (`product`, `api`, `package`, `internal`);
- the local `larecipe` MCP server.

To have every teammate's Claude Code offer it, register the marketplace and enable the plugin in the project's `.claude/settings.json`:

```json
{
  "extraKnownMarketplaces": {
    "larecipe": {
      "source": { "source": "github", "repo": "saleem-hadad/larecipe" }
    }
  },
  "enabledPlugins": {
    "larecipe@larecipe": true
  }
}
```

The plugin's hooks run the plugin's own copy of `larecipe-hook`, which then runs `php artisan larecipe:hook` in your app. The plugin is installed for you, not for one project, so the hook does nothing in a project without LaRecipe: it looks for the nearest Laravel app, stops at the git repository's root, and acts only when that app has `larecipe/larecipe` installed or required. Elsewhere it lets every command through.

Three things to know before you choose the plugin over the installer's files:

- **The installer's files come first.** The plugin starts the MCP server with `php ${CLAUDE_PROJECT_DIR}/artisan larecipe:mcp`, a route that is not yet verified end to end. The `.mcp.json` that `larecipe:install` writes is the recommended way to connect the server.
- **Its references come from the package, not from your app.** The plugin's skills never list your own components. Install the skills into the repository as well when you have custom components.
- **One set of hooks is enough.** With the plugin's hooks on, install with `larecipe:install --no-hooks` so the check does not run twice on every push.

</Accordion>

</AccordionGroup>

## Next

- [Agent instructions](agent-instructions.md): the block that tells agents when to use the skills.
- [Review modes](review-modes.md): whether the agent asks before it commits.
- [Custom React components](../customize/custom-components.md): components that appear in the skills' catalog.
