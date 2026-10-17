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
  - vendor/larecipe/larecipe/src/Domains/Sync/Commands/AgentsCommand.php
  - vendor/larecipe/larecipe/resources/boost/skills/larecipe-upgrade-v2/**
  - routes/web.php
---

# Upgrade from v2

`php artisan larecipe:upgrade` converts a LaRecipe 2 site to LaRecipe 3: your config, each version's sidebar and every page. It starts as a dry run that writes a report of what it would change, what it drops on purpose and what it leaves for you. When the plan looks right, `--apply` writes exactly that plan. Your coding agent then works through the rest with the `larecipe-upgrade-v2` skill.

This site went through the same upgrade: its [2.x docs](/2.x) are the LaRecipe 2 docs, converted.

## Upgrade in six steps

Start from a clean working tree, so the upgrade is one diff you can read and undo. LaRecipe 3 needs PHP 8.3 and Laravel 12.62 or newer; see [installation](get-started/installation.md).

<Steps>

<Step title="Require LaRecipe 3">

```bash
composer require larecipe/larecipe
```

The upgrade reads your v2 files (`config/larecipe.php`, the docs folder, `composer.json` and your app code), not the v2 package, so it works whether or not `binarytorch/larecipe` is still installed. If your app calls `BinaryTorch\LaRecipe` classes while it boots, such as `LaRecipe::script()` in a service provider, keep the v2 package until the upgrade has run, or artisan cannot start. If Composer cannot install both side by side, remove `binarytorch/larecipe` first and comment those calls out.

</Step>

<Step title="Run the dry run and read the report">

```bash
php artisan larecipe:upgrade
```

Nothing in your app changes. The command prints a summary and writes `larecipe-upgrade-report.md` (and its JSON twin) at the root of your project. Run it as often as you like.

</Step>

<Step title="Apply it">

```bash
php artisan larecipe:upgrade --apply --move-to=docs --relative-links
```

Both flags are optional. `--move-to=docs` moves the docs from `resources/docs/` into `docs/`, where v3 keeps them by default. `--relative-links` also rewrites links written as `/docs/{{version}}/page` into relative `.md` links; use it when your route prefix will change.

</Step>

<Step title="Swap the package">

Run the Composer commands the report lists, usually:

```bash
composer remove binarytorch/larecipe
```

Then change `BinaryTorch\LaRecipe\` to `LaRecipe\` in your code and update the `viewLarecipe` gate (below).

</Step>

<Step title="Finish with your coding agent">

Install the LaRecipe skills, then ask your agent to finish the upgrade:

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

## What the command does

| Run | Writes |
| --- | --- |
| `php artisan larecipe:upgrade` | Only the report: `larecipe-upgrade-report.md` and `larecipe-upgrade-report.json`. |
| `php artisan larecipe:upgrade --apply` | The new `config/larecipe.php` (your v2 file is kept as `config/larecipe.v2.php.bak`), `larecipe.json`, a `navigation.json` per version, every converted page, and the report. |

`--apply` writes new and converted files first and removes old ones last, so an interrupted run never loses a page. With `--move-to`, the target folder must be empty or missing, and the old folder is removed once nothing is left in it.

The command stops early when there is nothing to upgrade: when there is no `config/larecipe.php`, when that file already has the v3 `sets` key, or when the docs folder it names does not exist. After `--apply`, your config is v3, so to run the upgrade again, restore the v2 files from git first.

## What it converts

### Config

Your v2 config becomes two files, following the [two-files rule](get-started/configuration.md): infrastructure in `config/larecipe.php`, content and presentation in `larecipe.json`.

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

The manifest's `name` comes from your `APP_NAME`. The written config is short: it holds the `docs` set and nothing else, and every other key keeps the package default. Publish the full file with `php artisan vendor:publish --tag=larecipe-config` only if you want the reference in your app.

### Navigation

Each version's `index.md` was its v2 sidebar. The upgrade parses its `## Group` headings and links into that version's `navigation.json`, keeps a link's label when it differs from the page title, keeps outside links as links, and removes the old `index.md`. A sidebar line that is neither a group heading nor a link is reported. A version without an `index.md` gets [navigation from its folders](writing/navigation.md).

### Pages

Each page is rewritten line by line. Code blocks are never touched.

| v2 | v3 |
| --- | --- |
| `> {warning} text`, `> {info} text` and the other alert types | GitHub alerts: `primary` becomes `IMPORTANT`, `secondary` and `info` become `NOTE`, `success` becomes `TIP`, `warning` stays `WARNING`, `danger` becomes `CAUTION` |
| `<a name="x"></a>` before a heading | A `{#x}` heading id, so old `#x` links still land |
| `<larecipe-card>` | `<Card>` |
| `<larecipe-badge>`, `<larecipe-button>` on their own line | `<Badge>`, `<Button>` (check these, [below](#check-after-the-upgrade)) |
| `<larecipe-badge>`, `<larecipe-button>` inside a sentence | Their text |
| `/{{route}}/{{version}}/page#anchor` links | Relative links: `page.md#anchor` |
| `/docs/{{version}}/page` links | Kept, because v3 still fills in `{{version}}`; relative with `--relative-links` |
| Simple `<ul>` and `<ol>` lists, one `<li>` per line | Markdown lists |
| A line holding only `<br>` | Removed |
| The first `# Heading` and the paragraph after it | `title` and `description` frontmatter; the paragraph leaves the body unless it holds a link |
| A hand-written table of contents under the title | Removed: v3 builds one from the headings |

Frontmatter a page already had is kept; the upgrade only adds `title` and `description` when they are missing.

## What it only reports

Some things need a person, or your agent. The upgrade finds them and lists them under "Left for you" in the report, with the file and line:

- Blade in Markdown (`@if`, `@include`, `{{ $variable }}`, `{!! !!}`). v3 does not evaluate Blade in content.
- Custom Vue components, and any `larecipe-` tag it could not convert.
- Raw HTML, which v3 shows as text.
- An `<a name>` anchor with no heading after it.
- `LaRecipe::script()` and `LaRecipe::style()` calls, uses of the `BinaryTorch\LaRecipe` namespace and your `viewLarecipe` gate, in `app/`, `routes/`, `config/` and `bootstrap/`.
- Published v2 views in `resources/views/vendor/larecipe/`.
- Theme and asset packages in the v2 packages folder (`larecipe-components/` by default).
- `settings.guard`: v3 uses your default guard, so add that guard's auth middleware to the set's `route.middleware`, or make it the default.
- A `README.md` in a version folder, which v3 serves as a page.

## What it drops

These v2 settings have no v3 counterpart, on purpose. The report lists the ones your config used:

- `cache`: v3 always caches rendered pages by content. Run `php artisan larecipe:optimize` on deploy, or `php artisan optimize`, which runs it.
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

The v2 add-on packages are dropped too, with a `composer remove` line each: `binarytorch/larecipe-dark-theme` and `binarytorch/larecipe-rtl` (both built in now), `binarytorch/larecipe-feedback` (the feedback widget is built in), and `binarytorch/larecipe-swagger` (OpenAPI pages are planned for a later 3.x release; link to your spec until then).

## The report

`larecipe-upgrade-report.md` is for you, and `larecipe-upgrade-report.json` says the same for the skill. Both have:

1. **Composer and code**: the commands to run, the namespace change and the gate change.
2. **Left for you**: a checklist of manual items, each with its file and line and a suggested fix.
3. **Files**: every file the upgrade writes, copies or removes, and why.
4. **What changed**: what was converted, per page and in total.
5. **Dropped on purpose**: what has no v3 counterpart.
6. **Validation**: every converted page rendered with the v3 renderer and checked for invalid components, page problems, links to pages that do not exist and anchors that are missing.

The report names config keys but never copies their values, such as an Algolia key or an analytics ID, so it is safe to paste into an issue.

## The package and the namespace

LaRecipe 3 is a new package, `larecipe/larecipe`, in a new namespace:

| | LaRecipe 2 | LaRecipe 3 |
| --- | --- | --- |
| Package | `binarytorch/larecipe` | `larecipe/larecipe` |
| Namespace | `BinaryTorch\LaRecipe\` | `LaRecipe\` |

The report lists every line in your app that still uses the old namespace. Replace it (the v3 facade is `LaRecipe\Facades\LaRecipe`, aliased as `LaRecipe`), and check the class still exists in v3: much of the v2 API, such as `LaRecipe::script()` and `LaRecipe::style()`, is gone. `LaRecipe::head()` prints stylesheets and scripts into every docs page's `<head>`, and [custom components](customize/custom-components.md) replace custom JavaScript.

## The viewLarecipe gate

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

- `$page` is a `PageSummary`: `$page->ref->path`, `$page->ref->version`, `$page->ref->locale`, `$page->title` and `$page->private`. It is `null` when LaRecipe asks about the whole set or a private version.
- `$set` is the doc set's key, such as `docs`.
- Type the user as nullable (`?User $user`) if guests should ever pass. Laravel denies guests before your closure runs otherwise.

In v3 the gate also guards every surface, not only the HTML page: Markdown, search, `llms.txt`, MCP and Ask AI. [Gate blocks and private docs](writing/gate-blocks.md) covers access modes, private pages and the other gates.

## Finish with the larecipe-upgrade-v2 skill

The `larecipe-upgrade-v2` skill teaches your coding agent the rest of the job. `php artisan larecipe:agents` installs it into `.claude/skills/` and `.agents/skills/`, next to the other LaRecipe skills, and writes the LaRecipe instructions into `AGENTS.md`. With Laravel Boost, run `php artisan boost:update` instead.

The agent reads the report and its JSON twin, then works through the manual items: anything that breaks rendering first, then content, then looks.

- **Blade in Markdown** becomes components, snippet includes (`<<< @/path#region`) or plain text. Values that came from config become prose that names the key, never the value.
- **Custom Vue components** become React components built with `@larecipe/vite`, each with a PHP definition registered through `LaRecipe::component()`, so it renders without JavaScript and reads well to agents. Until one is rebuilt, the agent replaces the tag with plain Markdown.
- **Themes and custom CSS** move to the theme tokens in `larecipe.json` (accent, fonts, radius). The skill suggests a `theme.css` listed under `assets.css` for the rest, but LaRecipe does not load `assets.css` yet, so load that stylesheet with `LaRecipe::head()` instead. See [theme](customize/theme.md).
- **Every page** gets a `description` and, where it documents code, `sources:`, and pasted route URIs or command signatures become [live references](writing/live-references.md).

It finishes when `php artisan larecipe:validate` passes, and leaves the commit to you, following the review mode in `AGENTS.md`.

## Check after the upgrade

The converter is deterministic, not perfect. Two things on this site needed fixing by hand after it ran, and they are worth checking on yours.

**Badges and buttons came out empty.** The converter writes a v2 badge or button as a self-closing tag with `label` and `tone` props:

```markdown
<Badge label="Completed" tone="success" />
<Button label="View Zino Library" href="https://zino.binarytorch.com.my" />
```

The v3 components do not read a `label` prop: their text is the tag's content. `<Button>` takes `href` and `variant`, not `tone`. Unknown props pass silently, so the validator does not catch this. Rewrite each one with its text inside:

```markdown
<Badge tone="success">Completed</Badge>
<Button href="https://zino.binarytorch.com.my">View Zino Library</Button>
```

Find them with `grep -rn 'label="' docs/`.

**Links inside alerts were not rewritten.** The converter turns `> {info}` alerts into GitHub alerts, but leaves the links inside them as they were. On this site, a `/docs/{{version}}/...` link inside a converted alert kept its v2 form, even though the site's docs moved to the domain root. Search for what is left:

```bash
grep -rn '{{' docs/
```

Rewrite each link it finds as a relative `.md` link. A `{{version}}` that remains in a link still works, as long as the prefix in it is still right.

**Old URLs.** If your route prefix changed, keep old links and search results working with redirects in your app. This site moved its v2 docs from `/docs/{version}/{page}` to `/2.x/{page}`, and its `routes/web.php` ends with:

```php title="routes/web.php"
Route::permanentRedirect('/docs', '/');
Route::permanentRedirect('/docs/{version}', '/2.x');
Route::permanentRedirect('/docs/{version}/{page}', '/2.x/{page}');
```

Within one prefix, use `redirects` in `larecipe.json` instead; see [navigation](writing/navigation.md).

## v2 features in v3

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
| `LaRecipe::script()`, `LaRecipe::style()` | `LaRecipe::head()`, and React extensions |
| Theme and asset packages | Extensions built with `@larecipe/vite` |
| Published views | Named slots you override from an extension, such as `Header`, `Logo`, `Sidebar` and `Footer`, and `LaRecipe::head()` |
| Cache settings | Always cached by content; `php artisan larecipe:optimize` on deploy. See [caching](operate/caching.md). |
| SEO settings | Per-page `description`, canonical URLs, JSON-LD and a sitemap |
| `ga_id` | Analytics integrations in `larecipe.json`: GA4, Plausible, Fathom, PostHog or a custom script |
| Disqus | The feedback widget, and edit links when `repository` is set in `larecipe.json` |
| Keyboard shortcuts | `⌘K` or `/` to search, `?` for every shortcut |
| Feedback package | Built in, sending `FeedbackSubmitted` to your app |
| Swagger package | OpenAPI pages, planned for a later 3.x release |
| `larecipe:docs` | `larecipe:make:version` and `larecipe:make:page` |
| `larecipe:theme`, `larecipe:asset` | Extensions built with `@larecipe/vite` |

And what v2 never had: Markdown twins, `llms.txt`, `skill.md` and an MCP server for agents, live references, provenance on every page, and agents that keep the docs current with your code. Start with [how it works](keeping-current/how-it-works.md).

## Every option

Read live from this app's command registry:

<Artisan command="larecipe:upgrade" />

## Next

- [Configuration](get-started/configuration.md): what the new `config/larecipe.php` holds.
- [Gate blocks and private docs](writing/gate-blocks.md): the gates in v3.
- [Installation](get-started/installation.md): the agent setup, the MCP server and the hooks, for a site you have upgraded.
