<?php

namespace App\Providers;

use App\Support\LaRecipeCommands;
use App\Support\LaRecipeExtension;
use Illuminate\Support\ServiceProvider;
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
        // (resources/larecipe/Subscribe.tsx), a plain HTML form for readers
        // without JavaScript, and one line of Markdown for agents.
        LaRecipe::component('Subscribe')
            ->description('A sign-up form for the LaRecipe newsletter.')
            ->props(['list' => ['type' => 'string', 'default' => 'larecipe', 'description' => 'The newsletter list to join']])
            ->fallback(fn (array $props) => view('docs.subscribe', ['list' => $props['list']]))
            ->markdown(fn () => 'Subscribe to the LaRecipe newsletter: '.url('/newsletter'))
            ->island();

        // Load the extension that holds the React side of our components.
        LaRecipe::head(fn () => LaRecipeExtension::tags());

        // Let <Artisan command="larecipe:..." /> read LaRecipe's own commands on the web.
        if (! $this->app->runningInConsole()) {
            LaRecipeCommands::registerForWeb();
        }
    }
}
