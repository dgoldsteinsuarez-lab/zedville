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
      font-size: 11px;
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
      font-size: 11px;
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
      font-size: 11px;
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
      font-size: 11px;
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
<div class="flex items-center justify-between pb-6">
    <h1 class="text-xl font-bold whitespace-nowrap">
        Task 3
    </h1>

    <a href="{{ url()->previous() }}"
       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
        ← Back
    </a>
</div>
  @endpush
 <!-- ─── Loading screen ───────────────────────────────────────── -->
  <div id="loading-screen" class="screen">
    <div>
      <div id="loading-emoji">📊</div>
      <p id="loading-text">Loading the 50/30/20 Rule...</p>
    </div>
  </div>

  <!-- ─── Intro screen ─────────────────────────────────────────── -->
  <div id="intro-screen" class="screen hidden">
    <div class="intro-card">
      <div class="intro-icon">📊</div>
      <h1 class="intro-title">The 50/30/20 Budget Rule</h1>
      <p class="intro-description">
        The most popular budgeting rule in personal finance. Let's learn percentages and apply them to YOUR salary!
      </p>

      <div class="rule-list">
        <div class="rule-row needs">
          <div class="rule-icon">🏠</div>
          <div>
            <div class="rule-title">50% → NEEDS</div>
            <div class="rule-sub">Rent, groceries, utilities, transport — the essentials</div>
          </div>
        </div>
        <div class="rule-row wants">
          <div class="rule-icon">🎮</div>
          <div>
            <div class="rule-title">30% → WANTS</div>
            <div class="rule-sub">Dining out, streaming, hobbies — the fun stuff</div>
          </div>
        </div>
        <div class="rule-row savings">
          <div class="rule-icon">🏦</div>
          <div>
            <div class="rule-title">20% → SAVINGS</div>
            <div class="rule-sub">Emergency fund, future goals — paying your future self</div>
          </div>
        </div>
      </div>

      <div class="intro-bank">
        <div class="intro-bank-title" id="intro-bank-title"></div>
        <div class="intro-bank-sub" id="intro-bank-sub"></div>
      </div>

      <div class="intro-points-note">
        <p><strong>Finhero Badge Points: up to 30</strong> — up to 3 points per question. Get it right on your first approach for 3 points; each time you use up your tries, the hint gets stronger and the points available go down: 2, then 1, then 0 — but you can always keep trying. You'll calculate percentages of your salary and figure out what percentage your actual spending is.</p>
      </div>

      <button class="start-button" id="start-button">Let's Go! 📊</button>
    </div>
  </div>

  <!-- ─── Game screen ──────────────────────────────────────────── -->
  <div id="game-screen" class="hidden">
    <div class="container">
      <div class="header">
        <h1 class="game-title">Task 3: The 50/30/20 Rule</h1>
        <p class="game-subtitle">Learn percentages and apply them to your budget</p>
      </div>

      <div class="stats-row">
        <div class="stat-pill points" id="stat-points">🏆 Finhero Badge Points: 0 / 30</div>
        <div class="stat-pill done" id="stat-done">✅ 0 / 10 done</div>
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
          <h2 class="completion-title">Percentage Pro!</h2>
          <p class="completion-text" id="completion-text"></p>

          <div class="rule-recap">
            <div class="rule-recap-title">Remember the 50/30/20 Rule:</div>
            <div class="rule-recap-list">
              <div>🏠 <strong>50% for Needs</strong> — the essentials that keep you safe and working</div>
              <div>🎮 <strong>30% for Wants</strong> — the things that make life enjoyable</div>
              <div>🏦 <strong>20% for Savings</strong> — your future self will thank you!</div>
              <div class="rule-recap-note">This is a guideline, not a strict law. The important thing is being AWARE of where your money goes!</div>
            </div>
          </div>

          <div class="stat-box">
            <div class="stat-label">Finhero Badge Points</div>
            <div class="stat-value">
              <span id="final-points">0</span>
              <span class="stat-divisor">/ 30</span>
            </div>
          </div>
        </div>

        <div class="next-teaser">
          <div class="next-teaser-card">
            <div class="next-teaser-icon">🔜</div>
            <div class="next-teaser-title">Up Next: Budget Reality Check</div>
            <div class="next-teaser-sub">Compare your actual spending against the 50/30/20 ideal and make a budget plan!</div>
          </div>
        </div>
      </div>
    </div>
  </div>
 @endsection
@push('scripts')
  <script>
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
      2000: { label: "Low Income",          emoji: "🟢", color: "#059669", bg: "#D1FAE5", border: "#6EE7B7" },
      4000: { label: "Middle Income",       emoji: "🟡", color: "#CA8A04", bg: "#FEF9C3", border: "#FDE047" },
      6000: { label: "Upper-Middle Income", emoji: "🟠", color: "#EA580C", bg: "#FED7AA", border: "#FB923C" },
      9000: { label: "High Income",         emoji: "🟣", color: "#7C3AED", bg: "#EDE9FE", border: "#C4B5FD" },
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

      const fmt = n => n.toLocaleString();

      return [
        { id: 1,  question: `50% of ${fmt(salary)} zeds = ?`, answer: needs50, answerType: "number",
          hint: `50% means half. Divide your salary by 2: ${fmt(salary)} ÷ 2`,
          steps: `${fmt(salary)} ÷ 2 = ${fmt(needs50)} zeds`,
          tag: "🏠 50% — Needs budget" },
        { id: 2,  question: `30% of ${fmt(salary)} zeds = ?`, answer: wants30, answerType: "number",
          hint: `First find 10% (divide by 10), then multiply by 3: ${fmt(salary)} ÷ 10 = ${fmt(ten)}, then × 3`,
          steps: `${fmt(salary)} ÷ 10 = ${fmt(ten)}\n${fmt(ten)} × 3 = ${fmt(wants30)} zeds`,
          tag: "🎮 30% — Wants budget" },
        { id: 3,  question: `20% of ${fmt(salary)} zeds = ?`, answer: savings20, answerType: "number",
          hint: `20% means one fifth. Divide your salary by 5: ${fmt(salary)} ÷ 5`,
          steps: `${fmt(salary)} ÷ 5 = ${fmt(savings20)} zeds`,
          tag: "🏦 20% — Savings target" },
        { id: 4,  question: `Add them up: ${fmt(needs50)} + ${fmt(wants30)} + ${fmt(savings20)} = ?`, answer: salary, answerType: "number",
          hint: `Add all three amounts. They should equal your full salary!`,
          steps: `${fmt(needs50)} + ${fmt(wants30)} = ${fmt(needs50 + wants30)}\n${fmt(needs50 + wants30)} + ${fmt(savings20)} = ${fmt(salary)} zeds ✅`,
          tag: "✅ Balance check" },
        { id: 5,  question: `10% of ${fmt(salary)} zeds = ?`, answer: ten, answerType: "number",
          hint: `10% means one tenth. Just move the comma one place: ${fmt(salary)} ÷ 10`,
          steps: `${fmt(salary)} ÷ 10 = ${fmt(ten)} zeds`,
          tag: "🔟 10% — The building block" },
        { id: 6,  question: `75% of ${fmt(salary)} zeds = ?`, answer: seventyFive, answerType: "number",
          hint: `75% is three quarters. Find one quarter first (÷ 4), then × 3: ${fmt(salary)} ÷ 4 = ${fmt(salary/4)}, then × 3`,
          steps: `${fmt(salary)} ÷ 4 = ${fmt(salary/4)}\n${fmt(salary/4)} × 3 = ${fmt(seventyFive)} zeds`,
          tag: "📐 75% — Three quarters" },
        { id: 7,  question: `Your Needs spending is ${fmt(actual.needs)} out of ${fmt(salary)} zeds. What percentage is that?`, answer: needsPct, answerType: "percent",
          hint: `Divide Needs by your Salary, then × 100:\n(${fmt(actual.needs)} ÷ ${fmt(salary)}) × 100`,
          steps: `${fmt(actual.needs)} ÷ ${fmt(salary)} = ${(actual.needs/salary).toFixed(2)}\n${(actual.needs/salary).toFixed(2)} × 100 = ${needsPct}%`,
          tag: "🏠 What % are your Needs?", tolerance: 1 },
        { id: 8,  question: `Your Wants spending is ${fmt(actual.wants)} out of ${fmt(salary)} zeds. What percentage is that?`, answer: wantsPct, answerType: "percent",
          hint: `Divide Wants by your Salary, then × 100:\n(${fmt(actual.wants)} ÷ ${fmt(salary)}) × 100`,
          steps: `${fmt(actual.wants)} ÷ ${fmt(salary)} = ${(actual.wants/salary).toFixed(2)}\n${(actual.wants/salary).toFixed(2)} × 100 = ${wantsPct}%`,
          tag: "🎮 What % are your Wants?", tolerance: 1 },
        { id: 9,  question: `Your Savings is ${fmt(actual.savings)} out of ${fmt(salary)} zeds. What percentage is that?`, answer: savingsPct, answerType: "percent",
          hint: `Divide Savings by your Salary, then × 100:\n(${fmt(actual.savings)} ÷ ${fmt(salary)}) × 100`,
          steps: `${fmt(actual.savings)} ÷ ${fmt(salary)} = ${(actual.savings/salary).toFixed(2)}\n${(actual.savings/salary).toFixed(2)} × 100 = ${savingsPct}%`,
          tag: "🏦 What % are your Savings?", tolerance: 1 },
        { id: 10, question: `${fmt(needs50)} is what percentage of ${fmt(salary)}?`, answer: 50, answerType: "percent",
          hint: `Divide the first number by the second, then × 100: ${fmt(needs50)} ÷ ${fmt(salary)} = ?`,
          steps: `${fmt(needs50)} ÷ ${fmt(salary)} = 0.5\n0.5 × 100 = 50%`,
          tag: "🧠 Reverse percentage" },
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

    function redactFinalStep(stepsText) {
      const lines = stepsText.split("\n");
      const last = lines[lines.length - 1].replace(/=\s*[\d.,]+%?\s*$/, "= ?");
      return [...lines.slice(0, -1), last].join("\n");
    }

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
        `${s.emoji} Your Salary: ${state.salary.toLocaleString()} zeds/month (${s.label})`;
      $("intro-bank-sub").textContent =
        `Bank Statement: Needs ${state.actual.needs.toLocaleString()} | Wants ${state.actual.wants.toLocaleString()} | Savings ${state.actual.savings.toLocaleString()}`;
    }

    // ───────────────────────────────────────────────────────────────
    // BUILD STICKY REFERENCE
    // ───────────────────────────────────────────────────────────────
    function buildReference() {
      const s = state.scale;
      $("reference-salary").textContent =
        `${s.emoji} Salary: ${state.salary.toLocaleString()} zeds`;
      $("reference-actual").textContent =
        `Actual: 🏠 Needs ${state.actual.needs.toLocaleString()} | 🎮 Wants ${state.actual.wants.toLocaleString()} | 🏦 Savings ${state.actual.savings.toLocaleString()}`;
    }

    // ───────────────────────────────────────────────────────────────
    // BUILD QUESTION CARDS
    // ───────────────────────────────────────────────────────────────
    function buildQuestionsUI() {
      const container = $("questions");
      container.innerHTML = "";

      state.questions.forEach(q => {
        const isPercent = q.answerType === "percent";
        const unit = isPercent ? "%" : "zeds";
        const placeholder = isPercent ? "Type the percentage..." : "Type your answer...";

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
          <div class="msg-wrong hidden">Not quite right, try again! ✨</div>
          <div class="input-row">
            <div class="input-wrapper">
              <input type="text" inputmode="numeric" placeholder="${placeholder}" />
              <span class="currency-suffix">${unit}</span>
            </div>
            <button class="check-button" disabled>Check</button>
          </div>
          <div class="attempts-row">
            <div class="attempt-dot current"></div>
            <div class="attempt-dot"></div>
            <div class="attempt-dot"></div>
            <span class="attempts-label">First try — 1 point!</span>
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
      const cleaned = qs.input.replace(/[%,\s]/g, "");
      const num = parseFloat(cleaned);
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
              extraFeedback.textContent = "You’ve already finished this task, but feel free to give it another go!";
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
        hintEl.innerHTML = `💡 <strong>Hint:</strong> ${q.hint}`;
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
          <div class="msg-revealed-title">📝 Here's the setup — you finish the last step:</div>
          ${stepsHtml}
        `;
        revEl.classList.remove("hidden");
      } else {
        revEl.classList.add("hidden");
      }

      // Correct
      const corrEl = card.querySelector(".msg-correct");
      if (done) {
        const answerSuffix = isPercent ? "%" : " zeds";
        const ptsText = qs.earned > 0 ? ` +${qs.earned} Finhero Badge Point${qs.earned === 1 ? '' : 's'}!` : '';
        corrEl.innerHTML = `
          <span class="msg-correct-icon">🎉</span>
          <span>${q.answer.toLocaleString()}${answerSuffix} — Correct!${ptsText}</span>
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
        label.textContent = `Approach ${qs.tier + 1} of ${APPROACH_TIERS.length} — ${tier.points} point${tier.points === 1 ? '' : 's'} if correct`;
      }
    }

    // ───────────────────────────────────────────────────────────────
    // UPDATE PROGRESS
    // ───────────────────────────────────────────────────────────────
    function updateProgress() {
      const completedCount = Object.keys(state.scores).length;
      const totalPoints    = Object.values(state.scores).reduce((a, b) => a + b, 0);

      $("stat-points").textContent = `🏆 Finhero Badge Points: ${totalPoints} / ${TASK3_TOTAL_POINTS}`;
      $("stat-done").textContent   = `✅ ${completedCount} / ${TOTAL_QS} done`;
      $("progress-fill").style.width = ((completedCount / TOTAL_QS) * 100) + "%";
    }

    // ───────────────────────────────────────────────────────────────
    // CHECK ALL DONE — show completion
    // ───────────────────────────────────────────────────────────────
    function checkAllDone() {
      const completedCount = Object.keys(state.scores).length;
      if (completedCount < TOTAL_QS) return;

      const totalPoints = Object.values(state.scores).reduce((a, b) => a + b, 0);
      let message;
      if (totalPoints >= 24)      message = "Amazing! You really understand percentages and the 50/30/20 rule! 🌟";
      else if (totalPoints >= 15) message = "Great work! You've got a solid grip on budget percentages!";
      else                        message = "Now you know how percentages work with real money! Review the hints to master it.";

      $("completion-text").textContent = message;
      $("final-points").textContent    = totalPoints;
      $("completion").classList.remove("hidden");
    }
  </script>
  @endpush