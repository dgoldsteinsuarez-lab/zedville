{{-- "What is Zedville?" - the explanation for schools at the bottom of the
     log-in page (Daniel, 2026-10-23): a financial literacy platform for
     schools, what students learn, what teachers and schools get. --}}
<style>
    .ab { --ab-bg: #F7F5F0; --ab-card: #fff; --ab-line: #E6E0D3; --ab-text: #0F2A22; --ab-muted: #5E7069; --ab-accent: #00805F; --ab-soft: #E2F6EF;
        position: relative; z-index: 3; background: var(--ab-bg); color: var(--ab-text); font-family: "Manrope", sans-serif; padding: 90px 16px 40px; }
    .ab-wrap { max-width: 1100px; margin: 0 auto; }
    .ab-kicker { font-size: 12px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; color: var(--ab-accent); }
    .ab h2 { font-size: clamp(28px, 4vw, 44px); line-height: 1.1; letter-spacing: -.03em; margin: 12px 0 16px; max-width: 760px; }
    .ab-lead { font-size: 18px; line-height: 1.7; color: var(--ab-muted); max-width: 780px; margin: 0; }
    .ab-lead b { color: var(--ab-text); }
    .ab-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin: 46px 0; }
    .ab-card { background: var(--ab-card); border: 1px solid var(--ab-line); border-radius: 22px; padding: 26px 24px; }
    .ab-icon { width: 48px; height: 48px; border-radius: 14px; display: grid; place-items: center; font-size: 24px; background: var(--ab-soft); }
    .ab-card h3 { margin: 16px 0 4px; font-size: 13px; letter-spacing: .14em; text-transform: uppercase; color: var(--ab-accent); }
    .ab-card h4 { margin: 0 0 12px; font-size: 21px; letter-spacing: -.01em; }
    .ab-card ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 9px; }
    .ab-card li { position: relative; padding-left: 24px; font-size: 15px; line-height: 1.5; color: var(--ab-muted); }
    .ab-card li::before { content: ''; position: absolute; left: 0; top: 6px; width: 12px; height: 12px; border-radius: 50%; background: var(--ab-soft); box-shadow: inset 0 0 0 3px var(--ab-accent); }
    .ab-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; border: 1px solid var(--ab-line); border-radius: 22px; overflow: hidden; background: var(--ab-card); }
    .ab-step { padding: 22px 22px 24px; border-right: 1px solid var(--ab-line); }
    .ab-step:last-child { border-right: 0; }
    .ab-step b { display: block; font-size: 30px; font-weight: 800; color: var(--ab-accent); letter-spacing: -.02em; }
    .ab-step span { display: block; font-weight: 800; margin: 6px 0 4px; }
    .ab-step p { margin: 0; font-size: 14px; line-height: 1.5; color: var(--ab-muted); }
    .ab-band { display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; margin-top: 46px; padding: 30px 32px; border-radius: 24px; background: linear-gradient(120deg, #0E5C46, #00A47D); color: #fff; }
    .ab-band p { margin: 0; font-size: 20px; font-weight: 700; line-height: 1.45; max-width: 640px; }
    .ab-band-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .ab-band a { text-decoration: none; font-weight: 800; font-size: 15px; padding: 13px 22px; border-radius: 999px; }
    .ab-band .ab-primary { background: #fff; color: #0E5C46; }
    .ab-band .ab-ghost { border: 1.5px solid rgba(255, 255, 255, .55); color: #fff; }
    .ab-foot { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-top: 50px; padding: 22px 0 60px; border-top: 1px solid var(--ab-line); font-size: 13px; color: var(--ab-muted); }
    .ab-foot a { color: var(--ab-muted); text-decoration: none; font-weight: 700; margin-left: 16px; }
    @media (max-width: 900px) {
        .ab-cards { grid-template-columns: 1fr; }
        .ab-steps { grid-template-columns: 1fr 1fr; }
        .ab-step:nth-child(2) { border-right: 0; }
        .ab-step:nth-child(-n+2) { border-bottom: 1px solid var(--ab-line); }
    }
    @media (max-width: 520px) {
        .ab-steps { grid-template-columns: 1fr; }
        .ab-step { border-right: 0; border-bottom: 1px solid var(--ab-line); }
    }
</style>

<section class="ab" id="about">
    <div class="ab-wrap">
        <div class="ab-kicker">{{ __('login.what_is') }}</div>
        <h2>{{ __('login.about.title') }}</h2>
        <p class="ab-lead">{!! __('login.about.lead') !!}</p>

        <div class="ab-cards">
            @foreach (['students' => '🎒', 'teachers' => '🍎', 'schools' => '🏫'] as $who => $icon)
                <div class="ab-card">
                    <div class="ab-icon">{{ $icon }}</div>
                    <h3>{{ __("login.about.$who.label") }}</h3>
                    <h4>{{ __("login.about.$who.title") }}</h4>
                    <ul>
                        @foreach (__("login.about.$who.items") as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="ab-steps">
            @foreach (__('login.about.steps') as $i => [$step, $text])
                <div class="ab-step"><b>{{ sprintf('%02d', $i + 1) }}</b><span>{{ $step }}</span><p>{{ $text }}</p></div>
            @endforeach
        </div>

        <div class="ab-band">
            <p>{{ __('login.about.band') }}</p>
            <div class="ab-band-actions">
                <a class="ab-primary" href="{{ route('contact') }}">{{ __('login.about.bring') }}</a>
                <a class="ab-ghost" href="#top">{{ __('login.about.login') }}</a>
            </div>
        </div>

        <div class="ab-foot">
            <span>© {{ now()->year }} Zedville · {{ __('login.about.footer') }}</span>
            <span><a href="{{ url('/') }}">{{ __('login.about.home') }}</a><a href="{{ route('register') }}">{{ __('login.about.register') }}</a><a href="{{ route('contact') }}">{{ __('login.about.contact') }}</a></span>
        </div>
    </div>
</section>
