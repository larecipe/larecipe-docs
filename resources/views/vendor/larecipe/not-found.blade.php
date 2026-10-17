{{--
    LaRecipe's own 404. Customer docs say "Page not found"; on LaRecipe's site
    the page is 86'd, kitchen talk for an item that is off the menu
    (ART_DIRECTION.md). Same structure and suggestions as the package view.
--}}
<!doctype html>
<html lang="{{ $set->defaultLocale()->code }}" dir="{{ $set->defaultLocale()->dir->value }}" data-theme-mode="{{ $set->manifest->theme->mode }}" class="lr-html">
<head>
    @include('larecipe::partials.head', ['title' => '86\'d · '.$set->manifest->name, 'description' => null, 'canonical' => null, 'markdown' => null, 'noindex' => true, 'themeCss' => app(\LaRecipe\Domains\Delivery\Support\ThemeCss::class)->render($set->manifest->theme)])
</head>
<body class="lr-body-root">
    <main class="lr-not-found" data-lr-not-found>
        <p class="lr-label">404</p>
        <h1 class="lr-title">86'd</h1>
        <p class="lr-lead">That page is off the menu. There is no page at <code>/{{ $docs->path }}</code>.</p>
        @if (count($suggestions) > 0)
            <p class="lr-label">Try one of these</p>
            <ul class="lr-suggestions">
                @foreach ($suggestions as $suggestion)
                    <li><a href="{{ $suggestion['url'] }}">{{ $suggestion['title'] }}</a>@if ($suggestion['description']) <span>{{ $suggestion['description'] }}</span>@endif</li>
                @endforeach
            </ul>
        @endif
        <p><a href="{{ $home }}">Back to the {{ $set->manifest->name }} docs</a></p>
    </main>
</body>
</html>
