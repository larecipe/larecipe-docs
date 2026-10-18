---
title: Code blocks
description: Highlighted code with titles, line highlights, line numbers and diffs, and samples included straight from your codebase so they never drift.
sources:
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/CodeBlockRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/DTOs/CodeFence.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/CodeHighlighter.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Support/PassTheme.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/SnippetIncludeStartParser.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/SnippetIncludeParser.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Services/SnippetResolver.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/MarkdownServiceProvider.php
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Code blocks

Write a fenced code block with a language and LaRecipe highlights it on the server, so the colors are in the HTML before any JavaScript runs. A few words after the language add a title, highlighted lines, line numbers or a diff. And when the code you are showing lives in your app, a snippet include pulls it from the file itself, so the sample changes when the code does.

````markdown
```php title="app/Http/Controllers/SubscribeController.php" {3}
public function __invoke(Request $request): JsonResponse|RedirectResponse
{
    $data = $request->validate([
```
````

## Languages

The language is the first word of the fence. LaRecipe highlights it with Phiki, which reads the same TextMate grammars as VS Code, so `php`, `blade`, `bash`, `json`, `yaml`, `tsx`, `sql`, `diff` and the other languages you use every day are covered. `env` highlights as a dotenv file, and `terminal` and `cli` as shell.

`text`, `txt`, `plain`, `plaintext`, `none` and `output` mean no highlighting. A language Phiki does not know falls back to plain text, so a typo in a fence never breaks a page. Always write a language: it labels the block and tells agents what they are reading.

Code sits on a slate slab in both light and dark mode, in LaRecipe's own `larecipe-pass` theme. Highlighting is the slowest part of rendering, so each block's output is cached on its own: editing a paragraph re-renders the page, while every unchanged code block comes from the cache.

## Titles

`title="..."` names the block in its header, usually after the file it comes from. Without a title, the header shows the language. `[app/Models/Order.php]` is a short form of the same thing.

```php title="config/larecipe.php"
'search' => [
    'driver' => 'client',
    'budget_kb' => 300,
],
```

````markdown
```php title="config/larecipe.php"
'search' => [
    'driver' => 'client',
    'budget_kb' => 300,
],
```
````

## Highlighted lines

Line numbers in braces highlight those lines: single lines and ranges, separated by commas. Use it to point at the one line the paragraph above is about.

```json {4-5}
{
  "$schema": "https://larecipe.saleem.dev/schemas/3.x/larecipe.json",
  "name": "LaRecipe",
  "versions": [
    { "id": "3.x", "default": true },
    { "id": "2.x", "status": "maintained" }
  ]
}
```

````markdown
```json {4-5}
{
  "$schema": "https://larecipe.saleem.dev/schemas/3.x/larecipe.json",
  "name": "LaRecipe",
  "versions": [
    { "id": "3.x", "default": true },
    { "id": "2.x", "status": "maintained" }
  ]
}
```
````

## Line numbers

`showLineNumbers` adds a gutter with line numbers, for long samples that the text refers to by line.

```yaml showLineNumbers
---
title: Webhooks
description: Receive signed events from the app and verify them.
sources: ["app/Webhooks/**", "routes/webhooks.php"]
---
```

## Diffs

To show a change, add `diff` after the language and start each line with a marker, as in a patch: `+` for an added line, `-` for a removed one and a space for the rest. LaRecipe removes the markers, highlights the code as its own language and marks each line as added or removed in the gutter.

```php diff
 'sets' => [
     'docs' => [
-        'route' => ['prefix' => 'docs'],
+        'route' => ['prefix' => ''],
     ],
 ],
```

````markdown
```php diff
 'sets' => [
     'docs' => [
-        'route' => ['prefix' => 'docs'],
+        'route' => ['prefix' => ''],
     ],
 ],
```
````

A ` ```diff ` block keeps its `+` and `-` markers, because they are part of the syntax, and marks the lines the same way. Use it for real patches; use `diff` on a language for a readable change.

The parts of a fence combine in any order after the language: ` ```php title="routes/web.php" {2} showLineNumbers `.

## Code groups

Put several code blocks inside a `CodeGroup` to show alternatives as tabs over one panel. Each block's title, or its language, becomes the tab label. See [components](components.md) for a live one.

````markdown
<CodeGroup>

```bash title="Dry run"
php artisan larecipe:upgrade
```

```bash title="Apply"
php artisan larecipe:upgrade --apply
```

</CodeGroup>
````

## Copy and agents

The reader UI adds a copy button to every block, and copying gives you the code without line numbers or diff markers. In a page's agent Markdown, every block stays a fenced code block with its language and title, so an agent reads the same code a person copies.

## Snippet includes

Code samples that describe your app should come from your app. A snippet include is a line that starts with `<<<` and points at a file, from the root of your project:

```markdown
<<< @/resources/larecipe/extension.tsx {8}
```

LaRecipe reads the file when it renders the page and turns it into a code block, titled with its path, in the language its extension says. This one is live: it is this site's own extension entry, the file that registers the newsletter form at the bottom of the page.

<<< @/resources/larecipe/extension.tsx {8}

Anything after the path is the same as in a fence: highlights, `showLineNumbers`, `diff`, or a `title="..."` to replace the path in the header. Highlighted line numbers count from the top of the included code, not the file.

A rendered page remembers the files it included. When one of them changes, the page renders again on the next request, so an included sample is never older than the code.

### Include one region

Most files are too long to show whole. Mark the part you want with a `#region` comment, give it a name, and include only that region:

```php title="app/Billing/Invoice.php"
public function refund(int $amount): Refund
{
    // #region refund
    $refund = $this->payments->refund($this->payment_id, $amount);

    $this->refunds()->save($refund);
    // #endregion

    return $refund;
}
```

```markdown
<<< @/app/Billing/Invoice.php#refund
```

The included code is the lines between `#region refund` and its `#endregion`, with the common indentation removed. Regions can nest, and the marker works in any comment style: `//`, `#`, `/* */`, `<!-- -->`, `{{-- --}}`, `--` and `;`. When you include a whole file, LaRecipe leaves the region markers out, because they are for the docs and not for the reader.

### When a snippet is missing

A file that was moved or a region that was renamed does not fail quietly. The page shows a warning callout titled "Snippet not found" in place of the code, and `php artisan larecipe:validate` reports it as an error with the line, so a stale sample stops your CI instead of reaching a reader.

| Rule | When |
| --- | --- |
| `snippets.missing` | The file, or the region in it, does not exist. |
| `snippets.denied` | The path leaves the snippet root, or points at a file that may hold secrets. |
| `snippets.disabled` | The doc set has no snippet root. |

A page whose render has an error like these is not cached, so the fix shows up on the next request.

### What can be included

`@/` is the snippet root. For a repository doc set it is your project's base path. Set `snippets.root` in `config/larecipe.php` to narrow it to one folder, or `sets.{key}.snippets.root` for one doc set. A database doc set includes nothing unless its set configures a root, because its content may come from people outside the codebase.

Some files are never included, even inside the root, so docs can never become a side door to your configuration. It is the same list [`larecipe:sync`](../keeping-current/sync.md) keeps its agent away from:

- dotfiles and anything in a dotted folder (`.env`, `.git/`, `.ssh/`),
- `config/`, `bootstrap/cache/`, `storage/`, `vendor/` and `node_modules/`,
- Composer's `auth.json` and PHPUnit's files (`phpunit.xml`, `phpunit.xml.dist`), which set environment values,
- keys, certificates and key stores (`.key`, `.pem`, `.p8`, `.p12`, `.pfx`, `.ppk`, `.crt`, `.cer`, `.der`, `.jks`, `.keystore`, `.kdbx`), SSH keys (`id_rsa`) and key or secret files without an extension (`deploy_key`),
- credential files (`firebase-credentials.json`), databases (`.sqlite`, `.sqlite3`, `.db`), dumps and logs (`.sql`, `.dump`, `.log`), files ending in `.env`, and JSON kept under `database/`, `backups/` or `dumps/`.

A path with `..` that leaves the root is refused, and so is a symlink whose real file lives outside it.

> [!TIP]
> List the file you include in the page's `sources:` too. Then `php artisan larecipe:affected` names the page when that file changes, and your agent reads the prose around the sample again, not only the sample.
