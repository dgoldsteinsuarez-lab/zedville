{{-- Citizen Activation, step 1: the welcome announcement (Daniel,
     2026-10-23). Five scenes: a live Zedville TV broadcast, the Mayor at
     City Hall explaining the salary and the bills it must cover, a first
     needs-vs-wants decision, the citizenship letter the student signs,
     and the mission briefing (the activation steps and the real-life
     skills they train). Full-screen, outside the profile layout. --}}
@php
    $z = fn ($v, $d = 2) => 'Ƶ' . number_format($v, $d);
    $cheap = 60;
    $pricey = 180;
    $mayorLines = [
        $address
            ? __('welcome.mayor.line1', ['name' => e($firstName), 'id' => e($citizenId), 'address' => e($address)])
            : __('welcome.mayor.line1_short', ['name' => e($firstName), 'id' => e($citizenId)]),
        __('welcome.mayor.line2', ['salary' => $z($salary)]),
        __('welcome.mayor.line3'),
        __('welcome.mayor.line4'),
        __('welcome.mayor.line5'),
    ];
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" translate="no">
<head>
    <meta charset="utf-8">
    {{-- Zedville shows the class's language on purpose (a German class reads German):
         the browser must not machine-translate it (Daniel, 2026-10-25). --}}
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('welcome.page_title') }} · Zedville</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=Open+Sans:wght@400;600;700&family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Great+Vibes&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body { font-family: "Open Sans", sans-serif; color: #0F2A22; background: #0B1D17; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
        h1, h2, h3 { font-family: "Manrope", sans-serif; }
        button { font: inherit; cursor: pointer; }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation: none !important; transition: none !important; } }

        .zw-scene { min-height: 100vh; position: relative; display: none; }
        .zw-scene.is-on { display: block; animation: zwfade .7s ease; }
        @keyframes zwfade { from { opacity: 0; } to { opacity: 1; } }
        .zw-btn { border: 0; border-radius: 999px; padding: 16px 30px; font: 800 17px "Manrope"; background: #00A47D; color: #fff; text-decoration: none; display: inline-block; }
        .zw-btn:hover { background: #00B88C; }
        .zw-pulse { animation: zwpulse 2s infinite; }
        @keyframes zwpulse { 0% { box-shadow: 0 0 0 0 rgba(0, 164, 125, .6); } 70% { box-shadow: 0 0 0 18px rgba(0, 164, 125, 0); } 100% { box-shadow: 0 0 0 0 rgba(0, 164, 125, 0); } }
        @keyframes zwblink { 50% { opacity: .2; } }
        @keyframes zwrise { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: none; } }

        /* Top bar: back + progress (every page has a Back button) */
        .zw-top { position: fixed; top: 0; left: 0; right: 0; z-index: 50; display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; pointer-events: none; }
        .zw-top > * { pointer-events: auto; }
        .zw-back { border: 0; border-radius: 999px; padding: 9px 16px; font: 700 14px "Manrope"; background: rgba(15, 42, 34, .8); color: #fff; backdrop-filter: blur(6px); visibility: hidden; }
        .zw-back.is-on { visibility: visible; }
        .zw-steps { display: flex; gap: 6px; padding: 8px 12px; border-radius: 999px; background: rgba(15, 42, 34, .8); backdrop-filter: blur(6px); }
        .zw-steps i { width: 22px; height: 6px; border-radius: 999px; background: rgba(255, 255, 255, .25); transition: background .3s; }
        .zw-steps i.is-on { background: #2BC79F; }
        .zw-spacer { width: 90px; }

        /* 1. Live broadcast */
        .zw-city { position: absolute; inset: 0; background: url('{{ asset('asset/front/images/city/overview.jpg') }}') center / cover; animation: zwzoom 26s ease-in-out infinite alternate; }
        @keyframes zwzoom { from { transform: scale(1.05); } to { transform: scale(1.18) translate(-2%, -1%); } }
        .zw-shade { position: absolute; inset: 0; background: radial-gradient(ellipse at center, rgba(11, 29, 23, .35), rgba(11, 29, 23, .92) 75%); }
        #zws1 { overflow: hidden; }
        .zw-hero { position: relative; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding: 24px 16px; color: #fff; }
        .zw-live { display: inline-flex; align-items: center; gap: 8px; font: 700 12px "Manrope"; letter-spacing: .18em; text-transform: uppercase; padding: 6px 14px; border-radius: 999px; background: rgba(0, 0, 0, .45); border: 1px solid rgba(255, 255, 255, .2); }
        .zw-live i { width: 9px; height: 9px; border-radius: 50%; background: #FF4D4D; animation: zwblink 1.2s infinite; }
        .zw-title { font-size: clamp(34px, 6vw, 72px); font-weight: 800; line-height: 1.05; margin: 22px 0 12px; max-width: 900px; min-height: 2.2em; text-shadow: 0 4px 30px rgba(0, 0, 0, .5); }
        .zw-cursor { display: inline-block; width: .08em; background: #2BC79F; animation: zwblink .8s infinite; margin-left: 4px; }
        .zw-sub { font-size: 18px; color: #CFE9DF; max-width: 560px; margin: 0 0 30px; opacity: 0; transition: opacity 1s; }
        .zw-sub.is-on { opacity: 1; }

        /* 2. The Mayor at City Hall */
        .zw-hall { position: absolute; inset: 0; background: url('{{ asset('asset/front/images/city-hall.png') }}') center 40% / cover; }
        .zw-hall::after { content: ''; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(11, 29, 23, .1) 30%, rgba(11, 29, 23, .85)); }
        .zw-stage { position: relative; min-height: 100vh; display: flex; align-items: flex-end; justify-content: center; gap: 24px; padding: 24px 16px 0; flex-wrap: wrap; }
        .zw-mayor { width: min(300px, 42vw); height: auto; filter: drop-shadow(0 20px 30px rgba(0, 0, 0, .35)); animation: zwbob 3s ease-in-out infinite; }
        @keyframes zwbob { 50% { transform: translateY(-6px); } }
        .zw-speech { align-self: center; max-width: 520px; background: #fff; border-radius: 22px; padding: 22px 24px; position: relative; box-shadow: 0 20px 50px rgba(0, 0, 0, .35); margin-bottom: 60px; }
        .zw-speech::before { content: ''; position: absolute; left: -14px; bottom: 40px; border: 14px solid transparent; border-right-color: #fff; border-left: 0; }
        .zw-who { font: 800 12px "Manrope"; letter-spacing: .12em; text-transform: uppercase; color: #00A47D; margin-bottom: 8px; }
        .zw-line { font-size: 19px; line-height: 1.55; min-height: 5.2em; }
        .zw-line b { color: #016950; }
        .zw-bills { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .zw-bill { font: 700 12px "Manrope"; padding: 5px 10px; border-radius: 999px; background: #FFF0E0; color: #A3550A; opacity: 0; transform: translateY(6px); animation: zwpop .4s forwards; }
        @keyframes zwpop { to { opacity: 1; transform: none; } }
        .zw-nav { display: flex; justify-content: space-between; align-items: center; margin-top: 14px; }
        .zw-dots { display: flex; gap: 6px; }
        .zw-dots i { width: 8px; height: 8px; border-radius: 50%; background: #D2DDDB; }
        .zw-dots i.is-on { background: #00A47D; }
        .zw-small-btn { border: 0; background: #00A47D; color: #fff; border-radius: 999px; padding: 10px 20px; font: 800 14px "Manrope"; }

        /* 3. First decision */
        #zws3 { background: #F4F1EA; }
        .zw-wrap { max-width: 900px; margin: 0 auto; padding: 90px 16px 60px; }
        .zw-kicker { font: 800 12px "Manrope"; letter-spacing: .16em; text-transform: uppercase; color: #00805F; }
        .zw-h2 { font-size: clamp(28px, 4.4vw, 44px); margin: 10px 0 12px; letter-spacing: -.02em; }
        .zw-p { font-size: 18px; line-height: 1.65; color: #33443E; margin: 0 0 14px; }
        .zw-choice { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin: 22px 0 14px; }
        .zw-choice button { border: 2px solid #E2DCCD; background: #fff; border-radius: 18px; padding: 22px; text-align: left; transition: .2s; color: #0F2A22; }
        .zw-choice button:hover { border-color: #00A47D; transform: translateY(-2px); }
        .zw-choice button em { font-style: normal; font-size: 34px; display: block; margin-bottom: 6px; }
        .zw-choice button b { display: block; font: 800 19px "Manrope"; margin-bottom: 4px; }
        .zw-choice button.is-picked { border-color: #00A47D; background: #E2F6EF; }
        .zw-verdict { display: none; background: #0F2A22; color: #E6FFF6; border-radius: 16px; padding: 16px 18px; font-size: 16px; line-height: 1.6; }
        .zw-verdict.is-on { display: block; animation: zwrise .4s; }
        .zw-boxes { display: none; grid-template-columns: 1fr 1fr; gap: 14px; margin-top: 16px; }
        .zw-boxes.is-on { display: grid; animation: zwrise .5s; }
        .zw-box { border-radius: 16px; padding: 18px 20px; font-size: 15px; line-height: 1.6; }
        .zw-box h4 { margin: 0 0 6px; font: 800 12px "Manrope"; letter-spacing: .14em; text-transform: uppercase; }
        .zw-real { background: #fff; border: 1px solid #E2DCCD; }
        .zw-real h4 { color: #7C5A12; }
        .zw-learn { background: #E2F6EF; border: 1px solid #B6E6D5; }
        .zw-learn h4 { color: #00805F; }
        .zw-center { text-align: center; margin-top: 28px; }
        .zw-hidden { display: none; }

        /* 4. The citizenship letter */
        #zws4 { background: radial-gradient(ellipse at 50% 30%, #3B2A1E, #1E140D 70%); padding: 80px 16px 60px; }
        .zw-letter { max-width: 820px; margin: 0 auto; background: #FBF6EC; background-image: linear-gradient(rgba(120, 90, 50, .04) 1px, transparent 1px); background-size: 100% 32px; border-radius: 6px; box-shadow: 0 40px 80px rgba(0, 0, 0, .55); padding: clamp(24px, 5vw, 64px); position: relative; color: #2B2118; animation: zwrise 1s cubic-bezier(.2, .8, .2, 1); }
        .zw-lhead { display: flex; align-items: center; gap: 16px; border-bottom: 2px solid #2B2118; padding-bottom: 16px; }
        .zw-crest { width: 64px; height: 64px; flex: none; }
        .zw-org { font: 700 13px "Manrope"; letter-spacing: .2em; text-transform: uppercase; }
        .zw-doc { font: 500 26px "Fraunces"; margin-top: 2px; }
        .zw-meta { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; font: 500 14px "Fraunces"; color: #6B5232; margin: 18px 0 22px; }
        .zw-letter p { font: 400 17px/1.75 "Fraunces"; margin: 0 0 14px; }
        .zw-letter h3 { font: 700 13px "Manrope"; letter-spacing: .14em; text-transform: uppercase; color: #7A1F1F; margin: 26px 0 10px; }
        .zw-table { width: 100%; border-collapse: collapse; font: 500 16px "Fraunces"; }
        .zw-table td { padding: 8px 4px; border-bottom: 1px dashed #CDBB9C; }
        .zw-table td:last-child { text-align: right; white-space: nowrap; }
        .zw-table .in td { color: #1E6B3A; font-weight: 700; }
        .zw-table .total td { border-bottom: 0; border-top: 2px solid #2B2118; font-weight: 700; font-size: 18px; }
        .zw-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .zw-cols ul { margin: 0; padding-left: 20px; font: 400 16px/1.7 "Fraunces"; }
        .zw-sign { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 30px; margin-top: 34px; }
        .zw-mayor-sign { font: 400 40px "Great Vibes"; color: #1F2D5C; line-height: 1; }
        .zw-role { font: 600 12px "Manrope"; letter-spacing: .1em; text-transform: uppercase; color: #6B5232; border-top: 1px solid #2B2118; padding-top: 6px; margin-top: 4px; }
        .zw-stamp { position: absolute; right: 6%; top: 46px; transform: rotate(-14deg); border: 4px solid rgba(158, 28, 28, .65); color: rgba(158, 28, 28, .65); font: 800 22px "Manrope"; letter-spacing: .14em; padding: 6px 16px; border-radius: 8px; text-transform: uppercase; }
        .zw-yours { min-width: 260px; }
        .zw-yours button { border: 0; background: none; padding: 0; width: 100%; text-align: left; }
        .zw-sigline { height: 52px; border-bottom: 2px dashed #8C7350; display: flex; align-items: flex-end; font: 400 40px "Great Vibes"; color: #0E5C46; }
        .zw-sigline.is-empty { animation: zwglow 1.6s infinite; }
        .zw-sigline.is-empty::after { content: attr(data-hint); font: 600 14px "Open Sans"; color: #A08560; padding-bottom: 6px; }
        @keyframes zwglow { 50% { background: rgba(0, 164, 125, .08); } }

        /* 5. Mission briefing */
        #zws5 { background: #070F1F; color: #DCE7F5; background-image: linear-gradient(rgba(94, 240, 196, .05) 1px, transparent 1px), linear-gradient(90deg, rgba(94, 240, 196, .05) 1px, transparent 1px); background-size: 40px 40px; }
        .zw-m { max-width: 1080px; margin: 0 auto; padding: 80px 16px; }
        .zw-mstamp { display: inline-block; border: 2px solid #5EF0C4; color: #5EF0C4; padding: 6px 14px; border-radius: 6px; font: 700 13px "Space Mono"; letter-spacing: .2em; text-transform: uppercase; }
        .zw-mtitle { font: 800 clamp(32px, 5.5vw, 60px)/1.05 "Manrope"; letter-spacing: -.03em; margin: 22px 0 10px; color: #fff; }
        .zw-mtitle span { color: #5EF0C4; text-shadow: 0 0 24px rgba(94, 240, 196, .5); }
        .zw-msub { font: 400 16px/1.7 "Space Mono"; max-width: 720px; color: #A9BCD6; }
        .zw-mgrid { display: grid; grid-template-columns: 360px 1fr; gap: 24px; margin-top: 34px; }
        .zw-panel { background: rgba(14, 28, 54, .75); border: 1px solid rgba(94, 240, 196, .22); border-radius: 18px; padding: 22px; }
        .zw-panel h3 { margin: 0 0 14px; font: 700 12px "Space Mono"; letter-spacing: .2em; color: #5EF0C4; text-transform: uppercase; }
        .zw-id { position: relative; border-radius: 18px; padding: 22px; overflow: hidden; color: #fff; background: linear-gradient(135deg, #0E5C46, #123A6B 60%, #3B1F6B); box-shadow: 0 0 0 1px rgba(255, 255, 255, .15), 0 20px 50px rgba(0, 0, 0, .5); }
        .zw-id::after { content: ''; position: absolute; inset: -50%; background: linear-gradient(115deg, transparent 40%, rgba(255, 255, 255, .22) 50%, transparent 60%); animation: zwshine 4s infinite; }
        @keyframes zwshine { from { transform: translateX(-60%); } to { transform: translateX(60%); } }
        .zw-id-head { display: flex; justify-content: space-between; font: 700 11px "Space Mono"; letter-spacing: .16em; opacity: .85; text-transform: uppercase; }
        .zw-id-body { display: flex; gap: 16px; align-items: center; margin-top: 18px; }
        .zw-avatar { width: 72px; height: 72px; border-radius: 14px; background: rgba(255, 255, 255, .15); display: grid; place-items: center; font: 800 28px "Manrope"; border: 2px solid rgba(255, 255, 255, .35); flex: none; }
        .zw-id-name { font: 800 21px "Manrope"; }
        .zw-id-row { font: 400 12px "Space Mono"; opacity: .85; margin-top: 4px; }
        .zw-id-salary { margin-top: 18px; padding-top: 14px; border-top: 1px dashed rgba(255, 255, 255, .3); display: flex; justify-content: space-between; align-items: baseline; font: 400 12px "Space Mono"; text-transform: uppercase; }
        .zw-id-salary b { font: 800 26px "Manrope"; color: #5EF0C4; }
        .zw-stages { list-style: none; margin: 0; padding: 0; counter-reset: st; }
        .zw-stages li { position: relative; padding: 0 0 18px 50px; }
        .zw-stages li::before { counter-increment: st; content: counter(st); position: absolute; left: 0; top: 0; width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; font: 700 14px "Space Mono"; border: 2px solid rgba(94, 240, 196, .5); color: #5EF0C4; }
        .zw-stages li:first-child::before { background: #5EF0C4; color: #070F1F; box-shadow: 0 0 18px rgba(94, 240, 196, .6); }
        .zw-stages li:not(:last-child)::after { content: ''; position: absolute; left: 16px; top: 38px; bottom: 2px; width: 2px; background: rgba(94, 240, 196, .25); }
        .zw-stages b { display: block; color: #fff; font: 800 16px "Manrope"; }
        .zw-stages span { font-size: 14px; color: #A9BCD6; }
        .zw-skills { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .zw-skill { background: rgba(255, 255, 255, .03); border: 1px solid rgba(255, 255, 255, .08); border-radius: 14px; padding: 14px 16px; }
        .zw-skill b { font: 800 15px "Manrope"; color: #fff; }
        .zw-skill p { margin: 6px 0 0; font-size: 13px; line-height: 1.55; color: #A9BCD6; }
        .zw-launch { text-align: center; margin-top: 40px; }
        .zw-launch button { border: 0; font: 700 19px "Space Mono"; letter-spacing: .12em; color: #070F1F; background: #5EF0C4; padding: 20px 40px; border-radius: 14px; box-shadow: 0 0 0 6px rgba(94, 240, 196, .15), 0 0 40px rgba(94, 240, 196, .45); }
        .zw-launch p { color: #6F86A6; font: 400 12px "Space Mono"; margin-top: 16px; }

        @media (max-width: 860px) { .zw-mgrid { grid-template-columns: 1fr; } .zw-skills { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 640px) {
            .zw-speech { margin-bottom: 20px; }
            .zw-speech::before { display: none; }
            .zw-mayor { width: 150px; }
            .zw-stage { flex-direction: column-reverse; align-items: center; padding-top: 70px; }
            .zw-choice, .zw-boxes.is-on, .zw-cols, .zw-skills { grid-template-columns: 1fr; }
            .zw-stamp { position: static; display: inline-block; margin: 0 0 12px; }
            .zw-spacer { display: none; }
        }
    </style>
</head>
<body>
<div class="zw-top">
    <button type="button" class="zw-back" id="zwback" onclick="zwGo(zwScene - 1)">{{ __('welcome.back') }}</button>
    <div class="zw-steps" id="zwsteps">@for ($i = 1; $i <= 5; $i++)<i></i>@endfor</div>
    <div class="zw-spacer"></div>
</div>

{{-- 1. Live broadcast over the city --}}
<section class="zw-scene is-on" id="zws1">
    <div class="zw-city"></div><div class="zw-shade"></div>
    <div class="zw-hero">
        <span class="zw-live"><i></i> {{ __('welcome.tv.live') }}</span>
        <h1 class="zw-title" id="zwtitle"><span class="zw-cursor">&nbsp;</span></h1>
        <p class="zw-sub" id="zwsub">{{ __('welcome.tv.sub') }}</p>
        <button type="button" class="zw-btn zw-pulse" onclick="zwGo(2)">{{ __('welcome.tv.play') }}</button>
    </div>
</section>

{{-- 2. The Mayor speaks --}}
<section class="zw-scene" id="zws2">
    <div class="zw-hall"></div>
    <div class="zw-stage">
        @include('citizen-activation.layouts.mayor', ['class' => 'zw-mayor'])
        <div class="zw-speech">
            <div class="zw-who">{{ __('welcome.mayor.who') }}</div>
            <div class="zw-line" id="zwline"></div>
            <div class="zw-nav">
                <div class="zw-dots" id="zwdots"></div>
                <button type="button" class="zw-small-btn" id="zwnext" onclick="zwNextLine()">{{ __('welcome.next') }}</button>
            </div>
        </div>
    </div>
</section>

{{-- 3. A first decision: need or want --}}
<section class="zw-scene" id="zws3">
    <div class="zw-wrap">
        <div class="zw-kicker">{{ __('welcome.decision.kicker') }}</div>
        <h2 class="zw-h2">{{ __('welcome.decision.title') }}</h2>
        <p class="zw-p">{{ __('welcome.decision.text', ['cheap' => $z($cheap, 0), 'pricey' => $z($pricey, 0)]) }}</p>
        <div class="zw-choice">
            <button type="button" onclick="zwPick(this, 'need')"><em>👟</em><b>{{ __('welcome.decision.need', ['cheap' => $z($cheap, 0)]) }}</b>{{ __('welcome.decision.need_sub') }}</button>
            <button type="button" onclick="zwPick(this, 'want')"><em>✨</em><b>{{ __('welcome.decision.want', ['pricey' => $z($pricey, 0)]) }}</b>{{ __('welcome.decision.want_sub') }}</button>
        </div>
        <div class="zw-verdict" id="zwverdict-need">{!! __('welcome.decision.verdict_need', ['left' => $z($leftAfterBills - $cheap), 'diff' => $z($pricey - $cheap, 0)]) !!}</div>
        <div class="zw-verdict" id="zwverdict-want">{!! __('welcome.decision.verdict_want', ['left' => $z($leftAfterBills - $pricey), 'diff' => $z($pricey - $cheap, 0)]) !!}</div>
        <div class="zw-boxes" id="zwboxes">
            <div class="zw-box zw-real"><h4>{{ __('welcome.decision.real_title') }}</h4>{{ __('welcome.decision.real') }}</div>
            <div class="zw-box zw-learn"><h4>{{ __('welcome.decision.learn_title') }}</h4>{{ __('welcome.decision.learn') }}</div>
        </div>
        <div class="zw-center zw-hidden" id="zwdecided"><button type="button" class="zw-btn" onclick="zwGo(4)">{{ __('welcome.decision.next') }}</button></div>
    </div>
</section>

{{-- 4. The citizenship letter, signed by the student --}}
<section class="zw-scene" id="zws4">
    <article class="zw-letter">
        <div class="zw-stamp">{{ __('welcome.letter.approved') }}</div>
        <header class="zw-lhead">
            <svg class="zw-crest" viewBox="0 0 64 64" aria-hidden="true"><path d="M32 3 L58 12 V32 C58 47 46 57 32 61 C18 57 6 47 6 32 V12 Z" fill="#0E5C46"/><path d="M32 9 L52 16 V32 C52 43 43 51 32 55 C21 51 12 43 12 32 V16 Z" fill="none" stroke="#E7B53C" stroke-width="2"/><text x="32" y="41" text-anchor="middle" font-family="Fraunces" font-weight="700" font-size="26" fill="#E7B53C">Z</text></svg>
            <div><div class="zw-org">{{ __('welcome.letter.org') }}</div><div class="zw-doc">{{ __('welcome.letter.doc') }}</div></div>
        </header>
        <div class="zw-meta">
            <span>{{ __('welcome.letter.citizen_no', ['id' => $citizenId]) }}@if ($address)<br>{{ $address }}, Zedville @endif</span>
            <span>Zedville, {{ now()->locale(app()->getLocale())->isoFormat('LL') }}</span>
        </div>

        <p>{{ __('welcome.letter.dear', ['name' => $user->name]) }}</p>
        <p>{!! __('welcome.letter.p1') !!}</p>
        <p>{{ __('welcome.letter.p2') }}</p>

        <h3>{{ __('welcome.letter.table_title') }}</h3>
        <table class="zw-table">
            <tr class="in"><td>{{ __('welcome.letter.salary_row') }}</td><td>+ {{ $z($salary) }}</td></tr>
            @foreach ($bills as $bill => $amount)
                <tr><td>{{ __('welcome.letter.' . $bill) }}</td><td>− {{ $z($amount) }}</td></tr>
            @endforeach
            <tr class="total"><td>{{ __('welcome.letter.left_row') }}</td><td>{{ $z($leftAfterBills) }}</td></tr>
        </table>

        <div class="zw-cols">
            <div>
                <h3>{{ __('welcome.letter.rights_title') }}</h3>
                <ul>@foreach (__('welcome.letter.rights') as $right)<li>{{ $right }}</li>@endforeach</ul>
            </div>
            <div>
                <h3>{{ __('welcome.letter.duties_title') }}</h3>
                <ul>@foreach (__('welcome.letter.duties') as $duty)<li>{{ $duty }}</li>@endforeach</ul>
            </div>
        </div>

        <h3>{{ __('welcome.letter.learn_title') }}</h3>
        <p>{{ __('welcome.letter.learn') }}</p>
        <p>{{ __('welcome.letter.closing') }}</p>

        <div class="zw-sign">
            <div>
                <div class="zw-mayor-sign">{{ __('welcome.letter.mayor_sign') }}</div>
                <div class="zw-role">{{ __('welcome.letter.mayor_role') }}</div>
            </div>
            <div class="zw-yours">
                <button type="button" onclick="zwSign()">
                    <div class="zw-sigline is-empty" id="zwsig" data-hint="{{ __('welcome.letter.sign_hint') }}"></div>
                </button>
                <div class="zw-role">{{ __('welcome.letter.citizen_role', ['name' => $user->name]) }}</div>
            </div>
        </div>
        <div class="zw-center zw-hidden" id="zwsigned"><button type="button" class="zw-btn" onclick="zwGo(5)">{{ __('welcome.letter.next') }}</button></div>
    </article>
</section>

{{-- 5. Mission briefing: the activation steps and the skills they train --}}
<section class="zw-scene" id="zws5">
    <div class="zw-m">
        <div class="zw-mstamp">{{ __('welcome.mission.stamp') }}</div>
        <h1 class="zw-mtitle">{!! __('welcome.mission.title', ['name' => e($firstName)]) !!}</h1>
        <p class="zw-msub">{{ __('welcome.mission.sub') }}</p>

        <div class="zw-mgrid">
            <div>
                <div class="zw-id">
                    <div class="zw-id-head"><span>{{ __('welcome.mission.card_city') }}</span><span>{{ __('welcome.mission.card_id') }}</span></div>
                    <div class="zw-id-body">
                        <div class="zw-avatar">{{ $initials ?: 'Z' }}</div>
                        <div>
                            <div class="zw-id-name">{{ $user->name }}</div>
                            <div class="zw-id-row">{{ $citizenId }}</div>
                            @if ($address)<div class="zw-id-row">{{ $address }}</div>@endif
                        </div>
                    </div>
                    <div class="zw-id-salary"><span>{{ __('welcome.mission.card_salary') }}</span><b>{{ $z($salary) }}</b></div>
                </div>
            </div>
            <div class="zw-panel">
                <h3>{{ __('welcome.mission.stages_title') }}</h3>
                <ol class="zw-stages">
                    @foreach (__('welcome.mission.stages') as [$stage, $text])
                        <li><b>{{ $stage }}</b><span>{{ $text }}</span></li>
                    @endforeach
                </ol>
            </div>
        </div>

        <div class="zw-panel" style="margin-top:24px">
            <h3>{{ __('welcome.mission.skills_title') }}</h3>
            <div class="zw-skills">
                @foreach (__('welcome.mission.skills') as [$icon, $skill, $use])
                    <div class="zw-skill"><b>{{ $icon }} {{ $skill }}</b><p>{{ $use }}</p></div>
                @endforeach
            </div>
        </div>

        <form class="zw-launch" method="POST" action="{{ route('CitizenActivation.welcome.accept') }}">
            @csrf
            <button type="submit">{{ __('welcome.mission.start') }}</button>
            <p>{{ __('welcome.mission.time') }}</p>
        </form>
    </div>
</section>

<script>
    const ZW_LINES = {!! json_encode($mayorLines, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!};
    const ZW_BILLS = {!! json_encode(__('welcome.mayor.bills'), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!};
    const ZW_NEXT = @json(__('welcome.next'));
    const ZW_GO = @json(__('welcome.mayor.go'));
    const ZW_SIGNATURE = @json($user->name);
    let zwScene = 1, zwLine = 0;

    function zwGo(n) {
        if (n < 1 || n > 5) return;
        zwScene = n;
        document.querySelectorAll('.zw-scene').forEach(s => s.classList.toggle('is-on', s.id === 'zws' + n));
        document.querySelectorAll('#zwsteps i').forEach((d, k) => d.classList.toggle('is-on', k < n));
        document.getElementById('zwback').classList.toggle('is-on', n > 1);
        window.scrollTo(0, 0);
        if (n === 2) zwShowLine();
    }
    function zwShowLine() {
        const line = document.getElementById('zwline');
        line.innerHTML = ZW_LINES[zwLine];
        if (zwLine === 2) {
            line.innerHTML += '<div class="zw-bills">' + ZW_BILLS.map((b, k) => `<span class="zw-bill" style="animation-delay:${k * 0.18}s">${b}</span>`).join('') + '</div>';
        }
        document.getElementById('zwdots').innerHTML = ZW_LINES.map((_, k) => `<i class="${k === zwLine ? 'is-on' : ''}"></i>`).join('');
        document.getElementById('zwnext').textContent = zwLine === ZW_LINES.length - 1 ? ZW_GO : ZW_NEXT;
    }
    function zwNextLine() {
        if (zwLine < ZW_LINES.length - 1) { zwLine++; zwShowLine(); } else { zwGo(3); }
    }
    function zwPick(btn, kind) {
        btn.parentElement.querySelectorAll('button').forEach(b => b.classList.toggle('is-picked', b === btn));
        document.querySelectorAll('.zw-verdict').forEach(v => v.classList.toggle('is-on', v.id === 'zwverdict-' + kind));
        document.getElementById('zwboxes').classList.add('is-on');
        document.getElementById('zwdecided').classList.remove('zw-hidden');
    }
    function zwSign() {
        const sig = document.getElementById('zwsig');
        if (!sig.classList.contains('is-empty')) return;
        sig.classList.remove('is-empty');
        let i = 0;
        (function write() {
            sig.textContent = ZW_SIGNATURE.slice(0, ++i);
            if (i < ZW_SIGNATURE.length) setTimeout(write, 70);
            else document.getElementById('zwsigned').classList.remove('zw-hidden');
        })();
    }
    (function typeTitle() {
        const el = document.getElementById('zwtitle'), text = {!! json_encode(__('welcome.tv.headline', ['name' => $firstName]), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!};
        let i = 0;
        (function tick() {
            el.innerHTML = '';
            el.append(text.slice(0, i));
            const c = document.createElement('span'); c.className = 'zw-cursor'; c.innerHTML = '&nbsp;'; el.append(c);
            if (i++ < text.length) setTimeout(tick, 45); else document.getElementById('zwsub').classList.add('is-on');
        })();
    })();
    zwGo(1);
</script>
</body>
</html>
