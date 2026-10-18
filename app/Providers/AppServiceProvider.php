<?php

namespace App\Providers;

use App\Http\Controllers\SubscribeController;
use Illuminate\Support\ServiceProvider;
use LaRecipe\Domains\Markdown\Components\RenderedComponent;
use LaRecipe\Facades\LaRecipe;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The newsletter form, in three forms: React in the browser
        // (resources/larecipe/Subscribe.tsx, built by @larecipe/vite), a plain
        // HTML form for readers without JavaScript, and one line of Markdown
        // for agents. The fallback speaks the page's language.
        LaRecipe::component('Subscribe')
            ->description('A sign-up form for the LaRecipe newsletter.')
            ->props(['list' => ['type' => 'string', 'default' => 'larecipe', 'description' => 'The newsletter list to join']])
            ->fallback(fn (array $props, string $children, RenderedComponent $component) => view('docs.subscribe', [
                'list' => $props['list'],
                'locale' => in_array($component->locale, SubscribeController::LOCALES, true) ? $component->locale : 'en',
            ]))
            ->markdown(fn () => 'Subscribe to the LaRecipe newsletter: '.url('/newsletter'))
            ->island();
    }
}
