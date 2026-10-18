---
title: Validation and doctor
description: Check the docs with larecipe:validate and what agents get from your app with larecipe:doctor, read their reports, and run both in CI.
sources:
  - vendor/larecipe/larecipe/src/Domains/Validation/**
  - vendor/larecipe/larecipe/resources/schemas/validate.schema.json
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/ComponentRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/DocumentTransformer.php
  - vendor/larecipe/larecipe/src/Domains/References/Markdown/ResolveReferences.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/Doctor/**
  - vendor/larecipe/larecipe/src/Domains/Agents/Commands/DoctorCommand.php
  - vendor/larecipe/larecipe/src/Domains/Agents/DTOs/DoctorCheck.php
  - vendor/larecipe/larecipe/src/Domains/Agents/DTOs/DoctorReport.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/ServerComposition.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Actions/WriteCiCheckAction.php
  - vendor/larecipe/larecipe/stubs/larecipe-docs-check.yml
---

# Validation and doctor

Two read-only checks, for two different questions:

- **`larecipe:validate`**: are the docs correct? It reads every page through the real renderer and reports broken links, schema errors, unknown components, missing snippets, references that stopped resolving and more. Run it after every docs change. Your coding agent does.
- **`larecipe:doctor`**: what do agents get? It requests your docs through the app the way an agent would and grades the answers: Markdown twins, `llms.txt`, 404s, headers, redirects, `skill.md` and MCP.

Neither one writes anything, and neither one uses AI.

## larecipe:validate

<Artisan command="larecipe:validate" />

```bash
php artisan larecipe:validate
php artisan larecipe:validate --set=docs --docs-version=3.x
php artisan larecipe:validate --json
```

Without options it checks every version and language of every doc set. The version option is `--docs-version` because Symfony reserves `--version` for the application.

It exits with `1` when there is at least one error, so CI can stop on it. Warnings never fail it. A set or version that does not exist exits with `2`.

Each issue prints its level, rule, page and line, the message, and a fix when LaRecipe knows one:

```text
  ERROR  links.broken  3.x/agents/mcp line 220
         Link to ../sources/docs-migrations.md: there is no page at "sources/docs-migrations".
         Fix: Fix the link, or write the page it points to.
```

That one is from this site, while these docs were being written: the page it links to was next in line.

## Rules

Every issue has a stable rule id. Page rules run on each page, set rules on the doc set as a whole.

| Rule | Level | What it finds |
| --- | --- | --- |
| `page.empty` | error | A page with no content. |
| `frontmatter.schema` | error, warning | Frontmatter that is not valid YAML or has values of the wrong shape (error), or an unknown key that looks like a typo of a known one, such as `descripton` (warning). |
| `frontmatter.description.missing` | warning | A page without a `description`. It weakens `llms.txt`, search snippets and `list_pages`. |
| `components.unknown` | error | A component tag nothing defines. It renders as escaped text. |
| `components.props` | error | A missing or invalid prop, or a page that fails to render. |
| `references.missing` | error | A live reference (`<Route>`, `<Artisan>`, `<Model>`, `<Event>`) whose target no longer exists. |
| `snippets.missing` | error | A snippet include whose file or region is gone. |
| `snippets.denied` | error | An include outside the snippet root, or of a file that may hold secrets. |
| `snippets.disabled` | error | An include on a set with no snippet root (database sets, unless you configure one). |
| `media.placeholder` | warning | A video placeholder: a `Video` component with a `placeholder` prop and no `src` yet. |
| `links.broken` | error | A link to a page or an anchor that does not exist: relative `.md` links, absolute links into any version or language of the set, and `#anchors` on the same page. A link to a page not translated yet is fine: readers get the original, so the validator falls back to the default language the same way. |
| `links.private-from-public` | warning | A public page linking to a private one, where every guest gets a 404. |
| `links.unsafe` | error | A link in `navigation.json`, the navbar, the footer or the repository URL that is not `http`, `https`, `mailto` or a path on the site. Pages leave it out. |
| `markdown.size` | warning | A page whose agent Markdown is over `agents.markdown_budget` (50,000 characters by default), where agents start cutting. |
| `manifest.schema` | error, warning | `larecipe.json` that is not valid JSON or breaks the schema (error); a missing `larecipe.json`, or a key that looks like a typo (warning). |
| `manifest.integrations` | warning | Analytics `integrations` in a database set's `larecipe.json`, which it ignores. Move them to `sets.{key}.integrations` in `config/larecipe.php`. |
| `assets.missing` | error | A stylesheet listed under `assets.css` that is not next to `larecipe.json`. |
| `navigation.schema` | error | `navigation.json` that is not valid JSON or breaks the schema. |
| `navigation.missing-page` | error | A navigation entry with no page behind it. A language's own `navigation.json` may list pages not translated yet: they fall back to the original. |
| `pages.orphan` | warning | A page `navigation.json` leaves out and does not hide, so readers reach it only by URL. |
| `pages.duplicate` | error | Two files that become the same URL, such as `guides.md` and `guides/index.md`. |
| `redirects.loop` | error | Redirects (in `larecipe.json` or `redirect_from`) that lead back to where they started. |
| `redirects.shadowed` | warning | A redirect from a URL a page still lives at. The page wins, so the redirect never runs. |
| `search.budget` | warning | A version whose client search index would be over `search.budget_kb`. Only on the `client` [search driver](search.md). |
| `skills.spec` | error, warning | A hand-written `skills/{name}/SKILL.md` that breaks the Agent Skills spec (error), or one over 500 lines (warning). |
| `cache.store` | error | A database set on the `array` or `null` cache store, outside tests. It reads the set's own `source.cache` first. |
| `access.gate-syntax` | error | A `<Can>` or `<Cannot>` tag the parser could not read: inside a sentence, a table or a heading, split over lines, or in lowercase. The block around it is hidden from every reader until it is fixed. |
| `access.unknown-ability` | warning | A gate block that asks about an ability your app never defined, so it denies every reader. The fix names the closest defined ability. |
| `access.redundant-gate` | warning | A gate block on a page that is itself private, whose readers already passed `viewLarecipe`. |
| `migrations.conflict` | warning | A docs migration that met a change made in production and waits as a conflict at [the pass](../sources/database.md#the-pass-the-review-queue). The fix gives its address in the admin. |

Validation and rendering never disagree: the component, snippet, reference and placeholder rules come straight from the renderer, which reports every problem it meets while rendering the page.

## The JSON report

`--json` prints the report agents read. Its shape is published as a JSON Schema, in the package at `resources/schemas/validate.schema.json` and on every set at `/schemas/validate.json` (under the set's prefix).

A link with a typo in its anchor, added to this page for a moment, came back like this:

```json
{
    "ok": false,
    "summary": { "pages": 41, "errors": 48, "warnings": 3 },
    "issues": [
        {
            "level": "error",
            "rule": "links.broken",
            "set": "docs",
            "page": "3.x/operate/validation",
            "file": "3.x/operate/validation.md",
            "line": 8,
            "message": "Link to caching.md#the-cache-stor: anchor #the-cache-stor not found.",
            "fix": "Use #the-cache-store."
        }
    ]
}
```

The other 47 errors were links and navigation entries for pages not written yet.

- `ok` is `false` when there is at least one error.
- `page` is `{locale}/{version}/{path}`, leaving out the default language, and the version on a set without versions.
- `file` is relative to the set's root (`docs/` here), and `line` points into that file, frontmatter included.
- `fix` is there when LaRecipe can suggest one: the closest anchor, the closest page, the frontmatter key you probably meant. Treat it as a suggestion. For a link to a page that does not exist yet, the closest page is usually the wrong answer.

### Fixes for agents

The report is built for the agent that wrote the page a moment ago. Stable rule ids tell it what kind of problem it has, `file` and `line` tell it where, and `fix` often tells it what to change. The `larecipe-write-docs` and `larecipe-update-docs` skills tell agents to run the validator and fix every error before they stop.

Agents connected to the MCP server can call the **`validate_docs`** tool instead of the command. It returns the same report as `--json`, for one set and optionally one version. It is always on the local server (`php artisan larecipe:mcp`). On a set's web endpoint it is offered only to callers the `manageLarecipe` gate allows, because it reads every page, private ones included.

When an agent writes a page through the MCP author tools, the same page rules run on that page first, and a page with errors is not written. Link problems are only warnings at that point, because an agent writing several pages links to the next one before it exists. The full `larecipe:validate` run catches whatever is still broken.

## In CI

The installer can add an optional check for pull requests:

```bash
php artisan larecipe:install --ci=fail
```

It writes `.github/workflows/larecipe-docs-check.yml`, which runs `php artisan larecipe:validate --json` and `php artisan larecipe:affected` on every pull request. It runs no AI, writes nothing and needs no secrets for a repository set, so it is safe on pull requests from forks. Database sets apply the pull request's docs migrations to a throwaway database first, so a broken docs migration fails the check. `--ci=comment` posts the list of stale pages instead of failing. See [the CI check](../keeping-current/ci-check.md).

In any other CI, one step is enough. The exit code fails the job on errors:

```yaml
- run: php artisan larecipe:validate
```

## larecipe:doctor

<Artisan command="larecipe:doctor" />

The doctor grades each doc set the way the AFDocs checks grade a docs site for agents, from inside your app. It sends real requests through the app's HTTP kernel, with the user agent `LaRecipe-Doctor/1.0`, so it needs no running server and sees your middleware, headers and exception handler exactly as an agent would. URLs are built from `APP_URL`.

| Check | Passes when |
| --- | --- |
| `llms-txt` | `llms.txt` (and the default version's, on a versioned set) is served as text, stays under 50,000 characters, and has the llms.txt shape: a title, a summary and sections. |
| `llms-txt-links` | Up to 10 page links from `llms.txt` answer with Markdown. |
| `markdown` | The first five pages in reading order answer with Markdown at their `.md` URL and to `Accept: text/markdown`, and at least 95% of each article's words appear in its Markdown. Between 80% and 95% is a warning: usually a custom component without a Markdown form. |
| `not-found` | A missing page is a real `404` in HTML and in Markdown, with a Markdown body. |
| `discovery` | The HTML sends `Link` headers to the Markdown twin and `llms.txt`, sends `Vary: Accept`, and links to `llms.txt` in the page body. |
| `redirects` | Every redirect in `larecipe.json`, and the unversioned URL of a versioned set, redirects to a page that answers `200`. |
| `skill-md` | `skill.md` is served as Markdown with a valid `name` and `description`. |
| `mcp` | The set's MCP endpoint answers `tools/list` with `search_docs` and `read_page`. Skipped when it asks for authentication. |
| `cache-store` | A database set uses a persistent cache store. Skipped for repository sets. |

A pass counts fully, a warning half and a failure nothing; skipped checks do not count. The score becomes a grade: A from 90, B from 80, C from 70, D from 60. The command exits with `1` only when a check fails. `--set` limits it to one set, and `--json` prints the report.

This site's doctor run, from the local copy:

```text
  docs http://127.0.0.1:8780/

  PASS  llms.txt: llms.txt is served and every index is under 50,000 characters.
  PASS  llms.txt links: 10 sampled llms.txt links resolve to Markdown.
  WARN  Markdown twins: Only 93% of the words on http://127.0.0.1:8780/3.x are in its Markdown.
        Fix: Give custom components a Markdown form (LaRecipe::component(...)->markdown(...)).
  PASS  Missing pages: A missing page is a real 404, with a Markdown body for agents.
  PASS  Discovery hints: Pages send Link headers to their Markdown twin and llms.txt, and link to llms.txt in the page.
  PASS  Redirects: 1 redirects land on a page.
  PASS  skill.md: skill.md is served as the "larecipe" skill.
  PASS  MCP endpoint: http://127.0.0.1:8780/mcp serves search_docs, read_page, list_pages, submit_feedback.
  SKIP  Cache store: Repository sets read from files; no cache store is needed.

  Agent readiness: A (94 / 100)
```

## Which one when

| | `larecipe:validate` | `larecipe:doctor` |
| --- | --- | --- |
| Reads | The docs, through the renderer | The app's HTTP responses |
| Catches | Mistakes in the content | Problems in what is served: headers, routes, 404s, MCP |
| Fails on | Any error | A failed check |
| Run it | After every docs change, before every push, in CI | After changing routes, middleware, caching or the agent settings, and after a deploy |

A page can pass validation and still fail the doctor: a proxy that strips `Link` headers, or an exception handler that turns a 404 into a soft error page, never shows up in the content.

## Next

- [Search](search.md): the `search.budget` check and the server driver.
- [Deployment](deployment.md): where both commands fit in a deploy.
- [Agent surfaces](../agents/surfaces.md): the files the doctor checks.
