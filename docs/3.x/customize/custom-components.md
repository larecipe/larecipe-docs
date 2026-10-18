---
title: Custom React components
description: Write your own components in React, use them in Markdown like the built-ins, and give each one a no-JavaScript fallback and a Markdown form for agents.
sources:
  - vendor/larecipe/larecipe/src/LaRecipe.php
  - vendor/larecipe/larecipe/src/Domains/Markdown/Components/**
  - vendor/larecipe/larecipe/src/Domains/Markdown/Parser/ComponentRenderer.php
  - vendor/larecipe/larecipe/src/Domains/Delivery/Support/Assets.php
  - vendor/larecipe/larecipe/packages/ui/src/**
  - vendor/larecipe/larecipe/packages/vite/src/**
  - resources/larecipe/**
  - resources/views/docs/subscribe.blade.php
  - app/Providers/AppServiceProvider.php
  - app/Http/Controllers/SubscribeController.php
  - lang/*/newsletter.php
  - routes/web.php
  - vite.config.js
---

# Custom React components

When the built-in components are not enough (a newsletter form, a pricing table, a live status badge), write your own in React and use it in Markdown like any built-in: `<Subscribe list="larecipe" />`. A component has three faces, and you write all three:

- **React**, for readers whose browser runs JavaScript.
- **HTML**, a server fallback for readers without JavaScript, for crawlers, and for the first paint.
- **Markdown**, what agents get when they read the page as `.md` or through MCP.

The newsletter form at the end of the [Introduction](../index.md) is one. This page builds it in four steps, straight from this site's code:

1. [Write the React component](#the-react-component).
2. [Register it in the extension entry](#the-extension-entry).
3. [Define it in PHP](#the-php-definition), with its fallback and its Markdown form.
4. [Build it](#build-it) with Vite.

Then [use it](#use-it) in a page, or on every page.

<Accordion title="Every file the Subscribe form needs">

<FileTree>

- app/
  - Http/Controllers/SubscribeController.php
  - Providers/AppServiceProvider.php
- resources/
  - larecipe/
    - extension.tsx
    - Subscribe.tsx
    - subscribe.css
  - views/
    - docs/subscribe.blade.php
    - newsletter.blade.php
- routes/web.php
- vite.config.js

</FileTree>

</Accordion>

## The React component

A custom component is an ordinary React component. Its props are the tag's attributes, and Markdown between an opening and a closing tag arrives as `children`. Everything it imports from `@larecipe/ui` is provided by LaRecipe at runtime.

<<< @/resources/larecipe/Subscribe.tsx

<AccordionGroup>

<Accordion title="What @larecipe/ui gives your component">

| Export | What it does |
| --- | --- |
| `useLaRecipe()` | The current `page` (path, title, URLs, version, locale and `meta`), the `set` (key, name, versions, locales), the `locale` (code and direction), the `theme` (mode and the reader's preference), the signed-in `user` when you share one, `t` for LaRecipe's own UI strings in the page's language, and a `navigate(url)` helper. |
| `request(path, options)` | A fetch to your app's own routes. It refuses other origins, sends the session cookie, Laravel's `XSRF-TOKEN` as `X-XSRF-TOKEN` and `Accept: application/json`, encodes `body` as JSON, and returns `{ ok: true, status, data }` or `{ ok: false, status, data, errors, message }`, with Laravel's 422 validation errors in `errors`. |
| `Button`, `LinkButton`, `Input`, `Textarea`, `Field`, `Callout`, `Badge`, `Card`, `Kbd`, `Loader`, `Ticket`, `Perforation` | The parts the built-in UI is made of, in both themes and right to left. `Button` takes a `variant` (`primary`, `secondary`, `outline`, `ghost`, `ink`), a `size` and `loading`. |
| `Icons`, `Icon` | The UI's own icon set. |
| `Slot`, `useSlotDefault()` | Replace a built-in region of the page and still render the original. See [insert points and slots](insert-points.md). |
| `defineExtension()` | Registers your components, insert points and slot overrides with the page. |

</Accordion>

<Accordion title="Share the signed-in user and page values">

The signed-in user is shared only when you ask for it, field by field:

```php title="app/Providers/AppServiceProvider.php"
LaRecipe::shareUser(fn ($user) => ['name' => $user->name]);
```

Values a component needs from one page go under `meta:` in that page's frontmatter, and arrive as `page.meta`:

```yaml
---
title: Pricing
meta:
  plan: enterprise
---
```

`meta` is the only frontmatter beyond LaRecipe's own keys that reaches the browser. Other unknown keys stay on the server, because they often hold notes for the team, such as an owner or a ticket. See [frontmatter](../reference/frontmatter.md#unknown-keys).

</Accordion>

</AccordionGroup>

## The extension entry

One entry file registers every custom component of your app:

<<< @/resources/larecipe/extension.tsx

Its CSS reads LaRecipe's [theme variables](theme.md), so the form follows a team's accent, dark mode and right-to-left pages with no extra rules. The same rules style the React form and the HTML fallback, because both use the same class names.

<Accordion title="resources/larecipe/subscribe.css">

<<< @/resources/larecipe/subscribe.css

</Accordion>

## The PHP definition

Every custom component needs a definition in PHP, usually in `AppServiceProvider::boot()`. It names the component, describes its props, and gives its HTML fallback and its Markdown form:

<<< @/app/Providers/AppServiceProvider.php#subscribe

`props()` describes each prop, `fallback()` returns the server HTML, `markdown()` returns what agents read, and `island()` lets React mount over the fallback. A tag with no PHP definition is an unknown component: it renders as escaped text, and `php artisan larecipe:validate` reports `components.unknown`. So a page can never ask the browser to run code your app did not register.

<Accordion title="Every definition method">

| Method | What it sets |
| --- | --- |
| `props([...])` | Each prop's `type`, and optionally `default`, `required`, `enum` and `description` (or the short form `'list' => 'string'`). `larecipe:validate` and every render check them: a missing required prop or a value outside `enum` is a `components.props` error. On a repository set, props you do not describe still reach React. On a database set, whose authors may be less trusted than your codebase, they are dropped, so describe every prop you read. |
| `fallback(fn (array $props, string $children, RenderedComponent $context) => ...)` | The server HTML. Return a string, a view or anything `Htmlable`. `$children` is the rendered Markdown inside the tag, and `$context` carries the set, version, locale and page path. |
| `markdown(fn (array $props, string $children, $context) => ...)` | The line or block agents read. Without it, agents get the children. |
| `island()` | Wraps the fallback in a placeholder React mounts into. Without it, an inline tag stays as its fallback HTML and React never runs. At insert points, components are always wrapped. |
| `raw()` | Keeps the content between the tags as literal text instead of Markdown. |
| `description('...')` | One sentence for the generated component reference agents read. |

</Accordion>

### The fallback

The fallback is what a reader without JavaScript gets, so make it work on its own. Subscribe's is a plain form that posts to the same route:

<<< @/resources/views/docs/subscribe.blade.php

Rendered articles are cached and shared between readers, so a fallback must not bake in anything that belongs to one request. `LaRecipe::csrfField()` prints a placeholder that LaRecipe fills with the reader's CSRF token on every response. Without JavaScript, the form posts and lands on [the newsletter page](/newsletter), a plain page that shows whether it worked.

### The Markdown form

Agents should not fill in forms, so Subscribe's Markdown form is one line with a link, plus the faded note when `showcase` is set. Read the [Introduction as Markdown](https://larecipe.saleem.dev/3.x/index.md) and you will find it near the end:

```markdown
Subscribe to the LaRecipe newsletter, release notes by email to the address you give (unsubscribe any time): https://larecipe.saleem.dev/newsletter

This form is a custom React component this site adds to LaRecipe. See how it is built: https://larecipe.saleem.dev/3.x/customize/custom-components
```

<AccordionGroup>

<Accordion title="In the reader's language">

A component can follow the page's language like the built-in UI does. Subscribe reads `locale` from `useLaRecipe()` in React, and `$context->locale` in its fallback, then picks its words from `lang/en/newsletter.php` or `lang/ar/newsletter.php`. On the [Arabic Introduction](/ar/3.x) the same form reads right to left, in Arabic, and the route answers in Arabic too.

</Accordion>

<Accordion title="The route the form posts to">

The React form and the fallback both post to an ordinary route in your app, with your own middleware. This is that route, read from the running app:

<Route name="newsletter.subscribe" />

The controller validates the address and sends it to Nashra. It never fails with a 500: a missing API key or an unreachable service comes back as one friendly sentence, as JSON for `request()` and as a redirect for the plain form.

<<< @/app/Http/Controllers/SubscribeController.php

</Accordion>

</AccordionGroup>

## Build it

Custom components are the one part of LaRecipe that needs Node, and only at build time, on your machine or in CI. Add `@larecipe/vite` and `@larecipe/ui` as dev dependencies, then add the plugin to your Vite config. `@larecipe/vite` works with Vite 6, 7 and 8. This is this site's own config:

<<< @/vite.config.js

`npm run build` then writes the extension to `public/build/larecipe/`. Your server never runs Node: the built files are static. LaRecipe looks for `public/build/larecipe/manifest.json` on every docs page and, when the extension's entry is there, adds its stylesheet and module right after its own runtime. There is no script tag to add by hand.

<Accordion title="One React on the page">

The build is an ES module with a manifest. `react`, `react-dom` and `@larecipe/ui` are left out of the bundle on purpose. Every docs page prints an import map ahead of LaRecipe's scripts, which points the bare names `react`, `react/jsx-runtime`, `react-dom`, `react-dom/client` and `@larecipe/ui` at LaRecipe's own built files:

```html
<script type="importmap">{"imports":{"react":"/larecipe-assets/{build}/assets/react-….js","@larecipe/ui":"/larecipe-assets/{build}/assets/larecipe-ui-….js", "…": "…"}}</script>
```

Your extension keeps those imports bare, so the browser resolves them to the same modules LaRecipe uses. There is one React and one component registry, and hooks such as `useLaRecipe()` work inside your components. `defineExtension()` works whether your module loads before or after LaRecipe's runtime.

</Accordion>

## Use it

**Inline, in Markdown.** Write the tag on its own line, with blank lines around it, like any built-in. Props are strings:

```markdown
<Subscribe list="larecipe" />
```

**On every page.** Place it at an insert point in `larecipe.json`, as this site does with `"inserts": { "article.after": ["Subscribe"] }`. See [Insert points and slots](insert-points.md).

| Reader | Gets |
| --- | --- |
| A browser with JavaScript | The React form, which posts with `request()` and answers in place |
| A browser without JavaScript, a crawler | The fallback form, which posts and lands on `/newsletter` |
| An agent reading `.md` or MCP | One line: the newsletter's URL |

## Next

- [Insert points and slots](insert-points.md): place a component on every page.
- [Theme](theme.md): the tokens and CSS variables your components can read.
- [Components reference](../reference/components.md): the built-in components and the HTML they render.
