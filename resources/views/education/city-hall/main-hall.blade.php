@extends('layouts.profile')

@section('title', 'Main Hall - Educational Finance Department')

@section('content')
@push('styles')
 <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;1,400&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
    :root {
      --gold: #B8860B;
      --gold-light: rgba(184,134,11,0.12);
      --gold-border: rgba(184,134,11,0.32);
      --blue: #185FA5;
      --blue-light: rgba(24,95,165,0.10);
      --blue-border: rgba(24,95,165,0.28);
      --green: #3B6D11;
      --red: #A32D2D;
      --purple: #534AB7;
      --glass: rgba(255,255,255,0.86);
      --shadow: 0 4px 24px rgba(0,0,0,0.12);
      --shadow-sm: 0 2px 10px rgba(0,0,0,0.08);
      --radius: 16px;
      --radius-sm: 10px;
    }

    

    .ch-layout {
      position: relative; z-index: 1;
      max-width: 1060px;
      margin: 0 auto;
      padding: 2rem 1.5rem 3rem;
      display: flex; flex-direction: column; gap: 1rem;
    }

    /* ── HEADER ── */
    .ch-header {
      background: var(--glass);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border: 0.5px solid var(--gold-border);
      border-radius: var(--radius);
      padding: 1rem 1.5rem 0.8rem;
      box-shadow: var(--shadow);
      animation: fadeUp 0.4s ease both;
    }
    .ch-header-top {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 0.7rem;
    }
    .ch-header-left { display: flex; align-items: center; gap: 14px; }
    .ch-seal {
      width: 50px; height: 50px; border-radius: 50%;
      background: var(--gold-light); border: 2px solid var(--gold-border);
      display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;
    }
    .ch-city-name {
      font-family: 'Lora', serif; font-size: 22px; font-weight: 600; color: #2c2010;
    }
    .ch-city-sub { font-size: 12px; color: #7a6a50; margin-top: 2px; }
    .rename-btn {
      font-size: 11px; color: #a08040; background: none; border: none; cursor: pointer;
      display: inline-flex; align-items: center; gap: 3px;
      padding: 2px 7px; border-radius: 6px; transition: background 0.15s;
    }
    .rename-btn:hover { background: var(--gold-light); }
    .ch-mood-pill {
      display: flex; align-items: center; gap: 7px;
      padding: 6px 14px; border-radius: 20px;
      background: rgba(59,109,17,0.12); border: 0.5px solid rgba(59,109,17,0.30);
    }
    .mood-dot {
      width: 7px; height: 7px; border-radius: 50%; background: #3B6D11;
      animation: moodpulse 3s ease-in-out infinite;
    }
    @keyframes moodpulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.6;transform:scale(1.3)} }
    .mood-txt { font-size: 12px; font-weight: 500; color: #3B6D11; }
    .ch-quote-strip {
      border-top: 0.5px solid var(--gold-border); padding-top: 8px;
      display: flex; align-items: center; justify-content: space-between; gap: 12px;
    }
    .ch-quote-txt {
      font-family: 'Lora', serif; font-size: 12px; font-style: italic; color: #6b5a3a;
      padding-left: 12px; border-left: 2px solid var(--gold); flex: 1; line-height: 1.6;
    }
    .ch-quote-btn {
      font-size: 11px; font-weight: 500; color: var(--gold);
      background: var(--gold-light); border: 0.5px solid var(--gold-border);
      border-radius: 8px; padding: 4px 10px; cursor: pointer; white-space: nowrap;
      text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
      transition: background 0.15s;
    }
    .ch-quote-btn:hover { background: rgba(184,134,11,0.22); }

    /* ── SALARY BANNER ── */
    .salary-bar {
      background: var(--glass);
      backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
      border: 0.5px solid var(--gold-border); border-radius: var(--radius-sm);
      padding: 9px 1.25rem;
      display: flex; align-items: center; justify-content: space-between;
      box-shadow: var(--shadow-sm);
      animation: fadeUp 0.4s 0.08s ease both;
    }
    .salary-lbl { font-size: 12px; color: #8a6a20; display: flex; align-items: center; gap: 6px; }
    .salary-val { font-family: 'Lora', serif; font-size: 17px; font-weight: 500; color: var(--gold); }

    /* ── ALERT STRIP ── */
    .alert-strip {
      background: rgba(24,95,165,0.11);
      backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
      border: 0.5px solid rgba(24,95,165,0.28); border-radius: var(--radius-sm);
      padding: 8px 1rem;
      display: flex; align-items: center; gap: 10px;
      animation: fadeUp 0.4s 0.12s ease both;
    }
    .alert-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--blue); flex-shrink: 0; }
    .alert-txt { font-size: 12px; color: #0e3d6e; flex: 1; line-height: 1.5; }
    .alert-link {
      font-size: 12px; font-weight: 500; color: var(--blue);
      white-space: nowrap; text-decoration: none;
      display: flex; align-items: center; gap: 3px;
    }

    /* ── MIDDLE ROW ── */
    .ch-mid-row {
      display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
      animation: fadeUp 0.4s 0.18s ease both;
    }

    /* ── BULLETIN ── */
    .bulletin {
      background: var(--glass);
      backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
      border: 0.5px solid var(--gold-border); border-radius: var(--radius);
      padding: 1rem 1.25rem; box-shadow: var(--shadow); overflow: hidden;
    }
    .bulletin-header {
      display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem;
    }
    .panel-title { font-size: 13px; font-weight: 500; color: #2c2010; display: flex; align-items: center; gap: 6px; }
    .panel-title i { color: var(--gold); font-size: 15px; }
    .live-badge {
      display: flex; align-items: center; gap: 5px;
      padding: 3px 8px; border-radius: 10px;
      background: rgba(226,75,74,0.10); border: 0.5px solid rgba(226,75,74,0.25);
    }
    .live-dot-red { width: 6px; height: 6px; border-radius: 50%; background: #E24B4A; animation: livepulse 2s infinite; }
    @keyframes livepulse { 0%,100%{opacity:1} 50%{opacity:0.3} }
    .live-txt { font-size: 10px; font-weight: 500; color: #A32D2D; letter-spacing: 0.05em; text-transform: uppercase; }

    .bulletin-viewport { height: 162px; position: relative; overflow: hidden; }
    .bslide { position: absolute; top: 0; left: 0; right: 0; opacity: 0; transition: opacity 0.7s ease; pointer-events: none; }
    .bslide.active { opacity: 1; pointer-events: auto; }
    .slide-eyebrow { font-size: 10px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 5px; }
    .slide-headline { font-family: 'Lora', serif; font-size: 14px; font-weight: 500; color: #2c2010; line-height: 1.5; margin-bottom: 8px; }
    .slide-body { font-size: 12px; color: #6b5a3a; line-height: 1.6; }
    .stat-row { display: flex; gap: 7px; margin-top: 9px; }
    .stat-chip { flex: 1; border-radius: 8px; padding: 7px 9px; }
    .stat-chip-lbl { font-size: 10px; font-weight: 500; margin-bottom: 2px; }
    .stat-chip-val { font-size: 15px; font-weight: 500; }
    .sc-blue   { background: rgba(24,95,165,0.10);  } .sc-blue   .stat-chip-lbl,.sc-blue   .stat-chip-val { color: var(--blue);   }
    .sc-green  { background: rgba(59,109,17,0.10);  } .sc-green  .stat-chip-lbl,.sc-green  .stat-chip-val { color: var(--green);  }
    .sc-amber  { background: rgba(184,134,11,0.10); } .sc-amber  .stat-chip-lbl,.sc-amber  .stat-chip-val { color: var(--gold);   }
    .sc-red    { background: rgba(163,45,45,0.10);  } .sc-red    .stat-chip-lbl,.sc-red    .stat-chip-val { color: var(--red);    }
    .sc-purple { background: rgba(83,74,183,0.10);  } .sc-purple .stat-chip-lbl,.sc-purple .stat-chip-val { color: var(--purple); }
    .sc-teal   { background: rgba(15,110,86,0.10);  } .sc-teal   .stat-chip-lbl,.sc-teal   .stat-chip-val { color: #0F6E56;       }

    .progress-dots { display: flex; gap: 5px; justify-content: center; margin-top: 0.75rem; }
    .pdot {
      width: 5px; height: 5px; border-radius: 50%;
      background: rgba(184,134,11,0.25); cursor: pointer;
      transition: background 0.3s; border: none; padding: 0;
    }
    .pdot.on { background: var(--gold); }

    /* ── CITY MOOD ── */
    .mood-card {
      background: var(--glass);
      backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
      border: 0.5px solid rgba(59,109,17,0.25); border-radius: var(--radius);
      padding: 1rem 1.25rem; box-shadow: var(--shadow);
      cursor: pointer; text-decoration: none; display: block;
      transition: border-color 0.2s;
    }
    .mood-card:hover { border-color: rgba(59,109,17,0.5); }
    .mood-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem; }
    .mood-see { font-size: 11px; color: var(--blue); display: flex; align-items: center; gap: 3px; }
    .quadrants { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
    .quad { border-radius: 8px; padding: 8px 10px; }
    .q-hc { background: rgba(59,109,17,0.10);  } .q-hc .quad-name,.q-hc .quad-pct { color: var(--green);  } .q-hc .quad-sub { color: #639922; }
    .q-hu { background: rgba(184,134,11,0.10); } .q-hu .quad-name,.q-hu .quad-pct { color: var(--gold);   } .q-hu .quad-sub { color: #854F0B; }
    .q-lc { background: rgba(24,95,165,0.10);  } .q-lc .quad-name,.q-lc .quad-pct { color: var(--blue);   } .q-lc .quad-sub { color: #378ADD; }
    .q-lu { background: rgba(83,74,183,0.10);  } .q-lu .quad-name,.q-lu .quad-pct { color: var(--purple); } .q-lu .quad-sub { color: #7F77DD; }
    .quad-name { font-size: 10px; font-weight: 500; margin-bottom: 2px; }
    .quad-pct  { font-size: 18px; font-weight: 500; }
    .quad-sub  { font-size: 10px; }
    .mood-footer { margin-top: 8px; font-size: 11px; color: #7a6a50; display: flex; justify-content: space-between; align-items: center; }

    /* ── ROOM DOORS ── */
    .ch-doors { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; animation: fadeUp 0.4s 0.26s ease both; }
    .door-btn {
      display: flex; align-items: center; gap: 14px;
      background: var(--glass);
      backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
      border: 0.5px solid var(--gold-border); border-radius: var(--radius);
      padding: 1rem 1.25rem; cursor: pointer; text-decoration: none;
      box-shadow: var(--shadow); transition: transform 0.18s, border-color 0.18s, box-shadow 0.18s;
    }
    .door-btn:hover { transform: translateY(-2px); border-color: rgba(184,134,11,0.55); box-shadow: 0 8px 30px rgba(0,0,0,0.15); }
    .door-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .di-blue   { background: var(--blue-light);  color: var(--blue);   }
    .di-teal   { background: rgba(15,110,86,0.12); color: #0F6E56; }
    .door-info { flex: 1; }
    .door-name { font-family: 'Lora', serif; font-size: 15px; font-weight: 600; color: #2c2010; margin-bottom: 3px; }
    .door-desc { font-size: 12px; color: #7a6a50; line-height: 1.4; }
    .door-badge { font-size: 10px; font-weight: 500; padding: 2px 8px; border-radius: 10px; margin-top: 6px; display: inline-block; }
    .db-blue { background: var(--blue-light); color: var(--blue); }
    .db-teal { background: rgba(15,110,86,0.12); color: #0F6E56; }
    .door-arrow { font-size: 18px; color: rgba(184,134,11,0.45); }

    @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }

    @media (max-width: 700px) {
      .ch-mid-row, .ch-doors { grid-template-columns: 1fr; }
      .ch-header-top { flex-direction: column; align-items: flex-start; gap: 8px; }
    }

    /* ── WELCOME OVERLAY ── */
    .welcome-overlay {
      display: none; position: fixed; inset: 0; z-index: 200; pointer-events: none;
    }
    .welcome-overlay.show { display: block; }

    .receptionist-area {
      position: fixed; bottom: 0; left: 15%;
      display: flex; flex-direction: column; align-items: flex-start;
      pointer-events: auto;
      animation: receptionistIn 0.5s 0.3s ease both;
    }
    @keyframes receptionistIn {
      from { opacity:0; transform:translateY(20px); }
      to   { opacity:1; transform:translateY(0); }
    }

    .speech-bubble {
      background: white;
      border: 2px solid rgba(184,134,11,0.35);
      border-radius: 16px;
      padding: 1rem 1.15rem;
      box-shadow: 0 8px 32px rgba(0,0,0,0.18);
      position: relative;
      width: 310px;
      margin-left: 20px;
      margin-bottom: 6px;
      animation: bubbleIn 0.4s 0.7s cubic-bezier(.4,0,.2,1) both;
    }
    @keyframes bubbleIn {
      from { opacity:0; transform:scale(0.88) translateY(10px); }
      to   { opacity:1; transform:scale(1) translateY(0); }
    }
    .speech-bubble::after {
      content:''; position:absolute; bottom:-11px; left:24px;
      border-left:11px solid transparent; border-right:11px solid transparent;
      border-top:11px solid white;
    }
    .speech-bubble::before {
      content:''; position:absolute; bottom:-14px; left:22px;
      border-left:13px solid transparent; border-right:13px solid transparent;
      border-top:13px solid rgba(184,134,11,0.35);
    }
    .bubble-greeting {
      font-size:10px; font-weight:500; color:#B8860B;
      text-transform:uppercase; letter-spacing:0.08em; margin-bottom:5px;
    }
    .bubble-text { font-size:13px; color:#3a2e1a; line-height:1.6; margin-bottom:0.8rem; }
    .bubble-items { display:flex; flex-direction:column; gap:6px; margin-bottom:0.8rem; }
    .bubble-item  { display:flex; align-items:center; gap:8px; font-size:12px; color:#3a2e1a; }
    .bicon { width:24px; height:24px; border-radius:6px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:13px; }
    .bicon-blue   { background:rgba(24,95,165,0.10);  color:#185FA5; }
    .bicon-purple { background:rgba(83,74,183,0.10);  color:#534AB7; }
    .bicon-teal   { background:rgba(15,110,86,0.10);  color:#0F6E56; }
    .bicon-gold   { background:rgba(184,134,11,0.10); color:#B8860B; }
    .bicon-green  { background:rgba(59,109,17,0.10);  color:#3B6D11; }
    .bubble-actions { display:flex; gap:7px; }
    .bubble-btn-ok {
      flex:1; padding:7px 0; border-radius:8px; cursor:pointer;
      font-family:'DM Sans',sans-serif; font-size:12px; font-weight:500;
      background:rgba(184,134,11,0.10); color:#B8860B;
      border:0.5px solid rgba(184,134,11,0.30); transition:background 0.15s;
    }
    .bubble-btn-ok:hover { background:rgba(184,134,11,0.20); }
    .bubble-btn-hide {
      padding:7px 10px; border-radius:8px; cursor:pointer;
      font-family:'DM Sans',sans-serif; font-size:11px; color:#a09070;
      background:transparent; border:0.5px solid rgba(0,0,0,0.10); white-space:nowrap;
    }
    .bubble-btn-hide:hover { background:rgba(0,0,0,0.05); }

    .receptionist-avatar {
      width:58px; height:58px; border-radius:50%;
      background:white; border:2px solid rgba(184,134,11,0.35);
      box-shadow:0 4px 16px rgba(0,0,0,0.15);
      display:flex; align-items:center; justify-content:center;
      font-size:28px; cursor:pointer; transition:transform 0.15s;
      margin-left:4px;
    }
    .receptionist-avatar:hover { transform:scale(1.08); }
    .receptionist-hint {
      font-size:10px; color:rgba(255,255,255,0.80);
      margin-top:4px; margin-bottom:14px; margin-left:6px;
      text-shadow:0 1px 3px rgba(0,0,0,0.5);
    }
  </style>  
@endpush
<div class="ch-layout">

  <!-- HEADER -->
  <div class="ch-header">
    <div class="ch-header-top">
      <div class="ch-header-left">
        <div class="ch-seal">🏛️</div>
        <div>
          <div style="display:flex;align-items:center;gap:8px;">
            <span class="ch-city-name">{{ $cityName }}</span>
            <button class="rename-btn" onclick="location.href='{{ route('education.civic-chamber') }}?topic=city-name'">
              <i class="ti ti-pencil" style="font-size:11px;" aria-hidden="true"></i> propose rename
            </button>
          </div>
          <!-- <div class="ch-city-sub">City Hall · Gran Canaria</div> -->
        </div>
      </div>
      <!-- IT TEAM: replace mood label with top quadrant from mood_meter table -->
      <div class="ch-mood-pill">
        <div class="mood-dot"></div>
        <span class="mood-txt">City mood: Calm &amp; focused</span>
      </div>
    </div>
    <div class="ch-quote-strip">
      <span class="ch-quote-txt">"{{ $cityQuote ?? 'A budget is telling your money where to go instead of wondering where it went.' }}"</span>
      <a href="{{ route('education.civic-chamber') }}?topic=city-quote" class="ch-quote-btn">
        <i class="ti ti-pencil" style="font-size:11px;" aria-hidden="true"></i> Change quote
      </a>
    </div>
  </div>

  <!-- SALARY BANNER -->
  <!-- IT TEAM: replace salary data from salary_policy table -->
  <div class="salary-bar">
    <span class="salary-lbl">
      <i class="ti ti-report-money" style="font-size:15px;" aria-hidden="true"></i>
      Current salary policy — Scale B
    </span>
    <span class="salary-val">3,953 zeds / month</span>
  </div>

  <!-- ALERT STRIP -->
  <!-- IT TEAM: build $alerts array from active referendums + petitions tables -->
  <div class="alert-strip">
    <div class="alert-dot"></div>
    <span class="alert-txt">A new referendum is open · "Should the city invest in a community library?" · A petition needs your signature</span>
    <a href="{{ route('education.civic-chamber') }}" class="alert-link">
      See all <i class="ti ti-arrow-right" style="font-size:11px;" aria-hidden="true"></i>
    </a>
  </div>

  <!-- MIDDLE ROW -->
  <div class="ch-mid-row">

    <!-- ECONOMIC BULLETIN -->
    <div class="bulletin" id="bulletinBox">
      <div class="bulletin-header">
        <span class="panel-title">
          <i class="ti ti-chart-bar" aria-hidden="true"></i>
          City economic bulletin
        </span>
        <div class="live-badge">
          <div class="live-dot-red"></div>
          <span class="live-txt">Live</span>
        </div>
      </div>

      <div class="bulletin-viewport">
        <!-- IT TEAM: replace static slides with DB loop from bulletin_slides table -->

        <div class="bslide active" id="bslide-0">
          <div class="slide-eyebrow" style="color:#185FA5;">Citizens &amp; Economy</div>
          <!-- IT TEAM: replace 29 with citizen count from users WHERE role='student' -->
          <div class="slide-headline">Zedville has 29 active citizens this year</div>
          <div class="stat-row">
            <div class="stat-chip sc-blue"><div class="stat-chip-lbl">Total citizens</div><div class="stat-chip-val">29</div></div>
            <div class="stat-chip sc-green"><div class="stat-chip-lbl">City budget</div><div class="stat-chip-val">53,650 z</div></div>
            <div class="stat-chip sc-amber"><div class="stat-chip-lbl">Salary scale</div><div class="stat-chip-val">Scale B</div></div>
          </div>
        </div>

        <div class="bslide" id="bslide-1">
          <div class="slide-eyebrow" style="color:#3B6D11;">Salaries</div>
          <div class="slide-headline">Every citizen earns 1,850 zeds per month</div>
          <div class="slide-body">The salary policy is reviewed once a year. The scale can go up or down depending on how the city's finances are managed. Scale A starts at 1,500 z and Scale D reaches 2,600 z.</div>
          <div class="stat-row">
            <div class="stat-chip sc-green"><div class="stat-chip-lbl">Minimum salary</div><div class="stat-chip-val">1,500 z</div></div>
            <div class="stat-chip sc-amber"><div class="stat-chip-lbl">Current salary</div><div class="stat-chip-val">3,953 z</div></div>
          </div>
        </div>

        <div class="bslide" id="bslide-2">
          <div class="slide-eyebrow" style="color:#B8860B;">Wealth &amp; Inequality</div>
          <div class="slide-headline">14% of citizens are considered wealthy in Zedville</div>
          <div class="slide-body">A citizen is considered wealthy when their savings exceed 3 months of salary. The top spenders hold 28% of all zeds in circulation.</div>
          <div class="stat-row">
            <div class="stat-chip sc-amber"><div class="stat-chip-lbl">Wealthy citizens</div><div class="stat-chip-val">14%</div></div>
            <div class="stat-chip sc-purple"><div class="stat-chip-lbl">Wealth threshold</div><div class="stat-chip-val">5,550 z</div></div>
          </div>
        </div>

        <div class="bslide" id="bslide-3">
          <div class="slide-eyebrow" style="color:#A32D2D;">City Challenges</div>
          <div class="slide-headline">8 citizens are spending more than 40% on non-essentials</div>
          <div class="slide-body">Over-spending in Wants is rising. The city council has opened a referendum to discuss a spending awareness campaign.</div>
          <div class="stat-row">
            <div class="stat-chip sc-red"><div class="stat-chip-lbl">Over-spenders</div><div class="stat-chip-val">8 citizens</div></div>
            <div class="stat-chip sc-blue"><div class="stat-chip-lbl">Open referendums</div><div class="stat-chip-val">1</div></div>
          </div>
        </div>

        <div class="bslide" id="bslide-4">
          <div class="slide-eyebrow" style="color:#0F6E56;">Did you know?</div>
          <div class="slide-headline">In small European towns, 3 in 10 families have no emergency savings</div>
          <div class="slide-body">Around 30% of households cannot cover an unexpected expense of €400. Financial literacy programmes reduce this by up to 40%.</div>
          <div class="stat-row">
            <div class="stat-chip sc-teal"><div class="stat-chip-lbl">No savings</div><div class="stat-chip-val">~30%</div></div>
            <div class="stat-chip sc-green"><div class="stat-chip-lbl">With education</div><div class="stat-chip-val">up to 40%</div></div>
          </div>
        </div>

      </div><!-- /bulletin-viewport -->

      <div class="progress-dots" id="bulletinDots">
        <button class="pdot on" onclick="bulletinGoTo(0)" aria-label="Slide 1"></button>
        <button class="pdot" onclick="bulletinGoTo(1)" aria-label="Slide 2"></button>
        <button class="pdot" onclick="bulletinGoTo(2)" aria-label="Slide 3"></button>
        <button class="pdot" onclick="bulletinGoTo(3)" aria-label="Slide 4"></button>
        <button class="pdot" onclick="bulletinGoTo(4)" aria-label="Slide 5"></button>
      </div>
    </div><!-- /bulletin -->

    <!-- CITY MOOD -->
    <!-- IT TEAM: href → city mood page route -->
    <a href="{{ url('city-mood') }}" class="mood-card">
      <div class="mood-card-top">
        <span class="panel-title">
          <i class="ti ti-heart" style="color:#0F6E56;font-size:15px;" aria-hidden="true"></i>
          City mood today
        </span>
        <span class="mood-see">Full profile <i class="ti ti-arrow-right" style="font-size:11px;" aria-hidden="true"></i></span>
      </div>
      <!-- IT TEAM: replace percentages from mood_meter daily averages -->
      <div class="quadrants">
        <div class="quad q-hc"><span class="quad-name">Excited · Happy</span><span class="quad-pct">41%</span><span class="quad-sub">High energy, comfortable</span></div>
        <div class="quad q-hu"><span class="quad-name">Stressed · Anxious</span><span class="quad-pct">18%</span><span class="quad-sub">High energy, uncomfortable</span></div>
        <div class="quad q-lc"><span class="quad-name">Calm · Focused</span><span class="quad-pct">31%</span><span class="quad-sub">Low energy, comfortable</span></div>
        <div class="quad q-lu"><span class="quad-name">Sad · Tired</span><span class="quad-pct">10%</span><span class="quad-sub">Low energy, uncomfortable</span></div>
      </div>
      <div class="mood-footer">
        <!-- IT TEAM: replace with count of students who logged today -->
        <span>29 students logged today</span>
        <span class="mood-see">See city mood page <i class="ti ti-arrow-right" style="font-size:11px;" aria-hidden="true"></i></span>
      </div>
    </a>

  </div><!-- /ch-mid-row -->

  <!-- ROOM DOORS -->
  <div class="ch-doors">

    <a href="{{ route('education.civic-chamber') }}" class="door-btn">
      <div class="door-icon di-blue">
        <i class="ti ti-checkup-list" aria-hidden="true"></i>
      </div>
      <div class="door-info">
        <div class="door-name">Civic Chamber</div>
        <div class="door-desc">Referendums and petitions — your civic voice</div>
        <!-- IT TEAM: replace count from referendums + petitions tables -->
        <span class="door-badge db-blue">3 items need your attention</span>
      </div>
      <span class="door-arrow"><i class="ti ti-arrow-right" aria-hidden="true"></i></span>
    </a>

    <a href="{{ route('education.well-being-room') }}" class="door-btn">
      <div class="door-icon di-teal">
        <i class="ti ti-heart" aria-hidden="true"></i>
      </div>
      <div class="door-info">
        <div class="door-name">Wellbeing Room</div>
        <div class="door-desc">Articles and videos to balance your life</div>
        <!-- IT TEAM: replace with count of new wellbeing posts from DB -->
        <span class="door-badge db-teal">3 new articles</span>
      </div>
      <span class="door-arrow"><i class="ti ti-arrow-right" aria-hidden="true"></i></span>
    </a>

  </div><!-- /ch-doors -->


<!-- ── WELCOME OVERLAY ── -->
<!-- Shows on first visit, dismissed by student, stored in localStorage -->
<!-- IT TEAM: optionally use DB flag instead of localStorage to track per-student -->
<div class="welcome-overlay" id="welcomeOverlay">
  <div class="receptionist-area" id="receptionistArea">

    <!-- Speech bubble — shown on first visit -->
    <div class="speech-bubble" id="speechBubble">
      <div class="bubble-greeting">👋 Welcome to City Hall</div>
      <div class="bubble-text">Hi! I am here to help you find your way around. Here is what you can do in City Hall:</div>
      <div class="bubble-items">
        <div class="bubble-item">
          <span class="bicon bicon-blue"><i class="ti ti-checkup-list" aria-hidden="true"></i></span>
          <span><strong>Vote</strong> on referendums proposed by your tutor</span>
        </div>
        <div class="bubble-item">
          <span class="bicon bicon-purple"><i class="ti ti-writing" aria-hidden="true"></i></span>
          <span><strong>Start or sign</strong> a petition for your class</span>
        </div>
        <div class="bubble-item">
          <span class="bicon bicon-teal"><i class="ti ti-heart" aria-hidden="true"></i></span>
          <span><strong>Read</strong> wellbeing articles and watch videos</span>
        </div>
        <div class="bubble-item">
          <span class="bicon bicon-gold"><i class="ti ti-chart-bar" aria-hidden="true"></i></span>
          <span><strong>Follow</strong> your city's economic bulletin</span>
        </div>
        <div class="bubble-item">
          <span class="bicon bicon-green"><i class="ti ti-mood-smile" aria-hidden="true"></i></span>
          <span><strong>See</strong> how the whole city is feeling today</span>
        </div>
      </div>
      <div class="bubble-actions">
        <button class="bubble-btn-ok" onclick="closeBubble(false)">
          Got it, let me explore!
        </button>
        <button class="bubble-btn-hide" onclick="closeBubble(true)">
          Don't show again
        </button>
      </div>
    </div>

    <!-- Avatar — always visible, click to reopen bubble -->
    <div class="receptionist-avatar" id="receptionistAvatar"
      onclick="toggleBubble()" title="Click me for help">
      👩‍💼
    </div>
    <div class="receptionist-hint" id="avatarHint">Click me for help</div>

  </div>
</div>

</div><!-- /ch-layout -->

<script>
  // ── BULLETIN AUTO-SCROLL ──
  const BULLETIN_TOTAL = 5; // IT TEAM: set dynamically from DB slide count
  let bulletinCurrent = 0;

  function bulletinGoTo(n) {
    document.getElementById('bslide-' + bulletinCurrent).classList.remove('active');
    document.querySelectorAll('#bulletinDots .pdot')[bulletinCurrent].classList.remove('on');
    bulletinCurrent = n;
    document.getElementById('bslide-' + bulletinCurrent).classList.add('active');
    document.querySelectorAll('#bulletinDots .pdot')[bulletinCurrent].classList.add('on');
  }

  let bulletinTimer = setInterval(() => bulletinGoTo((bulletinCurrent + 1) % BULLETIN_TOTAL), 4000);
  document.getElementById('bulletinBox').addEventListener('mouseenter', () => clearInterval(bulletinTimer));
  document.getElementById('bulletinBox').addEventListener('mouseleave', () => {
    bulletinTimer = setInterval(() => bulletinGoTo((bulletinCurrent + 1) % BULLETIN_TOTAL), 4000);
  });

  // ── WELCOME BUBBLE ──
  // IT TEAM: replace localStorage key with student-specific DB flag if preferred
  // Key format: zedville_cityhall_welcome_{studentId}
  // For now uses localStorage so no DB change needed

  const WELCOME_KEY = 'zedville_cityhall_welcome';

  function initWelcome() {
    const dismissed = localStorage.getItem(WELCOME_KEY);
    const overlay   = document.getElementById('welcomeOverlay');
    const bubble    = document.getElementById('speechBubble');
    const hint      = document.getElementById('avatarHint');

    // Always show the overlay (avatar is always visible)
    overlay.classList.add('show');

    if (dismissed === 'permanent') {
      // Permanently dismissed — hide bubble, show only avatar
      bubble.style.display = 'none';
      hint.textContent = 'Ask me anything';
    } else if (dismissed === 'session') {
      // Dismissed this session — hide bubble on reload
      bubble.style.display = 'none';
    }
    // else: first visit — show bubble (default)
  }

  function closeBubble(permanent) {
    const bubble = document.getElementById('speechBubble');
    const hint   = document.getElementById('avatarHint');
    bubble.style.animation = 'none';
    bubble.style.opacity   = '0';
    bubble.style.transform = 'scale(0.88) translateY(10px)';
    bubble.style.transition = 'all 0.25s ease';
    setTimeout(() => {
      bubble.style.display = 'none';
      hint.textContent = 'Ask me anything';
    }, 250);
    localStorage.setItem(WELCOME_KEY, permanent ? 'permanent' : 'session');
  }

  function toggleBubble() {
    const bubble = document.getElementById('speechBubble');
    const hint   = document.getElementById('avatarHint');
    if (bubble.style.display === 'none' || bubble.style.display === '') {
      bubble.style.display = 'block';
      bubble.style.opacity = '0';
      bubble.style.transform = 'scale(0.88) translateY(10px)';
      bubble.style.transition = 'all 0.3s cubic-bezier(.4,0,.2,1)';
      requestAnimationFrame(() => {
        bubble.style.opacity   = '1';
        bubble.style.transform = 'scale(1) translateY(0)';
      });
      hint.textContent = 'Click me for help';
      // Remove permanent dismissal so they can read again
      if (localStorage.getItem(WELCOME_KEY) !== 'permanent') return;
      localStorage.removeItem(WELCOME_KEY);
    } else {
      closeBubble(false);
    }
  }

  // Init on load
  initWelcome();

</script>
@endsection