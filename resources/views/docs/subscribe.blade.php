{{--
    The Subscribe component without JavaScript: a plain form that posts to
    /newsletter/subscribe and lands on /newsletter with the outcome. Its markup
    matches resources/larecipe/Subscribe.tsx, so React mounts without a shift.
    The page is cached and shared, so the CSRF token comes from csrfField(),
    which LaRecipe fills in on every response.
--}}
<form class="lr-subscribe" method="post" action="/newsletter/subscribe" data-lr-subscribe="html">
    {!! \LaRecipe\Facades\LaRecipe::csrfField() !!}
    <input type="hidden" name="list" value="{{ $list }}">
    <div class="lr-subscribe-copy">
        <p class="lr-subscribe-label">Newsletter</p>
        <p class="lr-subscribe-title">Stay in the loop</p>
        <p class="lr-subscribe-body">LaRecipe release notes by email. Unsubscribe any time.</p>
    </div>
    <div class="lr-subscribe-row">
        <label class="lr-subscribe-field">
            <span class="lr-subscribe-sr">Email address</span>
            <input class="lr-input" type="email" name="email" required autocomplete="email" placeholder="you@example.com">
        </label>
        <button class="lr-button lr-button-secondary lr-subscribe-button" type="submit">Subscribe</button>
    </div>
</form>
