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

A route's URI, a command's options, a model's columns: pasted into Markdown once, they rot. A live reference reads that fact from your app every time the page renders, so it cannot drift. Write the tag with the name of what it describes, on its own line:

```markdown
<Route name="orders.store" />

<Artisan command="orders:prune" />

<Model name="Order" />

<Event name="OrderShipped" />
```

| Component | Shows |
| --- | --- |
| `<Route name="..." />` | The method, URI, middleware names, and the fields and rule names of the form request the action type-hints |
| `<Artisan command="..." />` | The description, usage, arguments and options, with the defaults its signature declares |
| `<Model name="..." />` | The table, columns with type, nullable, cast and fillable, and relations |
| `<Event name="..." />` | The public payload properties with their types, the listeners (sync or queued), and whether it broadcasts |

Rename the route and `php artisan larecipe:validate` fails on every page that still names the old one. Docs work fully without live references, so adopt them one page at a time.

This one is live, read from this site's app on every render: the route behind the newsletter form at the bottom of every page.

<Route name="newsletter.subscribe" />

## Write a reference

- **Route**: the route's name, never its URI. A route without a name cannot be referenced; naming it is a one-line change that makes the docs safer.
- **Artisan**: the command's name, as you type it after `php artisan`.
- **Model**: a class name in an allowed namespace (`Order` means `App\Models\Order` by default), or a fully qualified class name inside one.
- **Event**: a class name in an event namespace (`OrderShipped` means `App\Events\OrderShipped` by default), or any fully qualified event class that has listeners registered.

Write a sentence around each reference. The component shows the facts; your words say when and why they matter. Agents get the same facts: the page's Markdown twin renders each reference as a bold line followed by its tables.

There is no `<Config>` and no `<Env>`, by rule. Configuration and environment values are where secrets live. To mention a setting, write its name in inline code (`services.stripe.secret`) and say what it controls.

## When a reference breaks

A route, command or model that no longer exists stops rendering. The page shows a plain notice in its place, such as "The route `orders.store` is not defined in this app.", the Markdown twin shows the same sentence as a warning, and `larecipe:validate` reports a `references.missing` error with the line. So renaming a route without updating the docs fails the validator, in your agent's session and in CI. The [`larecipe-update-docs` skill](../keeping-current/skills.md) tells agents to fix these references along with the code change.

## Choose what may be described

Three settings in `config/larecipe.php` decide which routes and classes a page may point at:

```php title="config/larecipe.php"
'references' => [
    'routes' => [
        'allow' => null,                    // route names or URIs (with *); null: your app's own routes
    ],
    'models' => [
        'allow' => ['App\\Models\\*'],      // namespaces (with *) or exact classes
    ],
    'events' => [
        'namespaces' => ['App\\Events'],   // where short event names are looked up
    ],
],
```

`<Model>` describes only classes in `references.models.allow`, so a page can never aim the resolver at an arbitrary class.

## Details

<AccordionGroup>

<Accordion title="Two more references, live from this site">

The command your coding agent runs before it pushes:

<Artisan command="larecipe:affected" />

The event LaRecipe dispatches for every page view, for your own analytics:

<Event name="PageViewed" />

</Accordion>

<Accordion title="Which routes and events may be described">

`<Route>` describes the routes `references.routes.allow` lists, by name or by URI, with `*` as a wildcard (`'orders.*'`, `'api/*'`). With `null`, the default, it describes every route your app defines: routes that packages in `vendor/` register, such as an admin panel or a debug bar, are left out, except LaRecipe's own. A list includes package routes it names. `larecipe:references:snapshot` and `larecipe:validate` follow the same list.

`<Event>` describes classes in `references.events.namespaces`, and any other class your app registers listeners for. This site adds LaRecipe's own event namespaces to the list, which is how the `PageViewed` reference resolves.

A route the allow list leaves out, and a hidden command, read the same way as one that does not exist: the notice on the page, and a `references.missing` error from the validator.

</Accordion>

<Accordion title="What they read, and what they never do">

The resolvers read the public shape of your app and nothing else:

- **Route** reads what the route definition declares, and shows names only: each middleware without its parameters (`throttle`, not `throttle:5,1`), and each form request field with the names of its rules, never their arguments (`in`, not the list of values). Rule arguments and middleware parameters are often built from config, so a page never shows them. The route's domain is never shown either. The route is never run and its controller is never built, so middleware a controller adds in its constructor is not shown. The form request is created without the container, so it never validates anything.
- **Artisan** reads the command's signature. The command is never run, and hidden commands are never described. A default is shown only when it is written in the command's `$signature`: a command that builds its options at runtime, or a closure command, could fill a default from config, so its defaults are left out.
- **Model** uses Eloquent's model inspector, which needs a database connection for the column list. Column defaults are left out: they are values, and a reference shows shape.
- **Event** lists property names and types, never values.

</Accordion>

<Accordion title="How pages with references are cached">

Rendered pages are cached by their content, and a page with live references is also cached by a fingerprint of the parts of the app it reads:

| Kind | The fingerprint changes when |
| --- | --- |
| Route | the route cache file changes, or, without one, any route's name, methods, URI, domain, middleware or action |
| Artisan | any command's signature or description changes |
| Model | an allowed model class or a migration file changes |
| Event | an event class or its listeners change |

A renamed route re-renders exactly the pages that use `<Route>`, with no cache to clear by hand. A page whose references do not resolve is not cached at all, so the fix shows up on the next request.

</Accordion>

<Accordion title="CI without a database">

Routes and commands resolve whenever the app boots. `<Model>` needs a database schema, which most CI pipelines have (they run migrations on SQLite or a test database). When yours does not, write a snapshot where the schema is available:

<Artisan command="larecipe:references:snapshot" />

It writes every route, command, allowed model and event the app can describe to `bootstrap/cache/larecipe/references.json`. Resolvers use the snapshot only when the app cannot answer, for example when the database connection fails. A live "this no longer exists" always wins, so a stale snapshot can never hide a reference that broke. The file lives in a cache folder: generate it in the pipeline rather than committing it.

</Accordion>

<Accordion title="Live references and sources:">

Live references name their targets, so `larecipe:sources:suggest` reads them along with the classes, commands and route names in your inline code when it proposes `sources:` for a page. See [how keeping docs current works](../keeping-current/how-it-works.md).

</Accordion>

</AccordionGroup>

## Next

- [Gate blocks and private docs](gate-blocks.md): content per reader, on every surface.
- [Components](components.md): every built-in component.
- [Validation and doctor](../operate/validation.md): what `larecipe:validate` checks.
