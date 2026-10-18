---
title: Static export
description: Write your public docs to a folder of plain files any static host can serve, with search that works offline, and know exactly what a static site leaves out.
sources:
  - vendor/larecipe/larecipe/src/Domains/Export/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/DocsStylesheets.php
  - vendor/larecipe/larecipe/ui/src/app/router.ts
---

# Static export

`larecipe:export` writes your public doc sets to a folder of plain files: every page as HTML and as Markdown, `llms.txt`, `skill.md`, the sitemap, the search index and the UI. Any static host can serve the folder, and search keeps working because it runs in the browser.

It is an advanced, opt-in feature. Most apps serve their docs from the app itself, where gates, Ask AI, MCP and feedback work. Export when you need docs on a host that runs no PHP.

<Artisan command="larecipe:export" />

## Export a site

```bash
php artisan larecipe:export --out=build/docs --base-url=https://docs.acme.dev
```

- `--out` is the folder to write, relative to your app's root unless it starts with `/`. It defaults to `build/docs`.
- `--set` picks the doc sets to export. Repeat it for several: `--set=docs --set=api`. Without it, every set is exported, and private sets are skipped.
- `--base-url` is where the files will be served. Canonical links, absolute URLs in the Markdown twins, `llms.txt` and the sitemap use it, and a base URL with a path (`https://acme.github.io/docs`) prefixes every link and asset on the page. Without it, the export uses your `APP_URL` and says so.

This site's export, run while these pages were being written (the output folder shortened):

```text
  docs ......................................... 126 pages, 126 Markdown twins

  Left out of the static site:
    - Ask AI: it answers through your server and your AI provider.
    - The MCP server: agents connect to a running app.
    - Page feedback: it reports to your app as a FeedbackSubmitted event.
    - Server search (/_search?q=): the static site searches in the browser.
    - Share links: the server checks them.

   INFO  Wrote 443 files to /private/tmp/…/export.
```

The 126 pages are every page of 3.x and 2.x twice: once in English and once under the Arabic URLs, where a page without a translation falls back to English, as it does on the live site.

## What it writes

<FileTree>

- index.html
- 404.html
- llms.txt
- skill.md
- sitemap.xml
- larecipe-assets/
  - d6a0a96acf5f/
- brand/
- favicon.svg
- build/
  - larecipe/
- 3.x/
  - index.html
  - index.md
  - llms.txt
  - llms-full.txt
  - _search/
    - e83bc2e9096f.b3d38bfc22f8.json
  - operate/
    - caching/
      - index.html
    - caching.md
- 2.x/
- ar/

</FileTree>

- **Pages** as `path/index.html`, so a static host serves `/3.x/operate/caching/` without a rewrite rule, and each page's Markdown twin next to it as `path.md`.
- **Agent files**: `llms.txt`, `skill.md` and `sitemap.xml` for the set, and `llms.txt` and `llms-full.txt` for each version. A set without versions gets one `llms-full.txt`.
- **The search index** that guests download, one per version and language.
- **The UI**: LaRecipe's built files under `larecipe-assets/` (or `vendor/larecipe/` when you published them), plus images and other files your pages point at that the app serves through its routes, and the stylesheets listed under `assets.css` in `larecipe.json`.
- **Files from your `public/` folder** that the exported pages reference, such as your logo and favicon, copied as they are: never PHP files, dot files or anything outside `public/`. The build of your [custom components](../customize/custom-components.md) in `public/build/larecipe/` is copied whole, because it loads chunks no page names.
- **The set's entry**: its landing page, or a small page that redirects to the default version, and its not-found page as `404.html`, for versioned and unversioned sets alike.

Pages are adjusted for a static host: links to other pages end in a slash, CSRF tokens are emptied, and the page is marked static, so the UI loads each page in full instead of asking the server for JSON. Exported pages leave out what would call your server: the feedback widget, Ask AI, the Connect MCP action and the palette's search reporting.

## Exported as a guest

The export reads your site the way a signed-out reader would. It sends each request through your app's own HTTP kernel, in the same process, with your middleware, gates and renderers. No server needs to run.

- **Private sets** (`access.mode` set to `auth` or `gate`) are skipped and listed. A static host cannot check who is reading.
- **Private versions and pages** are left out and counted in the list.
- **Gate blocks** (`<Can>` and `<Cannot>`) render as a signed-out reader sees them.
- **Live references** resolve at export time. A `<Route>` or `<Artisan>` table holds the values your app had when you ran the export, so export again after you change them.

The requests carry the user agent `LaRecipe-Export/3 (static export)` and fire LaRecipe's [traffic events](analytics.md) like any other.

## What it leaves out

The list the command prints is the list of features that need a running app: Ask AI, MCP, page feedback, the server search endpoint and share links. Searches still work in the browser, but they no longer fire `SearchPerformed`, since there is no server to report them to. One more thing is yours to handle:

- **Forms and requests in custom components.** A component's fallback and its React code may post to routes in your app, such as this site's newsletter form. A static host has none of them.

## Next

- [Search](search.md): how the browser searches the exported index.
- [Deployment](deployment.md): serving the docs from your app instead.
- [Live references](../writing/live-references.md): what resolves at export time.
