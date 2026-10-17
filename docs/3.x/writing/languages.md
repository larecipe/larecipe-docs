---
title: Languages
description: Serve your docs in more than one language, with right-to-left support, a fallback for pages not yet translated, and agent files per language.
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

One doc set can serve the same docs in several languages. Each language gets its own URLs, navigation, search index and agent files, and a page that is not translated yet still opens: readers get the original, with a note that says so. Right-to-left languages such as Arabic are full renderings, not a theme.

This site serves English and Arabic. The [Arabic Introduction](/ar/3.x) and the [Arabic installation guide](/ar/3.x/get-started/installation) are translated; every other page falls back to English.

## Declare locales in larecipe.json

List the languages in the set's [manifest](../reference/manifest.md):

```json
{
  "locales": [
    { "code": "en", "label": "English", "default": true },
    { "code": "ar", "label": "العربية", "dir": "rtl" }
  ]
}
```

| Key | Default | Meaning |
| --- | --- | --- |
| `code` | required | The language code, used in folders and URLs: `ar`, `fr`, `pt-BR`. |
| `label` | the language's own name for common codes | What the language switcher shows. |
| `dir` | from the code | `ltr` or `rtl`. Arabic, Hebrew, Persian, Urdu and other right-to-left languages get `rtl` without being told. |
| `default` | `false` | The original language. When no locale says `true`, the first one is the default. |

A locale can be a plain string (`"locales": ["en", "ar"]`) when the defaults fit. A set with one locale, or none, is not multilingual and its URLs never carry a language.

## Where translations live

The default language stays where it was, at the version root. Each other language has a folder named after its code, inside each version:

<FileTree>

- docs/
  - larecipe.json
  - 3.x/
    - navigation.json
    - index.md
    - get-started/
      - installation.md
    - ar/
      - index.md
      - get-started/
        - installation.md

</FileTree>

A translation has the same path as its original: `3.x/ar/get-started/installation.md` translates `3.x/get-started/installation.md`. On a set without versions the language folders sit directly in `docs/`.

## URLs

The language comes first, then the version, then the page:

```text
/{prefix}/{locale}/{version}/{path}
```

The default language has no segment. On this site, the installation guide is `/3.x/get-started/installation` in English and `/ar/3.x/get-started/installation` in Arabic. The same shape holds for the Markdown twin (`.md`), `llms.txt` and the search index.

## Pages that are not translated yet

Every page of the original exists in every language. When a translation is missing, the page renders the original article inside the reader's language: the page keeps `lang="ar"` and `dir="rtl"`, the sidebar still links into the Arabic pages, the article stays in English with its own `lang` and `dir`, and a note above it reads "This page is not translated yet. You are reading the original." The Markdown twin falls back the same way.

The language switcher relies on this: switching language keeps you on the same page, translated or not. Each page also lists its `hreflang` alternates, one for every language it is written in, plus `x-default` for the original. A fallback is not an alternate; its canonical URL is the original page.

## Navigation in each language

The sidebar of a language lists every page of the original, in the same order, with the translated title wherever a translation exists. It reads the version's `navigation.json` unless the language folder has its own (`docs/3.x/ar/navigation.json`), which can give the groups translated labels or a different order. `larecipe:validate` checks a language's own `navigation.json` against the pages written in that language and reports every entry without a translation, so add one when the language is fully translated, and borrow the version's until then. In the same way, it reports a link from a translated page to a page that exists only in the original as broken, although readers reach the fallback: link those pages by their original URL (`/3.x/agents/mcp`) until they are translated.

## Right to left

The page's `dir` comes from the locale, and the stylesheets use logical properties, so the layout mirrors with no extra configuration. Thmanyah Sans sits in the default font stack right after Inter, so Arabic text is set in it while Latin words, numbers and code keep their Latin faces. Nothing in the Markdown needs to change; write the page in Arabic and leave code, commands and component tags as they are.

## Search and agents, per language

Search, `llms.txt` and the MCP server each work inside one language, and cover the pages written in it:

- The search index for Arabic is built from the Arabic pages, so readers search the language they read.
- `/ar/llms.txt` and `/ar/3.x/llms.txt` list the Arabic pages; the root and version indexes end with a Languages section that links the other languages.
- The MCP tools `search_docs`, `read_page` and `list_pages` take a `locale` argument and use the default language without one.

## Keep translations current

A translation falls behind when its original changes and it does not. `larecipe:affected` finds those pages:

```bash
php artisan larecipe:affected --uncommitted --base=main --locale=ar
```

It lists two kinds of Arabic pages: those whose original changed in your branch while the translation did not, and those whose original changed in a commit after the translation was last updated. A translation without `sources:` of its own uses its original's, so a code change that makes the English page stale flags the Arabic one too.

The `larecipe-update-docs` skill carries a translation workflow for coding agents: read the original's diff, translate only what changed, keep code, commands, paths and component tags exactly as they are, keep the headings in the same order so anchors line up, and say so instead of guessing when the agent cannot translate well. See [skills](../keeping-current/skills.md).

## Next

- [Versions](versions.md): languages live inside each version.
- [Navigation](navigation.md): `navigation.json`, per version and per language.
- [larecipe.json reference](../reference/manifest.md): every manifest key.
