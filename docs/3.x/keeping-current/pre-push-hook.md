---
title: The pre-push hook
description: When an agent's push leaves docs pages stale, the hook holds it once and tells the agent which pages to update, plus an optional git hook for people who push without an agent.
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

When your coding agent runs `git push` or `gh pr create` and the change leaves docs pages stale, the pre-push hook holds the push once and tells the agent which pages to update. The agent updates them and pushes again. If no docs change is needed, the agent tells you why and pushes again: the second attempt always goes through, so it is never stuck.

The installer adds the hook for Claude Code and Codex. It is deterministic and offline: it reads git and the docs, and never calls an LLM. Anything unexpected, such as no git repository or a broken bootstrap, lets the command through.

## What the agent is told

When the hook holds a push, the agent gets a reason like this:

```text
LaRecipe: this push changes code that 2 docs pages describe, and they were not updated:
  - docs/billing/webhooks.md   (sources: app/Webhooks/**)
  - docs/installation.md       (sources: composer.json)
Update them with the larecipe-update-docs skill, run php artisan larecipe:validate,
show the user the docs diff and wait for approval, then commit and push again.
If no docs change is needed, tell the user why and push again; the next push is allowed.
```

The next step follows the [review mode](review-modes.md):

- **`required`** (the default): the agent shows you the docs diff and waits for your approval before it commits, as above.
- **`auto`**: the line becomes "then commit and push again.", so the agent updates the pages, commits and pushes without asking.

When the push adds, removes or retitles pages, the message also asks the agent to run `php artisan larecipe:agents-md`, so the [docs index in `AGENTS.md`](agent-instructions.md#the-docs-index) stays current.

## Turn it off

- **For one session:** set `LARECIPE_HOOKS=off` in the environment the agent runs in, for example `LARECIPE_HOOKS=off claude`.
- **For the project:** set `hooks.enabled` to `false` in `config/larecipe.php` (or `LARECIPE_HOOKS=off` in `.env`), then run `php artisan larecipe:agents` so the compiled patterns agree.
- **To remove it:** delete the LaRecipe entries from `.claude/settings.json` and `.codex/hooks.json`. `larecipe:install --no-hooks` never adds them.

## The git hook, for people

Developers who push without an agent can get the same check as a git `pre-push` hook. By default it only warns: it prints the pages the push leaves stale and lets the push through.

```bash
php artisan larecipe:hooks:git --install
```

With `--strict` it blocks the push instead, every time, until the docs are updated. `git push --no-verify` skips it once when no docs change is needed. The installer can add it too: `larecipe:install --git-hook=warn` or `--git-hook=strict`.

<Accordion title="How the git hook works, and its options">

- It checks the commits being pushed. Uncommitted work does not travel with a push, so it does not count here.
- There is no "second attempt passes" for people in strict mode, because `--no-verify` is the familiar escape.
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

<Artisan command="larecipe:hooks:git" />

</Accordion>

## Agents without hooks

Cursor, Copilot and Gemini CLI have no pre-tool hook LaRecipe can use today. They rely on the [`AGENTS.md` instructions](agent-instructions.md), which they read at the start of every session. For pushes from those agents, add the git hook above and the [CI check](ci-check.md).

## Details

<AccordionGroup>

<Accordion title="How a push is checked">

1. Claude Code and Codex call `vendor/bin/larecipe-hook pre-push` before every Bash command, with the command in the hook payload. The script is plain PHP with no dependencies. Unless the command pushes code or opens a pull request, it exits in a few milliseconds, without booting Laravel.
2. For a push, the script runs `php artisan larecipe:hook pre-push`, which works out the change the pull request will contain: on a feature branch, everything since it left the default branch (`origin/main`, or `main` when there is no remote branch); on the default branch itself, the commits not yet pushed to its upstream. Uncommitted work counts too.
3. The push goes through when nothing is affected, when every affected page was updated in the same range (its Markdown file, or a docs migration that names it for a database set), when the hook is turned off, or when the same push was already held once. Otherwise the agent gets a PreToolUse deny decision (`"permissionDecision": "deny"`) with the reason shown above.

When some stale pages belong to a set in `required` mode, the message asks for approval. For a database set, the update line adds that database pages are edited through the LaRecipe MCP tools, which write the branch's docs migration in `database/migrations/docs/`.

When nothing is stale but the docs index is out of date, Claude Code gets the `larecipe:agents-md` line as a note and the push goes through. Codex gets nothing in that case, because it treats PreToolUse fields it does not support as a failed hook.

</Accordion>

<Accordion title="Claude Code: .claude/settings.json">

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
- **SessionStart** runs when a session starts, resumes or compacts, and gives the agent a short map of the docs.

The installer merges these into your existing settings and never overwrites them. Other hooks stay where they are, a file that already has a LaRecipe hook is left alone, and a file that is not valid JSON is skipped with a note.

The script finds your app from `$CLAUDE_PROJECT_DIR`, the payload's working directory or the current directory, walking up to the nearest folder with an `artisan` file, so it also works when the Laravel app sits in a subfolder of the repository. It stops at the git repository's root, and it acts only when that nearest app has LaRecipe (`vendor/larecipe/larecipe`, or `larecipe/larecipe` in `composer.json`); it never passes it for an app further up. Anywhere else it lets every command through.

</Accordion>

<Accordion title="Codex: .codex/hooks.json">

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
            "command": "\"$(git rev-parse --show-toplevel)\"/'vendor/bin/larecipe-hook' pre-push --client=codex",
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
            "command": "\"$(git rev-parse --show-toplevel)\"/'vendor/bin/larecipe-hook' session-start --client=codex",
            "timeout": 30
          }
        ]
      }
    ]
  }
}
```

The repository's top folder and the path inside it are quoted separately for the shell, so a folder name can never run as a command. When the app lives in a subfolder of the repository, the path includes that folder. Codex loads project hooks only once you trust the project in `~/.codex/config.toml` and approve the hooks with `/hooks`; the installer reminds you.

</Accordion>

<Accordion title="Block once, then allow">

The hook holds a push at most once for the same commit and the same list of stale pages. The second attempt always passes, so an agent (or a person) that decides no docs change is needed is never stuck. The `larecipe-update-docs` skill tells the agent to say why to you, then push again.

- The memory is per agent session, keyed by the session id in the hook payload, in a small JSON file under `storage/framework/larecipe/hooks/`. Files older than a week are cleaned up.
- A new commit that still leaves pages stale is a new push, and is held once again.
- Another agent session gets its own single block.

</Accordion>

<Accordion title="Which commands count as a push">

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

The script must decide without booting Laravel, so it reads the patterns from a compiled file, `bootstrap/cache/larecipe/hooks.php`, which `larecipe:install` and `larecipe:agents` write from your config. This site's copy:

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

</Accordion>

<Accordion title="SessionStart: what a new session learns">

At the start of every session, the SessionStart hook gives the agent a few hundred tokens of context: where the docs live, a compact map of the pages, how many pages have drifted, and the before-you-push rule. For this site it reads like this (the map is shortened here):

```text
LaRecipe docs: set `docs` in `docs/` (repository, version 3.x, 46 pages, review mode required).
Map: index, upgrade, agents/{ask-ai, mcp, surfaces}, customize/{custom-components, eject, insert-points, theme}, get-started/{configuration, installation, project-structure, your-first-docs}, ...
Stale: 7 pages document code that changed after they were last updated (php artisan larecipe:affected lists them).
Before you push or open a pull request: run php artisan larecipe:affected --uncommitted --base=main and update the pages it lists with the larecipe-update-docs skill. Search and read the docs with the larecipe MCP server.
```

The "Stale" line appears only when pages have drifted. The count is skipped when more than 200 pages declare sources, to keep the session start fast, and the whole context is cut at 1,600 characters.

</Accordion>

</AccordionGroup>

## Next

- [Review modes](review-modes.md): what `required` and `auto` change.
- [Skills](skills.md): the `larecipe-update-docs` skill the message points to.
- [The CI check](ci-check.md): go deeper with the last safety net, on pull requests.
