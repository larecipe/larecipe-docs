{{--
    The InstallTabs component without JavaScript: both panels, each under its
    label, with the prompt and the commands ready to select. Its markup matches
    resources/larecipe/InstallTabs.tsx (the agent tab open), so React mounts
    without a shift, and it speaks the page's language ($locale: en or ar).
--}}
<div class="lr-install" data-lr-install="html">
    <div class="lr-install-tabs lr-js-only" role="tablist" aria-label="{{ __('install.label', [], $locale) }}">
        <button type="button" role="tab" id="lr-install-agent-tab" class="lr-install-tab" data-tab="agent" aria-selected="true" aria-controls="lr-install-agent" tabindex="0">
            <svg class="lr-install-mark" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path class="lr-install-mark-star" d="M10 3.5c.5 3.6 2.4 5.5 6 6-3.6.5-5.5 2.4-6 6-.5-3.6-2.4-5.5-6-6 3.6-.5 5.5-2.4 6-6Z"/><path class="lr-install-mark-spark" d="M18 14c.25 1.8 1.2 2.75 3 3-1.8.25-2.75 1.2-3 3-.25-1.8-1.2-2.75-3-3 1.8-.25 2.75-1.2 3-3Z"/><circle class="lr-install-mark-dot" cx="18.5" cy="4.5" r="1.25"/></svg>
            <span>{{ __('install.agent_tab', [], $locale) }}</span>
        </button>
        <button type="button" role="tab" id="lr-install-manual-tab" class="lr-install-tab" data-tab="manual" aria-selected="false" aria-controls="lr-install-manual" tabindex="-1">
            @include('larecipe::partials.icon', ['name' => 'Terminal', 'iconSize' => 15, 'iconClass' => ''])
            <span>{{ __('install.manual_tab', [], $locale) }}</span>
        </button>
    </div>
    <div class="lr-install-panels">
        <div role="tabpanel" id="lr-install-agent" class="lr-install-panel" data-panel="agent" data-active="true" aria-labelledby="lr-install-agent-tab">
            <p class="lr-install-panel-label">{{ __('install.agent_tab', [], $locale) }}</p>
            <p class="lr-install-lead">{{ __('install.agent_lead', [], $locale) }}</p>
            <div class="lr-install-prompt">
                <div class="lr-install-prompt-head">
                    <span class="lr-install-prompt-label">{{ __('install.prompt_label', [], $locale) }}</span>
                    <button type="button" class="lr-button lr-button-primary lr-install-copy lr-js-only">@include('larecipe::partials.icon', ['name' => 'Copy', 'iconSize' => 14, 'iconClass' => ''])<span>{{ __('install.copy_prompt', [], $locale) }}</span></button>
                </div>
                <div class="lr-install-prompt-text">@foreach ($paragraphs as $paragraph)<div class="lr-install-prompt-para">{!! $paragraph !!}</div>@endforeach</div>
            </div>
        </div>
        <div role="tabpanel" id="lr-install-manual" class="lr-install-panel" data-panel="manual" aria-labelledby="lr-install-manual-tab">
            <p class="lr-install-panel-label">{{ __('install.manual_tab', [], $locale) }}</p>
            <p class="lr-install-lead">{{ __('install.manual_lead', [], $locale) }}</p>
            <figure class="lr-code" data-language="bash">
                <figcaption class="lr-code-header"><span class="lr-code-title">bash</span></figcaption>
                <pre class="lr-pre" tabindex="0"><code><span class="lr-line" data-line="1"><span class="tok-function">composer</span> <span class="tok-string">require</span> <span class="tok-string">larecipe/larecipe</span></span>
<span class="lr-line" data-line="2"><span class="tok-function">php</span> <span class="tok-string">artisan</span> <span class="tok-string">larecipe:install</span></span></code></pre>
            </figure>
            <ol class="lr-install-steps">
                @foreach ($steps as $step)
                    <li><span>{!! $step !!}</span></li>
                @endforeach
            </ol>
        </div>
    </div>
</div>
