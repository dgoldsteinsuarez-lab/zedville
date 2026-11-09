@extends('layouts.profile')

@section('title', 'Budget Rule')

@section('content')
@push('styles')

<style>
    /* ─── Base & layout ──────────────────────────────────────────── */
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Nunito', sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, #F0FDFA 0%, #EEF2FF 50%, #F5F3FF 100%);
    }

    .screen {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .container {
      max-width: 620px;
      margin: 0 auto;
      padding: 24px 16px;
    }

    .hidden { display: none !important; }

    /* ─── Loading ──────────────────────────────────────────────── */
    #loading-emoji { font-size: 48px; animation: pulse 1.5s infinite; text-align: center; }
    #loading-text { color: #7C3AED; font-weight: 700; font-size: 18px; text-align: center; }

    /* ─── Intro screen ─────────────────────────────────────────── */
    .intro-card {
      max-width: 580px;
      background: white;
      border-radius: 24px;
      padding: 36px 32px;
      box-shadow: 0 20px 60px rgba(124, 58, 237, 0.1), 0 0 0 1px rgba(124, 58, 237, 0.05);
      text-align: center;
    }

    .intro-icon { font-size: 56px; margin-bottom: 8px; }

    .intro-title {
      font-family: 'Fredoka One', cursive;
      font-size: 26px;
      color: #0F172A;
      margin: 0 0 6px;
    }

    .intro-description {
      color: #64748B;
      font-size: 15px;
      line-height: 1.6;
      margin: 0 0 20px;
    }

    .rule-list {
      text-align: left;
      margin-bottom: 20px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .rule-row {
      border-radius: 14px;
      padding: 14px 18px;
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .rule-row.needs   { background: #D1FAE5; }
    .rule-row.wants   { background: #DBEAFE; }
    .rule-row.savings { background: #EDE9FE; }

    .rule-icon { font-size: 32px; }

    .rule-title {
      font-weight: 900;
      font-size: 16px;
    }

    .rule-row.needs   .rule-title { color: #059669; }
    .rule-row.wants   .rule-title { color: #2563EB; }
    .rule-row.savings .rule-title { color: #7C3AED; }

    .rule-sub {
      font-size: 13px;
      color: #334155;
    }

    .intro-bank {
      border-radius: 14px;
      padding: 12px 16px;
      margin-bottom: 16px;
      border: 2px solid var(--scale-border);
      background: var(--scale-bg);
      text-align: left;
    }

    .intro-bank-title {
      font-weight: 800;
      color: var(--scale-color);
      font-size: 13px;
      margin-bottom: 4px;
    }

    .intro-bank-sub {
      font-size: 12px;
      color: #64748B;
    }

    .intro-points-note {
      background: #FEF9C3;
      border-radius: 12px;
      padding: 10px 14px;
      margin-bottom: 24px;
      text-align: left;
    }

    .intro-points-note p {
      color: #92400E;
      margin: 0;
      font-size: 13px;
      line-height: 1.5;
    }

    .start-button {
      background: linear-gradient(135deg, #7C3AED, #6D28D9);
      color: white;
      border: none;
      border-radius: 14px;
      padding: 14px 48px;
      font-size: 17px;
      font-weight: 800;
      font-family: 'Nunito', sans-serif;
      cursor: pointer;
      box-shadow: 0 4px 16px rgba(124, 58, 237, 0.3);
      transition: all 0.2s;
    }

    .start-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 24px rgba(124, 58, 237, 0.4);
    }

    /* ─── Game header ──────────────────────────────────────────── */
    .header { text-align: center; margin-bottom: 14px; }

    .game-title {
      font-family: 'Fredoka One', cursive;
      font-size: 22px;
      color: #0F172A;
      margin: 0 0 4px;
    }

    .game-subtitle {
      color: #64748B;
      font-size: 13px;
      margin: 0;
    }

    /* ─── Stats bar ────────────────────────────────────────────── */
    .stats-row {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-bottom: 12px;
      flex-wrap: wrap;
    }

    .stat-pill {
      background: white;
      border-radius: 10px;
      padding: 6px 14px;
      font-size: 13px;
      font-weight: 700;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .stat-pill.points { color: #7C3AED; }
    .stat-pill.done   { color: #64748B; }

    /* ─── Sticky reference bar ─────────────────────────────────── */
    .reference {
      background: white;
      border-radius: 14px;
      padding: 10px 16px;
      margin-bottom: 16px;
      border: 2px solid #E2E8F0;
      position: sticky;
      top: 8px;
      z-index: 10;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .reference-row-1 {
      display: flex;
      gap: 8px;
      justify-content: center;
      flex-wrap: wrap;
      margin-bottom: 6px;
    }

    .reference-salary {
      font-size: 12px;
      font-weight: 800;
      color: var(--scale-color);
    }

    .reference-row-2 {
      display: flex;
      gap: 8px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .reference-actual {
      font-size: 12px;
      color: #64748B;
      font-weight: 700;
    }

    /* ─── Progress bar ─────────────────────────────────────────── */
    .progress-bar {
      background: #E2E8F0;
      border-radius: 8px;
      height: 8px;
      margin-bottom: 20px;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      border-radius: 8px;
      transition: width 0.5s cubic-bezier(.4, 0, .2, 1);
      background: linear-gradient(90deg, #7C3AED, #0D9488);
      width: 0%;
    }

    /* ─── Questions ────────────────────────────────────────────── */
    .questions {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .question-card {
      background: white;
      border-radius: 16px;
      padding: 16px 18px;
      border: 2px solid #E2E8F0;
      transition: all 0.3s;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
    }

    .question-card.done-correct {
      background: #F0FDF4;
      border-color: #86EFAC;
    }

    .question-card.done-revealed {
      background: #FFFBEB;
      border-color: #FDE68A;
    }

    .question-header {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 10px;
    }

    .question-number {
      min-width: 30px;
      height: 30px;
      border-radius: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 900;
      font-size: 13px;
      background: var(--scale-color);
      flex-shrink: 0;
    }

    .question-card.done-correct  .question-number { background: #22C55E; }
    .question-card.done-revealed .question-number { background: #F59E0B; }

    .question-tag {
      font-size: 12px;
      font-weight: 800;
      color: var(--scale-color);
      text-transform: uppercase;
      letter-spacing: 0.3px;
      margin-bottom: 2px;
    }

    .question-text {
      font-size: 15px;
      font-weight: 700;
      color: #1E293B;
      line-height: 1.5;
    }

    /* Hint, revealed, correct, wrong messages */
    .msg-hint {
      background: #FEF9C3;
      border-radius: 10px;
      padding: 9px 13px;
      margin-bottom: 10px;
      font-size: 13px;
      color: #92400E;
      font-weight: 600;
      animation: fadeIn 0.3s;
      white-space: pre-line;
    }

    .msg-revealed {
      background: #DBEAFE;
      border-radius: 10px;
      padding: 11px 13px;
      margin-bottom: 10px;
      font-size: 13px;
      color: #1E40AF;
      animation: fadeIn 0.3s;
    }

    .msg-revealed-title {
      font-weight: 800;
      margin-bottom: 4px;
    }

    .msg-revealed-step {
      font-family: monospace;
      font-size: 14px;
      font-weight: 700;
      margin-bottom: 2px;
    }

    .msg-revealed-answer {
      margin-top: 6px;
      font-weight: 800;
      color: #1E3A5F;
      font-size: 15px;
    }

    .msg-correct {
      background: #D1FAE5;
      border-radius: 10px;
      padding: 9px 13px;
      font-size: 14px;
      color: #065F46;
      font-weight: 700;
      animation: fadeIn 0.3s;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .msg-correct-icon { font-size: 18px; }

    .msg-wrong {
      background: #FEE2E2;
      border-radius: 10px;
      padding: 7px 13px;
      margin-bottom: 10px;
      font-size: 13px;
      color: #991B1B;
      font-weight: 700;
      animation: shake 0.4s;
    }

    /* Input row */
    .input-row {
      display: flex;
      gap: 8px;
    }

    .input-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 6px;
      background: #F8FAFC;
      border-radius: 10px;
      padding: 0 12px;
      border: 2px solid #E2E8F0;
      transition: border-color 0.2s;
    }

    .input-wrapper:focus-within { border-color: #7C3AED; }

    .input-wrapper input {
      flex: 1;
      border: none;
      outline: none;
      background: transparent;
      padding: 9px 0;
      font-size: 15px;
      font-weight: 700;
      font-family: 'Nunito', sans-serif;
      color: #1E293B;
    }

    .input-wrapper .currency-suffix {
      font-size: 13px;
      color: #94A3B8;
      font-weight: 600;
    }

    .check-button {
      background: linear-gradient(135deg, #7C3AED, #6D28D9);
      color: white;
      border: none;
      border-radius: 10px;
      padding: 9px 18px;
      font-size: 14px;
      font-weight: 800;
      font-family: 'Nunito', sans-serif;
      cursor: pointer;
      transition: all 0.2s;
      white-space: nowrap;
    }

    .check-button:disabled {
      background: #CBD5E1;
      cursor: not-allowed;
    }

    /* Attempt indicator */
    .attempts-row {
      margin-top: 6px;
      display: flex;
      gap: 4px;
      align-items: center;
    }

    .attempt-dot {
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #E2E8F0;
      transition: all 0.3s;
    }

    .attempt-dot.used    { background: #FCA5A5; }
    .attempt-dot.current { background: var(--scale-color); }

    .attempts-label {
      font-size: 12px;
      color: #94A3B8;
      margin-left: 4px;
      font-weight: 600;
    }

    /* ─── Completion ───────────────────────────────────────────── */
    .completion {
      text-align: center;
      margin-top: 28px;
      animation: pop 0.5s;
    }

    .confetti-row {
      font-size: 32px;
      margin-bottom: 8px;
      position: relative;
      height: 40px;
      display: flex;
      justify-content: center;
    }

    .confetti-emoji {
      position: absolute;
      animation: confetti 1.2s ease-out;
    }

    .confetti-emoji:nth-child(1) { left: 20%; animation-delay: 0s; }
    .confetti-emoji:nth-child(2) { left: 35%; animation-delay: 0.15s; }
    .confetti-emoji:nth-child(3) { left: 50%; animation-delay: 0.30s; }
    .confetti-emoji:nth-child(4) { left: 65%; animation-delay: 0.45s; }
    .confetti-emoji:nth-child(5) { left: 80%; animation-delay: 0.60s; }

    .completion-card {
      background: white;
      border-radius: 20px;
      padding: 28px 32px;
      box-shadow: 0 8px 32px rgba(124, 58, 237, 0.12);
      display: inline-block;
    }

    .completion-icon { font-size: 42px; margin-bottom: 8px; }

    .completion-title {
      font-family: 'Fredoka One', cursive;
      font-size: 22px;
      color: #0F172A;
      margin: 0 0 8px;
    }

    .completion-text {
      color: #334155;
      font-size: 14px;
      line-height: 1.7;
      margin: 0 0 16px;
      max-width: 400px;
    }

    .rule-recap {
      background: #F5F3FF;
      border-radius: 14px;
      padding: 16px 20px;
      margin-bottom: 16px;
      text-align: left;
    }

    .rule-recap-title {
      font-weight: 800;
      color: #7C3AED;
      margin-bottom: 8px;
      font-size: 14px;
    }

    .rule-recap-list {
      font-size: 13px;
      color: #334155;
      line-height: 1.7;
    }

    .rule-recap-list strong { font-weight: 800; }

    .rule-recap-note {
      margin-top: 8px;
      font-style: italic;
      color: #64748B;
    }

    .stat-box {
      background: #F5F3FF;
      border-radius: 12px;
      padding: 12px 20px;
      display: inline-block;
    }

    .stat-label {
      font-size: 12px;
      font-weight: 700;
      color: #64748B;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .stat-value {
      font-size: 32px;
      font-weight: 900;
      color: #7C3AED;
    }

    .stat-divisor {
      font-size: 16px;
      color: #94A3B8;
    }

    /* Part B teaser */
    .next-teaser {
      text-align: center;
      margin-top: 24px;
      margin-bottom: 40px;
    }

    .next-teaser-card {
      background: white;
      border-radius: 16px;
      padding: 20px 24px;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
      display: inline-block;
      border: 2px dashed #C4B5FD;
    }

    .next-teaser-icon { font-size: 28px; margin-bottom: 6px; }

    .next-teaser-title {
      font-weight: 800;
      color: #7C3AED;
      font-size: 15px;
      margin-bottom: 4px;
    }

    .next-teaser-sub {
      font-size: 13px;
      color: #64748B;
    }

    /* ─── Animations ───────────────────────────────────────────── */
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20% { transform: translateX(-8px); }
      40% { transform: translateX(8px); }
      60% { transform: translateX(-5px); }
      80% { transform: translateX(5px); }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-6px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes pop {
      0% { transform: scale(0.8); opacity: 0; }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); opacity: 1; }
    }

    @keyframes confetti {
      0%   { transform: translateY(0) rotate(0); opacity: 1; }
      100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
    }
    .feedback {
    margin-bottom: 16px;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
    animation: fadeIn 0.3s ease;
  }

  .feedback.success {
    background: #D1FAE5;
    color: #065F46;
    border: 1px solid #6EE7B7;
  }

  .feedback.error {
    background: #FEE2E2;
    color: #991B1B;
    border: 1px solid #FCA5A5;
  }

  .feedback.hidden {
    display: none;
  }
  </style>
  @endpush
@include('finhero._intro_fit')
<div class="zv-task-head flex items-center justify-between pb-6">
    <h1 class="text-xl font-bold whitespace-nowrap">
        {{ __('finhero.task3.heading') }}
    </h1>
</div>
 <!-- ─── Loading screen ───────────────────────────────────────── -->
  <div id="loading-screen" class="screen">
    <div>
      <div id="loading-emoji">📊</div>
      <p id="loading-text">{{ __('finhero.task3.loading') }}</p>
    </div>
  </div>

  <!-- ─── Intro screen ─────────────────────────────────────────── -->
  <div id="intro-screen" class="screen hidden">
    <div class="intro-card">
      <div class="intro-icon">📊</div>
      <h1 class="intro-title">{{ __('finhero.task3.title') }}</h1>
      <p class="intro-description">
        {{ __('finhero.task3.intro') }}
      </p>

      <div class="rule-list">
        <div class="rule-row needs">
          <div class="rule-icon">🏠</div>
          <div>
            <div class="rule-title">{{ __('finhero.task3.rule_needs') }}</div>
            <div class="rule-sub">{{ __('finhero.task3.rule_needs_sub') }}</div>
          </div>
        </div>
        <div class="rule-row wants">
          <div class="rule-icon">🎮</div>
          <div>
            <div class="rule-title">{{ __('finhero.task3.rule_wants') }}</div>
            <div class="rule-sub">{{ __('finhero.task3.rule_wants_sub') }}</div>
          </div>
        </div>
        <div class="rule-row savings">
          <div class="rule-icon">🏦</div>
          <div>
            <div class="rule-title">{{ __('finhero.task3.rule_savings') }}</div>
            <div class="rule-sub">{{ __('finhero.task3.rule_savings_sub') }}</div>
          </div>
        </div>
      </div>

      <div class="intro-bank">
        <div class="intro-bank-title" id="intro-bank-title"></div>
        <div class="intro-bank-sub" id="intro-bank-sub"></div>
      </div>

      <div class="intro-points-note">
        <p>{!! __('finhero.task3.points_note') !!}</p>
      </div>

      <button class="start-button" id="start-button">{{ __('finhero.task3.start') }}</button>
    </div>
  </div>

  <!-- ─── Game screen ──────────────────────────────────────────── -->
  <div id="game-screen" class="hidden">
    <div class="container">
      <div class="header">
        <h1 class="game-title">{{ __('finhero.task3.game_title') }}</h1>
        <p class="game-subtitle">{{ __('finhero.task3.game_subtitle') }}</p>
      </div>

      <div class="stats-row">
        <div class="stat-pill points" id="stat-points">{{ __('finhero.common.points_of', ['points' => 0, 'total' => 30]) }}</div>
        <div class="stat-pill done" id="stat-done">{{ __('finhero.common.done_of', ['done' => 0, 'total' => 10]) }}</div>
      </div>

      <div class="reference">
        <div class="reference-row-1">
          <span class="reference-salary" id="reference-salary"></span>
        </div>
        <div class="reference-row-2">
          <span class="reference-actual" id="reference-actual"></span>
        </div>
      </div>

      <div class="progress-bar">
        <div class="progress-fill" id="progress-fill"></div>
      </div>
      <div id="extra-feedback" class="feedback hidden"></div>
      <br>
      <div class="questions" id="questions"></div>

      <div class="completion hidden" id="completion">
        <div class="confetti-row">
          <span class="confetti-emoji">🎉</span>
          <span class="confetti-emoji">📊</span>
          <span class="confetti-emoji">💰</span>
          <span class="confetti-emoji">🏆</span>
          <span class="confetti-emoji">✨</span>
        </div>
        <div class="completion-card">
          <div class="completion-icon">📊</div>
          <h2 class="completion-title">{{ __('finhero.task3.complete_title') }}</h2>
          <p class="completion-text" id="completion-text"></p>

          <div class="rule-recap">
            <div class="rule-recap-title">{{ __('finhero.task3.recap_title') }}</div>
            <div class="rule-recap-list">
              <div>{!! __('finhero.task3.recap_needs') !!}</div>
              <div>{!! __('finhero.task3.recap_wants') !!}</div>
              <div>{!! __('finhero.task3.recap_savings') !!}</div>
              <div class="rule-recap-note">{{ __('finhero.task3.recap_note') }}</div>
            </div>
          </div>

          <div class="stat-box">
            <div class="stat-label">{{ __('finhero.common.points_label') }}</div>
            <div class="stat-value">
              <span id="final-points">0</span>
              <span class="stat-divisor">/ 30</span>
            </div>
          </div>
        </div>

        <div class="next-teaser">
          <div class="next-teaser-card">
            <div class="next-teaser-icon">🔜</div>
            <div class="next-teaser-title">{{ __('finhero.task3.next_title') }}</div>
            <div class="next-teaser-sub">{{ __('finhero.task3.next_sub') }}</div>
          </div>
        </div>

        @include('finhero._finished')
      </div>
    </div>
  </div>
 @endsection
@push('scripts')
  @include('finhero._i18n')
  <script>
    // Texts in the student's language (lang/<language>/finhero.php).
    const T = {{ \Illuminate\Support\Js::from(['common' => __('finhero.common'), 'levels' => __('finhero.income_levels'), 'task' => __('finhero.task3')]) }};

    // ───────────────────────────────────────────────────────────────
    // CONFIG (same as JSX original)
    // ───────────────────────────────────────────────────────────────

    // ╔════════════════════════════════════════════════════════════╗
    // ║  🔌 IT TEAM: REPLACE THIS WITH A REAL API CALL             ║
    // ║                                                            ║
    // ║  Example:                                                  ║
    // ║    async function getStudentSalary() {                     ║
    // ║      const r = await fetch('/api/current-student/salary'); ║
    // ║      const d = await r.json();                             ║
    // ║      return d.salary;                                      ║
    // ║    }                                                       ║
    // ║                                                            ║
    // ║  Currently returns one of 4 random salaries for testing.   ║
    // ╚════════════════════════════════════════════════════════════╝
    /*async function getStudentSalary() {
      const salaries = [2000, 4000, 6000, 9000];
      return salaries[Math.floor(Math.random() * salaries.length)];
    }*/
    async function getStudentSalary() {
        return {{ $salary }};
    }
    const SCALE_STYLES = {
      2000: { label: T.levels.low.label,       emoji: "🟢", color: "#059669", bg: "#D1FAE5", border: "#6EE7B7" },
      4000: { label: T.levels.middle.label,    emoji: "🟡", color: "#CA8A04", bg: "#FEF9C3", border: "#FDE047" },
      6000: { label: T.levels.upper_mid.label, emoji: "🟠", color: "#EA580C", bg: "#FED7AA", border: "#FB923C" },
      9000: { label: T.levels.high.label,      emoji: "🟣", color: "#7C3AED", bg: "#EDE9FE", border: "#C4B5FD" },
    };

    const ACTUAL_SPENDING = {
      2000: { needs: 1100, wants: 500,  savings: 400  },
      4000: { needs: 2000, wants: 1200, savings: 800  },
      6000: { needs: 2700, wants: 1800, savings: 1500 },
      9000: { needs: 3600, wants: 2700, savings: 2700 },
    };

    function getStyle(salary) {
      if (salary <= 2500) return { ...SCALE_STYLES[2000], key: 2000 };
      if (salary <= 5000) return { ...SCALE_STYLES[4000], key: 4000 };
      if (salary <= 7500) return { ...SCALE_STYLES[6000], key: 6000 };
      return { ...SCALE_STYLES[9000], key: 9000 };
    }

    function buildQuestions(salary, actual) {
      const needs50    = salary / 2;
      const wants30    = (salary / 10) * 3;
      const savings20  = salary / 5;
      const ten        = salary / 10;
      const seventyFive= (salary / 4) * 3;
      const needsPct   = Math.round((actual.needs / salary) * 100);
      const wantsPct   = Math.round((actual.wants / salary) * 100);
      const savingsPct = Math.round((actual.savings / salary) * 100);

      const fmt = n => zvNum(n);
      const dec = (n, d) => zvNum(n, d);
      const zeds = T.common.zeds;
      const pctOf = (pct) => zvT(T.task.pct_of, { pct: pct, salary: fmt(salary) });
      const share = (part) => (part / salary).toFixed(2);

      return [
        { id: 1,  question: pctOf(50), answer: needs50, answerType: "number",
          hint: zvT(T.task.q1_hint, { salary: fmt(salary) }),
          steps: `${fmt(salary)} ÷ 2 = ${fmt(needs50)} ${zeds}`,
          tag: T.task.q1_tag },
        { id: 2,  question: pctOf(30), answer: wants30, answerType: "number",
          hint: zvT(T.task.q2_hint, { salary: fmt(salary), ten: fmt(ten) }),
          steps: `${fmt(salary)} ÷ 10 = ${fmt(ten)}\n${fmt(ten)} × 3 = ${fmt(wants30)} ${zeds}`,
          tag: T.task.q2_tag },
        { id: 3,  question: pctOf(20), answer: savings20, answerType: "number",
          hint: zvT(T.task.q3_hint, { salary: fmt(salary) }),
          steps: `${fmt(salary)} ÷ 5 = ${fmt(savings20)} ${zeds}`,
          tag: T.task.q3_tag },
        { id: 4,  question: zvT(T.task.q4, { first: fmt(needs50), second: fmt(wants30), third: fmt(savings20) }), answer: salary, answerType: "number",
          hint: T.task.q4_hint,
          steps: `${fmt(needs50)} + ${fmt(wants30)} = ${fmt(needs50 + wants30)}\n${fmt(needs50 + wants30)} + ${fmt(savings20)} = ${fmt(salary)} ${zeds} ✅`,
          tag: T.task.q4_tag },
        { id: 5,  question: pctOf(10), answer: ten, answerType: "number",
          hint: zvT(T.task.q5_hint, { salary: fmt(salary) }),
          steps: `${fmt(salary)} ÷ 10 = ${fmt(ten)} ${zeds}`,
          tag: T.task.q5_tag },
        { id: 6,  question: pctOf(75), answer: seventyFive, answerType: "number",
          hint: zvT(T.task.q6_hint, { salary: fmt(salary), quarter: fmt(salary/4) }),
          steps: `${fmt(salary)} ÷ 4 = ${fmt(salary/4)}\n${fmt(salary/4)} × 3 = ${fmt(seventyFive)} ${zeds}`,
          tag: T.task.q6_tag },
        { id: 7,  question: zvT(T.task.q7, { amount: fmt(actual.needs), salary: fmt(salary) }), answer: needsPct, answerType: "percent",
          hint: zvT(T.task.q7_hint, { amount: fmt(actual.needs), salary: fmt(salary) }),
          steps: `${fmt(actual.needs)} ÷ ${fmt(salary)} = ${dec(share(actual.needs), 2)}\n${dec(share(actual.needs), 2)} × 100 = ${needsPct}%`,
          tag: T.task.q7_tag, tolerance: 1 },
        { id: 8,  question: zvT(T.task.q8, { amount: fmt(actual.wants), salary: fmt(salary) }), answer: wantsPct, answerType: "percent",
          hint: zvT(T.task.q8_hint, { amount: fmt(actual.wants), salary: fmt(salary) }),
          steps: `${fmt(actual.wants)} ÷ ${fmt(salary)} = ${dec(share(actual.wants), 2)}\n${dec(share(actual.wants), 2)} × 100 = ${wantsPct}%`,
          tag: T.task.q8_tag, tolerance: 1 },
        { id: 9,  question: zvT(T.task.q9, { amount: fmt(actual.savings), salary: fmt(salary) }), answer: savingsPct, answerType: "percent",
          hint: zvT(T.task.q9_hint, { amount: fmt(actual.savings), salary: fmt(salary) }),
          steps: `${fmt(actual.savings)} ÷ ${fmt(salary)} = ${dec(share(actual.savings), 2)}\n${dec(share(actual.savings), 2)} × 100 = ${savingsPct}%`,
          tag: T.task.q9_tag, tolerance: 1 },
        { id: 10, question: zvT(T.task.q10, { amount: fmt(needs50), salary: fmt(salary) }), answer: 50, answerType: "percent",
          hint: zvT(T.task.q10_hint, { amount: fmt(needs50), salary: fmt(salary) }),
          steps: `${fmt(needs50)} ÷ ${fmt(salary)} = ${dec(0.5, 1)}\n${dec(0.5, 1)} × 100 = 50%`,
          tag: T.task.q10_tag },
      ];
    }

    // ───────────────────────────────────────────────────────────────
    // STATE
    // ───────────────────────────────────────────────────────────────
    // Zedville_FinHero_Point_Values_v1.pdf §2 "The Approach System" - same
    // tier table as app/Services/FinheroApproachSystem.php (server-side is
    // the actual source of truth for points awarded).
    const APPROACH_TIERS = [
      { maxWrongAttempts: 2,        points: 3, hint: "none" },
      { maxWrongAttempts: 2,        points: 2, hint: "light" },
      { maxWrongAttempts: 2,        points: 1, hint: "full" },
      { maxWrongAttempts: Infinity, points: 0, hint: "full" },
    ];

    const redactFinalStep = zvRedactLastStep;

    const state = {
      salary: null,
      scale: null,
      actual: null,
      questions: [],
      qState: {},   // { [id]: { tier: 0, wrongInTier: 0, status: null, earned: null, input: '' } }
      scores: {},
      firstTryCount: 0,
    };

    // §6 "Open Items": the doc registers Task 3 as 11 questions (Part A's
    // 12 + Part B's 21 = 33 pts), but only 10 question definitions were
    // ever built here (see buildQuestions() above) - the 11th was never
    // added. Not invented here; true achievable max is 10 x 3 = 30 until
    // that question is supplied. finhero_activity_defaults.task_3 is set
    // to 30 to match, not the doc's 33, to avoid overstating the monthly
    // total available.
    const TOTAL_QS = 10;
    const TASK3_TOTAL_POINTS = 30;

    // ───────────────────────────────────────────────────────────────
    // DOM REFS
    // ───────────────────────────────────────────────────────────────
    const $ = (id) => document.getElementById(id);

    // ───────────────────────────────────────────────────────────────
    // INITIALIZE
    // ───────────────────────────────────────────────────────────────
    (async function init() {
      state.salary    = await getStudentSalary();
      state.scale     = getStyle(state.salary);
      state.actual    = ACTUAL_SPENDING[state.scale.key];
      state.questions = buildQuestions(state.salary, state.actual);

      state.questions.forEach(q => {
        state.qState[q.id] = { tier: 0, wrongInTier: 0, status: null, earned: null, input: "" };
      });

      // Apply scale CSS variables to document
      document.documentElement.style.setProperty("--scale-color",  state.scale.color);
      document.documentElement.style.setProperty("--scale-bg",     state.scale.bg);
      document.documentElement.style.setProperty("--scale-border", state.scale.border);

      buildIntroBank();
      buildReference();
      buildQuestionsUI();

      $("loading-screen").classList.add("hidden");
      $("intro-screen").classList.remove("hidden");
    })();

    $("start-button").addEventListener("click", () => {
      $("intro-screen").classList.add("hidden");
      $("game-screen").classList.remove("hidden");
    });

    // ───────────────────────────────────────────────────────────────
    // BUILD INTRO BANK
    // ───────────────────────────────────────────────────────────────
    function buildIntroBank() {
      const s = state.scale;
      $("intro-bank-title").textContent =
        zvT(T.task.salary_title, { emoji: s.emoji, salary: zvMoney(state.salary), level: s.label });
      $("intro-bank-sub").textContent =
        zvT(T.task.statement_line, { needs: zvNum(state.actual.needs), wants: zvNum(state.actual.wants), savings: zvNum(state.actual.savings) });
    }

    // ───────────────────────────────────────────────────────────────
    // BUILD STICKY REFERENCE
    // ───────────────────────────────────────────────────────────────
    function buildReference() {
      const s = state.scale;
      $("reference-salary").textContent =
        zvT(T.task.ref_salary, { emoji: s.emoji, salary: zvMoney(state.salary) });
      $("reference-actual").textContent =
        zvT(T.task.ref_actual, { needs: zvNum(state.actual.needs), wants: zvNum(state.actual.wants), savings: zvNum(state.actual.savings) });
    }

    // ───────────────────────────────────────────────────────────────
    // BUILD QUESTION CARDS
    // ───────────────────────────────────────────────────────────────
    function buildQuestionsUI() {
      const container = $("questions");
      container.innerHTML = "";

      state.questions.forEach(q => {
        const isPercent = q.answerType === "percent";
        const unit = isPercent ? "%" : T.common.zeds;
        const placeholder = isPercent ? T.common.type_percentage : T.common.type_answer;

        const card = document.createElement("div");
        card.className = "question-card";
        card.dataset.qid = q.id;
        card.innerHTML = `
          <div class="question-header">
            <div class="question-number">${q.id}</div>
            <div style="flex:1;">
              <div class="question-tag">${q.tag}</div>
              <div class="question-text">${q.question}</div>
            </div>
          </div>
          <div class="msg-hint hidden"></div>
          <div class="msg-revealed hidden"></div>
          <div class="msg-correct hidden"></div>
          <div class="msg-wrong hidden">${T.common.not_quite}</div>
          <div class="input-row">
            <div class="input-wrapper">
              <input type="text" inputmode="numeric" placeholder="${placeholder}" />
              <span class="currency-suffix">${unit}</span>
            </div>
            <button class="check-button" disabled>${T.common.check}</button>
          </div>
          <div class="attempts-row">
            <div class="attempt-dot current"></div>
            <div class="attempt-dot"></div>
            <div class="attempt-dot"></div>
            <span class="attempts-label">${zvT(T.common.approach_points_many, { n: 1, total: APPROACH_TIERS.length, points: APPROACH_TIERS[0].points })}</span>
          </div>
        `;
        container.appendChild(card);

        const input = card.querySelector("input");
        const checkBtn = card.querySelector(".check-button");

        input.addEventListener("input", (e) => {
          const cleaned = e.target.value.replace(/[^0-9,.\-%]/g, "");
          e.target.value = cleaned;
          state.qState[q.id].input = cleaned;
          checkBtn.disabled = !cleaned.trim();
        });

        input.addEventListener("keydown", (e) => {
          if (e.key === "Enter" && !checkBtn.disabled) checkAnswer(q.id);
        });

        checkBtn.addEventListener("click", () => checkAnswer(q.id));
      });
    }

    // ───────────────────────────────────────────────────────────────
    // CHECK ANSWER LOGIC
    // ───────────────────────────────────────────────────────────────
    function checkAnswer(qid) {
      const q  = state.questions.find(x => x.id === qid);
      const qs = state.qState[qid];
      const num = zvParseNumber(qs.input);
      if (isNaN(num)) return;

      const tol = q.tolerance || 0;
      const correct = Math.abs(num - q.answer) <= tol;

      if (correct) {
        const pts = APPROACH_TIERS[qs.tier].points;
        if (qs.tier === 0) state.firstTryCount += 1;
        qs.earned = pts;
        qs.status = "correct";
        state.scores[qid] = pts;

        if (pts > 0) {
          fetch("{{ route('finhero.save-points') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                source_key: 'task_3_question_' + qid,
                approach: qs.tier
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data.success === false) {
              const extraFeedback = document.getElementById("extra-feedback");
              extraFeedback.className = "feedback error";
              extraFeedback.textContent = T.common.already_finished;
              setTimeout(() => {
                extraFeedback.classList.remove("show");
                extraFeedback.classList.add("hide");
                setTimeout(() => {
                    extraFeedback.textContent = "";
                    extraFeedback.className = "";
                }, 500);
              }, 3000);
            }
          });
        }

        renderQuestion(qid);
        updateProgress();
        checkAllDone();
      } else {
        qs.wrongInTier += 1;
        qs.status = "wrong";
        qs.input = "";
        renderQuestion(qid);

        setTimeout(() => {
          const tierMax = APPROACH_TIERS[qs.tier].maxWrongAttempts;
          if (qs.wrongInTier >= tierMax && qs.tier < APPROACH_TIERS.length - 1) {
            qs.tier += 1;
            qs.wrongInTier = 0;
          }
          qs.status = null;
          renderQuestion(qid);
        }, 1200);
      }
    }

    // ───────────────────────────────────────────────────────────────
    // RENDER A SINGLE QUESTION CARD
    // ───────────────────────────────────────────────────────────────
    function renderQuestion(qid) {
      const q  = state.questions.find(x => x.id === qid);
      const qs = state.qState[qid];
      const card = document.querySelector(`.question-card[data-qid="${qid}"]`);
      if (!card) return;

      const isPercent = q.answerType === "percent";
      const done = qs.status === "correct";
      const tier = APPROACH_TIERS[qs.tier];

      // Card-level state
      card.classList.toggle("done-correct",  done);

      // Number cell
      const num = card.querySelector(".question-number");
      num.textContent = done ? "✓" : q.id;

      // Light hint (tier 1+): the short formula, no computed numbers worked out
      const hintEl = card.querySelector(".msg-hint");
      if (tier.hint === "light" && !done) {
        hintEl.innerHTML = `💡 <strong>${T.common.hint}</strong> ${q.hint}`;
        hintEl.classList.remove("hidden");
      } else {
        hintEl.classList.add("hidden");
      }

      // Fuller hint (tier 2+): setup shown step by step, final answer never
      // auto-revealed - doc §2 "Beyond Third Approach... answer is never
      // auto-revealed." Stays visible at tier 3 too, unlimited attempts.
      const revEl = card.querySelector(".msg-revealed");
      if (tier.hint === "full" && !done) {
        const stepsHtml = redactFinalStep(q.steps).split("\n")
          .map(line => `<div class="msg-revealed-step">${line}</div>`).join("");
        revEl.innerHTML = `
          <div class="msg-revealed-title">${T.common.setup_title}</div>
          ${stepsHtml}
        `;
        revEl.classList.remove("hidden");
      } else {
        revEl.classList.add("hidden");
      }

      // Correct
      const corrEl = card.querySelector(".msg-correct");
      if (done) {
        const answerSuffix = isPercent ? "%" : " " + T.common.zeds;
        const ptsText = qs.earned > 0 ? zvT(qs.earned === 1 ? T.common.plus_points_one : T.common.plus_points_many, { points: qs.earned }) : '';
        corrEl.innerHTML = `
          <span class="msg-correct-icon">🎉</span>
          <span>${zvT(T.common.correct_answer, { answer: zvNum(q.answer) + answerSuffix })}${ptsText}</span>
        `;
        corrEl.classList.remove("hidden");
      } else {
        corrEl.classList.add("hidden");
      }

      // Wrong (transient)
      const wrongEl = card.querySelector(".msg-wrong");
      wrongEl.classList.toggle("hidden", qs.status !== "wrong");

      // Input row — visible only when not done and not in transient wrong state
      const inputRow = card.querySelector(".input-row");
      const showInput = !done && qs.status !== "wrong";
      inputRow.classList.toggle("hidden", !showInput);

      // Reset input field if needed
      const inputEl = card.querySelector("input");
      const checkBtn = card.querySelector(".check-button");
      if (showInput) {
        inputEl.value = qs.input;
        checkBtn.disabled = !qs.input.trim();
      } else {
        inputEl.value = "";
      }

      // Attempt indicator - hidden once unlimited (tier 3, no points left)
      const attemptsRow = card.querySelector(".attempts-row");
      attemptsRow.classList.toggle("hidden", done || tier.maxWrongAttempts === Infinity);

      if (!done && tier.maxWrongAttempts !== Infinity) {
        const dots = card.querySelectorAll(".attempt-dot");
        dots.forEach((d, i) => {
          d.classList.toggle("hidden",  i >= tier.maxWrongAttempts);
          d.classList.toggle("used",    i < qs.wrongInTier);
          d.classList.toggle("current", i === qs.wrongInTier);
        });

        const label = card.querySelector(".attempts-label");
        label.textContent = zvT(tier.points === 1 ? T.common.approach_points_one : T.common.approach_points_many, { n: qs.tier + 1, total: APPROACH_TIERS.length, points: tier.points });
      }
    }

    // ───────────────────────────────────────────────────────────────
    // UPDATE PROGRESS
    // ───────────────────────────────────────────────────────────────
    function updateProgress() {
      const completedCount = Object.keys(state.scores).length;
      const totalPoints    = Object.values(state.scores).reduce((a, b) => a + b, 0);

      $("stat-points").textContent = zvT(T.common.points_of, { points: totalPoints, total: TASK3_TOTAL_POINTS });
      $("stat-done").textContent   = zvT(T.common.done_of, { done: completedCount, total: TOTAL_QS });
      $("progress-fill").style.width = ((completedCount / TOTAL_QS) * 100) + "%";
    }

    // ───────────────────────────────────────────────────────────────
    // CHECK ALL DONE — show completion
    // ───────────────────────────────────────────────────────────────
    function checkAllDone() {
      const completedCount = Object.keys(state.scores).length;
      if (completedCount < TOTAL_QS) return;

      // Tells the server this task is finished (blackboard + Engagement badge).
      fetch("{{ route('finhero.task-complete') }}", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: JSON.stringify({ activity_key: @json($activityKey) })
      }).catch(() => {});

      const totalPoints = Object.values(state.scores).reduce((a, b) => a + b, 0);
      let message;
      if (totalPoints >= 24)      message = T.task.msg_amazing;
      else if (totalPoints >= 15) message = T.task.msg_great;
      else                        message = T.task.msg_keep;

      $("completion-text").textContent = message;
      $("final-points").textContent    = totalPoints;
      $("completion").classList.remove("hidden");
    }
  </script>
  @endpush