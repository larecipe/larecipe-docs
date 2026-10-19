{{--
    The DocsFlow diagram without JavaScript: the LaRecipe loop at rest, in the
    page's language ($locale: en or ar). Its markup matches
    resources/larecipe/DocsFlow.tsx, so React mounts without a shift and only
    adds the motion. The wire paths follow the geometry in
    resources/larecipe/docs-flow.css. At rest the pull request is merged with
    both its code and its docs.
--}}
@php
    $t = fn (string $key) => __('docs-flow.'.$key, [], $locale);
    $port = fn (float $x, float $y, string $port, int $i = 0, float $r = 3.25) => sprintf(
        '<circle class="lr-flow-ring" data-port="%1$s" cx="%2$s" cy="%3$s" r="%4$s" style="--i:%5$d"></circle><circle class="lr-flow-port" data-port="%1$s" cx="%2$s" cy="%3$s" r="%4$s" style="--i:%5$d"></circle>',
        $port, $x, $y, $r, $i,
    );
    $wire = fn (string $d, int $i) => sprintf(
        '<path class="lr-flow-track" d="%1$s"></path><path class="lr-flow-pulse" d="%1$s" pathLength="100" style="--i:%2$d"></path>',
        $d, $i,
    );
    $vertical = fn (string $from, string $to, int $i, bool $end = false) => '<svg class="lr-flow-wires lr-flow-wires-v'.($end ? ' is-end' : '').'" width="12" height="44" viewBox="0 0 12 44">'
        .$wire('M6 0V44', $i).$port(6, 0, $from, $i).$port(6, 44, $to, $i).'</svg>';
    $icon = '<svg class="lr-flow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">';
    $glyph = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">';
    $pullRequest = $icon.'<circle cx="18" cy="18" r="3"></circle><circle cx="6" cy="6" r="3"></circle><path d="M13 6h3a2 2 0 0 1 2 2v7"></path><path d="M6 9v12"></path></svg>';
    $check = '<svg class="lr-flow-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>';
@endphp
<figure class="lr-flow" data-lr-flow="html">
    <div class="lr-flow-stage" aria-hidden="true" data-markdown-ignore>
        <div class="lr-flow-pr"></div>

        <div class="lr-flow-label lr-flow-label-code">{{ $t('code') }}</div>
        <div class="lr-flow-sources">
            <div class="lr-flow-card lr-flow-source" style="--i:0">
                {!! $icon !!}<circle cx="12" cy="12" r="3"></circle><path d="M3 12h6"></path><path d="M15 12h6"></path></svg>
                <span class="lr-flow-source-id lr-flow-mono" dir="ltr">a1f3c9e</span>
                <span class="lr-flow-source-stat" dir="ltr"><span class="lr-flow-add">+42</span> <span class="lr-flow-remove">−7</span></span>
                <span class="lr-flow-source-title" dir="ltr">feat: refund invoices</span>
            </div>
            <div class="lr-flow-chain">
                <svg class="lr-flow-wires" width="12" height="22" viewBox="0 0 12 22">{!! $wire('M6 0V22', 0).$port(6, 0, 'src', 0).$port(6, 22, 'src', 4) !!}</svg>
            </div>
            <div class="lr-flow-card lr-flow-source lr-flow-stale" style="--i:4">
                {!! $icon !!}<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path><path d="M12 12v3"></path><path d="M12 18h.01"></path></svg>
                <span class="lr-flow-source-id lr-flow-mono" dir="ltr">refunds.md</span>
                <span class="lr-flow-source-stat lr-flow-word">{{ $t('stale') }}</span>
                <span class="lr-flow-source-title">{{ $t('stale_why') }}</span>
            </div>
        </div>

        <div class="lr-flow-link lr-flow-link-in">
            <svg class="lr-flow-wires lr-flow-wires-h" width="52" height="300" viewBox="0 0 52 300">{!!
                $wire('M0 189C26 189 26 150 52 150', 4).$port(0, 189, 'src', 4).$port(52, 150, 'hub-in', 0, 3.75)
            !!}</svg>
            {!! $vertical('src', 'hub-in', 4) !!}
        </div>

        <div class="lr-flow-label lr-flow-label-agent">{{ $t('agent') }}</div>
        <div class="lr-flow-card lr-flow-page">
            <div class="lr-flow-page-bar">
                <span class="lr-flow-path lr-flow-mono" dir="ltr">docs / billing / <b>refunds.md</b></span>
            </div>
            <div class="lr-flow-page-body">
                <div class="lr-flow-page-head">
                    <span class="lr-flow-page-title">{{ $t('title') }}</span>
                    <span class="lr-flow-pill" data-state="ago"><span class="lr-flow-dot"></span><span>{{ $t('updated_ago') }}</span></span>
                </div>
                <div class="lr-flow-tabs">
                    <span class="lr-flow-tab is-active">{{ $t('guide') }}</span>
                    <span class="lr-flow-tab">{{ $t('api') }}</span>
                </div>
                <div class="lr-flow-line lr-flow-text" data-line="text"><span>{{ $t('text') }}</span></div>
                <div class="lr-flow-code" dir="ltr"><span class="lr-flow-line" data-line="code"><span class="lr-flow-muted">php</span><span> artisan </span><span class="lr-flow-fn">invoices:refund</span><span> </span><span class="lr-flow-num">1042</span></span></div>
                <div class="lr-flow-bars">
                    <span class="lr-flow-bar"></span>
                    <span class="lr-flow-bar"></span>
                    <span class="lr-flow-bar"></span>
                    <span class="lr-flow-bar" data-line="line"></span>
                </div>
            </div>
            <div class="lr-flow-page-foot">
                {!! $pullRequest !!}
                <span>{{ $t('updated_by') }} · <span dir="ltr">PR #412</span></span>
            </div>
        </div>

        <div class="lr-flow-pr-bar">
            {!! $pullRequest !!}
            <span class="lr-flow-pr-id lr-flow-mono" dir="ltr">#412</span>
            <span class="lr-flow-pr-title" dir="ltr">Add invoice refunds</span>
            <span class="lr-flow-pr-files">
                <span class="lr-flow-pr-file lr-flow-word" data-state="on" style="--i:0">{!! $check !!}{{ $t('pr_code') }}</span>
                <span class="lr-flow-pr-plus">+</span>
                <span class="lr-flow-pr-file lr-flow-word" data-state="on" style="--i:1">{!! $check !!}{{ $t('pr_docs') }}</span>
            </span>
            <span class="lr-flow-pr-state lr-flow-word" data-state="merged"><span class="lr-flow-dot"></span>{{ $t('merged') }}</span>
        </div>

        <div class="lr-flow-link lr-flow-link-out">
            <svg class="lr-flow-wires lr-flow-wires-h" width="52" height="300" viewBox="0 0 52 300">{!!
                $wire('M0 150C26 150 26 59 52 59', 0).$wire('M0 150C26 150 26 241 52 241', 1)
                .$port(0, 150, 'hub-out', 0, 3.75).$port(52, 59, 'reader', 0).$port(52, 241, 'reader', 1)
            !!}</svg>
            {!! $vertical('hub-out', 'reader', 0) !!}
            {!! $vertical('hub-out', 'reader', 1, true) !!}
        </div>

        <div class="lr-flow-label lr-flow-label-agents">{{ $t('agents_read') }}</div>
        <div class="lr-flow-readers">
            <div class="lr-flow-card lr-flow-reader lr-flow-agents" style="--i:0">
                <div class="lr-flow-reader-head">
                    <span class="lr-flow-chips">
                        <span class="lr-flow-chip" title="Claude" style="--i:0">{!! $glyph !!}<path d="M12 4v16"></path><path d="M4 12h16"></path><path d="m6.3 6.3 11.4 11.4"></path><path d="m17.7 6.3-11.4 11.4"></path></svg></span>
                        <span class="lr-flow-chip" title="ChatGPT" style="--i:1">{!! $glyph !!}<path d="M12 3.5 19.4 7.75v8.5L12 20.5l-7.4-4.25v-8.5z"></path><circle cx="12" cy="12" r="2.5"></circle></svg></span>
                        <span class="lr-flow-chip" title="Cursor" style="--i:2">{!! $glyph !!}<path d="M5 4.5 19 11l-6.2 1.8L11 19z"></path></svg></span>
                    </span>
                    <span class="lr-flow-badge"><span class="lr-flow-dot"></span>MCP</span>
                </div>
                <div class="lr-flow-note">{{ $t('connected') }}</div>
                <div class="lr-flow-preview">
                    <span class="lr-flow-preview-head"><span class="lr-flow-word"># {{ $t('title') }}</span><span class="lr-flow-faint" dir="ltr">llms.txt</span></span>
                    <span class="lr-flow-bar"></span>
                </div>
            </div>
            <div class="lr-flow-label lr-flow-label-people">{{ $t('people_read') }}</div>
            <div class="lr-flow-card lr-flow-reader lr-flow-people" style="--i:1">
                <div class="lr-flow-people-head">
                    <span class="lr-flow-dot"></span>
                    <span class="lr-flow-people-title">{{ $t('title') }}</span>
                    <span class="lr-flow-time">{{ $t('ago') }}</span>
                </div>
                <div class="lr-flow-bars">
                    <span class="lr-flow-bar"></span>
                    <span class="lr-flow-bar"></span>
                </div>
                <div class="lr-flow-ask">
                    <span class="lr-flow-ask-text"><span class="lr-flow-ask-long">{{ $t('ask') }}</span><span class="lr-flow-ask-short">{{ $t('ask_short') }}</span></span>
                    <span class="lr-flow-send"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 7-7 7 7"></path><path d="M12 19V5"></path></svg></span>
                </div>
            </div>
        </div>
    </div>
    <div class="lr-sr">{{ $t('summary') }}</div>
    @if ($caption)
        <figcaption class="lr-flow-caption">{{ $caption }}</figcaption>
    @endif
</figure>
