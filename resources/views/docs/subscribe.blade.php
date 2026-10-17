{{--
    The Subscribe component without JavaScript: a plain form that posts to
    /newsletter/subscribe and lands on /newsletter with the outcome. Its markup
    matches resources/larecipe/Subscribe.tsx, so React mounts without a shift,
    and it speaks the page's language ($locale: en or ar).
    The page is cached and shared, so the CSRF token comes from csrfField(),
    which LaRecipe fills in on every response.
--}}
<form class="lr-subscribe" method="post" action="/newsletter/subscribe" data-lr-subscribe="html">
    {!! \LaRecipe\Facades\LaRecipe::csrfField() !!}
    <input type="hidden" name="list" value="{{ $list }}">
    <input type="hidden" name="locale" value="{{ $locale }}">
    <div class="lr-subscribe-copy">
        <p class="lr-subscribe-label">{{ __('newsletter.label', [], $locale) }}</p>
        <p class="lr-subscribe-title">{{ __('newsletter.title', [], $locale) }}</p>
        <p class="lr-subscribe-body">{{ __('newsletter.body', [], $locale) }}</p>
    </div>
    <div class="lr-subscribe-row">
        <label class="lr-subscribe-field">
            <span class="lr-sr">{{ __('newsletter.email', [], $locale) }}</span>
            <input class="lr-input" type="email" name="email" required autocomplete="email" placeholder="you@example.com" dir="ltr">
        </label>
        <button class="lr-button lr-button-ink" type="submit">{{ __('newsletter.submit', [], $locale) }}</button>
    </div>
</form>
