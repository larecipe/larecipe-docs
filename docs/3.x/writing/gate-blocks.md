---
title: Gate blocks and private docs
description: Decide who may read a doc set, a version, a page or one paragraph, with your app's own auth and gates, applied the same way on every surface people and agents read.
sources:
  - vendor/larecipe/larecipe/src/Domains/Access/**
  - vendor/larecipe/larecipe/src/Domains/Content/DTOs/PageSummary.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Controllers/PageController.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/ResponseHeaders.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/NotFoundResponder.php
  - vendor/larecipe/larecipe/src/Domains/Search/Laravel/Reader.php
  - vendor/larecipe/larecipe/src/Domains/Search/DTOs/SearchRecord.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Laravel/LaravelAgentDocs.php
  - vendor/larecipe/larecipe/src/Domains/Agents/Mcp/ServerComposition.php
  - vendor/larecipe/larecipe/src/Domains/Ask/AskServiceProvider.php
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Gate blocks and private docs

LaRecipe has no users or roles of its own. It asks your app, through Laravel's auth and gates, at three levels: who may read a doc set at all, which versions and pages are private, and which blocks inside a page a reader sees. One policy answers for every surface, so a page or a paragraph a reader may not see is missing from the HTML, the Markdown twin, search, `llms.txt` and MCP alike.

## Who may read the set

`access.mode` in [config/larecipe.php](../get-started/configuration.md) sets the door for a whole doc set:

```php title="config/larecipe.php"
'sets' => [
    'handbook' => [
        'route' => ['prefix' => 'handbook', 'middleware' => ['web', 'auth']],
        'source' => ['driver' => 'repository', 'path' => base_path('handbook')],
        'access' => ['mode' => 'gate'],
    ],
],
```

| Mode | Who may read | Everyone else gets |
| --- | --- | --- |
| `public` | Everyone, guests included. The default. | Nobody is turned away |
| `auth` | Anyone signed in with your default guard. | Your app's unauthenticated response, usually a redirect to the login page |
| `gate` | Readers your `viewLarecipe` gate allows, asked once for the set and again for every page. | A 404 |

In `gate` mode a guest gets a 404 like anyone else the gate refuses. To send guests to your login page first, add `auth` to the set's `route.middleware`, as above.

## The gates

LaRecipe asks three gates. Define them in a service provider, like any other gate:

| Gate | Arguments after the user | Asked |
| --- | --- | --- |
| `viewLarecipe` | `?PageSummary $page`, `string $set` | In `gate` mode, for the set (with no page) and for every page. In any mode, for private versions (with no page) and private pages. |
| `manageLarecipe` | `string $set` | Before the [admin UI](../sources/database.md#the-admin-ui) opens, before the web MCP endpoint offers the `validate_docs` tool, and before it accepts remote author tools on a database set. |
| `askLarecipe` | `string $set` | Before showing the Ask AI panel and before answering a question, when Ask AI is enabled. |

```php title="app/Providers/AppServiceProvider.php"
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use LaRecipe\Domains\Content\DTOs\PageSummary;

public function boot(): void
{
    Gate::define('viewLarecipe', function (?User $user, ?PageSummary $page, string $set) {
        if ($set === 'handbook') {
            return $user?->is_staff ?? false;
        }

        // Private pages in the public docs: any signed-in customer.
        return $user !== null;
    });

    Gate::define('manageLarecipe', fn (User $user, string $set) => $user->is_admin);

    Gate::define('askLarecipe', fn (?User $user, string $set) => $user !== null);
}
```

`$page` is a `PageSummary`, not the page body: read `$page->ref->path`, `$page->ref->version`, `$page->ref->locale`, `$page->title` and `$page->private`. It is `null` when LaRecipe asks about the set or a private version, so type it as nullable.

> [!IMPORTANT]
> Type the user as nullable (`?User $user`) wherever guests should be able to pass. Laravel denies a guest without running a gate whose first parameter cannot be `null`.

### When a gate is not defined

| Gate | Without a definition |
| --- | --- |
| `viewLarecipe` | Any signed-in reader passes in the `local` environment. Everyone else, everywhere else, is refused. |
| `manageLarecipe` | The same as `viewLarecipe`. |
| `askLarecipe` | Everyone may ask, guests included, as long as Ask AI is enabled. |

So a private page works out of the box on your machine for anyone signed in, and stays closed in production until you define `viewLarecipe`.

## Private versions and pages

A version is private when its entry in [larecipe.json](../reference/manifest.md) says so:

```json
{
  "versions": [
    { "id": "4.x", "status": "current", "visibility": "private" },
    { "id": "3.x", "default": true }
  ]
}
```

A page is private when its frontmatter says so:

```yaml
---
title: Roadmap
description: What ships next quarter, for customers on the Enterprise plan.
visibility: private
---
```

Both need `viewLarecipe`, in every access mode. For a private version the gate is asked with no page; for a private page it receives the page. A reader the gate refuses never learns either exists: they are left out of navigation, search, `llms.txt`, the sitemap and MCP, and a direct request answers 404.

### A page the reader may not see is a 404, never a 403

LaRecipe answers a page the reader may not open exactly as it answers a page that does not exist: a real 404, in HTML and in Markdown, with suggestions drawn only from pages the reader can see. A 403 would confirm that the page is there. The one exception is the door itself: in `auth` mode, a guest gets your app's login response for the whole set.

## Gate blocks: Can and Cannot

Sometimes most of a page is for everyone and one paragraph is not: an export only Enterprise plans have, a setting only admins can change. Wrap that part in `<Can>` or `<Cannot>` with the name of a gate your app defines:

```markdown
<Can ability="view-billing">
Enterprise plans can export invoices as CSV from **Billing**, then **Exports**.
</Can>

<Cannot ability="view-billing">
Ask your workspace owner for access to billing exports.
</Cannot>
```

`<Can>` shows its content to readers the ability allows. `<Cannot>` shows its content to everyone else, guests included, which makes it the place for "sign in to see more" or "ask your admin". The content is ordinary Markdown: lists, code blocks, components, headings.

The ability is any gate you define, checked for the current reader with no other arguments, as `Gate::allows('view-billing')` would:

```php title="app/Providers/AppServiceProvider.php"
Gate::define('view-billing', fn (?User $user) => $user?->plan === 'enterprise');
```

- **An ability you never defined denies everyone.** `<Can>` stays hidden and `<Cannot>` shows, so a typo hides content instead of leaking it.
- **A block without `ability` is hidden from every reader**, and `php artisan larecipe:validate` reports the missing prop.
- **Policies that need a model do not fit.** The block passes no arguments, so use a gate that decides from the user alone.

### Removed, not hidden

LaRecipe removes the blocks a reader may not see from the parsed page before anything renders it. Nothing is hidden with CSS, so there is nothing to find in the page source. Headings inside a removed block leave the table of contents too, and a quote that only held a removed block disappears with it.

Rendered pages are cached per audience, never per user: readers who hold the same abilities on a page share one cached render, and a reader who holds none of them gets the guest render.

## Every surface, the same rules

| Surface | Private sets, versions and pages | Gate blocks |
| --- | --- | --- |
| HTML page | Shown only to readers who may see them | Removed for readers who may not see them |
| Markdown twin (`.md`) | The same | The same view as the HTML, for the same reader |
| Search | Each reader's index holds only pages they may see | Records are split at gate blocks and kept only for readers they allow, in the browser index and the server ranker alike |
| `llms.txt`, `llms-full.txt` | Built for the caller, so they list only what the caller may see | `llms-full.txt` holds the guest view (below) |
| `skill.md`, `sitemap.xml` | `skill.md` is built for the caller; the sitemap lists only what a guest may see | Neither holds page content |
| MCP | `search_docs`, `read_page` and `list_pages` see only what the caller may see | `search_docs` follows the caller's abilities; `read_page` returns the guest view |
| Ask AI | Reads through the same MCP tools, as the reader | The guest view, through `read_page` |

The surfaces that hand an agent whole pages (`llms-full.txt`, the MCP `read_page` tool and Ask AI, which reads through it) serve every caller the guest view of gate blocks: `<Can>` content never appears there, and `<Cannot>` content does. They can show an agent less than the HTML shows its reader, never more.

To let agents read a private set over MCP, put an authentication middleware in front of its endpoint with `mcp.web.middleware`, such as `['auth:sanctum']`. Access is checked again on every tool call. See [the MCP server](../agents/mcp.md).

## Share one private page

To show a private page to someone without an account, such as a customer or a contractor, issue a share link:

```bash
php artisan larecipe:share 3.x/billing/roadmap --expires=7d
```

It prints the page URL with a `share` token. Anyone holding the link can read that one page until it expires, as HTML or Markdown, even when the page, its version or its whole set is private. Every other page stays closed, and the sidebar lists only what the reader could see anyway. Gate blocks still follow the reader: a guest with the link gets the guest view.

- **The page** is written `set/locale/version/path`, where the set, the language and the version are optional and recognized by name: `docs/ar/3.x/billing/roadmap`, or `billing/roadmap` alone for the default set, language and version.
- **The lifetime** is `30m`, `12h`, `7d` or `2w`, and defaults to `7d`.
- **Revoking.** Links are signed with your `APP_KEY`, so rotating the key revokes every share link at once. There is no way to revoke a single link before it expires.
- The command signs whatever path you give it, so open the link once to check it reaches the page you meant.
- A share link gets past LaRecipe's checks, not your route middleware. On a set whose `route.middleware` includes `auth`, a guest is sent to log in before the link is read, so use `access.mode` `auth` or `gate` without that middleware for sets you share from.

Read live from this app:

<Artisan command="larecipe:share" />

## Response headers

A response that only one reader may see is never stored by a shared cache and never indexed. That covers a page in a set that is not `public`, a private page or version, a page opened with a share link, and a page whose gate blocks granted the reader something guests do not get. Each of those answers with:

```text
Cache-Control: private, no-store
X-Robots-Tag: noindex
```

and no `ETag`. A page opened with a share link also sends `Referrer-Policy: no-referrer`, so the token never leaks to the next site in a `Referer` header. A public page that has gate blocks, served to a guest, stays cacheable but varies on `Accept` and `Cookie`.

Public pages are cacheable for a minute in browsers and ten minutes in shared caches; [agent surfaces](../agents/surfaces.md) lists every header a page sends.

## Next

- [Configuration](../get-started/configuration.md): `access.mode` and the rest of a doc set's settings.
- [The MCP server](../agents/mcp.md): authenticating agents for private sets.
- [Security](../operate/security.md): what LaRecipe does to keep private docs private.
