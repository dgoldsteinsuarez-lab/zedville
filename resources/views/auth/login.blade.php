<!DOCTYPE html>
{{-- The log-in page - design 3c, chosen by Daniel on 2026-10-25: the city's
     skyline (Courthouse, City Hall, the Library) under a clear blue sky, the
     golden Zed coin as the sun, "Hello Citizen, Welcome back to Zedville" in
     a log-in box on the right above the rooftops, and "What is Zedville?"
     for schools below. In the visitor's language (SetGuestLocale). --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" translate="no">
<head>
    <meta charset="utf-8">
    {{-- Zedville shows the class's language on purpose (a German class reads German):
         the browser must not machine-translate it (Daniel, 2026-10-25). --}}
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('login.title') }}</title>
    <link rel="icon" href="{{ asset('asset/front/images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        html, body { margin: 0; padding: 0; }
        body { font-family: "Manrope", sans-serif; -webkit-font-smoothing: antialiased; min-height: 100vh; background: #FFF6EA; color: #0F2A22; }
        a { color: inherit; }
        button { font: inherit; cursor: pointer; }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation: none !important; transition: none !important; } }

        /* The log-in form. */
        :root { --lf-text: #0F2A22; --lf-muted: #5E7069; --lf-line: #DDE5E1; --lf-input: #fff; --lf-accent: #00A47D; --lf-accent-text: #fff; --lf-radius: 14px; --lf-focus: rgba(0, 164, 125, .18); }
        .lf { display: grid; gap: 16px; color: var(--lf-text); }
        .lf-label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 7px; letter-spacing: .01em; }
        .lf-input { width: 100%; height: 52px; padding: 0 16px; font: 500 16px "Manrope", sans-serif; color: var(--lf-text); background: var(--lf-input); border: 1.5px solid var(--lf-line); border-radius: var(--lf-radius); outline: none; transition: border-color .2s, box-shadow .2s; }
        .lf-input::placeholder { color: var(--lf-muted); opacity: .7; }
        .lf-input:focus { border-color: var(--lf-accent); box-shadow: 0 0 0 4px var(--lf-focus); }
        .lf-pw { position: relative; display: block; }
        .lf-pw .lf-input { padding-right: 52px; }
        .lf-eye { position: absolute; right: 6px; top: 6px; width: 40px; height: 40px; border: 0; background: none; color: var(--lf-muted); border-radius: 10px; display: grid; place-items: center; }
        .lf-eye:hover { color: var(--lf-text); }
        .lf-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; font-size: 14px; color: var(--lf-muted); }
        .lf-remember { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }
        .lf-remember input { width: 17px; height: 17px; accent-color: var(--lf-accent); }
        .lf-row a { font-weight: 700; color: var(--lf-text); text-decoration: none; text-align: right; }
        .lf-row a:hover { text-decoration: underline; }
        .lf-submit { height: 54px; border: 0; border-radius: var(--lf-radius); background: var(--lf-accent); color: var(--lf-accent-text); font: 800 16px "Manrope", sans-serif; display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 4px; transition: transform .15s, filter .2s; }
        .lf-submit:hover { filter: brightness(1.07); transform: translateY(-1px); }
        .lf-submit svg { transition: transform .2s; }
        .lf-submit:hover svg { transform: translateX(3px); }
        .lf-alt { text-align: center; font-size: 14px; color: var(--lf-muted); margin: 4px 0 0; }
        .lf-alt a { font-weight: 800; color: var(--lf-accent); text-decoration: none; }
        .lf-error { padding: 12px 14px; border-radius: 12px; background: #FDECEC; color: #9B1C1C; font-size: 14px; font-weight: 600; border: 1px solid #F5C2C2; }

        .o3 { --city-h: max(150px, 21.28vw); --ground-h: 26px; --sun-size: 120px; --sun-x: 28%;
            position: relative; min-height: 100vh; overflow: hidden; display: flex; flex-direction: column; padding: 24px clamp(16px, 4vw, 40px) 0;
            background: linear-gradient(180deg, #3FA9F0 0%, #6DC0F5 24%, #A7DBF9 48%, #DCF1FB 70%, #FFF3DA 100%); }
        .o3-top { position: relative; z-index: 6; display: flex; flex-wrap: wrap; justify-content: flex-start; align-items: center; gap: 12px 22px; }
        .o3-logo { height: 24px; display: block; }
        .o3-links { display: flex; gap: 8px; }
        .o3-links a, .o3-langs a { color: #3D4F7A; background: rgba(255, 255, 255, .6); font-size: 14px; font-weight: 800; text-decoration: none; padding: 8px 14px; border-radius: 999px; backdrop-filter: blur(6px); }
        .o3-langs { display: flex; gap: 4px; margin-left: auto; }
        .o3-langs a { padding: 8px 10px; font-size: 13px; letter-spacing: .04em; }
        .o3-langs a[aria-current] { background: #0E7FC1; color: #fff; }

        /* The sun: the golden Zed coin with a soft glow, high in the sky. */
        .o3-sunwrap { position: absolute; z-index: 1; left: calc(var(--sun-x) - var(--sun-size) / 2); bottom: calc(var(--city-h) * 1.25 + var(--ground-h)); width: var(--sun-size); height: var(--sun-size); animation: o3rise 3.2s cubic-bezier(.2, .8, .2, 1) both; }
        @keyframes o3rise { from { transform: translateY(150px); } }
        .o3-glow { position: absolute; inset: -40%; border-radius: 50%; background: radial-gradient(circle, rgba(255, 240, 180, .9) 0 34%, rgba(255, 218, 130, .35) 48%, transparent 68%); animation: o3pulse 5s ease-in-out infinite; }
        @keyframes o3pulse { 50% { transform: scale(1.06); opacity: .85; } }
        .o3-sun { position: absolute; inset: 0; border-radius: 50%; background: repeating-conic-gradient(#E3A11F 0deg 3deg, #FFD45E 3deg 6deg); box-shadow: 0 0 0 3px rgba(255, 255, 255, .45), 0 10px 26px rgba(214, 135, 20, .3), 0 0 50px 14px rgba(255, 205, 100, .45); }
        .o3-face { position: absolute; inset: 7%; border-radius: 50%; overflow: hidden; display: grid; place-items: center;
            background: radial-gradient(circle at 34% 28%, #FFF8D6 0, #FFE27F 28%, #F7C548 58%, #E2A020 100%);
            box-shadow: inset 0 0 0 3px rgba(255, 246, 205, .65), inset 0 -10px 18px rgba(170, 100, 0, .28), inset 0 8px 14px rgba(255, 255, 255, .5); }
        .o3-face::before { content: ''; position: absolute; inset: 11%; border-radius: 50%; border: 2px solid rgba(176, 108, 8, .35); }
        .o3-face span { position: relative; font: 800 calc(var(--sun-size) * .44) / 1 "Manrope", sans-serif; color: #C98A1A; text-shadow: 0 2px 0 #FFF2BE, 0 -1px 0 rgba(130, 75, 0, .35); }
        .o3-face::after { content: ''; position: absolute; top: -50%; left: -70%; width: 38%; height: 200%; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .6), transparent); transform: rotate(25deg); animation: o3shine 7s ease-in-out infinite 2s; }
        @keyframes o3shine { 0%, 70% { left: -70%; } 100% { left: 140%; } }

        /* Clouds, birds, haze, the city and the street. */
        .o3-cloud { position: absolute; z-index: 2; height: 38px; border-radius: 999px; background: #fff; animation: o3drift linear infinite; filter: drop-shadow(0 6px 10px rgba(120, 170, 220, .25)); }
        .o3-cloud::before, .o3-cloud::after { content: ''; position: absolute; bottom: 8px; border-radius: 50%; background: inherit; }
        .o3-cloud::before { left: 18%; width: 68px; height: 68px; }
        .o3-cloud::after { left: 48%; width: 48px; height: 48px; }
        @keyframes o3drift { from { transform: translateX(-30vw); } to { transform: translateX(130vw); } }
        .o3-bird { position: absolute; z-index: 2; width: 22px; animation: o3fly linear infinite; }
        .o3-bird svg { width: 100%; display: block; animation: o3flap .5s ease-in-out infinite alternate; }
        @keyframes o3fly { from { transform: translate(-10vw, 0); } to { transform: translate(110vw, -60px); } }
        @keyframes o3flap { to { transform: scaleY(.45); } }
        .o3-haze { position: absolute; z-index: 0; left: 0; right: 0; bottom: var(--ground-h); height: calc(var(--city-h) + 140px); background: linear-gradient(0deg, rgba(255, 245, 235, .6) 40%, transparent); pointer-events: none; }
        .o3-city { position: absolute; z-index: 3; left: 0; right: 0; bottom: var(--ground-h); height: var(--city-h); width: 100%; object-fit: cover; object-position: 50% 100%; pointer-events: none; user-select: none; }
        .o3-ground { position: absolute; z-index: 3; left: 0; right: 0; bottom: 0; height: var(--ground-h); background: linear-gradient(#D8CFBF 0 7px, #4A5058 7px); }
        .o3-ground::after { content: ''; position: absolute; left: 0; right: 0; top: 16px; height: 2px; background: repeating-linear-gradient(90deg, rgba(255, 255, 255, .7) 0 26px, transparent 26px 52px); }

        /* The log-in on the right side, below the top bar and above the rooftops. */
        .o3-card { position: absolute; z-index: 7; top: 110px; right: 7vw; width: min(410px, calc(100% - 32px)); background: rgba(255, 255, 255, .8); backdrop-filter: blur(16px) saturate(1.2); -webkit-backdrop-filter: blur(16px) saturate(1.2); border: 1px solid rgba(255, 255, 255, .9); border-radius: 26px; padding: 30px 30px 24px; box-shadow: 0 30px 60px rgba(30, 80, 130, .18); animation: o3card .8s .2s both cubic-bezier(.2, .8, .2, 1); }
        @keyframes o3card { from { opacity: 0; transform: translateY(16px); } }
        .o3-kicker { font-size: 13px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #0E7FC1; }
        .o3-card h1 { font-size: 30px; letter-spacing: -.02em; line-height: 1.1; margin: 8px 0 8px; }
        .o3-card > p { margin: 0 0 20px; color: #5E7069; line-height: 1.55; }
        .o3-spacer { flex: 1; min-height: calc(var(--city-h) + var(--ground-h) + 40px); }

        /* Short screens (laptops): a more compact log-in, so the city fits under it. */
        @media (min-width: 701px) and (max-height: 860px) {
            .o3-card { top: 90px; padding: 22px 26px 18px; }
            .o3-card h1 { font-size: 24px; margin: 4px 0 12px; }
            .o3-card > p { display: none; }
            .o3 .lf { gap: 10px; }
            .o3 .lf-label { margin-bottom: 5px; }
            .o3 .lf-input { height: 44px; }
            .o3 .lf-eye { top: 2px; }
            .o3 .lf-submit { height: 46px; }
        }
        @media (max-width: 480px) {
            .o3-logo { height: 18px; }
            .o3-links a, .o3-langs a { font-size: 12px; padding: 6px 8px; }
        }
        @media (max-width: 700px) {
            .o3 { --sun-size: 96px; --sun-x: 22%; }
            .o3-top { justify-content: space-between; }
            .o3-langs { margin-left: 0; }
            .o3-card { position: relative; top: auto; right: auto; width: 100%; margin-top: 18px; padding: 26px 20px 22px; }
        }
    </style>
</head>
<body>
<div class="o3" id="top">
    <div class="o3-haze" aria-hidden="true"></div>
    <div class="o3-sunwrap" aria-hidden="true"><div class="o3-glow"></div><div class="o3-sun"><div class="o3-face"><span>Ƶ</span></div></div></div>

    <div class="o3-cloud" style="top:16%;width:130px;animation-duration:80s;animation-delay:-20s" aria-hidden="true"></div>
    <div class="o3-cloud" style="top:30%;width:96px;animation-duration:60s;animation-delay:-48s;opacity:.8" aria-hidden="true"></div>
    <div class="o3-cloud" style="top:9%;width:170px;animation-duration:100s;animation-delay:-70s" aria-hidden="true"></div>
    <div class="o3-cloud" style="top:42%;width:110px;animation-duration:70s;animation-delay:-5s;opacity:.7" aria-hidden="true"></div>
    @foreach ([[22, 26, 0], [26, 30, -1.2], [30, 24, -2.1], [55, 34, -9], [58, 38, -10.4]] as [$top, $dur, $delay])
        <div class="o3-bird" style="top:{{ $top }}%;animation-duration:{{ $dur }}s;animation-delay:{{ $delay }}s" aria-hidden="true"><svg viewBox="0 0 24 10"><path d="M1 8 Q6 1 12 7 Q18 1 23 8" fill="none" stroke="#5A4A6E" stroke-width="1.8" stroke-linecap="round"/></svg></div>
    @endforeach

    <header class="o3-top">
        <a href="{{ url('/') }}"><img class="o3-logo" src="{{ asset('asset/front/images/logo.svg') }}" alt="Zedville"></a>
        <div class="o3-links"><a href="#about">{{ __('login.what_is') }}</a></div>
        <nav class="o3-langs" aria-label="{{ __('login.language') }}">
            @foreach (config('zedville.languages') as $code => $language)
                <a href="{{ url()->current() }}?lang={{ $code }}" lang="{{ $code }}" title="{{ $language }}" @if (app()->getLocale() === $code) aria-current="true" @endif>{{ strtoupper($code) }}</a>
            @endforeach
        </nav>
    </header>

    <main class="o3-card">
        <div class="o3-kicker">{{ __('login.hello') }}</div>
        <h1>{{ __('login.welcome') }}</h1>
        <p>{{ __('login.intro') }}</p>

        <form method="POST" action="{{ route('login') }}" class="lf">
            @csrf
            @if ($errors->any())
                <div class="lf-error" role="alert">{{ $errors->first('email') ?: $errors->first() }}</div>
            @endif
            <label>
                <span class="lf-label">{{ __('login.email') }}</span>
                <input class="lf-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="{{ __('login.email_placeholder') }}">
            </label>
            <label>
                <span class="lf-label">{{ __('login.password') }}</span>
                <span class="lf-pw">
                    <input class="lf-input" type="password" name="password" required autocomplete="current-password" placeholder="{{ __('login.password_placeholder') }}">
                    <button type="button" class="lf-eye" data-toggle-pw data-show="{{ __('login.show_password') }}" data-hide="{{ __('login.hide_password') }}" aria-label="{{ __('login.show_password') }}">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </span>
            </label>
            <div class="lf-row">
                <label class="lf-remember"><input type="checkbox" name="remember"> {{ __('login.remember') }}</label>
                <a href="{{ route('password.request') }}">{{ __('login.forgot') }}</a>
            </div>
            <button type="submit" class="lf-submit">
                {{ __('login.submit') }}
                <svg width="18" height="18" viewBox="0 0 19 19" fill="none"><path d="M4 10h11m0 0-5.3-4.5M15 10l-5.3 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <p class="lf-alt">{{ __('login.new') }} <a href="{{ route('register') }}">{{ __('login.create_account') }}</a></p>
        </form>
    </main>
    <div class="o3-spacer"></div>

    <img class="o3-city" src="{{ asset('asset/front/images/city/skyline.webp') }}"
         srcset="{{ asset('asset/front/images/city/skyline-1800.webp') }} 1800w, {{ asset('asset/front/images/city/skyline.webp') }} 3600w" sizes="100vw"
         alt="{{ __('login.skyline') }}">
    <div class="o3-ground" aria-hidden="true"></div>
</div>

@include('auth.partials.login-about')

<script>
    document.querySelectorAll('[data-toggle-pw]').forEach(btn => btn.addEventListener('click', () => {
        const input = btn.parentElement.querySelector('input');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.setAttribute('aria-label', show ? btn.dataset.hide : btn.dataset.show);
    }));
</script>
</body>
</html>
