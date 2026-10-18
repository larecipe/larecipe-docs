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

Onboarding hands your app to the coding agent you already use. The agent reads your code and writes starter pages for the readers you pick. The pages land in your working tree, uncommitted, so you review them like any other change.

```bash
php artisan larecipe:onboard --audience=product,api
```

LaRecipe finds your agent, shows you the exact command it will run and asks "Run it now?". Nothing runs until you say yes. The agent's progress streams into your terminal, and when it finishes, LaRecipe lists the pages it wrote and runs `php artisan larecipe:validate` on them.

Onboarding is optional and runs on your own agent session, so the app needs no API key. Run it right after installing, or any time later, for example when you add an audience. The installer offers it as its last question when it finds an agent.

## Choose the audiences

Pick one or more, separated by commas:

| Audience | For | Pages it plans |
| --- | --- | --- |
| `product` | People who use the product, not its code | What the product does, getting started, one page per main feature, FAQ |
| `api` | Developers who call your HTTP API | Authentication, conventions, one page per resource with a `<Route>` reference per endpoint, webhooks, versioning |
| `package` | Developers who install your code with Composer | Installation, configuration, usage by feature, upgrade guide, public API |
| `internal` | The people who build and run the app | Architecture, domain glossary, local setup, conventions, data model, queues and jobs, deployment, runbooks |

The agent skips a page when your app has nothing for it: no API routes means no API pages. Its skill tells it never to write secrets, settings values, customer data or guesses.

> [!IMPORTANT]
> Internal docs belong in a private set. The `internal` preset suggests a `handbook` set that only your team can read. When that set does not exist yet, the agent says so in its report instead of writing internal pages into a public set. See [gate blocks and private docs](../writing/gate-blocks.md).

## Review the pages

Treat the starter docs as a pull request from a new teammate who read the whole codebase in a few minutes: mostly right, worth checking.

1. See what changed with `git status docs/` and `git diff docs/`.
2. Read each page in the browser, as your readers will.
3. Spot-check a few claims against the code each page lists in `sources:`. Delete a page you are not ready to stand behind.
4. Search the new pages for your domain names, `@` signs and anything that looks like a key.
5. Validate, refresh the docs index in `AGENTS.md`, and commit the docs with your code:

```bash
php artisan larecipe:validate
php artisan larecipe:agents-md
```

You can ask the same agent to make your edits in the same session. From here on, [the pre-push hook](../keeping-current/pre-push-hook.md) keeps these pages current as the code changes.

## Details

<AccordionGroup>

<Accordion title="What happens, step by step">

1. **It picks the doc set and the audiences.** Pages go into the default doc set unless you pass `--set=handbook`. Without `--audience`, it asks who the docs are for.
2. **It finds your agent.** It looks for an agent CLI on your `PATH` and picks it. With several installed, it asks which one should write. `--agent=codex` chooses for you.
3. **It prepares the agent.** It makes sure the LaRecipe skills are installed in `.claude/skills/` and `.agents/skills/` (with Laravel Boost, Boost ships them instead). For a [database set](../sources/database.md), it also adds the local `larecipe` MCP server to that agent's config, because the agent writes through it.
4. **It shows the exact command and asks.** You see the full command before anything runs.
5. **The agent writes, you watch.** For Claude Code, each step shows as a short line, such as `> Read app/Http/Controllers/OrderController.php` or `> Write docs/api/orders.md`, followed by what the agent says. Codex's own progress passes through as it is. There is no timeout; stop it at any time with `Ctrl+C`. If the agent stops with an error, LaRecipe reports its exit code. Nothing was committed.
6. **It lists and validates the pages.** LaRecipe compares the docs folder before and after the run (for a database set, the docs migrations folder) and lists each file as `created` or `updated`. Then it runs `php artisan larecipe:validate` on the set. When the pages pass, it reads "The new pages pass validation. Review them, then commit them with your code." When they do not, the command fails and lists the errors to fix first.

A repository set's pages are new files in your working tree. A database set's pages are applied to your local database and written to the branch's docs migration in `database/migrations/docs/`, so they reach production through the pull request like the rest of your change.

</Accordion>

<Accordion title="Start it from the installer">

Interactively, `larecipe:install` asks "Generate starter docs from your app with your coding agent now?" as its last question, when it finds an agent installed. Or answer up front:

```bash
php artisan larecipe:install --onboard=api --onboard-agent=claude
```

</Accordion>

<Accordion title="What each audience reads and how it writes">

Each audience is a preset in the `larecipe-write-docs` skill: an outline, a tone, what to read in your code and what never to write.

| Audience | What it reads |
| --- | --- |
| `product` | Routes and controllers behind user-facing features, Blade or Inertia pages, form validation messages, existing help text, the README |
| `api` | `routes/api.php` and the route files it loads, controllers, FormRequests, API Resources, auth guards and Sanctum or Passport setup, rate limiters, Scramble or Scribe output when present |
| `package` | `composer.json`, the service provider, the published config file's structure, public classes and their docblocks, tests as examples |
| `internal` | The whole codebase: `app/`, migrations, `routes/`, the `config/` structure, CI configuration, `AGENTS.md`, existing notes |

Product pages are task first and carry no class names or artisan commands. API pages are precise and scannable, with request and response examples taken from your Resources and tests. Package pages are copy-paste friendly, with configuration key names in prose. Internal pages are direct and dense, written for a Laravel developer on day one, with `<Model>` references for the data model.

A few spellings work too: `users` for `product`, `api-consumers` for `api`, `package-users` for `package`, and `team` or `handbook` for `internal`. Names it does not know are ignored.

</Accordion>

<Accordion title="What the agent never writes">

For every audience, the skill tells the agent to leave out:

- **Secrets and settings.** No `.env` values, keys, tokens, passwords, connection strings, hostnames or internal URLs. A config key may appear by name, never its value. API examples use a fake placeholder such as `YOUR_API_TOKEN`.
- **Customer data.** No names, emails, IDs or records from seeders or fixtures that look real.
- **Guesses.** If the code does not say it, the page does not say it. The agent leaves a note in its report instead.

Per audience, it also skips internal routes and admin features on product pages, internal-only endpoints on API pages, and classes marked `@internal` on package pages.

The `internal` preset's `handbook` set should have `access.mode` set to `auth` or `gate`. [Configuration basics](configuration.md) shows how to add a second set.

</Accordion>

<Accordion title="How the agent works">

The skill's onboarding workflow is the same for every agent. It reads `AGENTS.md`, `README.md`, `composer.json` and your existing docs first, and keeps the Introduction page the installer wrote, improving it rather than replacing it. It plans each page before writing, with the code each one will read. It groups the new pages by audience in the navigation, writes only inside the docs folder, runs the validator, and finishes with a list of the pages it wrote and anything it left out.

</Accordion>

<Accordion title="Which agents LaRecipe runs, and the command it shows">

LaRecipe detects an agent by its CLI on your `PATH` (`claude`, `codex`, `cursor-agent`, `gemini`, `copilot`) or its files in the project (`.claude`, `CLAUDE.md`, `.codex`, `.cursor`, `.gemini`, `GEMINI.md`, `.github/copilot-instructions.md`, `.github/skills`). Onboarding picks only an agent whose CLI is installed, because it has to run it.

| Agent | What LaRecipe does |
| --- | --- |
| Claude Code | Runs it headless, with writes limited to the docs folder. |
| Codex | Runs it headless in its `workspace-write` sandbox. |
| Cursor, Gemini CLI, GitHub Copilot | Prints the prompt for you to send. LaRecipe does not run these headless yet. |

What you read is what runs: the command on screen and the process LaRecipe starts are built from the same arguments. For a repository set in `docs/`:

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

For Cursor, Gemini CLI and GitHub Copilot, open the agent in your project and send the prompt LaRecipe prints:

```text
Use the larecipe-write-docs skill to run onboarding for audiences: product, api (set: docs)
```

</Accordion>

<Accordion title="Consent, scripts and dry runs">

LaRecipe never starts an agent without your yes:

- Interactively, it asks "Run it now?". Answer no and it prints the command to run later.
- `--yes` runs it without asking, for scripts.
- `--print` only prints the command.
- Without a terminal and without `--yes`, it prints the command and stops. That includes `larecipe:install --no-interaction --onboard=api`: the installer finishes, and you run `php artisan larecipe:onboard --audience=api --yes` when you are ready.
- `larecipe:install --dry-run --onboard=api` prints the command and runs nothing.

When you name an agent with `--agent` whose CLI is not on your `PATH`, LaRecipe prints the command and asks you to run it where the agent is installed.

</Accordion>

<Accordion title="Without an agent">

When no agent CLI is installed, your docs keep the Introduction page the installer wrote, and LaRecipe prints the command to run once you have an agent:

```bash
php artisan larecipe:onboard --audience=api --set=docs
```

The workflow lives in the `larecipe-write-docs` skill, not in the command, so any agent that reads skills can run it from a chat: send it the prompt shown above. You can also write the first pages yourself; [Markdown](../writing/markdown.md) and [components](../writing/components.md) cover everything a page can hold.

</Accordion>

<Accordion title="Every option of larecipe:onboard">

Read live from this app's command registry:

<Artisan command="larecipe:onboard" />

</Accordion>

</AccordionGroup>

## Next

- [Configuration basics](configuration.md): the few settings most apps change.
- [Project structure](project-structure.md): where the new pages live and how their paths become URLs.
- [How it works](../keeping-current/how-it-works.md): how the docs stay current after this first draft.
