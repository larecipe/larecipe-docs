---
title: Navigation
description: Get a sidebar from your folders with no config, write navigation.json when you want to choose the order and groups, and keep old URLs working with redirects.
sources:
  - vendor/larecipe/larecipe/resources/schemas/navigation.schema.json
  - vendor/larecipe/larecipe/src/Domains/Content/Services/NavigationResolver.php
  - vendor/larecipe/larecipe/src/Domains/Content/Services/ZeroConfigNavigation.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/NavigationTree.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/NavigationTab.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/NavigationNode.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/NavigationRule.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/RedirectsRule.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Commands/NavigationFreezeCommand.php
  - vendor/larecipe/larecipe/src/Domains/Onboarding/Actions/FreezeNavigationAction.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/ui/src/regions/Sidebar.tsx
  - docs/3.x/navigation.json
---

# Navigation

Every version has a sidebar. With no config, LaRecipe builds it from your folders and each page's frontmatter, so a new page shows up the moment you add the file.

When you want to choose the order and the groups yourself, write a `navigation.json` in the version folder (`docs/3.x/navigation.json`):

```json
{
  "$schema": "https://larecipe.saleem.dev/schemas/3.x/navigation.json",
  "groups": [
    { "group": "Get started", "pages": ["index", "installation"] },
    { "group": "Billing", "pages": ["billing/invoices", "billing/refunds"] }
  ]
}
```

Page entries are paths from the version root, without `.md`: `billing/refunds` is `docs/3.x/billing/refunds.md`, and `index` is the landing page. Pages appear in the order you list them, under the titles from their frontmatter. The `$schema` line lets your editor and your agent check the file as you type.

The same tree also sets the previous and next links at the bottom of each page, the sections of `llms.txt` and the outline in `skill.md`.

## Navigation from your folders

A version without a `navigation.json` gets its navigation from its files:

- The version's `index.md` comes first, then the other pages at the version root, then one group per folder.
- A folder's group is labelled with the title of its own page (`guides/index.md`, or a `guides.md` next to the folder). Without one, the folder name is used: `getting-started` becomes "Getting started".
- Pages sort by their frontmatter `order`, lowest first, then by title. Folders sort the same way, by their own page.
- Folders inside folders become nested groups, and pages with `hidden: true` are left out.

<Accordion title="An example, and the finer sorting rules">

This folder:

<FileTree>

- docs/3.x/
  - index.md
  - configuration.md
  - installation.md
  - billing/
    - index.md
    - refunds.md
    - invoices.md

</FileTree>

with `order: 1` on `installation.md` and `order: 2` on `configuration.md` gives this sidebar: the Introduction, Installation, Configuration, then a group labelled with the title of `billing/index.md`, holding that page first and then Invoices and Refunds, sorted by title.

- A folder's own page leads its group, and its `icon` and `order` stand for the whole folder.
- Pages without an `order` come after those with one.
- A numeric prefix in a folder name, such as `01-`, is dropped from its label.
- Hiding a folder's own page hides the whole folder.

</Accordion>

When you outgrow this, `php artisan larecipe:navigation:freeze` writes the tree LaRecipe derives today into a `navigation.json`, so you edit from there instead of starting from nothing.

## Entries in navigation.json

Every list in the file holds the same four kinds of entry:

| Entry | Shape | Shows |
| --- | --- | --- |
| A page | `"billing/refunds"` | The page, under its own title |
| A page with its own label | `{ "page": "index", "label": "Introduction" }` | The page, under `label` |
| A group | `{ "group": "Billing", "collapsed": true, "pages": [...] }` | A labelled group; its `pages` can hold any entry, groups included |
| A link | `{ "label": "Changelog", "href": "https://example.com/changelog" }` | A link to anywhere, marked as external |

A page's `label` changes only its sidebar entry; the page keeps its own title everywhere else. A link's `href` must be an `http`, `https` or `mailto` URL, or a path on your site.

Top-level groups are always open, unless you add `"collapsed": true`. Nested groups fold, and start open unless they say `"collapsed": true`. A folded group still opens when it holds the page you are reading.

<AccordionGroup>

<Accordion title="This site's navigation.json">

<<< @/docs/3.x/navigation.json

</Accordion>

<Accordion title="Tabs, bare lists and other shapes">

The file can take three shapes. Use the smallest one that fits.

**Groups**, the common case and the shape this site uses: one list of groups and pages, as above.

**Tabs**: several labelled sections, each with its own groups. The sidebar shows each tab's label above its groups.

```json
{
  "tabs": [
    {
      "label": "Guides",
      "groups": [
        { "group": "Get started", "pages": ["index", "installation"] }
      ]
    },
    {
      "label": "API",
      "groups": [
        { "group": "Resources", "pages": ["api/orders", "api/customers"] }
      ]
    }
  ]
}
```

**A bare list**, for a small set: pages and groups with nothing around them.

```json
["index", "installation", "configuration", "faq"]
```

Inside `{ "groups": [...] }` or a tab, you may also write `"pages": [...]` for entries that sit outside any group. When both are present, the `groups` entries come first.

The schema also accepts a tab with `href` instead of groups, as a link in place of a section. The current reader UI shows such a tab's label without the link, so use a link entry inside a group for now.

</Accordion>

<Accordion title="Nested and collapsed groups">

```json
{
  "groups": [
    {
      "group": "Billing",
      "pages": [
        "billing",
        "billing/invoices",
        { "group": "Webhooks", "collapsed": true, "pages": ["billing/webhooks/events", "billing/webhooks/retries"] }
      ]
    }
  ]
}
```

A group may be named with `label` instead of `group`.

</Accordion>

<Accordion title="Icons, badges and what each reader sees">

`icon` takes a [Lucide](https://lucide.dev/icons/) icon name on pages, groups, links and tabs. LaRecipe passes it to the reader UI, but the default sidebar does not draw navigation icons yet.

A page's `badge` frontmatter shows next to its entry, and pages that changed in the last seven days carry a small dot.

The navigation is the same file for everyone, but each reader sees only what they may read. Private pages are left out for readers who cannot open them, and a group with nothing left to show disappears. Entries for pages that do not exist are left out too, and `larecipe:validate` reports them.

</Accordion>

</AccordionGroup>

## Hidden pages

`hidden: true` in a page's frontmatter makes it routable but unlisted: it has a URL and a Markdown twin, but it stays out of the derived navigation, search, the sitemap and the docs index in `AGENTS.md`. Use it for pages you link to but do not want in the sidebar. A hidden page is not private: anyone with its URL can read it. For pages only some readers may see, use `visibility: private` ([gate blocks and private docs](gate-blocks.md)).

With a `navigation.json`, the file decides what is listed. A page you leave out of it gets a `pages.orphan` warning from `larecipe:validate`, because readers can only reach it by URL. Set `hidden: true` on it to say that is intended.

## Redirects

Pages move. Two features keep the old URLs working, and both redirect only when no page exists at the old URL.

List a page's old paths in its own frontmatter, so the redirect moves with the page:

```yaml
---
title: Webhooks
redirect_from:
  - hooks
  - guides/hooks
---
```

For URLs no page owns any more, list them in the set's `larecipe.json`:

```json
{
  "redirects": [
    { "from": "/docs/3.x/setup", "to": "/docs/3.x/installation" }
  ]
}
```

<AccordionGroup>

<Accordion title="How redirect_from matches">

Write each old path as the page path inside the version, as you would in `navigation.json`. A request for the old path redirects with a `301` to this page, in the same version and language, and a request for the old Markdown twin (`hooks.md`) redirects to the new twin. The full old URL path, such as `/docs/3.x/hooks`, matches as well.

LaRecipe looks for `redirect_from` among the pages of the version and language that was requested, and only among the pages the reader may see, so an old URL never reveals a private page. A page copied into a new version keeps its `redirect_from`, but a translation needs its own to redirect its old URL.

</Accordion>

<Accordion title="How redirects in larecipe.json match">

`from` is the full path of the old URL: the set's prefix, then the version, then the page (with the language segment for a translation). `to` is any path or URL, and `status` defaults to `301` (`302`, `307` and `308` work too).

On a versioned set, a `from` without a version applies in every version. `{ "from": "/docs/setup", "to": "/docs/installation" }` sends `/docs/3.x/setup` to `/docs/3.x/installation` and `/docs/2.x/setup` to `/docs/2.x/installation`: a `to` inside the set that names no version stays in the reader's version. Write the version in `from` to redirect one version only.

`larecipe:validate` reports redirects that loop (`redirects.loop`) and redirects from a URL a page still lives at (`redirects.shadowed`). To redirect URLs outside the docs, or a whole old prefix, use your app's routes. [Upgrade from v2](../upgrade.md) shows how this site keeps its LaRecipe 2 URLs working.

</Accordion>

</AccordionGroup>

## Details

<AccordionGroup>

<Accordion title="Navigation in each language">

Each language uses the version's navigation, with the translated title of every page that has a translation. To change the order or translate the group labels, give the language its own file next to its pages, such as `docs/3.x/ar/navigation.json`. LaRecipe looks there first, then falls back to the version's file. See [languages](languages.md).

</Accordion>

<Accordion title="Freeze the derived navigation">

`larecipe:navigation:freeze` writes the default version's file (`--docs-version=2.x` for another, `--locale=ar` for a language's own file, `--set=` for another doc set), with the `$schema` pointer and the groups in the order readers see them now. A version that already has a `navigation.json` is left alone. It works on repository sets; a database set changes its navigation with the `update_navigation` MCP tool. Read live from this app:

<Artisan command="larecipe:navigation:freeze" />

</Accordion>

<Accordion title="What larecipe:validate checks">

| Rule | Level | When |
| --- | --- | --- |
| `navigation.schema` | error | `navigation.json` is not valid JSON, or not a shape the schema allows |
| `navigation.missing-page` | error | An entry names a page that does not exist, with the closest path as a fix |
| `pages.orphan` | warning | The version has a `navigation.json`, and a page is neither in it nor `hidden` |
| `links.unsafe` | error | A link entry whose `href` is not `http`, `https`, `mailto` or a path on your site |

Derived navigation lists every page by construction, so it never has orphans.

</Accordion>

</AccordionGroup>

## Next

- [Versions](versions.md): one `navigation.json` per version.
- [Languages](languages.md): navigation per language.
- [Frontmatter reference](../reference/frontmatter.md): `order`, `icon`, `badge`, `hidden` and `redirect_from`.
