<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Newsletter · LaRecipe</title>
    <meta name="description" content="Get LaRecipe release notes by email.">
    <link rel="icon" href="/favicon.svg">
    <meta name="color-scheme" content="light dark">
    <style>
        :root { --paper: #fafaf8; --ink: rgb(28 27 34 / 0.92); --muted: rgb(28 27 34 / 0.66); --surface: #f2f1f4; --hairline: #e7e6ea; --strong: #d6d5db; --accent: oklch(0.55 0.2 279); --success: oklch(0.56 0.13 155); --danger: oklch(0.56 0.2 25); }
        @media (prefers-color-scheme: dark) { :root { --paper: #111016; --ink: rgb(244 243 248 / 0.92); --muted: rgb(244 243 248 / 0.64); --surface: #1b1a21; --hairline: #26252d; --strong: #34333c; --accent: oklch(0.74 0.14 279); --success: oklch(0.8 0.14 155); --danger: oklch(0.78 0.17 25); } }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--paper); color: var(--ink); font: 16px/1.75 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        main { max-width: 560px; margin: 0 auto; padding: 72px 16px; }
        .mark { display: inline-flex; align-items: center; gap: 10px; color: var(--ink); text-decoration: none; font-weight: 600; font-size: 15px; }
        .mark img { width: 20px; height: 24px; }
        .mark .dark { display: none; }
        @media (prefers-color-scheme: dark) { .mark .light { display: none; } .mark .dark { display: inline; } }
        .label { margin: 48px 0 8px; font: 500 11px/1.2 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); }
        h1 { margin: 0; font-size: 40px; line-height: 1.1; letter-spacing: -0.035em; font-weight: 600; }
        .lead { margin: 12px 0 32px; font-size: 18px; line-height: 1.6; color: var(--muted); }
        form { display: flex; gap: 8px; flex-wrap: wrap; }
        input[type=email] { flex: 1 1 240px; height: 40px; padding: 0 12px; border: 1px solid var(--hairline); border-radius: 6px; background: var(--surface); color: var(--ink); font: inherit; font-size: 15px; }
        input[type=email]:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 2px var(--paper), 0 0 0 4px color-mix(in oklch, var(--accent) 45%, transparent); }
        button { height: 40px; padding: 0 16px; border: 0; border-radius: 6px; background: var(--ink); color: var(--paper); font: inherit; font-size: 14px; font-weight: 500; cursor: pointer; }
        button:focus-visible { outline: none; box-shadow: 0 0 0 2px var(--paper), 0 0 0 4px color-mix(in oklch, var(--accent) 45%, transparent); }
        .status { margin: 0 0 24px; padding: 14px 16px; border: 1px solid var(--hairline); border-radius: 6px; background: var(--surface); }
        .status strong { display: block; font: 500 11px/1.2 'JetBrains Mono', ui-monospace, monospace; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 6px; }
        .status.ok strong { color: var(--success); }
        .status.error strong { color: var(--danger); }
        .back { margin-top: 40px; font-size: 14px; }
        a { color: var(--accent); }
    </style>
</head>
<body>
<main>
    <a class="mark" href="{{ url('/') }}">
        <img class="light" src="/brand/mark.svg" alt="">
        <img class="dark" src="/brand/mark-dark.svg" alt="">
        LaRecipe
    </a>

    <p class="label">Newsletter</p>
    <h1>Stay in the loop</h1>
    <p class="lead">LaRecipe release notes by email, when a release ships. Unsubscribe any time.</p>

    @if (session('subscribed'))
        <div class="status ok" role="status"><strong>Subscribed</strong>Thanks. {{ session('subscribed') }} is on the list.</div>
    @elseif ($errors->has('email'))
        <div class="status error" role="alert"><strong>Not subscribed</strong>{{ $errors->first('email') }}</div>
    @endif

    <form method="post" action="{{ route('newsletter.subscribe') }}">
        @csrf
        <input type="hidden" name="list" value="larecipe">
        <label for="email" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)">Email address</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com">
        <button type="submit">Subscribe</button>
    </form>

    <p class="back"><a href="{{ url('/') }}">Back to the docs</a></p>
</main>
</body>
</html>
