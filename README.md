# LaRecipe Documentation

The source of [larecipe.saleem.dev](https://larecipe.saleem.dev/): a Laravel 13 app that serves the LaRecipe docs with LaRecipe 3 itself. The docs are the landing page.

## Run it locally

LaRecipe 3 is in development, so this app requires the package from a sibling checkout (a Composer path repository at `../larecipe`, and `file:` links for `@larecipe/ui` and `@larecipe/vite`).

```bash
git clone https://github.com/saleem-hadad/larecipe ../larecipe   # the v3 branch
composer install
cp .env.example .env && php artisan key:generate
npm install && npm run build      # builds the Subscribe component
php artisan serve
```

## Where things are

- `docs/`: the docs. `larecipe.json`, `3.x/` (with `ar/` for Arabic) and `2.x/`.
- `config/larecipe.php`: the doc set, mounted at the domain root.
- `resources/larecipe/`, `app/Providers/AppServiceProvider.php`, `app/Http/Controllers/SubscribeController.php`: the Subscribe newsletter component.
- `AGENTS.md`: how coding agents keep these docs current.

Check the docs with `php artisan larecipe:validate`. The newsletter needs `NASHRA_API_KEY` in `.env` and the `nashra/sdk` package.
