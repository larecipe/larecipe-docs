{{--
    The Byline in the site footer, right after "Built with LaRecipe", in the
    page's language ($locale: en or ar). Server only, no JavaScript. The handle
    stays left to right inside Arabic text. resources/larecipe/byline.css
    moves the footer insert next to the credit.
--}}
<p class="lr-byline"><span class="lr-byline-dot" aria-hidden="true">·</span>{{ __('byline.by', [], $locale) }} <a class="lr-byline-link" href="https://www.linkedin.com/in/saleemhadad" rel="noopener" dir="ltr">@saleem-hadad</a></p>
