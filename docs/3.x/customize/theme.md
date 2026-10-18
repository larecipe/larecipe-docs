---
title: Theme
description: Make the default LaRecipe theme yours with a few tokens in larecipe.json, your logo and, when you need it, a stylesheet of your own.
sources:
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Theme.php
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Manifest.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ThemeCss.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/DocsStylesheets.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/DocsAssetController.php
  - vendor/larecipe/larecipe/src/Domains/Validation/Rules/StylesheetsRule.php
  - vendor/larecipe/larecipe/src/LaRecipe.php
  - vendor/larecipe/larecipe/resources/views/partials/head.blade.php
  - vendor/larecipe/larecipe/resources/views/prerender/header.blade.php
  - vendor/larecipe/larecipe/resources/schemas/larecipe.schema.json
  - vendor/larecipe/larecipe/ui/src/styles/tokens.css
  - docs/larecipe.json
---

# Theme

Make the default theme yours with a few tokens under `theme` in `larecipe.json`. They live next to the docs, so the theme travels with the content and needs no build step. Set only what you want to change:

```json title="docs/larecipe.json"
{
  "theme": {
    "accent": "#0f766e",
    "radius": "soft",
    "fonts": { "sans": "IBM Plex Sans", "mono": "IBM Plex Mono" },
    "mode": "system"
  }
}
```

| Key | Values | What it changes |
| --- | --- | --- |
| `accent` | Any CSS color | Links, the active page, focus rings and highlighted code lines. The dark-mode accent is derived from it, so it stays readable in both modes. |
| `radius` | `sharp` (default) or `soft` | `sharp` keeps the 4, 6 and 10 px corners. `soft` moves them to 6, 10 and 14 px. |
| `fonts.sans` | A font family name | Headings, prose and the interface. |
| `fonts.mono` | A font family name | Code, labels and metadata. |
| `fonts.arabic` | A font family name | Arabic text. The default is Thmanyah Sans. |
| `mode` | `system` (default), `light` or `dark` | The color mode a reader gets on their first visit. `system` follows the reader's operating system. |

Font tokens name a family; they do not download it. Load the font files yourself, for example from [your own stylesheet](#add-your-own-stylesheet).

## Logo and favicon

The logo sits in the header, next to the site name from `larecipe.json`. Give one file for both modes, or one per mode:

```json title="docs/larecipe.json"
{
  "name": "Acme",
  "logo": { "light": "/brand/acme.svg", "dark": "/brand/acme-dark.svg" },
  "favicon": "/favicon.svg"
}
```

- Paths are URLs, so put the files in your app's `public/` folder.
- The name is always printed next to the logo, so a mark works better than a full wordmark.
- Without a `logo`, LaRecipe shows its own logo, the violet recipe ticket. This site sets no logo, so that is the one you see here.
- `favicon` becomes the page's `<link rel="icon">`. An SVG favicon can switch colors with a `prefers-color-scheme` media query inside the file.

## Add your own stylesheet

For anything the tokens do not cover, add a `theme.css` next to `larecipe.json` and list it under `assets.css`:

```json title="docs/larecipe.json"
{
  "assets": { "css": ["theme.css"] }
}
```

<FileTree>

- docs/
  - larecipe.json
  - theme.css
  - 3.x/

</FileTree>

LaRecipe links each listed stylesheet on every page of the set, right after its own CSS, so your rules win where they overlap. It travels with the docs: it serves every version, lands in a [static export](../operate/static-export.md), and needs no build step. A listed file that does not exist gets no tag, and `php artisan larecipe:validate` reports it as `assets.missing`.

Write your CSS against the classes LaRecipe renders (`.lr-callout`, `.lr-card`, `.lr-code` and the rest; the [components reference](../reference/components.md) lists them) and the theme variables below, so it follows dark mode and right-to-left pages. CSS that ships with a [custom component](custom-components.md) goes in that component's extension instead.

## Details

<AccordionGroup>

<Accordion title="The default theme">

The default theme is called The Pass, after the counter in a restaurant kitchen where every plate is checked before it leaves. It is built for long reading and quick scanning:

- **Paper and ink.** A warm white page (`#FAFAF8`), or a near-black one in dark mode (`#111016`), with text at three fixed strengths.
- **One color for state.** Violet marks what is active, focused, linked or fresh, and nothing else, so a reader can find everything that is live by color alone. Your `accent` takes violet's place everywhere it means state.
- **Mono for what machines made.** Labels, paths, timestamps and code are set in JetBrains Mono. Prose is set in Inter, and Arabic in Thmanyah Sans.
- **The ticket.** A small card with a perforated header and a bitten bottom edge is the system's signature shape. LaRecipe's own mark is drawn from it.

Light and dark are both first-class, and right-to-left pages are the same design mirrored, not a variant. The fonts ship with LaRecipe's UI build, so a site looks right with no setup at all.

</Accordion>

<Accordion title="How tokens reach the page">

LaRecipe writes only the tokens you set, as CSS variables in a `<style>` tag in the page head. Each value is checked first, because it comes from a content file: a value that could close the tag or inject a rule is dropped. The dark-mode accent is derived from yours in OKLCH (lighter, a little less saturated). `fonts.arabic` sits right after the sans font in the stack, so Latin words, numbers and code inside Arabic text keep the sans face.

</Accordion>

<Accordion title="Theme variables">

These CSS variables hold the design system. They switch values in dark mode, so CSS that reads them follows the reader's mode with no extra rules.

| Variable | Holds |
| --- | --- |
| `--lr-paper` | The page background |
| `--lr-ink`, `--lr-ink-muted`, `--lr-ink-faint` | Text at its three strengths. Never put information in the faint step. |
| `--lr-surface`, `--lr-surface-2` | Callouts, inputs and cards, and their hover state |
| `--lr-hairline`, `--lr-hairline-strong` | Borders and dividers |
| `--lr-accent-current` | The accent for the current mode. Prefer it to `--lr-accent`, which is the light-mode value. |
| `--lr-accent-soft` | Selected backgrounds |
| `--lr-success`, `--lr-warning`, `--lr-danger` | Feedback colors, for labels and icons |
| `--lr-radius-xs`, `--lr-radius-sm`, `--lr-radius-md`, `--lr-radius-lg` | The corner radii (`radius: soft` changes them) |
| `--lr-font-sans`, `--lr-font-mono` | The font stacks, including your `fonts` tokens |

```css title="docs/theme.css"
.status-pill {
  border: 1px solid var(--lr-hairline);
  border-radius: var(--lr-radius-sm);
  color: var(--lr-ink-muted);
}

.status-pill[data-live] {
  color: var(--lr-accent-current);
}
```

</Accordion>

<Accordion title="How listed stylesheets are served">

A stylesheet listed under `assets.css` is served by the docs asset route with its content hash in the URL (`?v=...`), so browsers and CDNs keep it for a year and a changed file gets a new URL.

</Accordion>

<Accordion title="CSS from elsewhere, with LaRecipe::head()">

For CSS that lives outside the docs, or a stylesheet only some sets get, register it in a service provider with `LaRecipe::head()`, which prints whatever you return into the `<head>` of every docs page:

```php title="app/Providers/AppServiceProvider.php"
use LaRecipe\Facades\LaRecipe;

public function boot(): void
{
    LaRecipe::head(fn () => '<link rel="stylesheet" href="/css/docs.css">');
}
```

The callback receives the doc set, so one app can style its sets differently (`fn ($set) => $set->key === 'handbook' ? '...' : ''`). It may return HTML, a view or anything `Htmlable`. It is also a place for font stylesheets from a font service.

</Accordion>

<Accordion title="Dark mode and right to left">

- **Dark mode.** The page's `<html>` element carries `data-theme="light"` or `data-theme="dark"`. When a rule needs a different value in dark mode and no theme variable fits, scope it with `:root[data-theme='dark']`.
- **Right to left.** A locale with `"dir": "rtl"` sets `dir="rtl"` on the page, and LaRecipe's own CSS mirrors through logical properties. Write yours the same way (`margin-inline-start`, `padding-inline`, `inset-inline-end`) and it mirrors too. Code blocks, commands and version numbers stay left to right. See [Languages](../writing/languages.md).

</Accordion>

<Accordion title="The reader's own choices">

A reader's own choice of light or dark wins over `mode`. They make it with the theme button at the end of the header, which switches between light and dark in one click, and it is stored in their browser under `larecipe:theme`. The color mode is set before the first paint, so pages never flash the wrong theme.

The switch next to the logo hides the navigation on wide screens and shows it again, the way LaRecipe 2's did. With the navigation hidden, the article widens for easier reading. That choice is stored under `larecipe:sidebar` and applied before the first paint too. On phones the navigation opens as a sheet from the menu button instead.

</Accordion>

</AccordionGroup>

## Next

- [Custom React components](custom-components.md): add your own components, with their own CSS.
- [Insert points and slots](insert-points.md): place a component on every page.
- [larecipe.json](../reference/manifest.md): every manifest key.
