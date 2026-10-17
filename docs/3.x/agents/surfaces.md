---
title: Agent surfaces
description: Every page as Markdown, llms.txt indexes, a generated skill.md and discovery hints, so agents read the same docs people do.
sources:
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RequestResolver.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ResponseHeaders.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/NotFoundResponder.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/SitemapController.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/AgentMarkdownRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Agents/**
  - vendor/larecipe/larecipe/resources/views/prerender/rail.blade.php
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Agent surfaces

Every LaRecipe doc set serves agents the same content it serves people, in a form they can use: a Markdown twin of every page, `llms.txt` indexes, a full-text file per version, a generated `skill.md` and an [MCP server](mcp.md). They are on by default and follow the same access rules as the HTML, so a private page is private on every surface.

The examples below are this site's own, fetched from it. This doc set is mounted at the domain root, so its files sit at `/llms.txt` and `/skill.md`. A set mounted at `/docs` serves the same files at `/docs/llms.txt` and `/docs/skill.md`.

| Surface | This site | What it is |
| --- | --- | --- |
| Markdown twin | `/3.x/agents/surfaces.md` | Any page as Markdown. |
| Content negotiation | `Accept: text/markdown` | The same Markdown, at the page's own URL. |
| `llms.txt` | `/llms.txt`, `/3.x/llms.txt` | The map: every page with its description. |
| `llms-full.txt` | `/3.x/llms-full.txt` | One version's pages in one file. |
| `skill.md` | `/skill.md` | How an agent should use these docs. |
| Skills index | `/.well-known/agent-skills/index.json` | Every skill on the site, for `npx skills add`. |
| Sitemap | `/sitemap.xml` | Public pages, with language alternates. |
| MCP | `/mcp` | Search and read tools. See [the MCP server](mcp.md). |

## Every page as Markdown

Add `.md` to any page URL and you get the page as Markdown. Ask for the page's own URL with `Accept: text/markdown` and you get the same thing, so an agent that prefers Markdown never has to know the convention.

```bash
curl https://larecipe.saleem.dev/3.x/index.md
curl -H "Accept: text/markdown" https://larecipe.saleem.dev/3.x
```

LaRecipe picks the representation in this order: an explicit `.md` suffix wins; otherwise the `Accept` header decides, read in order of preference, so `text/markdown` wins only when the client ranks it above `text/html`. Browsers get HTML.

The Markdown twin is not the source file. It is a projection of the same parsed page the HTML comes from, written for models:

```markdown
---
title: 'Docs that keep up with your code'
description: 'LaRecipe lives inside your Laravel app. ...'
url: 'https://larecipe.saleem.dev/3.x'
version: 3.x
locale: en
last_updated: '2026-10-17'
---

> Docs index: https://larecipe.saleem.dev/3.x/llms.txt · MCP: https://larecipe.saleem.dev/mcp · Skill: https://larecipe.saleem.dev/skill.md

# Docs that keep up with your code

*Video coming soon: The maintainer explains LaRecipe*
```

- **A stable header.** `title`, `description`, `url`, `version` (on a versioned set), `locale` (on a multilingual set) and `last_updated`. Never the page's frontmatter as written, which may hold anything.
- **A discovery line.** One blockquote that points to `llms.txt`, the MCP endpoint and `skill.md`. Agents rarely find those files unless something points at them.
- **Components as portable Markdown.** A callout becomes a GitHub alert, tabs become labelled sections, a card becomes a link, a video becomes a link with its title. The [components reference](../reference/components.md) lists each one's Markdown form.
- **Live references resolved.** `<Route>`, `<Artisan>`, `<Model>` and `<Event>` arrive as their current values, as tables and code blocks. See [live references](../writing/live-references.md).
- **Absolute links.** Relative links and images become full URLs, and links to other pages point at their `.md` twins.
- **Only what the caller may see.** Gate blocks the reader may not see are removed before the Markdown is written. See [gate blocks and private docs](../writing/gate-blocks.md).

There is no separate copy to write or keep in sync. The page is written once and served twice.

## Markdown 404s

A `.md` URL or a `text/markdown` request for a page that does not exist gets a real `404` with a short Markdown body: the path it asked for, up to five similar pages from the search ranker, and the docs index.

```markdown
# Page not found

There is no page at `/agents/mcp-server` in LaRecipe.

Similar pages:

- [The MCP server](https://larecipe.saleem.dev/3.x/agents/mcp.md): ...

Docs index: https://larecipe.saleem.dev/llms.txt
```

A page that exists but is hidden from the caller gets exactly the same answer, so a 404 never confirms that a private page exists. HTML misses get the same suggestions on the not-found page.

## Headers on every page

Every page response, HTML or Markdown, carries the same headers:

```http
Vary: Accept
Link: <https://larecipe.saleem.dev/3.x/index.md>; rel="alternate"; type="text/markdown", <https://larecipe.saleem.dev/3.x/llms.txt>; rel="describedby"; type="text/plain", <https://larecipe.saleem.dev/mcp>; rel="mcp", <https://larecipe.saleem.dev/skill.md>; rel="describedby"; type="text/markdown"; title="skill"
ETag: "80ff5bd7412bd994f7dc"
Last-Modified: Sat, 17 Oct 2026 15:22:00 GMT
Cache-Control: max-age=60, public, s-maxage=600
X-LaRecipe-Build: 1b7598e6f59b
```

- `Vary: Accept` tells caches that the same URL answers with HTML or Markdown.
- `Link` advertises the Markdown twin, the version's `llms.txt`, the MCP endpoint and `skill.md` to any client that reads headers. A link appears only when its surface is on.
- `ETag` and `Last-Modified` let agents and caches revalidate cheaply. A matching `If-None-Match` gets a `304`.
- Public pages are cacheable for a minute in browsers and ten minutes in shared caches. A private page, or one rendered for a signed-in reader, is `private, no-store` with `X-Robots-Tag: noindex`.

## llms.txt

`llms.txt` is the map of a doc set, generated from its navigation, so it is never written by hand and never out of date. Each navigation group becomes a heading, and each page a line with its title, its Markdown URL and its description.

```markdown
# LaRecipe

> LaRecipe is the docs engine that lives inside your Laravel app: ...

Read the 3.x pages unless the user is on LaRecipe 2. ...

This index lists the pages of 3.x, the default version. Every version has its own index, under Versions.

Every page is also available as Markdown: the links below point to it, and adding `.md` to any page URL works too. Search and read these docs with the MCP server at `https://larecipe.saleem.dev/mcp` (tools: search_docs, read_page, list_pages). ...

## Get started

- [Installation](https://larecipe.saleem.dev/3.x/get-started/installation.md): ...

## Versions

- [3.x (default)](https://larecipe.saleem.dev/3.x/llms.txt): Current version.
- [2.x](https://larecipe.saleem.dev/2.x/llms.txt): Maintained: fixes only.

## Languages

- [English](https://larecipe.saleem.dev/llms.txt): en
- [العربية](https://larecipe.saleem.dev/ar/llms.txt): ar
```

- **The summary and instructions** come from `agents.llmsTxt.description` and `agents.llmsTxt.instructions` in [larecipe.json](../reference/manifest.md). Without them, the set's `description` is used.
- **On a versioned set,** the root `/llms.txt` lists the default version's pages, so an agent that starts there reaches the current docs in one fetch, and links every version's own index (`/3.x/llms.txt`, `/2.x/llms.txt`) with its status.
- **On a multilingual set,** each language has its own index: `/ar/llms.txt`, `/ar/3.x/llms.txt`.
- **It stays small.** Each file stays under `agents.llms_txt_limit` characters (50,000 by default, the AFDocs threshold). When a version outgrows it, each top-level navigation group moves to its own file, such as `/3.x/get-started/llms.txt`, and the index links to them. Descriptions are dropped only as a last resort.

## llms-full.txt

`/3.x/llms-full.txt` holds the agent Markdown of every page in one version, in reading order, each page with its own header. It streams, so a large version never has to fit in memory.

There is no whole-site dump on purpose: it would overflow an agent's context. A versioned set's root `/llms-full.txt` redirects to the default version's file. Past `agents.llms_full_limit` characters (400,000 by default) a version splits by navigation group, like `llms.txt`, and anything left out is listed by link, never cut silently. The Markdown twins and MCP stay the better way to read one page.

## skill.md

`/skill.md` is a skill an agent can install to learn how to use these docs: search first, read whole pages as Markdown, cite the URLs. LaRecipe writes it from the manifest (name, description, agent instructions), the navigation (up to 40 key pages with their Markdown links) and the set's connection details (MCP, `llms.txt`, `llms-full.txt`, versions). Its frontmatter uses only the portable Agent Skills keys, `name` and `description`, so any agent can load it.

```markdown
---
name: larecipe
description: 'Use when working with LaRecipe: ... Explains how to search and read the LaRecipe docs (MCP tools, llms.txt and a Markdown version of every page) and lists the key pages.'
---

# LaRecipe

## Read the docs

1. Search first: call `search_docs` on the MCP server at https://larecipe.saleem.dev/mcp, or scan the index at https://larecipe.saleem.dev/llms.txt.
2. Read whole pages as Markdown: call `read_page`, or add `.md` to any page URL.
3. Answer from the pages you read, and cite their URLs.
```

This is the skill a doc set publishes about its product, for its readers' agents. The skills LaRecipe installs in your own repository to write docs are a different thing; see [skills and the Claude Code plugin](../keeping-current/skills.md).

### Hand-written skills

A repository set can ship its own skills in a `skills/` folder at the root of its docs, one folder per skill:

```text
docs/
├── larecipe.json
├── skills/
│   └── billing-integration/
│       ├── SKILL.md
│       └── references/
│           └── webhooks.md
└── 3.x/
```

Each one is served at `/skills/{name}/SKILL.md`, with its `references/`, `scripts/` and `assets/` files next to it. A hand-written skill named like the generated one replaces `skill.md`. `php artisan larecipe:validate` checks every `SKILL.md` against the Agent Skills spec (`skills.spec`).

### The skills index

`/.well-known/agent-skills/index.json` lists every skill of every public set on the site, each with its URL and a `sha256` digest of the exact bytes served, so tools such as `npx skills add https://larecipe.saleem.dev` can find and verify them.

```json
{
  "$schema": "https://schemas.agentskills.io/discovery/0.2.0/schema.json",
  "skills": [
    {
      "name": "larecipe",
      "type": "skill-md",
      "description": "Use when working with LaRecipe: ...",
      "url": "https://larecipe.saleem.dev/skill.md",
      "digest": "sha256:b688d79694302d4ae0866b9c855ef418c38dae9714691a1e6065278581184c39"
    }
  ]
}
```

## Sitemap

`/sitemap.xml` lists every public page of a public set, with `lastmod` and `hreflang` alternates for its translations. A sitemap is a public artifact, so it lists only what a guest may see: private sets answer `404`, and private versions, private pages, hidden pages and `noindex` pages are left out.

## The search endpoint for agents without MCP

Agents that can fetch a URL but have no MCP client can search through `GET /ask?q=` (under a prefixed set, `/docs/ask?q=`). It is off by default; turn it on with `agents.ask_endpoint`:

```php
// config/larecipe.php
'agents' => [
    'ask_endpoint' => true,
],
```

It answers with ranked pages as JSON (`title`, `url`, `markdown_url`, `heading`, `snippet`, `description`, `score`), using the same ranker and the same access rules as the MCP `search_docs` tool. `version`, `locale` and `limit` (up to 20) are optional query parameters.

## For agents, on every page

Each page's rail has a **For agents** block, so a reader can hand the page to their own agent in one click. On phones the same actions sit in a menu under the title.

| Action | What it does |
| --- | --- |
| Copy as Markdown | Copies the page's agent Markdown to the clipboard. |
| View as Markdown | Opens the page's `.md` twin. |
| Open in Claude, Open in ChatGPT, Open in Cursor | Opens that agent with a prompt that points it at the page's Markdown. |
| Connect MCP | Copies the `claude mcp add` command for this set's MCP server. |

The copy actions need JavaScript; without it the block still links the Markdown twin.

## Turning surfaces off

Each surface has a switch under `agents` in `config/larecipe.php`, on by default. A set can override any of them in its own entry under `sets`.

| Key | Controls |
| --- | --- |
| `agents.llms_txt` | `llms.txt` and its path-scoped files |
| `agents.llms_full` | `llms-full.txt` |
| `agents.skill_md` | `skill.md` and the hand-written `skills/` |
| `agents.well_known_skills` | `/.well-known/agent-skills/index.json` |
| `agents.sitemap` | `sitemap.xml` |
| `agents.ask_endpoint` | the opt-in `/ask?q=` search (off by default) |
| `mcp.web.enabled` | the set's MCP endpoint |

Every fetch of these files fires a Laravel event (`MarkdownServed`, `LlmsTxtServed`, `SkillServed`) with the request kind, so agent traffic that client-side analytics never see can reach your own tools. See [analytics and events](../operate/analytics.md).

`php artisan larecipe:doctor` grades all of this from inside the app, the way AFDocs grades it from outside: Markdown parity, `llms.txt` size and links, 404 behavior, headers and redirects. See [validation and doctor](../operate/validation.md).
