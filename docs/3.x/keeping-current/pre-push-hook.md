---
title: The pre-push hook
description: How LaRecipe holds an agent's push once when it leaves docs pages stale, what the agent is told, and the git hook for people who push without an agent.
sources:
  - vendor/larecipe/larecipe/bin/larecipe-hook
  - vendor/larecipe/larecipe/src/Domains/Sync/Support/PushCommandDetector.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/CheckPrePushAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/BuildSessionContextAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/CompileHookPatternsAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/InstallGitHookAction.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/HookCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/GitHooksCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/LoopGuard.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/HookMessages.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/PushRange.php
  - vendor/larecipe/larecipe/src/Domains/Sync/DTOs/HookPayload.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/InstallAgentHooksAction.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - .claude/settings.json
---

# The pre-push hook

Instructions can be forgotten in a long session. The pre-push hook reinforces them at the one moment that matters: when your coding agent is about to push a branch or open a pull request. If the change leaves docs pages stale, the hook holds the push once and tells the agent which pages to update. The agent updates them, and pushes again.

The hook is deterministic and offline. It reads git and the docs, decides, and never calls an LLM.

## What happens on a push

<Steps>

<Step title="The agent runs a shell command">

Claude Code and Codex call `vendor/bin/larecipe-hook pre-push` before every Bash command, with the command in the hook payload. The script is plain PHP with no dependencies. Unless the command pushes code or opens a pull request, it exits in a few milliseconds, without booting Laravel.

</Step>

<Step title="A push boots the check">

For a push, the script runs `php artisan larecipe:hook pre-push`, which works out the change the pull request will contain: on a feature branch, everything since it left the default branch (`origin/main`, or `main` when there is no remote branch); on the default branch itself, the commits not yet pushed to its upstream. Uncommitted work counts too.

</Step>

<Step title="It allows the push, or holds it once">

The push goes through when nothing is affected, when every affected page was updated in the same range (its Markdown file, or a docs migration that names it for a database set), when the hook is turned off, or when the same push was already held once. Otherwise the agent gets a deny decision with a short reason: the stale pages and the next steps.

</Step>

</Steps>

Anything unexpected lets the command through: no git repository, an app without LaRecipe, a broken bootstrap. The check reinforces the instructions; it must never trap the agent.

## Claude Code

The installer adds two hooks to `.claude/settings.json`. This is this site's own file:

```json
{
  "hooks": {
    "PreToolUse": [
      {
        "matcher": "Bash",
        "hooks": [
          {
            "type": "command",
            "command": "\"$CLAUDE_PROJECT_DIR\"/vendor/bin/larecipe-hook pre-push",
            "timeout": 60
          }
        ]
      }
    ],
    "SessionStart": [
      {
        "matcher": "startup|resume|compact",
        "hooks": [
          {
            "type": "command",
            "command": "\"$CLAUDE_PROJECT_DIR\"/vendor/bin/larecipe-hook session-start",
            "timeout": 30
          }
        ]
      }
    ]
  }
}
```

- **PreToolUse** runs on every Bash tool call and does the pre-push check.
- **SessionStart** runs when a session starts, resumes or compacts, and gives the agent a short map of the docs (below).

The installer merges these into your existing settings and never overwrites them. Other hooks stay where they are, a file that already has a LaRecipe hook is left alone, and a file that is not valid JSON is skipped with a note. `larecipe:install --no-hooks` skips this step.

The script finds your app from `$CLAUDE_PROJECT_DIR`, the payload's working directory or the current directory, walking up to the nearest folder with an `artisan` file, so it also works when the Laravel app sits in a subfolder of the repository.

## Codex

Codex reads the same hook shape from `.codex/hooks.json`. Codex gives hooks no project folder, so the installer's command finds the app from the repository root and passes `--client=codex`:

```json
{
  "hooks": {
    "PreToolUse": [
      {
        "matcher": "Bash",
        "hooks": [
          {
            "type": "command",
            "command": "\"$(git rev-parse --show-toplevel)/vendor/bin/larecipe-hook\" pre-push --client=codex",
            "timeout": 60
          }
        ]
      }
    ],
    "SessionStart": [
      {
        "matcher": "startup|resume|compact",
        "hooks": [
          {
            "type": "command",
            "command": "\"$(git rev-parse --show-toplevel)/vendor/bin/larecipe-hook\" session-start --client=codex",
            "timeout": 30
          }
        ]
      }
    ]
  }
}
```

When the app lives in a subfolder of the repository, the path includes that folder. Codex loads project hooks only once you trust the project in `~/.codex/config.toml` and approve the hooks with `/hooks`; the installer reminds you.

## What the agent is told

When the hook holds a push, the agent receives a PreToolUse deny decision (`"permissionDecision": "deny"`) whose reason reads like this:

```text
LaRecipe: this push changes code that 2 docs pages describe, and they were not updated:
  - docs/billing/webhooks.md   (sources: app/Webhooks/**)
  - docs/installation.md       (sources: composer.json)
Update them with the larecipe-update-docs skill, run php artisan larecipe:validate,
show the user the docs diff and wait for approval, then commit and push again.
If no docs change is needed, tell the user why and push again; the next push is allowed.
```

The next step follows the [review mode](review-modes.md), the same way the [`AGENTS.md` block](agent-instructions.md) does:

- **`review: required`** (the default): the agent shows you the docs diff and waits for your approval before it commits, as above.
- **`review: auto`**: the line becomes "then commit and push again.", so the agent updates the pages, commits and pushes without asking.

When some stale pages belong to a set in `required` mode, the message asks for approval. For a database set, the update line adds that database pages are edited through the LaRecipe MCP tools, which write the branch's docs migration in `database/migrations/docs/`.

When the push adds, removes or retitles pages and `AGENTS.md` carries a docs index, the message ends with one more line:

```text
Pages were added, removed or retitled: run php artisan larecipe:agents-md so the docs index in AGENTS.md stays current.
```

When nothing is stale but the index is out of date, Claude Code gets the same line as a note and the push goes through. Codex gets nothing in that case, because it treats PreToolUse fields it does not support as a failed hook.

## Block once, then allow

The hook holds a push at most once for the same commit and the same list of stale pages. The second attempt always passes, so an agent (or a person) that decides no docs change is needed is never stuck. The `larecipe-update-docs` skill tells the agent to say why to you, then push again.

- The memory is per agent session, keyed by the session id in the hook payload, in a small JSON file under `storage/framework/larecipe/hooks/`. Files older than a week are cleaned up.
- A new commit that still leaves pages stale is a new push, and is held once again.
- Another agent session gets its own single block.

## Which commands count

A command counts as a push when any part of it runs one of the patterns in `hooks.push_commands`:

```php
// config/larecipe.php
'hooks' => [
    'enabled' => env('LARECIPE_HOOKS', 'on') !== 'off',
    'push_commands' => [
        'git push',
        'gh pr create',
        'glab mr create',
        'hub pull-request',
    ],
],
```

The first word of a pattern is the program; the other words must be its first arguments, ignoring options. Chained commands, subshells and `bash -c '...'` are looked inside:

| Counts as a push | Does not |
| --- | --- |
| `git push --force-with-lease -u origin feature` | `git status` |
| `git -C app push` | `git log --grep push` |
| `git add . && git commit -m "wip" && git push` | `git commit -m "git push later"` |
| `npm test; git push -u origin feature` | `grep -r 'git push' docs` |
| `FOO=1 git push`, `env GIT_TRACE=1 git push` | `gh pr view 12` |
| `sh -c 'cd app && git push'` | `git pushx` |
| `gh pr create --fill` | `gh pr list` |

The script must decide without booting Laravel, so it reads the patterns from a compiled file, `bootstrap/cache/larecipe/hooks.php`. `larecipe:install` and `larecipe:agents` write it from your config. This site's copy:

```php
<?php

// Compiled by php artisan larecipe:agents for vendor/bin/larecipe-hook, so ordinary
// shell commands never boot the framework. Change config/larecipe.php instead.

return array (
  'enabled' => true,
  'push_commands' => 
  array (
    0 => 'git push',
    1 => 'gh pr create',
    2 => 'glab mr create',
    3 => 'hub pull-request',
  ),
);
```

After you change `hooks.push_commands` or `hooks.enabled`, run `php artisan larecipe:agents` to compile them again. The file lives in a cache folder and is not committed; without it, the hook uses the four default patterns.

## SessionStart

At the start of every session, the SessionStart hook gives the agent a few hundred tokens of context: where the docs live, a compact map of the pages, how many pages have drifted, and the before-you-push rule. This is what it tells an agent working on this site:

```text
LaRecipe docs: set `docs` in `docs/` (repository, version 3.x, 12 pages, review mode required).
Map: index, agents/{surfaces}, customize/{custom-components, theme}, get-started/{installation}, operate/{caching}, writing/{code-blocks, components, languages, live-references, markdown, versions}
Before you push or open a pull request: run php artisan larecipe:affected --uncommitted --base=main and update the pages it lists with the larecipe-update-docs skill. Search and read the docs with the larecipe MCP server.
```

When pages have drifted, a line such as "Stale: 3 pages document code that changed after they were last updated" sits before the rule. The count is skipped when more than 200 pages declare sources, to keep the session start fast, and the whole context is cut at 1,600 characters.

## Turn it off

- **For one session:** set `LARECIPE_HOOKS=off` in the environment the agent runs in, for example `LARECIPE_HOOKS=off claude`. Both hooks then do nothing.
- **For the project:** set `hooks.enabled` to `false` in `config/larecipe.php` (or `LARECIPE_HOOKS=off` in `.env`), then run `php artisan larecipe:agents` so the compiled file agrees.
- **To remove it:** delete the LaRecipe entries from `.claude/settings.json` and `.codex/hooks.json`.

## The git hook, for people

Developers who push without an agent can get the same check as a git `pre-push` hook:

```bash
php artisan larecipe:hooks:git --install
```

By default it only warns: it prints the pages the push leaves stale, in the same format as `larecipe:affected`, and lets the push through. With `--strict` it blocks the push instead, and `git push --no-verify` skips it once when no docs change is needed:

```bash
php artisan larecipe:hooks:git --install --strict
```

- It checks the commits being pushed. Uncommitted work does not travel with a push, so it does not count here.
- It holds a strict push every time until the docs are updated; there is no "second attempt passes" for people, because `--no-verify` is the familiar escape.
- It respects `core.hooksPath`. When a shell `pre-push` hook already exists, LaRecipe adds its own marked block to it instead of replacing it. A hook that is not a shell script is never touched; the command prints the line to add yourself.
- `LARECIPE_HOOKS=off git push` skips it for one push. `--uninstall` removes LaRecipe's block, and deletes the hook when nothing else is left in it. `--dry-run` shows what would change.
- It never calls an AI. A git hook that needs keys, a network and model time would be slow and unpredictable.

A new hook looks like this:

```bash
#!/bin/sh

# larecipe:start
# LaRecipe docs check. Deterministic: it reads git and the docs, and never calls an AI.
# Lists docs pages this push leaves stale, then lets the push through.
# Turn it off for one push with LARECIPE_HOOKS=off, or remove it: php artisan larecipe:hooks:git --uninstall
if [ "$LARECIPE_HOOKS" != "off" ]; then
  (cd "$(git rev-parse --show-toplevel)" && php artisan larecipe:hook git-pre-push) || true
fi
# larecipe:end
```

The installer can add it too: `larecipe:install --git-hook=warn` or `--git-hook=strict`.

<Artisan command="larecipe:hooks:git" />

## Agents without hooks

Cursor, Copilot and Gemini CLI have no pre-tool hook LaRecipe can use today. They rely on the [`AGENTS.md` instructions](agent-instructions.md), which they read at the start of every session. For pushes from those agents, add the git hook above and the [optional CI check](ci-check.md).

## Next

- [Review modes](review-modes.md): what `required` and `auto` change.
- [Skills and the Claude Code plugin](skills.md): the plugin ships the same hooks.
- [The optional CI check](ci-check.md): the last safety net.
