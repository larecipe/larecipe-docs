---
title: Your first docs
description: Let the coding agent you already use read your app and write real starter docs for the readers you choose, then review them before anything is committed.
sources:
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/OnboardCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/InstallCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Enums/Audience.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Enums/Agent.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/DetectAgentsAction.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/BuildOnboardingCommandAction.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/RunOnboardingAction.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/ValidateWrittenPagesAction.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/DTOs/OnboardingRequest.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/DTOs/AgentInvocation.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Services/AgentOutput.php
  - vendor/larecipe/larecipe/resources/boost/skills/larecipe-write-docs/SKILL.md
  - vendor/larecipe/larecipe/resources/boost/skills/larecipe-write-docs/references/onboarding.md
---

# Your first docs

The installer gives you a working site with one real page. Onboarding gives you the rest of a first draft: it hands your app to the coding agent you already use, which reads the code and writes starter pages for the readers you pick, such as product users, API consumers or your own team. The pages land in your working tree, uncommitted, so you review them like any other change.

Onboarding is optional. It runs on your own agent session, so it needs no API key in the app. Run it when you install, or any time later, for example when you add an audience.

## Run it

```bash
php artisan larecipe:onboard --audience=product,api
```

You can also start it from the installer. Interactively, `larecipe:install` asks "Generate starter docs from your app with your coding agent now?" as its last question, when it finds an agent installed. Or answer up front:

```bash
php artisan larecipe:install --onboard=api --onboard-agent=claude
```

Here is what happens, in order:

<Steps>

<Step title="It picks the doc set and the audiences">

The pages go into the default doc set unless you pass `--set=handbook`. Without `--audience`, it asks who the docs are for.

</Step>

<Step title="It finds your agent">

It looks for an agent CLI on your `PATH` and picks it. With several installed, it asks which one should write. `--agent=codex` chooses for you.

</Step>

<Step title="It prepares the agent">

It makes sure the LaRecipe skills are installed in `.claude/skills/` and `.agents/skills/` (with Laravel Boost, Boost ships them instead). For a [database set](../sources/database.md), it also adds the local `larecipe` MCP server to that agent's config, because the agent writes through it.

</Step>

<Step title="It shows the exact command and asks">

You see the full command before anything runs, and LaRecipe asks "Run it now?". Nothing runs until you say yes.

</Step>

<Step title="The agent writes, you watch">

The agent's progress streams into your terminal: the files it reads, the pages it writes, and what it says along the way.

</Step>

<Step title="It validates and lists the pages">

When the agent finishes, LaRecipe lists every page it created or updated, then runs `php artisan larecipe:validate` on the set.

</Step>

</Steps>

## Choose the audiences

Each audience is a preset in the `larecipe-write-docs` skill: an outline, a tone, what to read in your code and what never to write. Combine them with commas, for example `--audience=api,internal`.

| Audience | For | Pages it plans | What it reads |
| --- | --- | --- | --- |
| `product` | People who use the product, not its code | What the product does, getting started, one page per main feature, FAQ | Routes and controllers behind user-facing features, Blade or Inertia pages, form validation messages, existing help text, the README |
| `api` | Developers who call your HTTP API | Authentication; conventions (pagination, errors, rate limits, versioning); one page per resource with a `<Route>` reference for each endpoint; webhooks; changelog or versioning policy | `routes/api.php` and the route files it loads, controllers, FormRequests, API Resources, auth guards and Sanctum or Passport setup, rate limiters, Scramble or Scribe output when present |
| `package` | Developers who install your code as a Composer package | Installation; configuration (key names in prose); usage by feature; upgrade guide; public API by class | `composer.json`, the service provider, the published config file's structure, public classes and their docblocks, tests as examples |
| `internal` | The people who build and run the app | Architecture overview, domain glossary, local setup, conventions, data model with `<Model>` references, queues, jobs and schedules, deployment, runbooks | The whole codebase: `app/`, migrations, `routes/`, the `config/` structure, CI configuration, `AGENTS.md`, existing notes |

A few spellings work too: `users` for `product`, `api-consumers` for `api`, `package-users` for `package`, and `team` or `handbook` for `internal`. Names it does not know are ignored.

Each preset also sets a tone. Product pages are task first and carry no class names or artisan commands. API pages are precise and scannable, with request and response examples taken from your Resources and tests. Package pages are copy-paste friendly. Internal pages are direct and dense, written for a Laravel developer on day one.

### What the agent never writes

For every audience, the skill tells the agent to leave out:

- **Secrets and settings.** No `.env` values, keys, tokens, passwords, connection strings, hostnames or internal URLs. A config key may appear by name, never its value. API examples use a fake placeholder such as `YOUR_API_TOKEN`.
- **Customer data.** No names, emails, IDs or records from seeders or fixtures that look real.
- **Guesses.** If the code does not say it, the page does not say it. The agent leaves a note in its report instead.

Per audience, it also skips internal routes and admin features on product pages, internal-only endpoints on API pages, and classes marked `@internal` on package pages.

### How the agent works

The skill's onboarding workflow is the same for every agent. It reads `AGENTS.md`, `README.md`, `composer.json` and your existing docs first, and keeps the Introduction page the installer wrote, improving it rather than replacing it. It plans each page before writing, with the code each one will read, and skips a page when your app has nothing for it: no API routes means no API pages. It groups the new pages by audience in the navigation, writes only inside the docs folder, runs the validator, and finishes with a list of the pages it wrote and anything it left out.

> [!IMPORTANT]
> Internal docs belong in a private set. The `internal` preset suggests a `handbook` set with `access.mode` set to `auth` or `gate`. When that set does not exist, the agent says so in its report instead of writing internal pages into a public set. [Configuration](configuration.md) shows how to add a second set, and [gate blocks and private docs](../writing/gate-blocks.md) covers access.

## Which agent writes

LaRecipe detects an agent in two ways: its CLI on your `PATH` (`claude`, `codex`, `cursor-agent`, `gemini`, `copilot`), or its files in the project (`.claude`, `CLAUDE.md`, `.codex`, `.cursor`, `.gemini`, `GEMINI.md`, `.github/copilot-instructions.md`, `.github/skills`). Onboarding picks only an agent whose CLI is installed, because it has to run it.

| Agent | What LaRecipe does |
| --- | --- |
| Claude Code | Runs it headless, with writes limited to the docs folder. |
| Codex | Runs it headless in its `workspace-write` sandbox. |
| Cursor, Gemini CLI, GitHub Copilot | Prints the prompt for you to send. LaRecipe does not run these headless yet. |

## The command it shows you

What you read is what runs: the command on screen and the process LaRecipe starts are built from the same arguments. For a repository set in `docs/`, it looks like this:

<CodeGroup>

```bash title="Claude Code"
claude -p "Use the larecipe-write-docs skill to run onboarding for audiences: product, api (set: docs)" --allowedTools "Read,Grep,Glob,Edit(docs/**),Write(docs/**),Bash(php artisan larecipe:*)" --output-format stream-json --verbose
```

```bash title="Codex"
codex exec --sandbox workspace-write 'Use the $larecipe-write-docs skill to run onboarding for audiences: product, api (set: docs)'
```

</CodeGroup>

Claude Code may read your whole project but write only inside `docs/`, and run only `php artisan larecipe:*` commands. For a database set, the allowed tools are `Read,Grep,Glob,mcp__larecipe,Bash(php artisan larecipe:*)`: the agent writes only through the LaRecipe MCP author tools, which record each change in a docs migration.

Codex's sandbox cannot narrow writes to one folder, so LaRecipe says so under the command: review the diff before you commit. To write a database set, Codex also needs the `larecipe` MCP server in `.codex/config.toml`, in a project Codex trusts.

For Cursor, Gemini CLI and GitHub Copilot, LaRecipe prints the prompt itself. Open the agent in your project and send it:

```text
Use the larecipe-write-docs skill to run onboarding for audiences: product, api (set: docs)
```

### Consent

LaRecipe never starts an agent without your yes:

- Interactively, it asks "Run it now?". Answer no and it prints the command to run later.
- `--yes` runs it without asking, for scripts.
- `--print` only prints the command.
- Without a terminal and without `--yes`, it prints the command and stops. That includes `larecipe:install --no-interaction --onboard=api`: the installer finishes, and you run `php artisan larecipe:onboard --audience=api --yes` when you are ready.
- `larecipe:install --dry-run --onboard=api` prints the command and runs nothing.

When you name an agent with `--agent` whose CLI is not on your `PATH`, LaRecipe prints the command and asks you to run it where the agent is installed.

## While it runs

For Claude Code, each step the agent takes shows as a short line, such as `> Read app/Http/Controllers/OrderController.php` or `> Write docs/api/orders.md`, followed by what the agent says. Codex's own progress passes through as it is. There is no timeout: a set of starter pages takes minutes, and you can stop it at any time with `Ctrl+C`.

If the agent stops with an error, LaRecipe reports its exit code. Nothing was committed; look at the docs folder before you run it again.

## After the run

LaRecipe compares the docs folder before and after the run (for a database set, the docs migrations folder) and lists each file as `created` or `updated`. If the agent changed nothing, it says so.

Then it runs `php artisan larecipe:validate` on the set. When the pages pass, it reads "The new pages pass validation. Review them, then commit them with your code." When they do not, the command fails and lists the errors to fix first.

Nothing is committed. A repository set's pages are new files in your working tree. A database set's pages are applied to your local database and written to the branch's docs migration in `database/migrations/docs/`, so they reach production through the pull request like the rest of your change.

## Without an agent

When no agent CLI is installed, nothing is lost. Your docs keep the Introduction page the installer wrote, and LaRecipe prints the command to run once you have an agent:

```bash
php artisan larecipe:onboard --audience=api --set=docs
```

The workflow lives in the `larecipe-write-docs` skill, not in the command, so any agent that reads skills can run it from a chat: send it the prompt shown above. You can also write the first pages yourself; [Markdown](../writing/markdown.md) and [components](../writing/components.md) cover everything a page can hold.

## Review the pages

Treat the starter docs as a pull request from a new teammate who read the whole codebase in a few minutes: mostly right, worth checking.

<Steps>

<Step title="See what changed">

```bash
git status docs/
git diff docs/
```

New pages show up as untracked files in `git status`, and edits to existing pages, such as the Introduction or `navigation.json`, show up in `git diff`.

</Step>

<Step title="Read them as your readers will">

Open your docs in the browser and read each page. Check that the navigation groups make sense for each audience and that nothing meant for your team sits in a public set.

</Step>

<Step title="Check the claims against the code">

Each page lists the code it read in its `sources:` frontmatter. Spot-check a few claims there. Delete a page about something you are not ready to document, rather than leaving it half true.

</Step>

<Step title="Look for anything that should not be public">

The agent is told never to write settings, hostnames, emails or customer data, but a quick search costs nothing. Search the new pages for your domain names, `@` signs and anything that looks like a key.

</Step>

<Step title="Validate again and commit">

After your edits, run the validator, refresh the docs index in `AGENTS.md` so every agent session knows the new pages, then commit the docs with your code:

```bash
php artisan larecipe:validate
php artisan larecipe:agents-md
```

</Step>

</Steps>

You can ask the same agent to make the changes for you, in the same session. From here on, the [pre-push check](../keeping-current/pre-push-hook.md) keeps these pages current as the code changes.

## Every option

Read live from this app's command registry:

<Artisan command="larecipe:onboard" />

## Next

- [Configuration](configuration.md): doc sets, routes, access and the rest of `config/larecipe.php`.
- [Project structure](project-structure.md): where the new pages live and how their paths become URLs.
- [How it works](../keeping-current/how-it-works.md): how the docs stay current after this first draft.
