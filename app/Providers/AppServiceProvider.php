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
        // #region subscribe
        LaRecipe::component('Subscribe')
            ->description('A sign-up form for the LaRecipe newsletter.')
            ->props([
                'list' => ['type' => 'string', 'default' => 'larecipe', 'description' => 'The newsletter list to join'],
                'showcase' => ['type' => 'boolean', 'default' => false, 'description' => 'Add a faded line under the form that links to how it is built'],
            ])
            ->fallback(function (array $props, string $children, RenderedComponent $component) {
                $locale = in_array($component->locale, SubscribeController::LOCALES, true) ? $component->locale : 'en';

                return view('docs.subscribe', [
                    'list' => $props['list'],
                    'locale' => $locale,
                    'showcase' => (bool) $props['showcase'],
                    'guide' => url(($locale === 'ar' ? 'ar/' : '').($component->version ?? '3.x').'/customize/custom-components'),
                ]);
            })
            ->markdown(fn () => 'Subscribe to the LaRecipe newsletter: '.url('/newsletter'))
            ->island();
        // #endregion

        // The Introduction's install box: a ready prompt for an AI coding
        // agent, or the two commands by hand (resources/larecipe/InstallTabs.tsx).
        // Without JavaScript both panels show; agents read the prompt and the
        // commands as Markdown.
        LaRecipe::component('InstallTabs')
            ->description('Install LaRecipe with an AI coding agent prompt or by hand.')
            ->fallback(function (array $props, string $children, RenderedComponent $component) {
                $locale = in_array($component->locale, SubscribeController::LOCALES, true) ? $component->locale : 'en';

                return view('docs.install-tabs', [
                    'locale' => $locale,
                    'paragraphs' => array_map($this->inlineCode(...), explode("\n\n", implode("\n", __('install.prompt', [], $locale)))),
                    'steps' => array_map($this->inlineCode(...), __('install.steps', [], $locale)),
                ]);
            })
            ->markdown(function (array $props, string $children, RenderedComponent $component) {
                $locale = in_array($component->locale, SubscribeController::LOCALES, true) ? $component->locale : 'en';

                return __('install.agent_lead', [], $locale)."\n\n```text\n".implode("\n", __('install.prompt', [], $locale))."\n```\n\n"
                    .__('install.manual_markdown', [], $locale)."\n\n```bash\ncomposer require larecipe/larecipe\nphp artisan larecipe:install\n```";
            })
            ->island();

        // The LaRecipe loop on the Introduction: an animated diagram in React
        // (resources/larecipe/DocsFlow.tsx), the same diagram at rest for
        // readers without JavaScript, and a short numbered list for agents.
        // Its words live in lang/{en,ar}/docs-flow.php.
        LaRecipe::component('DocsFlow')
            ->description('A diagram of the LaRecipe loop: code changes, the coding agent updates the docs, people and agents read them.')
            ->props(['caption' => ['type' => 'string', 'description' => 'An optional caption under the diagram']])
            ->fallback(fn (array $props, string $children, RenderedComponent $component) => view('docs.docs-flow', [
                'caption' => $props['caption'] ?? null,
                'locale' => $component->locale === 'ar' ? 'ar' : 'en',
            ]))
            ->markdown(fn (array $props, string $children, RenderedComponent $component) => ltrim(($props['caption'] ?? '')."\n\n"
                .__('docs-flow.markdown', [], $component->locale === 'ar' ? 'ar' : 'en')))
            ->island();
    }

    /** Escape a line of copy and set its `backtick` spans as code. */
    private function inlineCode(string $text): string
    {
        return (string) preg_replace('/`([^`]+)`/', '<code>$1</code>', e($text));
    }
}
