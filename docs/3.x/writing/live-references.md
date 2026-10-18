---
title: Live references
description: Let a page read routes, artisan commands, models and events from your running app, so those facts cannot go stale.
sources:
  - vendor/larecipe/larecipe/src/Domains/References/**
  - vendor/larecipe/larecipe/src/Domains/Delivery/Services/RenderCache.php
  - vendor/larecipe/larecipe/src/Domains/Sync/Services/CodeMentions.php
  - vendor/larecipe/larecipe/config/larecipe.php
---

# Live references

Most docs rot through pasted values: a route's URI, a command's options, a model's columns, copied into Markdown once and never touched again. A live reference reads that value from your app every time the page renders, so it cannot drift, and `larecipe:validate` fails the moment the thing it names disappears.

There are four, one per kind of fact your app already knows about itself:

| Component | Reads | Shows |
| --- | --- | --- |
| `<Route name="..." />` | the route collection | method, URI, domain, middleware, and the rules of the form request the action type-hints |
| `<Artisan command="..." />` | the console kernel | description, usage, arguments and options |
| `<Model name="..." />` | Eloquent and the database schema | table, columns with type, nullable, cast and fillable, and relations |
| `<Event name="..." />` | the event class and the event dispatcher | public payload properties with their types, listeners (sync or queued), and whether it broadcasts |

Live references are an advanced feature. Docs work fully without them, and you can adopt them one page at a time.

## See them on this page

These three are live. They are read from this site's own app on every render.

The command your coding agent runs before it pushes:

<Artisan command="larecipe:affected" />

The route behind the newsletter form at the bottom of every page:

<Route name="newsletter.subscribe" />

The event LaRecipe dispatches for every page view, for your own analytics:

<Event name="PageViewed" />

Agents get the same facts. The Markdown twin of this page ([live-references.md](https://larecipe.saleem.dev/3.x/writing/live-references.md)) renders each reference as a bold line followed by its tables, with the command's usage in a `bash` block.

## Write a reference

Each component takes one prop that names its target, on its own line:

```markdown
<Route name="orders.store" />

<Artisan command="orders:prune" />

<Model name="Order" />

<Event name="OrderShipped" />
```

- **Route**: the route's name, never its URI. A route without a name cannot be referenced; naming it is a one-line change that makes the docs safer.
- **Artisan**: the command's name, as you type it after `php artisan`.
- **Model**: a class name in an allowed namespace (`Order` means `App\Models\Order` by default), or a fully qualified class name inside one.
- **Event**: a class name in an event namespace (`OrderShipped` means `App\Events\OrderShipped` by default), or any fully qualified event class that has listeners registered.

Write a sentence around each reference. The component shows the facts; your words say when and why they matter.

## What they read, and what they never do

The resolvers read the public shape of your app and nothing else:

- **Route** reads what the route definition declares. The route is never run and its controller is never built, so middleware a controller adds in its constructor is not shown. The form request is created without the container, so it never validates anything.
- **Artisan** reads the command's signature. The command is never run.
- **Model** uses Eloquent's model inspector, which needs a database connection for the column list. Column defaults are left out: they are values, and a reference shows shape.
- **Event** lists property names and types, never values.

There is no `<Config>` and no `<Env>`, by rule. Configuration and environment values are where secrets live, and no allowlist is safe enough to put them one typo away from a public page. To mention a setting, write its name in inline code (`services.stripe.secret`) and say what it controls.

## Choose what may be described

Two settings in `config/larecipe.php` decide which classes a page may point at:

```php
'references' => [
    'models' => [
        'allow' => ['App\\Models\\*'],      // namespaces (with *) or exact classes
    ],
    'events' => [
        'namespaces' => ['App\\Events'],   // where short event names are looked up
    ],
],
```

`<Model>` describes only classes in `references.models.allow`, so a page can never aim the resolver at an arbitrary class. `<Event>` describes classes in `references.events.namespaces`, and any other class your app registers listeners for. This site adds LaRecipe's own event namespaces to the list, which is how the `PageViewed` reference above resolves.

## When a reference breaks

Rename a route, a command or a model and every page that references the old name stops rendering it:

- The page shows a plain notice in its place, such as "The route `orders.store` is not defined in this app.", and the Markdown twin shows the same sentence as a warning.
- `php artisan larecipe:validate` reports a `references.missing` error on that page, with the line of the reference.

So renaming a route without updating the docs fails the validator, in your agent's session and in CI. The [`larecipe-update-docs` skill](../keeping-current/skills.md) tells agents to fix these references along with the code change.

## Caching

Rendered pages are cached by their content, and a page with live references is also cached by a fingerprint of the parts of the app it reads:

| Kind | The fingerprint changes when |
| --- | --- |
| Route | the route cache file changes, or, without one, any route's name, methods, URI, domain, middleware or action |
| Artisan | any command's signature or description changes |
| Model | an allowed model class or a migration file changes |
| Event | an event class or its listeners change |

A renamed route re-renders exactly the pages that use `<Route>`, with no cache to clear by hand. A page whose references do not resolve is not cached at all, so the fix shows up on the next request.

## CI without a database

Routes and commands resolve whenever the app boots. `<Model>` needs a database schema, which most CI pipelines have (they run migrations on SQLite or a test database). When yours does not, write a snapshot where the schema is available:

<Artisan command="larecipe:references:snapshot" />

It writes every route, command, allowed model and event the app can describe to `bootstrap/cache/larecipe/references.json`. Resolvers use the snapshot only when the app cannot answer, for example when the database connection fails. A live "this no longer exists" always wins, so a stale snapshot can never hide a reference that broke. The file lives in a cache folder: generate it in the pipeline rather than committing it.

## Drift detection

Live references name their targets, so `larecipe:sources:suggest` reads them along with the classes, commands and route names in your inline code when it proposes `sources:` for a page. See [how keeping docs current works](../keeping-current/how-it-works.md).

## Next

- [Components](components.md): every built-in component.
- [Gate blocks and private docs](gate-blocks.md): content per reader, on every surface.
- [Validation and doctor](../operate/validation.md): what `larecipe:validate` checks.
