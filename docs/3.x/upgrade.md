---
title: Upgrade from v2
description: Move a LaRecipe 2 site to LaRecipe 3 with one command and a report, then finish the manual items with your coding agent and the larecipe-upgrade-v2 skill.
sources:
  - vendor/larecipe/larecipe/src/Domains/Upgrade/**
  - vendor/larecipe/larecipe/src/Domains/Access/Services/PageAccess.php
  - vendor/larecipe/larecipe/src/Domains/Access/Laravel/GateAuthorizer.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/PageSummary.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/BuiltIn/Inline.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/DocumentProcessor.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/DocsStylesheets.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/AgentsCommand.php
  - vendor/larecipe/larecipe/resources/boost/skills/larecipe-upgrade-v2/**
  - routes/web.php
---

# Upgrade from v2

`php artisan larecipe:upgrade` converts a LaRecipe 2 site to LaRecipe 3: your config, each version's sidebar and every page. It starts as a dry run that only writes a report. When the plan looks right, `--apply` writes exactly that plan, and your coding agent finishes the rest with the `larecipe-upgrade-v2` skill.

Start from a clean working tree, so the upgrade is one diff you can read and undo. LaRecipe 3 needs PHP 8.3 and Laravel 12.62 or newer; see [installation](get-started/installation.md).

<Steps>

<Step title="Require LaRecipe 3">

```bash
composer require larecipe/larecipe:^3.0
```

The upgrade reads your v2 files, not the v2 package. If your app calls `BinaryTorch\LaRecipe` classes while it boots, such as `LaRecipe::script()` in a service provider, keep `binarytorch/larecipe` installed until the upgrade has run. If Composer cannot install both, remove the v2 package first and comment those calls out.

</Step>

<Step title="Read the dry run">

```bash
php artisan larecipe:upgrade
```

Nothing in your app changes. The command writes `larecipe-upgrade-report.md` and its JSON twin at the root of your project. Run it as often as you like.

</Step>

<Step title="Apply it">

```bash
php artisan larecipe:upgrade --apply --move-to=docs --relative-links
```

Both flags are optional. `--move-to=docs` moves the docs from `resources/docs/` into `docs/`, where v3 keeps them. `--relative-links` turns `/docs/{{version}}/page` links into relative `.md` links; use it when your route prefix will change.

</Step>

<Step title="Swap the package">

```bash
composer remove binarytorch/larecipe
```

Then change `BinaryTorch\LaRecipe\` to `LaRecipe\` in your code and [update the viewLarecipe gate](#update-the-viewlarecipe-gate). The report lists every line to change.

</Step>

<Step title="Finish with your coding agent">

Install the LaRecipe skills, then ask your agent to finish:

```bash
php artisan larecipe:agents
```

```text
Use the larecipe-upgrade-v2 skill to finish the LaRecipe upgrade.
```

</Step>

<Step title="Check the result">

```bash
php artisan larecipe:validate
```

Then go through [check after the upgrade](#check-after-the-upgrade), open the docs in your browser and commit.

</Step>

</Steps>

This site went through the same upgrade: its [2.x docs](/2.x) are the LaRecipe 2 docs, converted.

## Update the viewLarecipe gate

The gate keeps its name, but its arguments changed. v2 passed the documentation page object. v3 passes a page summary and the set key:

```php
// LaRecipe 2
Gate::define('viewLarecipe', function ($user, $documentation) {
    return $user->isAdmin();
});
```

```php
// LaRecipe 3
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use LaRecipe\Domains\Content\DTOs\PageSummary;

Gate::define('viewLarecipe', function (?User $user, ?PageSummary $page = null, ?string $set = null) {
    return $user?->isAdmin() ?? false;
});
```

Type the user as nullable if guests should ever pass: Laravel denies guests before your closure runs otherwise. In v3 the gate guards every surface, not only the HTML page. [Gate blocks and private docs](writing/gate-blocks.md) covers the arguments, access modes and the other gates.

## Finish with the larecipe-upgrade-v2 skill

`php artisan larecipe:agents` installs the skill into `.claude/skills/` and `.agents/skills/`, next to the other LaRecipe skills, and writes the LaRecipe instructions into `AGENTS.md`. With Laravel Boost, run `php artisan boost:update` instead.

The agent reads the report and its JSON twin, then works through the manual items: anything that breaks rendering first, then content, then looks.

- **Blade in Markdown** becomes components, snippet includes (`<<< @/path#region`) or plain text. Values that came from config become prose that names the key, never the value.
- **Custom Vue components** become React components built with `@larecipe/vite`, each registered with `LaRecipe::component()`. Until one is rebuilt, the agent replaces the tag with plain Markdown. See [custom components](customize/custom-components.md).
- **Themes and custom CSS** move to the theme tokens in `larecipe.json` (accent, fonts, radius), and the rest goes in a `theme.css` listed under `assets.css`. See [theme](customize/theme.md#add-your-own-stylesheet).
- **Every page** gets a `description` and, where it documents code, `sources:`. Pasted route URIs or command signatures become [live references](writing/live-references.md).

It finishes when `php artisan larecipe:validate` passes, and leaves the commit to you, following the review mode in `AGENTS.md`.

## Check after the upgrade

The converter is deterministic, not perfect. Work through the "Left for you" list in the report, then check these:

- **Badges and buttons.** A v2 badge or button on its own line becomes `<Badge tone="success">Completed</Badge>` or `<Button href="..." variant="primary">Text</Button>`. A v2 type with no v3 match, icons and styling attributes are dropped and listed in the report, and a badge with no text is removed. Look at each one in the browser.
- **Links that kept `{{version}}`.** A `/docs/{{version}}/...` link stays as it was unless you passed `--relative-links`. It still works while the prefix in it is right. Find what is left with `grep -rn '{{' docs/`, and rewrite each one as a relative `.md` link if your prefix changed.
- **Old URLs.** If your route prefix changed, keep old links and search results working with redirects.

<Accordion title="How this site keeps its v2 URLs working">

This site moved its v2 docs from `/docs/{version}/{page}` to `/2.x/{page}`, and its `routes/web.php` holds:

```php title="routes/web.php"
Route::permanentRedirect('/docs', '/');
Route::permanentRedirect('/docs/{version}', '/2.x');
Route::permanentRedirect('/docs/{version}/{page}', '/2.x/{page}');
```

Use your app's routes for a whole old prefix. Within one prefix, use `redirects` in `larecipe.json` instead; see [navigation](writing/navigation.md).

</Accordion>

## What the upgrade changes

The details behind the report, for when you want to know exactly what happened to a file.

<AccordionGroup>

<Accordion title="What the command writes">

| Run | Writes |
| --- | --- |
| `php artisan larecipe:upgrade` | Only the report: `larecipe-upgrade-report.md` and `larecipe-upgrade-report.json`. |
| `php artisan larecipe:upgrade --apply` | The new `config/larecipe.php` (your v2 file is kept as `config/larecipe.v2.php.bak`), `larecipe.json`, a `navigation.json` per version, every converted page, and the report. |

`--apply` writes new and converted files first and removes old ones last, so an interrupted run never loses a page. With `--move-to`, the target folder must be empty or missing, and the old folder is removed once nothing is left in it.

The command stops early when there is nothing to upgrade: when there is no `config/larecipe.php`, when that file already has the v3 `sets` key, or when the docs folder it names does not exist. After `--apply` your config is v3, so to run the upgrade again, restore the v2 files from git first.

</Accordion>

<Accordion title="Config: one file becomes two">

Your v2 config becomes two files: infrastructure in `config/larecipe.php`, content and presentation in `larecipe.json`. See [configuration basics](get-started/configuration.md).

| v2 | v3 |
| --- | --- |
| `docs.route` | `sets.docs.route.prefix` |
| `docs.path` | `sets.docs.source.path` (or the `--move-to` folder) |
| `docs.middleware` and `settings.middleware` | `sets.docs.route.middleware` |
| `settings.auth` | `access.mode` `auth` |
| A `viewLarecipe` gate in your code | `access.mode` `gate` (otherwise `public`) |
| `versions.published`, `versions.default` | `versions` in `larecipe.json` |
| `docs.landing` | `landing` in `larecipe.json` |
| `ui.fav` | `favicon` in `larecipe.json` |
| `ui.colors.primary` | `theme.accent` in `larecipe.json` |
| `seo.description` | `description` in `larecipe.json` |
| `settings.ga_id` | A GA4 integration under `integrations.analytics` in `larecipe.json` |

The manifest's `name` comes from your `APP_NAME`. The written config holds the `docs` set and nothing else; every other key keeps the package default. Publish the full file with `php artisan vendor:publish --tag=larecipe-config` only if you want it in your app.

</Accordion>

<Accordion title="Navigation: the v2 sidebar becomes navigation.json">

Each version's `index.md` was its v2 sidebar. The upgrade parses its `## Group` headings and links into that version's `navigation.json`, keeps a link's label when it differs from the page title, keeps outside links as links, and removes the old `index.md`. A sidebar line that is neither a group heading nor a link is reported. A version without an `index.md` gets [navigation from its folders](writing/navigation.md).

</Accordion>

<Accordion title="Pages: what each line becomes">

Each page is rewritten line by line. Code blocks are never touched.

| v2 | v3 |
| --- | --- |
| `> {warning} text`, `> {info} text` and the other alert types | GitHub alerts: `primary` becomes `IMPORTANT`, `secondary` and `info` become `NOTE`, `success` becomes `TIP`, `warning` stays `WARNING`, `danger` becomes `CAUTION` |
| `<a name="x"></a>` before a heading | A `{#x}` heading id, so old `#x` links still land |
| `<larecipe-card>` | `<Card>` |
| `<larecipe-badge>`, `<larecipe-button>` on their own line | `<Badge tone="success">Text</Badge>`, `<Button href="..." variant="primary">Text</Button>`, with the v2 type mapped to a tone or variant |
| `<larecipe-badge>`, `<larecipe-button>` inside a sentence | Their text |
| `/{{route}}/{{version}}/page#anchor` links | Relative links: `page.md#anchor` |
| `/docs/{{version}}/page` links | Kept, because v3 still fills in `{{version}}`; relative with `--relative-links` |
| Simple `<ul>` and `<ol>` lists, one `<li>` per line | Markdown lists |
| A line holding only `<br>` | Removed |
| The first `# Heading` and the paragraph after it | `title` and `description` frontmatter; the paragraph leaves the body unless it holds a link |
| A hand-written table of contents under the title | Removed: v3 builds one from the headings |

Links are rewritten everywhere, inside converted alerts too. Frontmatter a page already had is kept; the upgrade only adds `title` and `description` when they are missing.

</Accordion>

<Accordion title="What it only reports">

Some things need a person, or your agent. The upgrade lists them under "Left for you" in the report, with the file and line:

- Blade in Markdown (`@if`, `@include`, `{{ $variable }}`, `{!! !!}`). v3 does not evaluate Blade in content.
- Custom Vue components, and any `larecipe-` tag it could not convert.
- Raw HTML, which v3 shows as text. Replace it with Markdown or a component, leave it as text, or let a repository set allow it (see [Markdown](writing/markdown.md#raw-html)).
- An `<a name>` anchor with no heading after it.
- `LaRecipe::script()` and `LaRecipe::style()` calls, uses of the `BinaryTorch\LaRecipe` namespace and your `viewLarecipe` gate, in `app/`, `routes/`, `config/` and `bootstrap/`.
- Published v2 views in `resources/views/vendor/larecipe/`.
- Theme and asset packages in the v2 packages folder (`larecipe-components/` by default).
- `settings.guard`: v3 uses your default guard, so add that guard's auth middleware to the set's `route.middleware`, or make it the default.
- A `README.md` in a version folder, which v3 serves as a page.

</Accordion>

<Accordion title="What it drops on purpose">

These v2 settings have no v3 counterpart. The report lists the ones your config used:

- `cache`: v3 always caches rendered pages by content. Run `php artisan optimize` on deploy, which runs `larecipe:optimize`.
- `search` and Algolia: search is built in. Remove the Algolia keys from your `.env`.
- `ui.code_theme`: code blocks follow the page's light or dark mode.
- `ui.colors.secondary`: v3 themes have one accent color.
- `ui.fa_v4_shims` and Font Awesome: v3 uses Lucide icons. Icons on v2 alerts are dropped; GitHub alerts carry their own.
- `ui.theme_order`: there are no stacked theme packages.
- `ui.show_side_bar`: use `layout: wide` or `layout: full` in a page's frontmatter.
- `seo.author`, `seo.keywords`, `seo.og`: each page describes itself through its frontmatter.
- `forum` (Disqus): the built-in feedback widget sends a `FeedbackSubmitted` event you can forward anywhere.
- `blade-parser`.
- `<larecipe-progress>` bars, and the styling attributes on cards, badges and buttons.

The v2 add-on packages go too, with a `composer remove` line each: `binarytorch/larecipe-dark-theme` and `binarytorch/larecipe-rtl` (both built in now), `binarytorch/larecipe-feedback` (the feedback widget is built in), and `binarytorch/larecipe-swagger` (OpenAPI pages are planned for a later 3.x release; link to your spec until then).

</Accordion>

<Accordion title="The report">

`larecipe-upgrade-report.md` is for you, and `larecipe-upgrade-report.json` says the same for the skill. Both have:

1. **Composer and code**: the commands to run, the namespace change and the gate change.
2. **Left for you**: a checklist of manual items, each with its file and line and a suggested fix.
3. **Files**: every file the upgrade writes, copies or removes, and why.
4. **What changed**: what was converted, per page and in total.
5. **Dropped on purpose**: what has no v3 counterpart.
6. **Validation**: every converted page rendered with the v3 renderer and checked for invalid components, page problems, links to pages that do not exist and missing anchors.

The report names config keys but never copies their values, such as an Algolia key or an analytics ID, so it is safe to paste into an issue.

</Accordion>

<Accordion title="The package and the namespace">

| | LaRecipe 2 | LaRecipe 3 |
| --- | --- | --- |
| Package | `binarytorch/larecipe` | `larecipe/larecipe` |
| Namespace | `BinaryTorch\LaRecipe\` | `LaRecipe\` |

The v3 facade is `LaRecipe\Facades\LaRecipe`, aliased as `LaRecipe`. Check each class you use still exists: much of the v2 API, such as `LaRecipe::script()` and `LaRecipe::style()`, is gone. `LaRecipe::head()` prints stylesheets and scripts into every docs page's `<head>`, and [custom components](customize/custom-components.md) replace custom JavaScript.

</Accordion>

<Accordion title="v2 features in v3">

| LaRecipe 2 | LaRecipe 3 |
| --- | --- |
| One docs site per app | [Doc sets](get-started/configuration.md): several independent sites in one app, each with its own route, source, access and agent surfaces |
| Versions in config | `versions` in `larecipe.json`, with a status per version, one folder each. See [versions](writing/versions.md). |
| Sidebar in each version's `index.md` | `navigation.json`, or navigation derived from folders. See [navigation](writing/navigation.md). |
| `settings.auth` and the `viewLarecipe` gate | `access.mode` (`public`, `auth`, `gate`), the same gate on every surface, private pages and versions, and `<Can>` blocks. See [gate blocks and private docs](writing/gate-blocks.md). |
| Parsedown | league/commonmark with GitHub Flavored Markdown. See [Markdown](writing/markdown.md). |
| `> {type}` alerts | GitHub alerts and `<Callout>` |
| Blade in Markdown | Not evaluated. [Components](writing/components.md), [snippet includes](writing/code-blocks.md) and live references instead. |
| `{{version}}` in links | Still filled in; relative `.md` links are preferred |
| `{{route}}` in links | Relative `.md` links |
| Card, badge and button Vue components | `<Card>`, `<Badge>`, `<Button>` |
| Progress bar component | Removed |
| Custom Vue components | [Custom React components](customize/custom-components.md) with a server fallback |
| Algolia or the built-in search | Built-in search in the browser, a server ranker, or a custom driver. See [search](operate/search.md). |
| Dark theme package | Built in: light, dark or the reader's system setting |
| RTL package | Built in: a locale with `"dir": "rtl"`. See [languages](writing/languages.md). |
| `colors.primary`, `fav` | `theme.accent` and `favicon` in `larecipe.json`. See [theme](customize/theme.md). |
| `code_theme` | Code blocks follow light and dark mode |
| `show_side_bar` | `layout: wide` or `layout: full` in frontmatter |
| Font Awesome | Lucide icons |
| `LaRecipe::script()`, `LaRecipe::style()` | `assets.css` in `larecipe.json`, `LaRecipe::head()`, and React extensions |
| Theme and asset packages | Extensions built with `@larecipe/vite` |
| Published views | Named slots you override from an extension, such as `Header`, `Logo`, `Sidebar` and `Footer`, and `LaRecipe::head()` |
| Cache settings | Always cached by content; `php artisan optimize` on deploy. See [caching](operate/caching.md). |
| SEO settings | Per-page `description`, canonical URLs, JSON-LD and a sitemap |
| `ga_id` | Analytics integrations: GA4, Plausible, Fathom, PostHog or a custom script |
| Disqus | The feedback widget, and edit links when `repository` is set in `larecipe.json` |
| Keyboard shortcuts | `⌘K` or `/` to search, `?` for every shortcut |
| Feedback package | Built in, sending `FeedbackSubmitted` to your app |
| Swagger package | OpenAPI pages, planned for a later 3.x release |
| `larecipe:docs` | `larecipe:make:version` and `larecipe:make:page` |
| `larecipe:theme`, `larecipe:asset` | Extensions built with `@larecipe/vite` |

And what v2 never had: Markdown twins, `llms.txt`, `skill.md` and an MCP server for agents, live references, provenance on every page, and agents that keep the docs current with your code. Start with [how it works](keeping-current/how-it-works.md).

</Accordion>

<Accordion title="Every option">

Read live from this app's command registry:

<Artisan command="larecipe:upgrade" />

</Accordion>

</AccordionGroup>

## Next

- [Configuration basics](get-started/configuration.md): what the new `config/larecipe.php` holds.
- [Gate blocks and private docs](writing/gate-blocks.md): the gates in v3.
- [Installation](get-started/installation.md): the agent setup, the MCP server and the hooks, for a site you have upgraded.
