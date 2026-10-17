# larecipe-docs

The source of larecipe.saleem.dev: a Laravel 13 app that serves the LaRecipe docs with LaRecipe v3 itself. The docs set is mounted at the domain root, so the landing page is the docs Introduction.

- `docs/`: the docs. `larecipe.json` (the manifest), `3.x/` (current, with `ar/` for Arabic), `2.x/` (the converted LaRecipe 2 docs).
- `app/Http/Controllers/SubscribeController.php`, `routes/web.php`: the newsletter endpoint behind the `Subscribe` component.
- `resources/larecipe/`: the React side of the site's custom components, built into `public/build/larecipe/` by `npm run build`.
- `config/larecipe.php`: the doc set (root mount, repository source, public access).

The package itself lives in a sibling checkout (`../larecipe`, a Composer path repository). The docs describe the package: their `sources:` point at `vendor/larecipe/larecipe/...`, so check the package code before you change a page.

Writing rules for these docs: plain words, short sentences, no em or en dashes, no exclamation marks, no emoji. Every page has `title`, `description` and `sources`. Never claim behavior the package does not ship.

<!-- larecipe:start -->
## Documentation (LaRecipe)

This app's docs live in `docs/` and are served by LaRecipe. Docs ship in the same push as the code they describe.

Before you push a branch or open a pull request:

1. Run `php artisan larecipe:affected --uncommitted --base=main` to list the docs pages your change affects.
2. Update each affected page with the `larecipe-update-docs` skill. Keep unrelated text untouched.
3. Run `php artisan larecipe:validate` and fix what it reports.
4. Commit the docs with the code and push them together.

Review mode is `required`: show the user the docs diff and wait for approval before committing it.

Search and read the docs with the `larecipe` MCP server. Never write configuration or environment values into docs.

### Docs index: LaRecipe

1 page. One line per page: path, title, description. Read a page with the `read_page` tool of the `larecipe` MCP server, or open its file in `docs/`. Refresh this index with `php artisan larecipe:agents-md`.

- `index`: Introduction. Documentation for LaRecipe.
<!-- larecipe:end -->
