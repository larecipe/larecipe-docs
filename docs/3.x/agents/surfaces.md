---
title: Agent surfaces
description: Every page as Markdown, llms.txt indexes, a generated skill.md and an MCP server, so agents read the same docs people do, under the same access rules.
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

Every doc set serves agents the same pages it serves people, in forms agents can use. They are on by default, and they follow the same access rules as the HTML, so a private page is private on every surface.

| Surface | On this site | What it is |
| --- | --- | --- |
| Markdown twin | `/3.x/agents/surfaces.md` | Any page as Markdown: add `.md`, or send `Accept: text/markdown`. |
| `llms.txt` | `/llms.txt`, `/3.x/llms.txt` | The map: every page with its description. |
| `llms-full.txt` | `/3.x/llms-full.txt` | One version's pages in one file. |
| `skill.md` | `/skill.md` | How an agent should use these docs. |
| MCP | `/mcp` | Search and read tools. See [the MCP server](mcp.md). |

```bash
curl https://larecipe.saleem.dev/3.x/index.md
```

This set is mounted at the domain root, so its files sit at `/llms.txt` and `/skill.md`. A set mounted at `/docs` serves the same files at `/docs/llms.txt` and `/docs/skill.md`.

## Every page as Markdown

Add `.md` to any page URL, or request the page's own URL with `Accept: text/markdown`, and you get the page as Markdown. An agent that prefers Markdown never has to know the convention. A landing page's twin is its URL with `.md`: `/3.x.md` and `/ar.md` on this site, or `/docs.md` for a set at `/docs`.

The twin is not your source file. It is rendered from the same parsed page as the HTML: components become portable Markdown, live references arrive as their current values, links become absolute, and gate blocks the reader may not see are removed. There is no second copy to keep in sync.

<Accordion title="What a Markdown twin holds">

```markdown
---
title: 'Beautiful docs that keep up with your code'
description: 'LaRecipe is a docs site that lives inside your Laravel app, written and kept current by ...'
url: 'https://larecipe.saleem.dev/3.x'
version: 3.x
locale: en
last_updated: '2026-10-17'
---

> Docs index: https://larecipe.saleem.dev/3.x/llms.txt · MCP: https://larecipe.saleem.dev/mcp · Skill: https://larecipe.saleem.dev/skill.md

# Beautiful docs that keep up with your code

You write pages in Markdown, in your own repository, next to the code they describe. ...
```

- **A stable header.** `title`, `description`, `url`, `version` (on a versioned set), `locale` (on a multilingual set) and `last_updated`. Never the page's frontmatter as written, which may hold anything.
- **A discovery line.** One blockquote that points to `llms.txt`, the MCP endpoint and `skill.md`. Agents rarely find those files unless something points at them.
- **Components as portable Markdown.** A callout becomes a GitHub alert, tabs become labelled sections, a card becomes a link, a video becomes a link with its title. The [components reference](../reference/components.md) lists each one's Markdown form.
- **Live references resolved.** `<Route>`, `<Artisan>`, `<Model>` and `<Event>` arrive as tables and code blocks. See [live references](../writing/live-references.md).
- **Absolute links.** Relative links and images become full URLs, and links to other pages point at their `.md` twins.
- **Only what the caller may see.** See [gate blocks and private docs](../writing/gate-blocks.md).

</Accordion>

<Accordion title="How LaRecipe picks HTML or Markdown">

An explicit `.md` suffix wins. Otherwise the `Accept` header decides, read in order of preference, so `text/markdown` wins only when the client ranks it above `text/html`. Browsers get HTML.

</Accordion>

## llms.txt and llms-full.txt

`llms.txt` is the map of a doc set. It is generated from the navigation, so you never write it by hand and it is never out of date: each navigation group becomes a heading, and each page a line with its title, its Markdown URL and its description.

The summary at the top comes from `agents.llmsTxt.description` in [larecipe.json](../reference/manifest.md), and the instructions under it from `agents.llmsTxt.instructions`. Without them, the set's `description` is used.

`/3.x/llms-full.txt` holds the Markdown of every page in one version, in reading order. There is no whole-site dump on purpose: it would overflow an agent's context.

<AccordionGroup>

<Accordion title="This site's llms.txt">

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

</Accordion>

<Accordion title="Versions, languages and size limits">

- **On a versioned set,** the root `/llms.txt` lists the default version's pages, so an agent that starts there reaches the current docs in one fetch, and links every version's own index (`/3.x/llms.txt`, `/2.x/llms.txt`) with its status.
- **On a multilingual set,** each language has its own index: `/ar/llms.txt`, `/ar/3.x/llms.txt`.
- **`llms.txt` stays small.** Each file stays under `agents.llms_txt_limit` characters (50,000 by default, the AFDocs threshold). When a version outgrows it, each top-level navigation group moves to its own file, such as `/3.x/get-started/llms.txt`, and the index links to them. Descriptions are dropped only as a last resort.
- **`llms-full.txt` streams,** so a large version never has to fit in memory. A versioned set's root `/llms-full.txt` redirects to the default version's file. Past `agents.llms_full_limit` characters (400,000 by default) a version splits by navigation group, like `llms.txt`, and anything left out is listed by link, never cut silently. The Markdown twins and MCP stay the better way to read one page.

</Accordion>

</AccordionGroup>

## skill.md

`/skill.md` is a skill an agent can install to learn how to use these docs: search first, read whole pages as Markdown, cite the URLs. LaRecipe writes it from the manifest (name, description, agent instructions), the navigation (up to 40 key pages with their Markdown links) and the set's connection details (MCP, `llms.txt`, `llms-full.txt`, versions).

This is the skill a doc set publishes about its product, for its readers' agents. The skills LaRecipe installs in your repository to write docs are a different thing; see [skills and the Claude Code plugin](../keeping-current/skills.md).

<AccordionGroup>

<Accordion title="This site's skill.md">

Its frontmatter uses only the portable Agent Skills keys, `name` and `description`, so any agent can load it.

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

</Accordion>

<Accordion title="Hand-written skills">

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

</Accordion>

<Accordion title="The skills index">

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

</Accordion>

</AccordionGroup>

## For agents, on every page

Each page's rail has a **For agents** block, so a reader can hand the page to their own agent in one click. On phones the same actions sit in a menu under the title.

| Action | What it does |
| --- | --- |
| Copy as Markdown | Copies the page's agent Markdown to the clipboard. |
| View as Markdown | Opens the page's `.md` twin. |
| Open in Claude, Open in ChatGPT, Open in Cursor | Opens that agent with a prompt that points it at the page's Markdown. |
| Connect MCP | Copies the `claude mcp add` command for this set's MCP server. |

The copy actions need JavaScript; without it the block still links the Markdown twin.

## Turn surfaces off

Each surface has a switch under `agents` in `config/larecipe.php`, on by default except the `/ask` endpoint. A set can override any of them in its own entry under `sets`.

| Key | Controls |
| --- | --- |
| `agents.llms_txt` | `llms.txt` and its path-scoped files |
| `agents.llms_full` | `llms-full.txt` |
| `agents.skill_md` | `skill.md` and the hand-written `skills/` |
| `agents.well_known_skills` | `/.well-known/agent-skills/index.json` |
| `agents.sitemap` | `sitemap.xml` |
| `agents.ask_endpoint` | the opt-in `/ask?q=` search (off by default) |
| `mcp.web.enabled` | the set's MCP endpoint |

Every page's Markdown twin is always served: it is the same content as the HTML page.

## Details

<AccordionGroup>

<Accordion title="Markdown 404s">

A `.md` URL or a `text/markdown` request for a page that does not exist gets a real `404` with a short Markdown body: the path it asked for, up to five similar pages from the search ranker, and the docs index.

```markdown
# Page not found

There is no page at `/agents/mcp-server` in LaRecipe.

Similar pages:

- [The MCP server](https://larecipe.saleem.dev/3.x/agents/mcp.md): ...

Docs index: https://larecipe.saleem.dev/llms.txt
```

A page that exists but is hidden from the caller gets exactly the same answer, so a 404 never confirms that a private page exists. HTML misses get the same suggestions on the not-found page.

</Accordion>

<Accordion title="Discovery headers">

Every page response, HTML or Markdown, carries headers that point agents at the other surfaces:

```http
Vary: Accept
Link: <https://larecipe.saleem.dev/3.x/index.md>; rel="alternate"; type="text/markdown", <https://larecipe.saleem.dev/3.x/llms.txt>; rel="describedby"; type="text/plain", <https://larecipe.saleem.dev/mcp>; rel="mcp", <https://larecipe.saleem.dev/skill.md>; rel="describedby"; type="text/markdown"; title="skill"
ETag: "80ff5bd7412bd994f7dc"
Last-Modified: Sat, 17 Oct 2026 15:22:00 GMT
```

- `Vary: Accept` tells caches that the same URL answers with HTML or Markdown.
- `Link` advertises the Markdown twin, the version's `llms.txt`, the MCP endpoint and `skill.md` to any client that reads headers. A link appears only when its surface is on.
- `ETag` and `Last-Modified` let agents and caches revalidate cheaply. A matching `If-None-Match` gets a `304`.
- Every page body also holds a link to the version's `llms.txt`, hidden from sight and from the keyboard, for agents that read the HTML but not its headers.

How long each response may be cached, for guests, signed-in readers and private pages, is listed under [security](../operate/security.md).

</Accordion>

<Accordion title="Sitemap">

`/sitemap.xml` lists every public page of a public set, with `lastmod` and `hreflang` alternates for its translations. A sitemap is a public artifact, so it lists only what a guest may see: private sets answer `404`, and private versions, private pages, hidden pages and `noindex` pages are left out.

</Accordion>

<Accordion title="The search endpoint for agents without MCP">

Agents that can fetch a URL but have no MCP client can search through `GET /ask?q=` (under a prefixed set, `/docs/ask?q=`). It is off by default; turn it on with `agents.ask_endpoint`:

```php
// config/larecipe.php
'agents' => [
    'ask_endpoint' => true,
],
```

It answers with ranked pages as JSON (`title`, `url`, `markdown_url`, `heading`, `snippet`, `description`, `score`), using the same ranker and the same access rules as the MCP `search_docs` tool. `version`, `locale` and `limit` (up to 20) are optional query parameters.

</Accordion>

<Accordion title="Events and the doctor">

Every fetch of these files fires a Laravel event (`MarkdownServed`, `LlmsTxtServed`, `SkillServed`) with the request kind, so agent traffic that client-side analytics never see can reach your own tools. See [analytics and events](../operate/analytics.md).

`php artisan larecipe:doctor` grades all of this from inside the app, the way AFDocs grades it from outside: Markdown parity, `llms.txt` size and links, 404 behavior, headers and redirects. See [validation and doctor](../operate/validation.md).

</Accordion>

</AccordionGroup>

## Next

- [The MCP server](mcp.md): search and read tools for agents.
- [Ask AI](ask-ai.md): answers for your readers, from the same tools.
- [Gate blocks and private docs](../writing/gate-blocks.md): go deeper on the access rules every surface shares.
