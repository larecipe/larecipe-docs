{{--
    The Features grid on the Introduction: LaRecipe's main features, each an
    icon, a title and one line that links to the page explaining it. The words
    and URLs arrive in the page's language. Server only, no JavaScript. The icons
    come from LaRecipe's content set, the same ones <Card icon="..."> draws.
    Styled by resources/larecipe/features.css.
--}}
<div class="lr-features">
    <ul class="lr-features-grid" role="list">
        @foreach ($features as $feature)
            <li>
                <a class="lr-features-item" href="{{ $feature['url'] }}">
                    <span class="lr-features-icon">{!! \LaRecipe\Domains\Markdown\Support\ContentIcons::svg($feature['icon'], 18) !!}</span>
                    <span class="lr-features-title">{{ $feature['title'] }}</span>
                    <span class="lr-features-line">{{ $feature['line'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</div>
