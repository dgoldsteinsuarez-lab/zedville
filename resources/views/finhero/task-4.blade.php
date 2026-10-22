@extends('layouts.profile')

@section('title', 'Budget Reality Check')

@section('content')
@push('styles')
<style>
    /* ─── Base & layout ──────────────────────────────────────────── */
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Nunito', sans-serif;
      min-height: 100vh;
      background: linear-gradient(160deg, #F0FDFA 0%, #ECFDF5 40%, #F0FDF4 100%);
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
    #loading-text { color: #0D9488; font-weight: 700; font-size: 18px; text-align: center; }

    /* ─── Intro screen ─────────────────────────────────────────── */
    .intro-card {
      max-width: 580px;
      background: white;
      border-radius: 24px;
      padding: 36px 32px;
      box-shadow: 0 20px 60px rgba(13, 148, 136, 0.12);
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

    .intro-bank {
      background: #F0FDFA;
      border-radius: 16px;
      padding: 16px 20px;
      margin-bottom: 16px;
      text-align: left;
      border: 2px solid #99F6E4;
    }

    .intro-bank-title {
      font-weight: 800;
      color: #0D9488;
      font-size: 13px;
      margin-bottom: 10px;
    }

    .intro-bank-grid {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 8px;
      text-align: center;
    }

    .intro-bank-tile {
      background: white;
      border-radius: 10px;
      padding: 8px 6px;
    }

    .intro-bank-tile-icon { font-size: 16px; }

    .intro-bank-tile-label {
      font-size: 11px;
      font-weight: 700;
      color: #64748B;
    }

    .intro-bank-tile-val {
      font-size: 16px;
      font-weight: 900;
      color: #1E293B;
    }

    .intro-bank-tile-pct {
      font-size: 11px;
      font-weight: 800;
    }

    .intro-bank-tile-pct.needs   { color: #059669; }
    .intro-bank-tile-pct.wants   { color: #2563EB; }
    .intro-bank-tile-pct.savings { color: #7C3AED; }

    .intro-bank-tile-pct .ideal { color: #94A3B8; }

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
      background: linear-gradient(135deg, #0D9488, #0F766E);
      color: white;
      border: none;
      border-radius: 14px;
      padding: 14px 48px;
      font-size: 17px;
      font-weight: 800;
      font-family: 'Nunito', sans-serif;
      cursor: pointer;
      box-shadow: 0 4px 16px rgba(13, 148, 136, 0.3);
      transition: all 0.2s;
    }

    .start-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 24px rgba(13, 148, 136, 0.4);
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
      margin-bottom: 14px;
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

    .stat-pill.points { color: #0D9488; }
    .stat-pill.done   { color: #64748B; }

    /* ─── Comparison bars panel ────────────────────────────────── */
    .comparison-panel {
      background: white;
      border-radius: 18px;
      padding: 20px 20px 8px;
      margin-bottom: 16px;
      border: 2px solid #E2E8F0;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    }

    .comparison-title {
      font-size: 14px;
      font-weight: 800;
      color: #1E293B;
      margin-bottom: 14px;
      text-align: center;
    }

    .comparison-bar-wrap {
      margin-bottom: 16px;
    }

    .comparison-bar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 4px;
    }

    .comparison-bar-label {
      font-size: 14px;
      font-weight: 800;
    }

    .comparison-bar-status {
      font-size: 12px;
      font-weight: 800;
      border-radius: 6px;
      padding: 2px 8px;
    }

    .comparison-bar-track {
      position: relative;
      height: 32px;
      background: #F1F5F9;
      border-radius: 10px;
      overflow: hidden;
    }

    .healthy-zone {
      position: absolute;
      height: 100%;
      opacity: 0.5;
    }

    .ideal-marker {
      position: absolute;
      width: 2px;
      height: 100%;
      opacity: 0.6;
    }

    .actual-fill {
      position: absolute;
      left: 0;
      height: 100%;
      border-radius: 10px;
      transition: width 1s cubic-bezier(.4, 0, .2, 1);
    }

    .actual-pct-label {
      position: absolute;
      left: 8px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 12px;
      font-weight: 900;
      color: white;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    .ideal-label-top {
      position: absolute;
      top: -18px;
      transform: translateX(-50%);
      font-size: 10px;
      font-weight: 700;
      white-space: nowrap;
    }

    .comparison-bar-footer {
      display: flex;
      justify-content: space-between;
      margin-top: 3px;
    }

    .comparison-bar-footer span {
      font-size: 10px;
      color: #94A3B8;
    }

    .comparison-bar-footer .healthy-range {
      font-weight: 700;
    }

    /* ─── Sticky reference bar ─────────────────────────────────── */
    .reference {
      background: white;
      border-radius: 12px;
      padding: 8px 14px;
      margin-bottom: 16px;
      border: 2px solid #E2E8F0;
      position: sticky;
      top: 8px;
      z-index: 10;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
      display: flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap;
      font-size: 12px;
      font-weight: 700;
    }

    .reference .ref-needs   { color: #059669; }
    .reference .ref-wants   { color: #2563EB; }
    .reference .ref-savings { color: #7C3AED; }
    .reference .ref-ideal   { color: #94A3B8; }

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
      background: linear-gradient(90deg, #0D9488, #7C3AED);
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
      background: #0D9488;
      flex-shrink: 0;
    }

    .question-card.done-correct  .question-number { background: #22C55E; }
    .question-card.done-revealed .question-number { background: #F59E0B; }

    .question-tag {
      font-size: 11px;
      font-weight: 800;
      color: #0D9488;
      text-transform: uppercase;
      letter-spacing: 0.3px;
      margin-bottom: 2px;
    }

    .question-text {
      font-size: 14px;
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

    /* Number input row */
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

    .input-wrapper:focus-within { border-color: #0D9488; }

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
      background: linear-gradient(135deg, #0D9488, #0F766E);
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

    /* Choice buttons */
    .choice-row {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .choice-button {
      flex: 1;
      min-width: 90px;
      padding: 10px 14px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 800;
      font-family: 'Nunito', sans-serif;
      cursor: pointer;
      background: white;
      border: 2px solid #99F6E4;
      color: #0D9488;
      transition: all 0.2s;
    }

    .choice-button:hover {
      background: #CCFBF1;
      transform: translateY(-1px);
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
    .attempt-dot.current { background: #0D9488; }

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
      margin-bottom: 40px;
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
      box-shadow: 0 8px 32px rgba(13, 148, 136, 0.12);
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
      max-width: 420px;
    }

    .health-summary {
      background: #F0FDFA;
      border-radius: 14px;
      padding: 16px 20px;
      margin-bottom: 16px;
      text-align: left;
    }

    .health-summary-title {
      font-weight: 800;
      color: #0D9488;
      margin-bottom: 10px;
      font-size: 14px;
    }

    .health-summary-row {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 6px;
    }

    .health-summary-cat {
      font-weight: 800;
      min-width: 70px;
    }

    .health-summary-cat.needs   { color: #059669; }
    .health-summary-cat.wants   { color: #2563EB; }
    .health-summary-cat.savings { color: #7C3AED; }

    .health-summary-pct {
      font-size: 13px;
      font-weight: 700;
    }

    .health-summary-status {
      font-size: 12px;
      font-weight: 800;
      border-radius: 6px;
      padding: 1px 8px;
    }

    .health-summary-note {
      margin-top: 10px;
      font-size: 13px;
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
      color: #0D9488;
    }

    .stat-divisor {
      font-size: 16px;
      color: #94A3B8;
    }

    /* ─── Animations ───────────────────────────────────────────── */
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50%      { transform: scale(1.05); }
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%      { transform: translateX(-8px); }
      40%      { transform: translateX(8px); }
      60%      { transform: translateX(-5px); }
      80%      { transform: translateX(5px); }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-6px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes pop {
      0%   { transform: scale(0.8); opacity: 0; }
      50%  { transform: scale(1.05); }
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
  <div class="flex items-center justify-between pb-6">
    <h1 class="text-xl font-bold whitespace-nowrap">
        Task 4
    </h1>

    <a href="{{ url()->previous() }}"
       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
        ← Back
    </a>
</div>
 <!-- ─── Loading screen ───────────────────────────────────────── -->
  <div id="loading-screen" class="screen">
    <div>
      <div id="loading-emoji">📋</div>
      <p id="loading-text">Loading your bank statement...</p>
    </div>
  </div>

  <!-- ─── Intro screen ─────────────────────────────────────────── -->
  <div id="intro-screen" class="screen hidden">
    <div class="intro-card">
      <div class="intro-icon">📋</div>
      <h1 class="intro-title">Budget Reality Check</h1>
      <p class="intro-description">
        Now let's compare your REAL spending from your bank statement with the perfect 50/30/20 rule. How close are you?
      </p>

      <div class="intro-bank">
        <div class="intro-bank-title" id="intro-bank-title"></div>
        <div class="intro-bank-grid" id="intro-bank-grid"></div>
      </div>

      <div class="intro-points-note">
        <p><strong>Finhero Badge Points: up to 24</strong> — up to 3 points per question. Get it right on your first approach for 3 points; each time you use up your tries, the hint gets stronger and the points available go down: 2, then 1, then 0 — but you can always keep trying. Compare your actual spending against the 50/30/20 ideal!</p>
      </div>

      <button class="start-button" id="start-button">Let's Go! 📋</button>
    </div>
  </div>

  <!-- ─── Game screen ──────────────────────────────────────────── -->
  <div id="game-screen" class="hidden">
    <div class="container">
      <div class="header">
        <h1 class="game-title">Task 4: Budget Reality Check</h1>
        <p class="game-subtitle">Your real spending vs the 50/30/20 ideal</p>
      </div>

      <div class="stats-row">
        <div class="stat-pill points" id="stat-points">🏆 Finhero Badge Points: 0 / 24</div>
        <div class="stat-pill done" id="stat-done">✅ 0 / 8 done</div>
      </div>

      <div class="comparison-panel">
        <div class="comparison-title" id="comparison-title"></div>
        <div id="comparison-bars"></div>
      </div>

      <div class="reference">
        <span class="ref-needs"   id="ref-needs"></span>
        <span class="ref-wants"   id="ref-wants"></span>
        <span class="ref-savings" id="ref-savings"></span>
        <span class="ref-ideal">| Ideal: 50% / 30% / 20%</span>
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
          <span class="confetti-emoji">📋</span>
          <span class="confetti-emoji">💰</span>
          <span class="confetti-emoji">🏆</span>
          <span class="confetti-emoji">✨</span>
        </div>
        <div class="completion-card">
          <div class="completion-icon">📋</div>
          <h2 class="completion-title">Budget Reality Check Complete!</h2>
          <p class="completion-text" id="completion-text"></p>

          <div class="health-summary">
            <div class="health-summary-title">Your Budget Health:</div>
            <div id="health-summary-rows"></div>
            <div class="health-summary-note">
              Healthy ranges: Needs 45–55% | Wants 25–35% | Savings 15–25%
            </div>
          </div>

          <div class="stat-box">
            <div class="stat-label">Finhero Badge Points</div>
            <div class="stat-value">
              <span id="final-points">0</span>
              <span class="stat-divisor">/ <span id="final-total">24</span></span>
            </div>
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
    // ║  These values must come from the student's Monthly Bank    ║
    // ║  Statement (Needs vs Wants sorting activity).              ║
    // ║                                                            ║
    // ║  Example:                                                  ║
    // ║    async function getStudentBankStatement() {              ║
    // ║      const r = await fetch('/api/student/bank-statement'); ║
    // ║      const d = await r.json();                             ║
    // ║      return {                                              ║
    // ║        salary:        d.salary,                            ║
    // ║        actualNeeds:   d.needsTotal,                        ║
    // ║        actualWants:   d.wantsTotal,                        ║
    // ║        actualSavings: d.savingsTotal,                      ║
    // ║      };                                                    ║
    // ║    }                                                       ║
    // ╚════════════════════════════════════════════════════════════╝
    async function getStudentBankStatement() {
      return {
        //salary:        4000,
        salary: {{ $salary ?? 0 }},
        actualNeeds:   2200,
        actualWants:   1100,
        actualSavings: 700,
      };
    }

    // ╔════════════════════════════════════════════════════════════╗
    // ║  🎯 HEALTHY RANGES — IT team can adjust if needed          ║
    // ╚════════════════════════════════════════════════════════════╝
    const HEALTHY_RANGES = {
      needs:   { ideal: 50, min: 45, max: 55, label: "Needs",   emoji: "🏠", color: "#059669", bg: "#D1FAE5", border: "#6EE7B7" },
      wants:   { ideal: 30, min: 25, max: 35, label: "Wants",   emoji: "🎮", color: "#2563EB", bg: "#DBEAFE", border: "#93C5FD" },
      savings: { ideal: 20, min: 15, max: 25, label: "Savings", emoji: "🏦", color: "#7C3AED", bg: "#EDE9FE", border: "#C4B5FD" },
    };

    // ───────────────────────────────────────────────────────────────
    // STATUS HELPERS
    // ───────────────────────────────────────────────────────────────
    function getStatus(actualPct, range) {
      if (actualPct >= range.min && actualPct <= range.max) return "healthy";
      if (range.label === "Savings") return actualPct < range.min ? "low" : "high";
      return actualPct > range.max ? "over" : "under";
    }

    function getStatusLabel(status, catLabel) {
      if (status === "healthy") return "ON TRACK ✅";
      if (catLabel === "Savings") return status === "low" ? "BELOW TARGET ⚠️" : "ABOVE TARGET 🌟";
      return status === "over" ? "OVERSPENDING ⚠️" : "UNDERSPENDING";
    }

    function getStatusColor(status, catLabel) {
      if (status === "healthy") return "#059669";
      if (catLabel === "Savings" && status === "high") return "#7C3AED";
      if (status === "over") return "#DC2626";
      return "#CA8A04";
    }

    function getStatusBg(status) {
      if (status === "healthy") return "#D1FAE5";
      if (status === "over")    return "#FEE2E2";
      return "#FEF9C3";
    }

    // ───────────────────────────────────────────────────────────────
    // BUILD QUESTIONS
    // ───────────────────────────────────────────────────────────────
    function buildQuestions(data) {
      const { salary, actualNeeds, actualWants, actualSavings } = data;
      const idealNeeds   = salary * 0.5;
      const idealWants   = salary * 0.3;
      const idealSavings = salary * 0.2;

      const needsDiff   = Math.abs(actualNeeds   - idealNeeds);
      const wantsDiff   = Math.abs(actualWants   - idealWants);
      const savingsDiff = Math.abs(actualSavings - idealSavings);

      const needsDir   = actualNeeds   > idealNeeds   ? "MORE" : actualNeeds   < idealNeeds   ? "LESS" : "EXACTLY RIGHT";
      const wantsDir   = actualWants   > idealWants   ? "MORE" : actualWants   < idealWants   ? "LESS" : "EXACTLY RIGHT";
      const savingsDir = actualSavings > idealSavings ? "MORE" : actualSavings < idealSavings ? "LESS" : "EXACTLY RIGHT";

      const leftover = salary - actualNeeds - actualWants - actualSavings;

      // Find which category is most over ideal (Needs or Wants)
      const overNeeds = actualNeeds - idealNeeds;
      const overWants = actualWants - idealWants;
      const diffs = [
        { cat: "Needs", over: overNeeds },
        { cat: "Wants", over: overWants },
      ];
      const biggestOver = diffs.reduce((a, b) => b.over > a.over ? b : a);
      const moveAmount  = biggestOver.over > 0 ? biggestOver.over : 0;

      const fmt = n => n.toLocaleString();

      const questions = [
        {
          id: 1, type: "number",
          question: `The 50/30/20 rule says Needs should be ${fmt(idealNeeds)} zeds (50%). Your actual Needs is ${fmt(actualNeeds)} zeds. What is the difference?`,
          answer: needsDiff,
          hint: `Subtract the smaller from the bigger: ${fmt(Math.max(actualNeeds, idealNeeds))} − ${fmt(Math.min(actualNeeds, idealNeeds))}`,
          steps: `${fmt(Math.max(actualNeeds, idealNeeds))} − ${fmt(Math.min(actualNeeds, idealNeeds))} = ${fmt(needsDiff)} zeds`,
          tag: "🏠 Needs: Ideal vs Actual",
        },
        {
          id: 2, type: "choice",
          question: "Are you spending MORE or LESS on Needs than the 50/30/20 rule recommends?",
          answer: needsDir,
          options: ["MORE", "LESS", "EXACTLY RIGHT"],
          hint: `Your actual Needs: ${fmt(actualNeeds)} zeds. The rule says: ${fmt(idealNeeds)} zeds. Which number is bigger?`,
          steps: `Actual ${fmt(actualNeeds)} vs Ideal ${fmt(idealNeeds)} → You spend ${needsDir}`,
          tag: "🏠 Needs: Over or Under?",
        },
        {
          id: 3, type: "number",
          question: `The 50/30/20 rule says Wants should be ${fmt(idealWants)} zeds (30%). Your actual Wants is ${fmt(actualWants)} zeds. What is the difference?`,
          answer: wantsDiff,
          hint: `Subtract the smaller from the bigger: ${fmt(Math.max(actualWants, idealWants))} − ${fmt(Math.min(actualWants, idealWants))}`,
          steps: `${fmt(Math.max(actualWants, idealWants))} − ${fmt(Math.min(actualWants, idealWants))} = ${fmt(wantsDiff)} zeds`,
          tag: "🎮 Wants: Ideal vs Actual",
        },
        {
          id: 4, type: "choice",
          question: "Are you spending MORE or LESS on Wants than the 50/30/20 rule recommends?",
          answer: wantsDir,
          options: ["MORE", "LESS", "EXACTLY RIGHT"],
          hint: `Your actual Wants: ${fmt(actualWants)} zeds. The rule says: ${fmt(idealWants)} zeds. Which number is bigger?`,
          steps: `Actual ${fmt(actualWants)} vs Ideal ${fmt(idealWants)} → You spend ${wantsDir}`,
          tag: "🎮 Wants: Over or Under?",
        },
        {
          id: 5, type: "number",
          question: `The 50/30/20 rule says Savings should be ${fmt(idealSavings)} zeds (20%). Your actual Savings is ${fmt(actualSavings)} zeds. What is the difference?`,
          answer: savingsDiff,
          hint: `Subtract the smaller from the bigger: ${fmt(Math.max(actualSavings, idealSavings))} − ${fmt(Math.min(actualSavings, idealSavings))}`,
          steps: `${fmt(Math.max(actualSavings, idealSavings))} − ${fmt(Math.min(actualSavings, idealSavings))} = ${fmt(savingsDiff)} zeds`,
          tag: "🏦 Savings: Ideal vs Actual",
        },
        {
          id: 6, type: "choice",
          question: "Are you saving MORE or LESS than the 50/30/20 rule recommends?",
          answer: savingsDir,
          options: ["MORE", "LESS", "EXACTLY RIGHT"],
          hint: `Your actual Savings: ${fmt(actualSavings)} zeds. The rule says: ${fmt(idealSavings)} zeds. Which number is bigger?`,
          steps: `Actual ${fmt(actualSavings)} vs Ideal ${fmt(idealSavings)} → You save ${savingsDir}`,
          tag: "🏦 Savings: Over or Under?",
        },
        {
          id: 7, type: "number",
          question: `After Needs (${fmt(actualNeeds)}), Wants (${fmt(actualWants)}), and Savings (${fmt(actualSavings)}), how much of your ${fmt(salary)} zeds is unaccounted for?`,
          answer: leftover,
          hint: `Subtract all three from your salary: ${fmt(salary)} − ${fmt(actualNeeds)} − ${fmt(actualWants)} − ${fmt(actualSavings)}`,
          steps: `${fmt(salary)} − ${fmt(actualNeeds)} = ${fmt(salary - actualNeeds)}\n${fmt(salary - actualNeeds)} − ${fmt(actualWants)} = ${fmt(salary - actualNeeds - actualWants)}\n${fmt(salary - actualNeeds - actualWants)} − ${fmt(actualSavings)} = ${fmt(leftover)} zeds`,
          tag: "💸 Unaccounted money",
        },
      ];

      // Question 8 — varies based on whether anything is over budget
      if (moveAmount > 0) {
        questions.push({
          id: 8, type: "number",
          question: `To match the 50/30/20 rule, you would move ${fmt(moveAmount)} zeds from ${biggestOver.cat} to Savings. What would your new Savings be?`,
          answer: actualSavings + moveAmount,
          hint: `Add the move amount to your current Savings: ${fmt(actualSavings)} + ${fmt(moveAmount)}`,
          steps: `${fmt(actualSavings)} + ${fmt(moveAmount)} = ${fmt(actualSavings + moveAmount)} zeds`,
          tag: "🔄 Budget adjustment",
        });
      } else {
        questions.push({
          id: 8, type: "number",
          question: `If you could increase your Savings by 10% of your salary, what would your new Savings total be?`,
          answer: actualSavings + (salary * 0.1),
          hint: `Find 10% of salary (${fmt(salary)} ÷ 10), then add to current Savings`,
          steps: `${fmt(salary)} ÷ 10 = ${fmt(salary * 0.1)}\n${fmt(actualSavings)} + ${fmt(salary * 0.1)} = ${fmt(actualSavings + salary * 0.1)} zeds`,
          tag: "🔄 Savings boost",
        });
      }

      return questions;
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
      const last = lines[lines.length - 1].replace(/=\s*[\d.,]+%?\s*(zeds)?\s*$/, "= ?");
      return [...lines.slice(0, -1), last].join("\n");
    }

    const state = {
      data: null,
      questions: [],
      qState: {},   // { [id]: { tier, wrongInTier, status, earned, input } }
      scores: {},
      pcts: { needs: 0, wants: 0, savings: 0 },
      firstTryCount: 0,
    };

    const TASK4_TOTAL_POINTS = 24;

    // ───────────────────────────────────────────────────────────────
    // DOM REFS
    // ───────────────────────────────────────────────────────────────
    const $ = (id) => document.getElementById(id);

    // ───────────────────────────────────────────────────────────────
    // INITIALIZE
    // ───────────────────────────────────────────────────────────────
    (async function init() {
      state.data      = await getStudentBankStatement();
      state.questions = buildQuestions(state.data);

      state.questions.forEach(q => {
        state.qState[q.id] = { tier: 0, wrongInTier: 0, status: null, earned: null, input: "" };
      });

      // Pre-compute percentages
      state.pcts.needs   = Math.round((state.data.actualNeeds   / state.data.salary) * 100);
      state.pcts.wants   = Math.round((state.data.actualWants   / state.data.salary) * 100);
      state.pcts.savings = Math.round((state.data.actualSavings / state.data.salary) * 100);

      buildIntroBank();
      buildComparisonPanel();
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
      $("intro-bank-title").textContent =
        `💰 Your Real Bank Statement — ${state.data.salary.toLocaleString()} zeds/month`;

      const items = [
        { label: "Needs",   val: state.data.actualNeeds,   pct: state.pcts.needs,   ideal: 50, icon: "🏠", colorClass: "needs"   },
        { label: "Wants",   val: state.data.actualWants,   pct: state.pcts.wants,   ideal: 30, icon: "🎮", colorClass: "wants"   },
        { label: "Savings", val: state.data.actualSavings, pct: state.pcts.savings, ideal: 20, icon: "🏦", colorClass: "savings" },
      ];

      $("intro-bank-grid").innerHTML = items.map(item => `
        <div class="intro-bank-tile">
          <div class="intro-bank-tile-icon">${item.icon}</div>
          <div class="intro-bank-tile-label">${item.label}</div>
          <div class="intro-bank-tile-val">${item.val.toLocaleString()}</div>
          <div class="intro-bank-tile-pct ${item.colorClass}">${item.pct}% <span class="ideal">/ ${item.ideal}%</span></div>
        </div>
      `).join("");
    }

    // ───────────────────────────────────────────────────────────────
    // BUILD COMPARISON PANEL (3 horizontal bars)
    // ───────────────────────────────────────────────────────────────
    function buildComparisonPanel() {
      $("comparison-title").textContent =
        `📊 Your Budget vs the 50/30/20 Rule — ${state.data.salary.toLocaleString()} zeds/month`;

      const bars = [
        { range: HEALTHY_RANGES.needs,   actualPct: state.pcts.needs,   idealPct: 50 },
        { range: HEALTHY_RANGES.wants,   actualPct: state.pcts.wants,   idealPct: 30 },
        { range: HEALTHY_RANGES.savings, actualPct: state.pcts.savings, idealPct: 20 },
      ];

      $("comparison-bars").innerHTML = bars.map(({ range, actualPct, idealPct }) => {
        const status      = getStatus(actualPct, range);
        const statusLabel = getStatusLabel(status, range.label);
        const statusColor = getStatusColor(status, range.label);
        const statusBg    = getStatusBg(status);
        const fillWidth   = Math.min(actualPct, 100);

        return `
          <div class="comparison-bar-wrap">
            <div class="comparison-bar-header">
              <div class="comparison-bar-label" style="color:${range.color};">${range.emoji} ${range.label}</div>
              <div class="comparison-bar-status" style="color:${statusColor};background:${statusBg};">${statusLabel}</div>
            </div>
            <div class="comparison-bar-track">
              <div class="healthy-zone"
                   style="left:${range.min}%; width:${range.max - range.min}%; background:${range.bg};
                          border-left:2px dashed ${range.color}; border-right:2px dashed ${range.color};"></div>
              <div class="ideal-marker" style="left:${idealPct}%; background:${range.color};"></div>
              <div class="actual-fill"
                   style="width:${fillWidth}%;
                          background:linear-gradient(90deg, ${range.color}88, ${range.color});"></div>
              <div class="actual-pct-label">${actualPct}%</div>
              <div class="ideal-label-top" style="left:${idealPct}%; color:${range.color};">Ideal: ${idealPct}%</div>
            </div>
            <div class="comparison-bar-footer">
              <span>0%</span>
              <span class="healthy-range" style="color:${range.color};">Healthy: ${range.min}–${range.max}%</span>
              <span>100%</span>
            </div>
          </div>
        `;
      }).join("");
    }

    // ───────────────────────────────────────────────────────────────
    // BUILD STICKY REFERENCE
    // ───────────────────────────────────────────────────────────────
    function buildReference() {
      $("ref-needs").textContent   = `🏠 Needs: ${state.data.actualNeeds.toLocaleString()} (${state.pcts.needs}%)`;
      $("ref-wants").textContent   = `🎮 Wants: ${state.data.actualWants.toLocaleString()} (${state.pcts.wants}%)`;
      $("ref-savings").textContent = `🏦 Savings: ${state.data.actualSavings.toLocaleString()} (${state.pcts.savings}%)`;

      const totalQs = state.questions.length;
      $("stat-points").textContent = `🏆 Finhero Badge Points: 0 / ${TASK4_TOTAL_POINTS}`;
      $("stat-done").textContent   = `✅ 0 / ${totalQs} done`;
    }

    // ───────────────────────────────────────────────────────────────
    // BUILD QUESTION CARDS
    // ───────────────────────────────────────────────────────────────
    function buildQuestionsUI() {
      const container = $("questions");
      container.innerHTML = "";

      state.questions.forEach(q => {
        const card = document.createElement("div");
        card.className = "question-card";
        card.dataset.qid = q.id;

        // Common header + message blocks
        let inner = `
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
        `;

        if (q.type === "number") {
          inner += `
            <div class="input-row">
              <div class="input-wrapper">
                <input type="text" inputmode="numeric" placeholder="Type your answer..." />
                <span class="currency-suffix">zeds</span>
              </div>
              <button class="check-button" disabled>Check</button>
            </div>
          `;
        } else {
          // Choice question
          inner += `
            <div class="choice-row">
              ${q.options.map(opt =>
                `<button class="choice-button" data-option="${opt}">${opt}</button>`
              ).join("")}
            </div>
          `;
        }

        inner += `
          <div class="attempts-row">
            <div class="attempt-dot current"></div>
            <div class="attempt-dot"></div>
            <div class="attempt-dot"></div>
            <span class="attempts-label">First try — 1 point!</span>
          </div>
        `;

        card.innerHTML = inner;
        container.appendChild(card);

        // Wire up handlers
        if (q.type === "number") {
          const input    = card.querySelector("input");
          const checkBtn = card.querySelector(".check-button");

          input.addEventListener("input", (e) => {
            const cleaned = e.target.value.replace(/[^0-9,.\-]/g, "");
            e.target.value = cleaned;
            state.qState[q.id].input = cleaned;
            checkBtn.disabled = !cleaned.trim();
          });

          input.addEventListener("keydown", (e) => {
            if (e.key === "Enter" && !checkBtn.disabled) checkNumber(q.id);
          });

          checkBtn.addEventListener("click", () => checkNumber(q.id));
        } else {
          card.querySelectorAll(".choice-button").forEach(btn => {
            btn.addEventListener("click", () => checkChoice(q.id, btn.dataset.option));
          });
        }
      });
    }

    // ───────────────────────────────────────────────────────────────
    // CHECK ANSWER LOGIC — Number
    // ───────────────────────────────────────────────────────────────
    function checkNumber(qid) {
      const q  = state.questions.find(x => x.id === qid);
      const qs = state.qState[qid];
      const num = parseInt(qs.input.replace(/,/g, ""), 10);
      if (isNaN(num)) return;

      const correct = Math.abs(num - q.answer) <= 5;
      processResult(qid, correct);
    }

    // ───────────────────────────────────────────────────────────────
    // CHECK ANSWER LOGIC — Choice
    // ───────────────────────────────────────────────────────────────
    function checkChoice(qid, option) {
      const q = state.questions.find(x => x.id === qid);
      processResult(qid, option === q.answer);
    }

    function processResult(qid, correct) {
      const qs = state.qState[qid];

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
                source_key: 'task_4_question_' + qid,
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

      const done = qs.status === "correct";

      // Card-level state
      const tier = APPROACH_TIERS[qs.tier];
      card.classList.toggle("done-correct", done);

      // Number cell
      const num = card.querySelector(".question-number");
      num.textContent = done ? "✓" : q.id;

      // Light hint (tier 1+): same hint text for both question types - for
      // "choice" questions the hint already stops short of the answer
      // (e.g. "which number is bigger?"), so it stands in for "full" too.
      const hintEl = card.querySelector(".msg-hint");
      if (tier.hint === "light" && !done) {
        hintEl.innerHTML = `💡 <strong>Hint:</strong> ${q.hint}`;
        hintEl.classList.remove("hidden");
      } else {
        hintEl.classList.add("hidden");
      }

      // Fuller hint (tier 2+): "number" questions get the worked setup with
      // the final answer never auto-revealed (doc §2 "Beyond Third
      // Approach... answer is never auto-revealed"). "choice" questions have
      // no extra step content beyond the hint itself, so they just keep
      // showing the hint - there is nothing further to redact or reveal.
      const revEl = card.querySelector(".msg-revealed");
      if (q.type === "number" && tier.hint === "full" && !done) {
        const stepsHtml = redactFinalStep(q.steps).split("\n")
          .map(line => `<div class="msg-revealed-step">${line}</div>`).join("");
        revEl.innerHTML = `
          <div class="msg-revealed-title">📝 Here's the setup — you finish the last step:</div>
          ${stepsHtml}
        `;
        revEl.classList.remove("hidden");
      } else if (q.type === "choice" && tier.hint === "full" && !done) {
        hintEl.innerHTML = `💡 <strong>Hint:</strong> ${q.hint}`;
        hintEl.classList.remove("hidden");
        revEl.classList.add("hidden");
      } else {
        revEl.classList.add("hidden");
      }

      // Correct
      const corrEl = card.querySelector(".msg-correct");
      if (done) {
        const answerSuffix = q.type === "number" ? " zeds" : "";
        const answerDisplay = q.answer.toLocaleString ? q.answer.toLocaleString() : q.answer;
        const ptsText = qs.earned > 0 ? ` +${qs.earned} Finhero Badge Point${qs.earned === 1 ? '' : 's'}!` : '';
        corrEl.innerHTML = `
          <span class="msg-correct-icon">🎉</span>
          <span>${answerDisplay}${answerSuffix} — Correct!${ptsText}</span>
        `;
        corrEl.classList.remove("hidden");
      } else {
        corrEl.classList.add("hidden");
      }

      // Wrong (transient)
      const wrongEl = card.querySelector(".msg-wrong");
      wrongEl.classList.toggle("hidden", qs.status !== "wrong");

      // Input/choice row visibility
      const inputRow  = card.querySelector(".input-row");
      const choiceRow = card.querySelector(".choice-row");
      const showInput = !done && qs.status !== "wrong";

      if (inputRow)  inputRow.classList.toggle("hidden",  !showInput);
      if (choiceRow) choiceRow.classList.toggle("hidden", !showInput);

      // Reset number input
      if (q.type === "number") {
        const inputEl  = card.querySelector("input");
        const checkBtn = card.querySelector(".check-button");
        if (showInput) {
          inputEl.value = qs.input;
          checkBtn.disabled = !qs.input.trim();
        } else {
          inputEl.value = "";
        }
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
      const totalQs        = state.questions.length;

      $("stat-points").textContent = `🏆 Finhero Badge Points: ${totalPoints} / ${TASK4_TOTAL_POINTS}`;
      $("stat-done").textContent   = `✅ ${completedCount} / ${totalQs} done`;
      $("progress-fill").style.width = ((completedCount / totalQs) * 100) + "%";
    }

    // ───────────────────────────────────────────────────────────────
    // CHECK ALL DONE — show completion
    // ───────────────────────────────────────────────────────────────
    function checkAllDone() {
      const completedCount = Object.keys(state.scores).length;
      const totalQs        = state.questions.length;
      if (completedCount < totalQs) return;

      // Tells the server this task is finished (blackboard + Engagement badge).
      fetch("{{ route('finhero.task-complete') }}", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: JSON.stringify({ activity_key: @json($activityKey) })
      }).catch(() => {});

      const totalPoints = Object.values(state.scores).reduce((a, b) => a + b, 0);

      let message;
      if (totalPoints >= 18)      message = "Excellent! You clearly understand how your real spending compares to the ideal budget! 🌟";
      else if (totalPoints >= 9)  message = "Good work! You're building awareness of where your money goes.";
      else                        message = "Now you can see exactly where your budget differs from the 50/30/20 rule. That awareness is powerful!";

      $("completion-text").textContent = message;
      $("final-points").textContent    = totalPoints;
      $("final-total").textContent     = TASK4_TOTAL_POINTS;

      // Build health summary
      const cats = [
        { ...HEALTHY_RANGES.needs,   pct: state.pcts.needs,   colorClass: "needs"   },
        { ...HEALTHY_RANGES.wants,   pct: state.pcts.wants,   colorClass: "wants"   },
        { ...HEALTHY_RANGES.savings, pct: state.pcts.savings, colorClass: "savings" },
      ];

      $("health-summary-rows").innerHTML = cats.map(cat => {
        const st = getStatus(cat.pct, cat);
        return `
          <div class="health-summary-row">
            <span>${cat.emoji}</span>
            <span class="health-summary-cat ${cat.colorClass}">${cat.label}</span>
            <span class="health-summary-pct">${cat.pct}%</span>
            <span class="health-summary-status"
                  style="color:${getStatusColor(st, cat.label)};background:${getStatusBg(st)};">
              ${getStatusLabel(st, cat.label)}
            </span>
          </div>
        `;
      }).join("");

      $("completion").classList.remove("hidden");
    }
  </script>
  @endpush