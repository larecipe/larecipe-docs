---
title: MCP tools
description: Every tool, resource and prompt the LaRecipe MCP server exposes, with descriptions, annotations, input schemas and which server offers each one.
sources:
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Sources/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Sync/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Validation/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/Domains/Agents/AgentsServiceProvider.php
  - vendor/larecipe/larecipe/config/larecipe.php
---

# MCP tools

Every tool, resource and prompt the LaRecipe MCP server exposes. What a caller sees depends on the server, the set and who is calling. To connect an agent, see [the MCP server](../agents/mcp.md).

| Server | Where | Serves |
| --- | --- | --- |
| Web | `POST /{prefix}/mcp` per set (`/mcp` on this site) | That set only. Read tools for everyone the set lets in; more for callers your gates allow. |
| Local | `php artisan larecipe:mcp`, over stdio | Every set. Read tools take a `set` argument. All tools, author tools included. |

## Which tools each server exposes

| Tool | Local | Web |
| --- | --- | --- |
| `search_docs`, `read_page`, `list_pages` | yes | yes |
| `submit_feedback` | when `feedback.enabled` | when `feedback.enabled` |
| `validate_docs` | yes | only for callers the `manageLarecipe` gate allows |
| `write_page`, `move_page`, `delete_page`, `update_navigation`, `update_manifest` | yes | only on a database set with `mcp.web.author_tools` on, for a signed-in caller the `manageLarecipe` gate allows |
| `affected_pages` | yes | never: it reads your working tree |

A repository set never exposes author tools on the web. Each web endpoint is bound to its own set: a call that names another set in `set` is refused. A guest on this site's endpoint sees four tools: `search_docs`, `read_page`, `list_pages` and `submit_feedback`. Settings are under [`mcp` in the configuration reference](configuration.md#mcp).

## Read tools

All three are annotated `readOnlyHint: true`, `idempotentHint: true` and `openWorldHint: false`. On the local server each one also takes `set` (string): the doc set to use, which defaults to `larecipe.default`.

### search_docs

> Search the documentation and get the best matching pages, with the section and a snippet for each. Search before you read or write a page. Pass a result's path to read_page to read it.

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `query` | string | yes | What to look for: a few words, an API or class name, a command, an error message. |
| `version` | string | no | The docs version to search. Defaults to the current version. |
| `locale` | string | no | The language to search, as a locale code. Defaults to the main language. |
| `limit` | integer, 1 to 20 | no | How many results to return. Default 8. |

Returns structured results: `set`, `version`, `query` and `results`, each with `title`, `path`, `url`, `markdown_url`, `heading`, `snippet`, `description` and `score`.

### read_page

> Read one docs page as Markdown. Pass the path from search_docs or list_pages (for example "guides/webhooks"), or the page URL. Pass section (a heading or its #anchor) to read only that part of the page. The result ends with the page's content_hash (also in its _meta): pass it as based_on when you write, move or delete the page.

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `path` | string | yes | The page path (`guides/webhooks`), or its URL or `.md` URL. |
| `version` | string | no | The docs version. Defaults to the current version. |
| `locale` | string | no | The language, as a locale code. Defaults to the main language. |
| `section` | string | no | Only return this section: a heading text or its anchor id. |

Returns the page's agent Markdown, with `title`, `path`, `version`, `locale`, `url`, `markdown_url` and `content_hash` in the result's `_meta`. The text ends with a line that carries the same `content_hash`, for clients that show only the text. Ask AI gets the Markdown without that line.

### list_pages

> List the docs as the navigation shows them: groups, then pages with their title, path and description. Pass group to list one group only. Read a page with read_page and its path.

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `version` | string | no | The docs version. Defaults to the current version. |
| `locale` | string | no | The language, as a locale code. Defaults to the main language. |
| `group` | string | no | Only list the group with this label. |

## Feedback

### submit_feedback

> Tell the docs owners whether a page helped. Use it when a page was wrong, outdated or missing something you needed, and say what in the comment.

Annotations: `readOnlyHint: false`, `destructiveHint: false`, `idempotentHint: false`, `openWorldHint: false`. On the local server it also takes `set`.

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `path` | string | yes | The page path or URL the feedback is about. |
| `helpful` | boolean | yes | Whether the page helped. |
| `comment` | string, up to 2000 characters | no | What was wrong or missing, in a sentence or two. |
| `version` | string | no | The docs version. Defaults to the current version. |
| `locale` | string | no | The language, as a locale code. Defaults to the main language. |

It fires `FeedbackSubmitted` with `source` set to `agent`. LaRecipe stores nothing. See [events](events.md).

## Validation

### validate_docs

> Check the docs for broken links and anchors, navigation entries without pages, schema errors in larecipe.json, navigation.json and frontmatter, unknown components, missing snippets and more. Run it after writing, and fix every error. Each issue has a stable rule id and often a fix.

Annotations: `readOnlyHint: true`, `idempotentHint: true`, `openWorldHint: false`.

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `version` | string | no | Only check this version. Defaults to every version. |
| `set` | string | no | Local server only: the doc set to check. Defaults to `larecipe.default`. |

Returns the same report as `php artisan larecipe:validate --json`.

## Author tools

Author tools change the docs: files in the working tree on a repository set; on a database set, the local database plus the branch's docs migration. Each one returns `outcome` (`published`, `drafted`, `conflicted`, `unchanged` or `rejected`), `message`, `revision`, `validation` and `docs_migration`.

Every author tool is annotated `destructiveHint: true`, and takes these arguments besides its own:

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `version` | string | no | The version, for versioned sets. Defaults to the default version. Not on `update_manifest`. |
| `locale` | string | no | The locale code. Defaults to the default locale. Not on `update_manifest`. |
| `summary` | string | no | One line describing the change, for the revision list and the pull request. |
| `based_on` | string | no | The content hash the change was written against. The change is refused if the content changed since. See below for where it comes from. |
| `set` | string | no | The doc set key, when the app has more than one. Defaults to the default set. On a web endpoint, only its own set. |

Where `based_on` comes from:

- **`write_page`, `move_page`, `delete_page`:** the `content_hash` that `read_page` returned for the page. Leave it out for a new page.
- **`update_navigation`, `update_manifest`:** the `revision.content_hash` your last call of the same tool returned (for navigation, for that version and locale). Leave it out to change the navigation or the manifest as it is now.

The `revision` every author tool returns carries `content_hash`, the hash of what it wrote, so an agent can chain several changes to the same page.

### write_page

> Create or replace one docs page: frontmatter and Markdown body. Read the page first with read_page and pass the content_hash it returns as based_on, so you never overwrite a newer edit (leave based_on out for a new page). On a repository set the file changes in the working tree; on a database set the change is applied to the local database and written into the branch's docs migration (commit it with the code). Returns the revision and the validation result; fix every validation error it reports.

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `path` | string | yes | The page path inside its version, without `.md`, for example `billing/exports`. `index` is the landing page. |
| `frontmatter` | object | no | Page-level frontmatter: `title`, `description`, `sources` and the rest. Never configuration or environment values. |
| `body` | string | yes | The Markdown body, without the frontmatter block. |

### move_page

> Move one docs page to another path (or version, or locale) in the same set. Refused when the new path is taken. Update links and the navigation afterwards (update_navigation with a rename patch). Pass the content_hash read_page returned as based_on.

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `from` | string | yes | The current page path, for example `billing/exports`. |
| `to` | string | yes | The new page path, for example `billing/csv-exports`. |
| `to_version` | string | no | Move into another version. Defaults to the same version. |
| `to_locale` | string | no | Move into another locale. Defaults to the same locale. |

### delete_page

> Delete one docs page. Remove it from the navigation too (update_navigation with a remove patch). On a database set the page stays in the revision history and can be restored. Pass the content_hash read_page returned as based_on.

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `path` | string | yes | The page path, for example `billing/legacy`. |

### update_navigation

> Change the navigation (navigation.json) of one version: send a whole tree, or a patch, a list of operations such as {"add": "billing/exports", "group": "Billing", "after": "billing/overview"}, {"move": "a", "group": "B"}, {"remove": "a"} or {"rename": "a", "to": "b"}. A version without navigation.json starts from the navigation derived from its folders.

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `tree` | object | no | A whole `navigation.json`: `{"groups": [...]}` or `{"tabs": [...]}`. |
| `patch` | list of objects | no | Operations applied in order: `add`, `move`, `remove`, `rename`. |

### update_manifest

> Change the set's larecipe.json (name, description, theme, versions, locales, navbar, redirects, agent description) with a JSON merge patch: keys replace, null removes a key, objects merge, lists are replaced whole. Presentation and structure only: routes, drivers, AI settings and secrets live in config/larecipe.php and never here, and so do a database set's analytics integrations.

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `patch` | object | yes | A JSON merge patch (RFC 7386) for `larecipe.json`. |

### affected_pages

> List the docs pages whose `sources:` match files changed between `base` (default: the default branch) and `head`, plus uncommitted work. Each page says whether the change already updated it (status "updated") or left it stale (status "stale"). Update stale pages before you push.

Local server only: it reads your working tree and git, so no web endpoint offers it. Annotated `readOnlyHint: true`, and it takes only its own arguments:

| Argument | Type | Required | Description |
| --- | --- | --- | --- |
| `base` | string | no | Branch or commit to compare with. Default: the default branch. |
| `head` | string | no | End of the range. Default: `HEAD`. |
| `uncommitted` | boolean | no | Include staged, unstaged and untracked changes. Default: true. |
| `set` | string | no | Only this doc set. Default: every set. |
| `drift` | boolean | no | Also list pages that drifted before this change. Default: false. |

It is the same check as [`larecipe:affected`](commands.md#larecipeaffected).

## Resources

| Name | URI template | Type | Description |
| --- | --- | --- | --- |
| `docs-page` | `docs://{set}/{version}/{path}` | `text/markdown` | A docs page as agent Markdown, for example `docs://docs/3.x/agents/surfaces`. An unversioned set uses `current` as the version, and another language goes in front of the path (`ar/index`). |

The server offers the template only; `resources/list` is empty. A web endpoint reads pages of its own set only.

## Prompts

| Name | Arguments | What it gives the agent |
| --- | --- | --- |
| `write-docs-page` | `topic` (required), `path`, `set` | Steps for writing a new docs page: check what exists, write from the code, add it to the navigation and validate. |
| `document-change` | `change` (required: a summary, a diff or a commit range), `set` | Steps for updating the docs a code change made stale, in the same change as the code. |

## Instructions on connect

Every client gets the same workflow on connect: search first with `search_docs`, read before writing with `read_page`, validate after writing with `validate_docs`, cite the page URLs, and use `submit_feedback` when a page was wrong or missing. With several sets, the local server also lists them. [The MCP server](../agents/mcp.md) shows the full text.

## Next

- [The MCP server](../agents/mcp.md): connect an agent, and authenticate a private set.
- [Events](events.md): `McpToolCalled`, `SearchPerformed` and `FeedbackSubmitted`.
