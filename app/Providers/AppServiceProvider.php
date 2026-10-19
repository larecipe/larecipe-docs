<?php

namespace App\Providers;

use App\Http\Controllers\SubscribeController;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use LaRecipe\Domains\Delivery\Services\DocsUrls;
use LaRecipe\Domains\Markdown\Components\RenderedComponent;
use LaRecipe\Facades\LaRecipe;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The sites that featured LaRecipe, as the v2 landing page listed them.
     * Each logo is public/images/featured/{logo}.png. The Medium entry never
     * had an article link, so it shows without one.
     */
    private const FEATURED_ON = [
        ['name' => 'Laravel News', 'logo' => 'laravelnews', 'href' => 'https://laravel-news.com/larecipe-documentation-package'],
        ['name' => 'DevDojo', 'logo' => 'devdojo', 'href' => 'https://devdojo.com/episode/larecipe'],
        ['name' => 'Medium', 'logo' => 'medium', 'href' => null],
        ['name' => 'Packalyst', 'logo' => 'packalyst', 'href' => 'https://packagist.org/packages/binarytorch/larecipe'],
        ['name' => 'Product Hunt', 'logo' => 'producthunt', 'href' => 'https://www.producthunt.com/posts/larecipe-beautiful-docs'],
    ];

    /**
     * The Introduction's feature grid: an icon from LaRecipe's content set and
     * the page that explains each feature, from the version root. The words
     * live in lang/{en,ar}/features.php under the same keys.
     */
    private const FEATURES = [
        'current' => ['icon' => 'git-pull-request', 'page' => 'keeping-current/how-it-works'],
        'agents' => ['icon' => 'bot', 'page' => 'agents/surfaces'],
        'ask' => ['icon' => 'sparkles', 'page' => 'agents/ask-ai'],
        'references' => ['icon' => 'activity', 'page' => 'writing/live-references'],
        'private' => ['icon' => 'lock', 'page' => 'writing/gate-blocks'],
        'languages' => ['icon' => 'languages', 'page' => 'writing/languages'],
    ];

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
            ->markdown(function (array $props, string $children, RenderedComponent $component) {
                $locale = in_array($component->locale, SubscribeController::LOCALES, true) ? $component->locale : 'en';
                $line = __('newsletter.markdown', ['url' => url('/newsletter')], $locale);

                if (! $props['showcase']) {
                    return $line;
                }

                return $line."\n\n".__('newsletter.note', [], $locale).' '.__('newsletter.note_link', [], $locale).': '
                    .url(($locale === 'ar' ? 'ar/' : '').($component->version ?? '3.x').'/customize/custom-components');
            })
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
                    .__('install.manual_markdown', [], $locale)."\n\n```bash\ncomposer require larecipe/larecipe:^3.0\nphp artisan larecipe:install\n```";
            })
            ->island();

        // The LaRecipe loop on the Introduction: an animated diagram in React
        // (resources/larecipe/DocsFlow.tsx), the same diagram at rest for
        // readers without JavaScript, and a short numbered list for agents.
        // Its words live in lang/{en,ar}/docs-flow.php.
        LaRecipe::component('DocsFlow')
            ->description('A diagram of the LaRecipe loop: a code change makes a docs page stale, the coding agent updates it, the code and the docs merge together in one pull request, and people and agents read the result.')
            ->props(['caption' => ['type' => 'string', 'description' => 'An optional caption under the diagram']])
            ->fallback(fn (array $props, string $children, RenderedComponent $component) => view('docs.docs-flow', [
                'caption' => $props['caption'] ?? null,
                'locale' => $component->locale === 'ar' ? 'ar' : 'en',
            ]))
            // The summary sentence is the diagram's text for screen readers, so
            // agents get the same words (the illustration itself is marked
            // data-markdown-ignore), then the loop step by step.
            ->markdown(function (array $props, string $children, RenderedComponent $component) {
                $locale = $component->locale === 'ar' ? 'ar' : 'en';

                return ltrim(($props['caption'] ?? '')."\n\n".__('docs-flow.summary', [], $locale)."\n\n".__('docs-flow.markdown', [], $locale));
            })
            ->island();

        // The Introduction's social proof: LaRecipe's downloads and the sites
        // that featured it, as one quiet row of logos. Server only (no
        // island): the Blade view is the whole component, and agents read one
        // line. Its words live in lang/{en,ar}/featured-on.php.
        LaRecipe::component('FeaturedOn')
            ->description('LaRecipe\'s downloads and the sites that featured it, as a row of logos.')
            ->fallback(fn (array $props, string $children, RenderedComponent $component) => view('docs.featured-on', [
                'locale' => $this->pageLocale($component),
                'outlets' => self::FEATURED_ON,
            ]))
            ->markdown(function (array $props, string $children, RenderedComponent $component) {
                $locale = $this->pageLocale($component);
                $outlets = array_map(fn (array $outlet) => $outlet['href'] !== null ? "[{$outlet['name']}]({$outlet['href']})" : $outlet['name'], self::FEATURED_ON);

                return __('featured-on.markdown', [
                    'outlets' => Arr::join($outlets, __('featured-on.separator', [], $locale), __('featured-on.last_separator', [], $locale)),
                ], $locale);
            });

        // The Introduction's feature showcase: LaRecipe's main features, each
        // linking to the page that explains it. Server only, like FeaturedOn.
        // Agents read the same list as Markdown links.
        LaRecipe::component('Features')
            ->description('A grid of LaRecipe\'s main features, each linking to the page that explains it.')
            ->fallback(fn (array $props, string $children, RenderedComponent $component) => view('docs.features', [
                'features' => $this->features($component, markdown: false),
            ]))
            ->markdown(fn (array $props, string $children, RenderedComponent $component) => implode("\n", array_map(
                fn (array $feature) => "- [{$feature['title']}]({$feature['url']}): {$feature['line']}",
                $this->features($component, markdown: true),
            )));

        // The byline in the site footer, next to "Built with LaRecipe"
        // (placed at the footer insert point in docs/larecipe.json). Server
        // only, like FeaturedOn. Its words live in lang/{en,ar}/byline.php.
        LaRecipe::component('Byline')
            ->description('The site author\'s byline in the footer, next to the LaRecipe credit.')
            ->fallback(fn (array $props, string $children, RenderedComponent $component) => view('docs.byline', [
                'locale' => $this->pageLocale($component),
            ]))
            ->markdown(fn (array $props, string $children, RenderedComponent $component) => __('byline.by', [], $this->pageLocale($component)).' [@saleem-hadad](https://www.linkedin.com/in/saleemhadad)');
    }

    /** The page's language for this site's own strings: Arabic or English. */
    private function pageLocale(RenderedComponent $component): string
    {
        return $component->locale === 'ar' ? 'ar' : 'en';
    }

    /**
     * The feature grid in the page's language, each with its page's URL in
     * the same version and language: domain-relative for the cached HTML, as
     * LaRecipe resolves page links, or the absolute .md twin for agents.
     *
     * @return list<array{icon: string, title: string, line: string, url: string}>
     */
    private function features(RenderedComponent $component, bool $markdown): array
    {
        $locale = $this->pageLocale($component);
        $set = LaRecipe::set($component->set);
        $urls = app(DocsUrls::class);

        return array_map(function (string $key, array $feature) use ($component, $locale, $set, $urls, $markdown) {
            $page = $set->ref($feature['page'], $component->version, $component->locale);

            return [
                'icon' => $feature['icon'],
                'title' => __("features.{$key}.title", [], $locale),
                'line' => __("features.{$key}.line", [], $locale),
                'url' => $markdown ? $urls->page($set, $page, markdown: true) : $urls->pagePath($set, $page),
            ];
        }, array_keys(self::FEATURES), self::FEATURES);
    }

    /** Escape a line of copy and set its `backtick` spans as code. */
    private function inlineCode(string $text): string
    {
        return (string) preg_replace('/`([^`]+)`/', '<code>$1</code>', e($text));
    }
}
