---
title: The MCP server
description: Connect any MCP client to your docs, over HTTP for your readers' agents or locally for your own coding agent, and see which tools each one gets.
sources:
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/AgentRoutes.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/BindMcpSet.php
  - vendor/larecipe/larecipe/src/Domains/Agents/AgentsServiceProvider.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Commands/McpCommand.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Mcp/**
  - vendor/larecipe/larecipe/src/Domains/Sync/Mcp/AffectedPagesTool.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Mcp/ValidateDocsTool.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/InstallMcpConfigAction.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Services/CodexToml.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Enums/Agent.php
  - vendor/larecipe/larecipe/src/Domains/Access/Laravel/GateAuthorizer.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/config/larecipe.php
  - .mcp.json
---

# The MCP server

Every doc set comes with an MCP server, so any agent that speaks the Model Context Protocol can search and read your docs with real tools. It runs two ways, both on by default:

- **A web endpoint per doc set**, at `/{prefix}/mcp`, for your readers' agents. It serves one set and follows the same access rules as the HTML.
- **A local server for your own coding agent**, `php artisan larecipe:mcp`. It runs over stdio in your repository, serves every set, and adds the tools that write docs.

To connect Claude Code to this site's docs:

```bash
claude mcp add --transport http larecipe https://larecipe.saleem.dev/mcp
```

Any MCP client with HTTP transport takes the same URL. Readers do not have to remember it: the **For agents** menu on every page has **Connect MCP**, which copies this command for the set they are reading.

## Your own coding agent

You rarely start the local server yourself. `php artisan larecipe:install` registers it as `larecipe` in the project config file of each coding agent you use (`.mcp.json` for Claude Code, `.codex/config.toml`, `.cursor/mcp.json`, `.gemini/settings.json`, `.vscode/mcp.json`). Commit the file so every teammate's agent gets it, and start your agent from the project root.

## Tools

Both servers run the same tool classes, so an answer from MCP matches the page a person reads.

| Tool | Does | Web endpoint | Local server |
| --- | --- | --- | --- |
| `search_docs` | Ranked pages for a query, each with its section, a snippet and its URLs | Yes | Yes |
| `read_page` | One page as Markdown, the same text as its `.md` twin, or one `section` of it, with its `content_hash` | Yes | Yes |
| `list_pages` | The navigation: groups, then pages with title, path and description | Yes | Yes |
| `submit_feedback` | Tells the owners whether a page helped, with an optional comment | When `feedback.enabled` | When `feedback.enabled` |
| `validate_docs` | The `larecipe:validate --json` report for a set | For callers the `manageLarecipe` gate allows | Yes |
| `affected_pages` | The pages a change made stale, from the `sources:` of each page | Never | Yes |
| `write_page`, `move_page`, `delete_page`, `update_navigation`, `update_manifest` | The author tools: change pages, navigation and `larecipe.json` | Database sets with `mcp.web.author_tools` | Yes |

The read tools return only what the caller may see: private pages, private versions and gate blocks are left out exactly as on the HTML pages. `validate_docs` reads every page, private ones included, which is why the web endpoint offers it only to managers. Every tool's arguments and results are in the [MCP tools reference](../reference/mcp-tools.md).

## Private doc sets

A set's access rules apply to MCP on every call: a guest who calls a tool on a private set gets "These docs are not available." To let signed-in agents in, authenticate the endpoint with the guard your app already uses:

```php
// config/larecipe.php
'sets' => [
    'handbook' => [
        // route, source, access ...
        'mcp' => ['web' => ['middleware' => ['auth:sanctum']]],
    ],
],
```

With `auth:sanctum`, agents send a Sanctum token as a bearer token:

```bash
claude mcp add --transport http handbook https://example.com/handbook/mcp \
  --header "Authorization: Bearer $HANDBOOK_TOKEN"
```

To use laravel/mcp's OAuth flow instead, set the middleware to `['auth:api']` and register `Mcp::oauthRoutes()` in your app. Either way, access is checked again on every tool call, page by page. [Gate blocks and private docs](../writing/gate-blocks.md) covers the rules.

## Details

<AccordionGroup>

<Accordion title="Where the web endpoint lives">

Each set with `mcp.web.enabled` gets one endpoint at its prefix plus `mcp.web.path` (`mcp` by default). A set mounted at `/docs` answers at `/docs/mcp`. This site's set is mounted at the domain root, so its endpoint is `/mcp`. A set with a `route.domain` gets its endpoint on that domain.

LaRecipe registers these routes itself, from `AgentRoutes::global()`, so there is nothing to add to `routes/ai.php`. Read live from this app's router:

<Route name="larecipe.docs.mcp" />

The endpoint takes `POST` only. It sits outside the set's route group on purpose: MCP clients post JSON without a session or a CSRF token, so the set's `route.middleware` (`web` by default) does not apply. After laravel/mcp's own middleware, LaRecipe adds `mcp.web.middleware`, then the `larecipe-mcp` rate limiter, then the binding to its set. The read tools on an endpoint only ever see that endpoint's set: an agent connected to `/help/mcp` cannot read `/handbook`.

</Accordion>

<Accordion title="Try it with curl">

MCP over HTTP is JSON-RPC. This lists the tools of this site's endpoint, running locally:

```bash
curl -s -X POST http://127.0.0.1:8780/mcp \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
```

The response, trimmed (property descriptions, the output schema and three of the four input schemas removed):

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "tools": [
      {
        "name": "search_docs",
        "title": "Search the docs",
        "description": "Search the documentation and get the best matching pages, with the section and a snippet for each. Search before you read or write a page. Pass a result's path to read_page to read it.",
        "inputSchema": {
          "type": "object",
          "properties": {
            "query": { "type": "string" },
            "version": { "type": "string" },
            "locale": { "type": "string" },
            "limit": { "type": "integer", "minimum": 1, "maximum": 20 }
          },
          "required": ["query"]
        },
        "annotations": { "readOnlyHint": true, "idempotentHint": true, "openWorldHint": false }
      },
      { "name": "read_page", "title": "Read a docs page" },
      { "name": "list_pages", "title": "List the docs pages" },
      { "name": "submit_feedback", "title": "Send feedback on a page" }
    ]
  }
}
```

Four tools: a public set's endpoint offers the read tools and `submit_feedback` to everyone. Call one the same way, with `"method":"tools/call"` and `"params":{"name":"search_docs","arguments":{"query":"mcp endpoint"}}`.

</Accordion>

<Accordion title="The local server, command and config files">

<Artisan command="larecipe:mcp" />

It is a short alias for `php artisan mcp:start larecipe`. To inspect it by hand, run `php artisan mcp:inspector larecipe`.

The installer merges the `larecipe` entry into each agent's file: other servers stay, and a `larecipe` entry you already wrote is left alone. This is the `.mcp.json` this site commits for Claude Code:

```json
{
  "mcpServers": {
    "larecipe": {
      "command": "php",
      "args": [
        "artisan",
        "larecipe:mcp"
      ]
    }
  }
}
```

Cursor (`.cursor/mcp.json`) and Gemini CLI (`.gemini/settings.json`) use the same `mcpServers` shape. GitHub Copilot (`.vscode/mcp.json`) uses `servers` in place of `mcpServers`. Codex reads TOML, in `.codex/config.toml`:

```toml
[mcp_servers.larecipe]
command = "php"
args = ["artisan", "larecipe:mcp"]
```

The command uses a relative `artisan` path, so start your agent from the project root. On the local server, the tools take an optional `set` argument to choose a doc set, and with more than one set, `list_pages` without one lists the sets first.

</Accordion>

<Accordion title="How the author tools write">

Before an agent writes, moves or deletes a page, it reads the page with `read_page` and passes the `content_hash` from the end of the result as `based_on`, so a change never overwrites a newer edit. For `update_navigation` and `update_manifest`, `based_on` is the `revision.content_hash` the tool's last call returned. See [MCP tools](../reference/mcp-tools.md#author-tools).

On the local server, the author tools write where the set lives. On a repository set they change files in your working tree, so the change becomes part of your next commit. On a database set they apply the change to your local database for preview and write it into the branch's [docs migration](../sources/docs-migrations.md), so it still travels in the pull request. Each change is attributed to the agent, named after the MCP client.

</Accordion>

<Accordion title="Author tools on the web">

By default the web endpoint never writes. A database set can open its author tools to remote agents, for changes made outside a pull request, such as an agent fixing a typo in production docs:

```php
'sets' => [
    'handbook' => [
        'source' => ['driver' => 'database'],
        'mcp' => [
            'web' => [
                'middleware' => ['auth:sanctum'],
                'author_tools' => true,
            ],
        ],
    ],
],
```

The tools appear only when all of this holds: the set uses the database source, `mcp.web.author_tools` is on, the caller is signed in, and the `manageLarecipe` gate allows them for the set. Each endpoint is bound to its own set: a call that names another set is refused, so a token for one set's endpoint never writes to another. `affected_pages` reads your working tree, so no web endpoint offers it. Their changes follow the set's [review mode](../keeping-current/review-modes.md): with `review: required` they wait as drafts until a person publishes them, with `review: auto` they publish at once, still revisioned and attributed.

A repository set never exposes author tools on the web, whatever the config says. Its changes belong in git, in a pull request.

</Accordion>

<Accordion title="Feedback and events">

`submit_feedback` fires the same `FeedbackSubmitted` event as the "Was this page helpful?" widget, with its source set to `agent`, so an agent can report a page that misled it. Every tool call, on either server, also fires `McpToolCalled` with the set, the tool, the transport and the scalar arguments. LaRecipe stores neither; see [analytics and events](../operate/analytics.md).

</Accordion>

<Accordion title="Resources">

Every page is also an MCP resource, with its Markdown as the content:

```text
docs://{set}/{version}/{path}
```

For example `docs://docs/3.x/agents/mcp` on this site. An unversioned set uses `current` as its version, and a page in another language puts the locale in front of its path (`docs://docs/3.x/ar/index`). A web endpoint serves only its own set's resources.

</Accordion>

<Accordion title="Prompts">

| Prompt | Arguments | Gives the agent |
| --- | --- | --- |
| `write-docs-page` | `topic`, `path` (optional), `set` (optional) | The steps for a new page: search first, write from the code, frontmatter with `sources`, components, navigation, validate |
| `document-change` | `change`, `set` (optional) | The steps for updating the pages a code change made stale, in the same push as the code |

</Accordion>

<Accordion title="Server instructions">

Every client gets the same workflow when it connects. This is what this site's endpoint sends:

```text
The documentation for LaRecipe. Read it to answer questions, and keep it current when the code changes.

How to work with these docs:
1. Search first: call search_docs before you read or write anything.
2. Read before you write: read_page returns a page as Markdown (pass section to read one heading). list_pages shows the navigation.
3. Validate after you write: run validate_docs and fix every error it reports.

Cite the page URLs you used. If the docs do not cover something, say so, and use submit_feedback to tell the owners when a page was wrong or missing.
```

The server names itself after the set (`LaRecipe Docs` here). On the local server with several sets, the instructions end with a line that lists them and names the default.

</Accordion>

<Accordion title="Rate limit">

Each web endpoint is throttled by the `larecipe-mcp` rate limiter, set by `mcp.web.rate_limit` as `"requests,minutes"`. The default `"60,1"` allows 60 requests a minute, counted per set and per signed-in user, or per IP address for guests. Responses carry `X-RateLimit-Limit` and `X-RateLimit-Remaining`, and a client over the limit gets `429`. A set can set its own limit in `sets.{key}.mcp.web.rate_limit`. The local server is not throttled.

</Accordion>

<Accordion title="Configuration">

The defaults, from `config/larecipe.php`. Each set can override any `mcp.web` key in its own entry under `sets`.

```php
'mcp' => [
    'web' => [
        'enabled' => true,
        'path' => 'mcp',
        'author_tools' => false,
        'middleware' => [],
        'rate_limit' => '60,1',
    ],
    'local' => true,
],
```

Turning `mcp.web.enabled` off removes the route and the **Connect MCP** action; turning `mcp.local` off unregisters `larecipe` from `mcp:start`. Every key is in the [configuration reference](../reference/configuration.md#mcp).

</Accordion>

</AccordionGroup>

## Next

- [Agent surfaces](surfaces.md): Markdown twins, `llms.txt` and `skill.md`.
- [Ask AI](ask-ai.md): the same `search_docs` and `read_page` tools, answering your readers.
- [MCP tools reference](../reference/mcp-tools.md): every tool's arguments and results.
