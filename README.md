# LaRecipe Documentation

The source of [larecipe.saleem.dev](https://larecipe.saleem.dev/): a Laravel 13 app that serves the LaRecipe docs with LaRecipe 3 itself. The docs are the landing page.

## Run it locally

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate               # sessions, cache and jobs live in SQLite
npm install && npm run build      # builds this site's custom components
php artisan serve
```

To work on the package and the site together, point Composer at a local checkout: `composer config repositories.larecipe path ../larecipe`, then `composer require larecipe/larecipe:@dev`. Do not commit that change.

## Where things are

- `docs/`: the docs. `larecipe.json`, `3.x/` (with `ar/` for Arabic) and `2.x/`.
- `config/larecipe.php`: the doc set, mounted at the domain root.
- `resources/larecipe/`, `resources/views/docs/`, `app/Providers/AppServiceProvider.php`: this site's custom components (the install box, the docs flow diagram, the newsletter form and others). `app/Http/Controllers/SubscribeController.php` is the newsletter endpoint.
- `AGENTS.md`: how coding agents keep these docs current.

Check the docs with `php artisan larecipe:validate`. The newsletter needs `NASHRA_API_KEY` in `.env`.
