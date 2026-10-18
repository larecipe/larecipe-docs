---
title: Security
description: How LaRecipe keeps docs content from running code or leaking secrets, applies one access policy to every surface, and keeps agents and CI read-only by default.
sources:
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/MarkdownEnvironmentFactory.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/DocumentTransformer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/SnippetResolver.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/PageSanitizer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/UndeclaredPropsFilter.php
  - vendor/larecipe/larecipe/src/Domains/Content/Support/SafeHref.php
  - vendor/larecipe/larecipe/src/Support/SecretFiles.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/ComponentRenderer.php
  - vendor/larecipe/larecipe/src/Domains/References/Markdown/ReferenceComponents.php
  - vendor/larecipe/larecipe/src/Domains/Access/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ResponseHeaders.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/AnalyticsTags.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/Assets.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/Search/Controllers/SearchIndexController.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/ServerComposition.php
  - vendor/larecipe/larecipe/src/LaRecipe.php
  - vendor/larecipe/larecipe/packages/ui/src/request.ts
  - vendor/larecipe/larecipe/stubs/larecipe-docs-check.yml
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Security

Docs content is data, never code. Pages cannot run PHP, cannot inject scripts, cannot read your configuration and cannot include files that hold secrets. Every surface, from the HTML page to the MCP server, answers through one access policy, and a page a reader may not see does not exist for them. Agents on the web and the optional CI check are read-only unless you opt in.

This page lists what LaRecipe does, so you can review it and know where your own code takes over.

## Content cannot run code

- **Raw HTML is escaped.** A `<script>`, an `<iframe>` or an inline `onclick` in Markdown shows up as text. A repository set, whose pages are reviewed in git, may allow raw HTML with `html_input` set to `allow`; even then the tags GitHub filters, such as `<script>`, `<iframe>` and `<style>`, stay escaped. A database set always escapes it, and every page it renders also goes through an HTML sanitizer, so a gap anywhere else can never put a script, an event handler or a `javascript:` URL on a page. See [Markdown](../writing/markdown.md#raw-html).
- **Unsafe links are dropped.** `javascript:`, `vbscript:`, `file:` and most `data:` links in Markdown lose their target, and a component's URL props (`href`, `src`, `poster`) arrive empty when they use one of those schemes. Links in navigation, the navbar, the footer and the repository link must be `http`, `https` or `mailto` URLs or paths on your site: anything else is left out of every page, `larecipe:validate` reports it as `links.unsafe`, and a database set refuses to save it.
- **Attributes are allowlisted.** The `{#id .class}` syntax may set `id` and `class`, nothing else.
- **No Blade.** Markdown is never compiled or evaluated as Blade. `{{ $user->email }}` in a page is text.
- **Components are allowlisted.** A tag renders only when a PHP definition exists for it: a built-in, or one your app registered with `LaRecipe::component()`. Any other tag is escaped text and a `components.unknown` error. Props are checked against the definition on every render and by `larecipe:validate`. Built-in components drop every prop they do not declare, and so does every component on a database set, so a value LaRecipe derives (a video's player URL, a diagram's source) never comes from the author. A `Video` frames only YouTube, Vimeo and Loom players over `https`. Component code ships only from your app's own build, never from content.
- **Settings from content are checked.** Theme tokens and analytics IDs are checked against the shape they should have before they reach the page, a custom analytics script must be an `https` URL, and nothing in `larecipe.json` can add inline code. A database set takes its analytics from `config/larecipe.php` only, since its `larecipe.json` can be edited in the admin UI and over MCP, and a self-hosted analytics host counts only from config. See [analytics](analytics.md).
- **Visibility fails closed.** A `visibility` in frontmatter or on a version is trimmed and lowercased, and any value other than `public` makes the page or version private.

## Content cannot read secrets

- **No `<Config>`, no `<Env>`.** No component reads configuration or environment values, on purpose. A page that mentions a setting names it in inline code.
- **Live references show only public shape.** `<Route>`, `<Artisan>`, `<Model>` and `<Event>` describe a route's method, URI and middleware names, a command's signature, an event's payload and listeners, and models only in the namespaces `references.models.allow` lists. A `<Route>` shows the names of a form request's rules but never their arguments, middleware without parameters, never the domain, and only routes `references.routes.allow` allows (by default, your app's own, not those of packages in `vendor/`). An `<Artisan>` never describes a hidden command, and shows a default only when the command's `$signature` declares it. A `<Route>` never runs the route. See [Live references](../writing/live-references.md).
- **Snippet includes stay inside their root.** `<<< @/path` resolves only inside `snippets.root`: your app's base path for repository sets, and nothing at all for database sets unless you set a root. Even inside the root, LaRecipe refuses the files that may hold secrets, from one list shared with the [`larecipe:sync`](../keeping-current/sync.md) agent:
  - any path with a segment that starts with a dot, such as `.env`, `.git/` or `.ssh/`
  - `config/`, `bootstrap/cache/`, `storage/`, `vendor/` and `node_modules/`
  - `auth.json` and PHPUnit's files
  - keys, certificates, key stores and SSH keys, credential files, databases, dumps and logs ([the full list](../writing/code-blocks.md#what-can-be-included))

  A refused include renders a warning in its place and a `snippets.denied` error. Symlinks are followed to where the file really is, and checked there.
- **Paths never leave their folder.** Pages, docs assets and LaRecipe's own UI files are resolved to real paths and refused when they land outside their root, so `../` in a URL or a link reaches nothing.
- **The asset route serves only docs files.** `/{prefix}/_assets/...` never serves a page's Markdown source (`.md`, in any case), `larecipe.json`, `navigation.json`, a dot file or a file of unknown type, and every file goes out with `X-Content-Type-Options: nosniff`. A private version's files need the same access as its pages. The route is rate limited per reader by `assets.rate_limit` (600 requests a minute by default).

## One access policy

Every surface asks the same policy whether a reader may see a page: the HTML page, its Markdown twin, the navigation JSON, `llms.txt`, `llms-full.txt`, `skill.md`, the sitemap, search, MCP, Ask AI and page feedback.

- **Who may enter** is the set's `access.mode`: `public`, `auth` (any signed-in user) or `gate` (your `viewLarecipe` gate). Private versions and pages always need the gate.
- **A hidden page is a 404, never a 403**, in every format. The response is the same as for a page that does not exist, so access rules never confirm that a private page is there, and the suggestions on a Markdown 404 list only pages the caller may see.
- **Gate blocks are removed, not hidden.** A `<Can>` block a reader may not see is taken out of the page before it renders, so it is not in the HTML, the Markdown, the table of contents or the search records they get. See [Gate blocks](../writing/gate-blocks.md).
- **Search never ships what a reader may not see.** Each reader's browser downloads an index built for what they may see, and asking for another reader's index file redirects to your own. The server ranker filters the same way. See [Search](search.md).

Responses say how they may be cached, so a shared cache or a CDN never hands one reader's page to another:

| Response | `Cache-Control` | `X-Robots-Tag` |
| --- | --- | --- |
| A public page, for a guest | `public, max-age=60, s-maxage=600` | |
| A public page that carries a CSRF token, or the HTML a signed-in reader gets | `private, no-cache` | |
| A private page, a page opened with a share link, or a page whose gate blocks render differently for this reader | `private, no-store`, with no `ETag` | `noindex` |
| A page with `noindex: true` in its frontmatter | as above | `noindex` |

A public page with gate blocks also sends `Vary: Accept, Cookie`, and a page opened with a [share link](../reference/commands.md) sends `Referrer-Policy: no-referrer`, so the token in its URL never leaks to the next site.

## Custom components

Your own React components run with your app's permissions, so LaRecipe keeps their reach small:

- **`request()` calls your own routes only.** It refuses any URL on another origin, sends the session cookie and Laravel's XSRF token, and goes through your routes and middleware like any other request.
- **No user data reaches the browser** unless you share it with `LaRecipe::shareUser()`, which picks the fields: `LaRecipe::shareUser(fn ($user) => ['name' => $user->name])`.
- **CSRF tokens are never cached.** Rendered pages are cached and shared between readers, so a fallback form prints `LaRecipe::csrfField()`, a placeholder LaRecipe fills with the reader's own token on every response. A response that carries one is marked `private`, and a [static export](static-export.md) empties it.
- **A Content Security Policy nonce** set with `LaRecipe::useCspNonce()` is added to every script tag LaRecipe prints: the UI, the import map, your extension, the color-mode script and the analytics tags.

## Agents are read-only by default

- **The web MCP endpoint** of each set serves the read tools (`search_docs`, `read_page`, `list_pages`) and `submit_feedback`, which fires an event and stores nothing. Every call goes through the access policy with the caller's identity, and the endpoint is rate limited by `mcp.web.rate_limit`. To serve private docs to agents, put authentication in front of it with `mcp.web.middleware`, for example `['auth:sanctum']`.
- **`validate_docs`** reads every page, private ones included, so the web endpoint offers it only to callers your `manageLarecipe` gate allows.
- **Author tools never run on the web for a repository set**: its changes belong in git. For a database set they appear only when you turn on `mcp.web.author_tools`, and then only for a signed-in caller the `manageLarecipe` gate allows.
- **The local MCP server** (`php artisan larecipe:mcp`) has the author tools. It runs on your machine, as you, for the coding agent you started.

## No AI in CI

The optional CI check that `larecipe:install` can add runs `larecipe:validate` and `larecipe:affected` on pull requests. It runs no AI agent, never commits or pushes, and needs no secrets for a repository set, so it is safe on pull requests from forks. Its token can read the repository and, in comment mode, write the comment, nothing else. Your coding agent updates the docs on your machine, before the push; CI only checks that it did. See [the CI check](../keeping-current/ci-check.md).

## Next

- [Gate blocks](../writing/gate-blocks.md): content per reader, on every surface.
- [Custom React components](../customize/custom-components.md): `request()`, `shareUser()` and the fallback form.
- [Deployment](deployment.md): the production checklist.
