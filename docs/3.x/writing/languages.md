---
title: Languages
description: Serve your docs in more than one language, with right-to-left support, a fallback for pages not yet translated, and a check that finds translations falling behind.
sources:
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/Locale.php
  - vendor/larecipe/larecipe/src/Domains/Content/Enums/Direction.php
  - vendor/larecipe/larecipe/src/Domains/Sources/Drivers/RepositorySource.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RequestResolver.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PagePresenter.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/PageCatalog.php
  - vendor/larecipe/larecipe/src/Domains/Search/Services/SearchIndexRepository.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Services/LlmsTxtBuilder.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Services/ScopeParser.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/FindAffectedPagesAction.php
  - vendor/larecipe/larecipe/resources/boost/skills/larecipe-update-docs/references/translations.md
---

# Languages

One doc set can serve the same docs in several languages. List the languages in the set's `larecipe.json`:

```json title="docs/larecipe.json"
{
  "locales": [
    { "code": "en", "label": "English", "default": true },
    { "code": "ar", "label": "العربية", "dir": "rtl" }
  ]
}
```

Then put each translation in a folder named after its code, inside the version, at the same path as its original:

<FileTree>

- docs/3.x/
  - index.md
  - get-started/
    - installation.md
  - ar/
    - index.md
    - get-started/
      - installation.md

</FileTree>

The default language stays at the version root, and on a set without versions the language folders sit directly in `docs/`. A page you have not translated yet still opens in every language: readers get the original, with a note that says so.

This site serves English and Arabic. The [Arabic Introduction](/ar/3.x) and the [Arabic installation guide](/ar/3.x/get-started/installation) are translated; every other page falls back to English.

## URLs

The language comes first, then the version, then the page: `/{prefix}/{locale}/{version}/{path}`. The default language has no segment. On this site, the installation guide is `/3.x/get-started/installation` in English and `/ar/3.x/get-started/installation` in Arabic. The Markdown twin, `llms.txt` and the search index follow the same shape.

## Locale keys

| Key | Default | Meaning |
| --- | --- | --- |
| `code` | required | The language code, used in folders and URLs: `ar`, `fr`, `pt-BR`. |
| `label` | the language's own name for common codes | What the language switcher shows. |
| `dir` | from the code | `ltr` or `rtl`. Arabic, Hebrew, Persian, Urdu and other right-to-left languages get `rtl` without being told. |
| `default` | `false` | The original language. When no locale says `true`, the first one is the default. |

A locale can be a plain string (`"locales": ["en", "ar"]`) when the defaults fit. A set with one locale, or none, is not multilingual and its URLs never carry a language.

## Right to left

The page's direction comes from the locale, and the stylesheets use logical properties, so the layout mirrors with no extra configuration. Write the page in Arabic and leave code, commands and component tags as they are. Thmanyah Sans sits in the default font stack right after Inter, so Arabic text is set in it while Latin words, numbers and code keep their Latin faces.

## Keep translations current

A translation falls behind when its original changes and it does not. `larecipe:affected` finds those pages:

```bash
php artisan larecipe:affected --uncommitted --base=main --locale=ar
```

It lists the Arabic pages whose original changed in your branch while the translation did not, and those whose original changed after the translation was last updated. A translation without `sources:` of its own uses its original's, so a code change that makes the English page stale flags the Arabic one too.

The `larecipe-update-docs` [skill](../keeping-current/skills.md) carries a translation workflow for coding agents: read the original's diff, translate only what changed, keep code, commands, paths and component tags exactly as they are, keep the headings in the same order so anchors line up, and say so instead of guessing when the agent cannot translate well.

## Details

<AccordionGroup>

<Accordion title="How a page that is not translated yet renders">

Every page of the original exists in every language. When a translation is missing, the page renders the original article inside the reader's language: the page keeps `lang="ar"` and `dir="rtl"`, the sidebar still links into the Arabic pages, the article stays in English with its own `lang` and `dir`, and a note above it reads "This page is not translated yet. You are reading the original." The Markdown twin falls back the same way.

The language switcher relies on this: switching language keeps you on the same page, translated or not. Each page also lists its `hreflang` alternates, one for every language it is written in, plus `x-default` for the original. A fallback is not an alternate; its canonical URL is the original page.

</Accordion>

<Accordion title="Navigation and links in each language">

The sidebar of a language lists every page of the original, in the same order, with the translated title wherever a translation exists. It reads the version's `navigation.json` unless the language folder has its own (`docs/3.x/ar/navigation.json`), which can give the groups translated labels or a different order. A language's own file lists the same pages as the version's: entries for pages not translated yet show the original. This site's Arabic sidebar works that way, with Arabic group labels over a mix of translated and English pages.

Link between pages the usual way, with relative `.md` links, from a translation too: a link to a page that exists only in the original reaches its fallback in the reader's language. `larecipe:validate` follows the same rule as the reader, so neither a navigation entry nor a link to an untranslated page is reported.

</Accordion>

<Accordion title="Search and agents in each language">

Search, `llms.txt` and the MCP server each work inside one language, and cover the pages written in it:

- The search index for Arabic is built from the Arabic pages, so readers search the language they read.
- `/ar/llms.txt` and `/ar/3.x/llms.txt` list the Arabic pages; the root and version indexes end with a Languages section that links the other languages.
- The MCP tools `search_docs`, `read_page` and `list_pages` take a `locale` argument and use the default language without one.

</Accordion>

</AccordionGroup>

## Next

- [Versions](versions.md): languages live inside each version.
- [Navigation](navigation.md): `navigation.json`, per version and per language.
- [larecipe.json reference](../reference/manifest.md): every manifest key.
