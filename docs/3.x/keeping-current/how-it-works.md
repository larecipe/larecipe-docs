---
title: How it works
description: Every page lists the code it describes, larecipe:affected maps each change to the pages it made stale, and your coding agent updates them in the same push as the code.
sources:
  - vendor/larecipe/larecipe/src/Domains/Sync/**
  - vendor/larecipe/larecipe/config/larecipe.php
---

# How it works

Every page lists the code it describes in its `sources:` frontmatter. When code changes, `php artisan larecipe:affected` maps the changed files to the pages that describe them, with no AI and no network. The coding agent that made the change then updates those pages before it pushes, so the docs land in the same pull request as the code.

You do not run any of this by hand. The installer sets it up, and your agent follows it.

```text
Change the code
   ↓
Your agent updates the affected pages in the same pull request
   ↓
You review them with the code (or let them through, in auto mode)
   ↓
Every reader, human or agent, gets the current answer
```

## Declare what a page documents

List the code a page documents as paths or globs from the project root:

```yaml
---
title: Webhooks
description: Receive signed events from the app and verify them.
sources:
  - app/Webhooks/**
  - routes/webhooks.php
---
```

A page without `sources:` is invisible to drift detection. [`larecipe:sources:suggest`](#find-sources-for-pages-that-have-none) proposes them from what the page already mentions.

<Accordion title="Glob syntax and regions">

- `*` stays inside one folder, `**` crosses folders, `?` is one character, `{a,b}` picks one of several and `[abc]` is a character class: the dialect of `.gitignore` and GitHub Actions.
- A path without wildcards matches that file, or everything inside it when it names a folder. `app/Billing` and `app/Billing/**` are the same.
- `path#region` narrows a source to the lines between `#region webhooks` and the next `#endregion` in that file, behind any comment prefix (`// #region webhooks`). Write it as `routes/api.php#webhooks`. The page is affected only when a change touches those lines. When LaRecipe cannot find the region or the changed lines, the whole file counts: a false "stale" costs a look, a false "fine" costs a wrong page.
- Changes inside the docs folder never count as code changes for other pages.

</Accordion>

## Docs ship with the code

Docs ship in the same push as the code they describe: as Markdown files for a repository set, as a [docs migration](../sources/docs-migrations.md) for a database set. There is no separate docs pull request and no AI agent running in CI.

The reason is context. The agent that changed the code knows exactly what changed and why, right now. In an hour, or in a follow-up pull request written by someone else, that context is gone.

## The layers

Four layers, each optional above the first. All of them ask the same question of the same code, so they never disagree about which pages are stale.

| Layer | What it is | When it runs | Writes |
| --- | --- | --- | --- |
| L0, the deterministic core | `sources:` frontmatter, `larecipe:affected`, `larecipe:validate` | Always on | Nothing |
| L1, instructions in the repo | The LaRecipe block in `AGENTS.md` (or the Laravel Boost guideline), the four skills, the local MCP server | At the start of every AI coding session | The agent writes the docs |
| L2, the pre-push check | An agent hook on `git push` and `gh pr create` that holds the push once and lists the stale pages; an optional git hook for people | When a change is about to leave the machine | Nothing |
| L3, the optional CI check | `larecipe:validate` and `larecipe:affected` on each pull request | On pull requests | Nothing |

- **L1** is plain text every AI session reads. See [agent instructions](agent-instructions.md) and [skills](skills.md).
- **L2** catches the moment an agent forgets, and never calls an LLM. See [the pre-push hook](pre-push-hook.md).
- **L3** is a safety net for pushes made without an agent. See [the CI check](ci-check.md).

The agent writes the docs because it has the context. Nothing downstream writes them for it. To have an agent draft an update without a local session, run [`larecipe:sync`](sync.md) on demand.

<Accordion title="Recommended setups">

| Team | Turn on | Install with |
| --- | --- | --- |
| Solo developer with Claude Code or Codex | L0, L1, L2 (agent hook) | `php artisan larecipe:install` |
| Team with pull request review | L0, L1, L2 (agent hook, plus the git hook for people who push without an agent), L3 in comment or fail mode | `php artisan larecipe:install --git-hook=warn --ci=comment` |
| No local coding agents | L0, L2 (git hook), L3; `larecipe:sync` on demand when someone wants an agent to draft the update | `php artisan larecipe:install --agents=none --git-hook=warn --ci=fail` |
| Database set | L0, L1, L2 (the local MCP server writes docs migrations into the same push), L3 | `php artisan larecipe:install --driver=database --ci=fail` |

Every flag is explained in [installation](../get-started/installation.md). Add a layer later by running the installer again, or the command for that layer.

</Accordion>

## larecipe:affected

`larecipe:affected` answers one question: which docs pages does this change touch, and did the change update them? This is what your agent runs before it pushes:

```bash
php artisan larecipe:affected --uncommitted --base=main
```

```text
LaRecipe: 4 files changed since main, plus uncommitted work.

Stale (the code changed, the page did not):
  - docs/billing/webhooks.md   (sources: app/Webhooks/**)

Updated in this change:
  - docs/billing/refunds.md   (sources: app/Billing/Refund.php)

Drifted earlier (what they document changed after they were last updated):
  - docs/installation.md   its sources changed in 3f2a9c1 (2026-10-02): Require PHP 8.4

Update the stale pages with the larecipe-update-docs skill, then run php artisan larecipe:validate.
```

- **Stale**: a changed file matches the page's `sources:`, and the page itself did not change in the same range. These are the pages to update.
- **Updated**: the page changed in the same range as its code: its Markdown file, or, for a database set, a changed docs migration that names it.
- **Drifted earlier**: pages this change does not touch, whose sources changed in a commit after the page was last updated. LaRecipe names that commit, so you can see when the page fell behind. `--no-drift` skips this part.

The same check is the `affected_pages` tool on the local [MCP server](../agents/mcp.md).

<AccordionGroup>

<Accordion title="Choose the range">

| You run | It compares |
| --- | --- |
| `larecipe:affected` | Uncommitted work only: staged, unstaged and untracked files. |
| `larecipe:affected --base=main` | The commits on this branch since it left `main` (`main...HEAD`), without uncommitted work. |
| `larecipe:affected --base=main --uncommitted` | Both. This is what your agent runs before it pushes. |
| `larecipe:affected --base=main --head=feature/refunds` | The commits on another branch, without checking it out. |

When the base is not a known branch or commit, the report says so and suggests `git fetch origin`. In CI the base is the pull request's target, `--base=origin/${{ github.base_ref }}`.

</Accordion>

<Accordion title="Formats: text, json, github and markdown">

`--format` picks who reads the report:

- `text` (the default): for people, as above.
- `json`: for programs and agents. A `summary` with counts, `changed_files`, each affected page with its `status` (`stale` or `updated`), `reason`, `matched_sources` and `changed_files`, the `drift` list with each page's `stale_since` commit, and any `notes`.
- `github`: GitHub Actions annotations. Each stale page becomes a warning on its file (an error with `--fail-on-stale`), drift becomes a notice, and inside a GitHub Actions run the command also adds a Markdown report to the job summary and sets a `stale` step output.
- `markdown`: a pull request comment, with a table of stale pages, the sources that matched and the files that changed. It also sets the `stale` step output.

An abridged `json` report (each page also carries its `version`, `locale` and full `sources`):

```json
{
    "summary": { "changed_files": 4, "affected": 2, "stale": 1, "updated": 1, "drift": 1 },
    "pages": [
        {
            "set": "docs",
            "path": "billing/webhooks",
            "title": "Webhooks",
            "file": "docs/billing/webhooks.md",
            "status": "stale",
            "reason": "sources",
            "matched_sources": ["app/Webhooks/**"],
            "changed_files": ["app/Webhooks/Handler.php"]
        }
    ]
}
```

`--fail-on-stale` makes the command exit with `1` when the change leaves any page stale. The [CI check](ci-check.md) uses it in fail mode.

</Accordion>

<Accordion title="Sets, versions and translations">

- `--set=handbook` checks one doc set instead of all of them.
- `--docs-version=2.x` checks another version, and `--docs-version=all` checks every version. By default each set's default version is checked.
- `--locale=ar` checks a language's pages against the default language:

```bash
php artisan larecipe:affected --uncommitted --base=main --locale=ar
```

A translation is stale when the code it documents changed (a translation without `sources:` of its own uses its original's), or when its original page changed in the range and the translation did not. Under drift, it lists translations whose original changed in a commit after the translation was last updated. See [languages](../writing/languages.md).

</Accordion>

<Accordion title="Database sets">

A database set's pages are rows, so `larecipe:affected` reads their `sources:` from the local database. "Updated" means a changed docs migration in the migrations folder names the page, and for drift, "last updated" is the page's own update time. When the local database has no pages, the report says so. Refresh it from a production export with `larecipe:pull` (`--from` takes a bundle made on production with `larecipe:source:export`). See [docs migrations](../sources/docs-migrations.md#start-from-what-is-live).

</Accordion>

<Accordion title="Every option">

Read live from this site's command registry:

<Artisan command="larecipe:affected" />

</Accordion>

</AccordionGroup>

## Find sources for pages that have none

`larecipe:sources:suggest` proposes `sources:` for pages that declare none, from the classes, commands and routes they mention. Add `--apply` to write them into the pages:

```bash
php artisan larecipe:sources:suggest
```

<Accordion title="What it reads, and its options">

It looks for:

- fully qualified class names in inline code, such as `App\Billing\Invoice` or `App\Billing\Invoice::refund()`;
- artisan commands in inline code (`orders:prune`, `php artisan orders:prune`) and in shell code blocks;
- route names, as `route('orders.store')` or a bare `orders.store`;
- live references: `<Route name="...">`, `<Artisan command="...">`, `<Model name="...">` (short names in `App\Models`) and `<Event name="...">` (short names in `App\Events`).

It asks the running app where each one is defined (Composer's class map, the console kernel, the router) and keeps only files inside your project, never `vendor/`. Without `--apply` it prints its suggestions:

```text
docs/billing/refunds.md
  sources: ["app/Console/Commands/PruneRefunds.php","app/Http/Controllers/RefundController.php"]
    app/Console/Commands/PruneRefunds.php  `refunds:prune`
    app/Http/Controllers/RefundController.php  <Route name="refunds.store" />
```

- `--apply` adds the suggestions to repository pages that declare no sources yet. It inserts one `sources:` line and leaves the rest of the frontmatter exactly as it was. `--dry-run` shows what it would change.
- `--page=billing/refunds` limits it to one page, `--set` to one doc set, and `--all` also suggests for pages that already declare sources.
- `--json` prints the suggestions for an agent.
- It reads each set's default version and language. `--apply` never edits database pages; update their `sources:` through the MCP author tools.

<Artisan command="larecipe:sources:suggest" />

</Accordion>

## Next

- [Agent instructions](agent-instructions.md): the `AGENTS.md` block every AI session reads.
- [The pre-push hook](pre-push-hook.md): what happens when an agent pushes stale docs.
- [The CI check](ci-check.md): go deeper with a safety net for pushes made without an agent.
