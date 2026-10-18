{{--
    The FeaturedOn strip on the Introduction: LaRecipe's downloads and the
    sites that featured it, as one row of logos, in the page's language
    ($locale: en or ar). Server only, no JavaScript. Each logo is a one-color
    mask filled with the text color (resources/larecipe/featured-on.css), so
    it follows light and dark. Its name is in the element for screen readers,
    and shows as plain text when the stylesheet is missing.
--}}
@php
    $count = '<strong class="lr-featured-count">'.e(__('featured-on.count', [], $locale)).'</strong>';
@endphp
<div class="lr-featured">
    <p class="lr-featured-head">
        <span class="lr-featured-label">{{ __('featured-on.label', [], $locale) }}</span>
        <span class="lr-featured-downloads">{!! \LaRecipe\Domains\Markdown\Support\ContentIcons::svg('download', 13, 'lr-featured-icon') !!}<span>{!! str_replace(':count', $count, e(__('featured-on.downloads', [], $locale))) !!}</span></span>
    </p>
    <ul class="lr-featured-logos" role="list">
        @foreach ($outlets as $outlet)
            <li>
                @if ($outlet['href'] !== null)
                    <a class="lr-featured-logo" data-logo="{{ $outlet['logo'] }}" href="{{ $outlet['href'] }}" style="--lr-featured-src: url('/images/featured/{{ $outlet['logo'] }}.png')"><span class="lr-sr">{{ $outlet['name'] }}</span></a>
                @else
                    <span class="lr-featured-logo" data-logo="{{ $outlet['logo'] }}" style="--lr-featured-src: url('/images/featured/{{ $outlet['logo'] }}.png')"><span class="lr-sr">{{ $outlet['name'] }}</span></span>
                @endif
            </li>
        @endforeach
    </ul>
</div>
