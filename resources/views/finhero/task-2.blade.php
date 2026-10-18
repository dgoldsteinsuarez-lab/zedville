@extends('layouts.profile')

@section('title', 'Budget Math!')

@section('content')
@push('styles')

<style>
    /* ─── Base & layout ──────────────────────────────────────────── */
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Nunito', sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, #F0FDFA 0%, #ECFDF5 50%, #F0FDF4 100%);
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
      max-width: 560px;
      background: white;
      border-radius: 24px;
      padding: 40px 36px;
      box-shadow: 0 20px 60px rgba(13, 148, 136, 0.12), 0 0 0 1px rgba(13, 148, 136, 0.06);
      text-align: center;
    }

    .intro-icon { font-size: 56px; margin-bottom: 12px; }

    .intro-title {
      font-family: 'Fredoka One', cursive;
      font-size: 28px;
      color: #0F172A;
      margin: 0 0 8px;
    }

    .intro-description {
      color: #64748B;
      font-size: 16px;
      line-height: 1.6;
      margin: 0 0 24px;
    }

    .intro-bank {
      border-radius: 16px;
      padding: 16px 20px;
      margin-bottom: 20px;
      text-align: left;
      border: 2px solid var(--scale-border);
      background: var(--scale-bg);
    }

    .intro-bank-title {
      font-weight: 800;
      color: var(--scale-color);
      font-size: 14px;
      margin-bottom: 10px;
    }

    .intro-bank-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
    }

    .bank-tile {
      background: rgba(255, 255, 255, 0.7);
      border-radius: 10px;
      padding: 8px 12px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .bank-tile-icon { font-size: 18px; }

    .bank-tile-label {
      font-size: 11px;
      font-weight: 700;
      color: #64748B;
      text-transform: uppercase;
    }

    .bank-tile-value {
      font-size: 16px;
      font-weight: 900;
      color: #1E293B;
    }

    .intro-points-note {
      background: #FEF9C3;
      border-radius: 12px;
      padding: 12px 16px;
      margin-bottom: 28px;
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
    .header { text-align: center; margin-bottom: 16px; }

    .game-title {
      font-family: 'Fredoka One', cursive;
      font-size: 24px;
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

    .stat-pill.points { color: #0D9488; }
    .stat-pill.done   { color: #64748B; }

    /* ─── Sticky bank statement ────────────────────────────────── */
    .bank-sticky {
      background: var(--scale-bg);
      border-radius: 14px;
      padding: 10px 16px;
      margin-bottom: 16px;
      border: 2px solid var(--scale-border);
      display: flex;
      gap: 12px;
      justify-content: center;
      flex-wrap: wrap;
      position: sticky;
      top: 8px;
      z-index: 10;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    }

    .bank-item {
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .bank-item-icon { font-size: 14px; }
    .bank-item-label { font-size: 12px; font-weight: 800; color: var(--scale-color); }
    .bank-item-value { font-size: 13px; font-weight: 900; color: #1E293B; }

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
      background: linear-gradient(90deg, var(--scale-color), #0D9488);
      width: 0%;
    }

    /* ─── Questions ────────────────────────────────────────────── */
    .questions {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .question-card {
      background: white;
      border-radius: 16px;
      padding: 18px 20px;
      border: 2px solid #E2E8F0;
      transition: all 0.3s;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
    }

    .question-card.done-correct {
      background: #F0FDF4;
      border-color: #86EFAC;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    }

    .question-card.done-revealed {
      background: #FFFBEB;
      border-color: #FDE68A;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    }

    .question-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 12px;
    }

    .question-number {
      width: 32px;
      height: 32px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 900;
      font-size: 14px;
      background: var(--scale-color);
      flex-shrink: 0;
    }

    .question-card.done-correct  .question-number { background: #22C55E; }
    .question-card.done-revealed .question-number { background: #F59E0B; }

    .question-text {
      flex: 1;
      font-size: 15px;
      font-weight: 700;
      color: #1E293B;
      line-height: 1.4;
    }

    .question-op {
      font-size: 12px;
      opacity: 0.5;
    }

    /* Hint, revealed, correct, wrong messages */
    .msg-hint {
      background: #FEF9C3;
      border-radius: 10px;
      padding: 10px 14px;
      margin-bottom: 12px;
      font-size: 13px;
      color: #92400E;
      font-weight: 600;
      animation: fadeIn 0.3s;
    }

    .msg-revealed {
      background: #DBEAFE;
      border-radius: 10px;
      padding: 12px 14px;
      margin-bottom: 12px;
      font-size: 13px;
      color: #1E40AF;
      animation: fadeIn 0.3s;
    }

    .msg-revealed-title {
      font-weight: 800;
      margin-bottom: 6px;
    }

    .msg-revealed-step {
      font-family: monospace;
      font-size: 14px;
      font-weight: 700;
      margin-bottom: 2px;
    }

    .msg-revealed-answer {
      margin-top: 8px;
      font-weight: 800;
      color: #1E3A5F;
      font-size: 15px;
    }

    .msg-correct {
      background: #D1FAE5;
      border-radius: 10px;
      padding: 10px 14px;
      font-size: 14px;
      color: #065F46;
      font-weight: 700;
      animation: fadeIn 0.3s;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .msg-correct-icon { font-size: 20px; }

    .msg-wrong {
      background: #FEE2E2;
      border-radius: 10px;
      padding: 8px 14px;
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
      align-items: center;
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

    .input-wrapper:focus-within {
      border-color: var(--scale-color);
    }

    .input-wrapper input {
      flex: 1;
      border: none;
      outline: none;
      background: transparent;
      padding: 10px 0;
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
      padding: 10px 20px;
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
      margin-top: 8px;
      display: flex;
      gap: 4px;
      align-items: center;
    }

    .attempt-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #E2E8F0;
      transition: all 0.3s;
    }

    .attempt-dot.used    { background: #FCA5A5; }
    .attempt-dot.current { background: var(--scale-color); }

    .attempts-label {
      font-size: 11px;
      color: #94A3B8;
      margin-left: 6px;
      font-weight: 600;
    }

    /* ─── Completion ───────────────────────────────────────────── */
    .completion {
      text-align: center;
      margin-top: 32px;
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
      font-size: 15px;
      line-height: 1.6;
      margin: 0 0 20px;
      max-width: 380px;
    }

    .completion-stats {
      display: flex;
      justify-content: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .stat-box {
      background: #F0FDFA;
      border-radius: 12px;
      padding: 12px 20px;
    }

    .stat-box.attempts { background: #F8FAFC; }

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

    .stat-box.attempts .stat-value { color: #334155; }

    .stat-divisor {
      font-size: 16px;
      color: #94A3B8;
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
    margin-top: 10px;
    padding: 10px 15px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    }

    .feedback.error {
        background: #FEE2E2;
        color: #991B1B;
        border: 1px solid #FCA5A5;
    }

    .feedback.success {
        background: #D1FAE5;
        color: #065F46;
        border: 1px solid #6EE7B7;
    }
  </style>
  @endpush
<div class="flex items-center justify-between pb-6">
    <h1 class="text-xl font-bold whitespace-nowrap">
        Task 2
    </h1>

    <a href="{{ url()->previous() }}"
       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
        ← Back
    </a>
</div>
 <!-- ─── Loading screen ───────────────────────────────────────── -->
  <div id="loading-screen" class="screen">
    <div>
      <div id="loading-emoji">🧮</div>
      <p id="loading-text">Loading your budget...</p>
    </div>
  </div>

  <!-- ─── Intro screen ─────────────────────────────────────────── -->
  <div id="intro-screen" class="screen hidden">
    <div class="intro-card">
      <div class="intro-icon">🧮</div>
      <h1 class="intro-title">Budget Math!</h1>
      <p class="intro-description">
        Time to crunch the numbers! Use your bank statement totals to answer 8 calculation questions about your budget.
      </p>

      <div class="intro-bank" id="intro-bank">
        <div class="intro-bank-title" id="intro-bank-title"></div>
        <div class="intro-bank-grid" id="intro-bank-grid"></div>
      </div>

      <div class="intro-points-note">
        <p><strong>Finhero Badge Points: up to 24</strong> — up to 3 points per question. Get it right on your first approach for 3 points; each time you use up your tries, the hint gets stronger and the points available go down: 2, then 1, then 0 — but you can always keep trying.</p>
      </div>

      <button class="start-button" id="start-button">Let's Go! 🚀</button>
    </div>
  </div>

  <!-- ─── Game screen ──────────────────────────────────────────── -->
  <div id="game-screen" class="hidden">
    <div class="container">
      <div class="header">
        <h1 class="game-title">Task 2: Budget Calculations</h1>
        <p class="game-subtitle">Answer each question using your bank statement numbers</p>
      </div>

      <div class="stats-row">
        <div class="stat-pill points" id="stat-points">🏆 Finhero Badge Points: 0 / 24</div>
        <div class="stat-pill done" id="stat-done">✅ 0 / 8 done</div>
      </div>

      <div class="bank-sticky" id="bank-sticky"></div>

      <div class="progress-bar">
        <div class="progress-fill" id="progress-fill"></div>
      </div>
      <div id="extra-feedback"></div>
      <br>
      <div class="questions" id="questions"></div>

      <div class="completion hidden" id="completion">
        <div class="confetti-row">
          <span class="confetti-emoji">🎉</span>
          <span class="confetti-emoji">⭐</span>
          <span class="confetti-emoji">🧮</span>
          <span class="confetti-emoji">🏆</span>
          <span class="confetti-emoji">✨</span>
        </div>
        <div class="completion-card">
          <div class="completion-icon">🧮</div>
          <h2 class="completion-title">Budget Math Complete!</h2>
          <p class="completion-text" id="completion-text"></p>
          <div class="completion-stats">
            <div class="stat-box">
              <div class="stat-label">Finhero Badge Points</div>
              <div class="stat-value">
                <span id="final-points">0</span>
                <span class="stat-divisor">/ 24</span>
              </div>
            </div>
            <div class="stat-box attempts">
              <div class="stat-label">First-Try Correct</div>
              <div class="stat-value">
                <span id="final-firsttry">0</span>
                <span class="stat-divisor">/ 8</span>
              </div>
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
        return {{ $salary ?? 0 }};
    }
    const SALARY_SCALES = {
      2000: { label: "Low Income",          emoji: "🟢", color: "#059669", bg: "#D1FAE5", border: "#6EE7B7", income: 2000, needs: 1100, wants: 500,  savings: 400,  cutAmount: 200, saveGoal: 1000 },
      4000: { label: "Middle Income",       emoji: "🟡", color: "#CA8A04", bg: "#FEF9C3", border: "#FDE047", income: 4000, needs: 2000, wants: 1200, savings: 800,  cutAmount: 400, saveGoal: 2000 },
      6000: { label: "Upper-Middle Income", emoji: "🟠", color: "#EA580C", bg: "#FED7AA", border: "#FB923C", income: 6000, needs: 2700, wants: 1800, savings: 1500, cutAmount: 600, saveGoal: 3000 },
      9000: { label: "High Income",         emoji: "🟣", color: "#7C3AED", bg: "#EDE9FE", border: "#C4B5FD", income: 9000, needs: 3600, wants: 2700, savings: 2700, cutAmount: 900, saveGoal: 5000 },
    };

    function getScale(salary) {
      if (salary <= 2500) return SALARY_SCALES[2000];
      if (salary <= 5000) return SALARY_SCALES[4000];
      if (salary <= 7500) return SALARY_SCALES[6000];
      return SALARY_SCALES[9000];
    }

    function buildQuestions(s) {
      const totalSpending     = s.needs + s.wants;
      const leftover          = s.income - totalSpending - s.savings;
      const needsMoreThanWants= s.needs - s.wants;
      const newWants          = s.wants - s.cutAmount;
      const newSavings        = s.savings + s.cutAmount;
      const moreToSave        = s.saveGoal - s.savings;
      const threeMonths       = s.income * 3;
      const needsPerWeek      = Math.round(s.needs / 4);

      const fmt = n => n.toLocaleString();

      return [
        {
          id: 1,
          question: "How much do you spend in total? (Needs + Wants)",
          answer: totalSpending,
          hint: `Add your Needs and Wants together: ${fmt(s.needs)} + ${fmt(s.wants)}`,
          steps: `${fmt(s.needs)} + ${fmt(s.wants)} = ${fmt(totalSpending)} zeds`,
          operation: "addition",
        },
        {
          id: 2,
          question: "After all spending and saving, how much is left over?",
          answer: leftover,
          hint: `Start with your Income, then subtract total spending and savings: ${fmt(s.income)} − ${fmt(totalSpending)} − ${fmt(s.savings)}`,
          steps: `${fmt(s.income)} − ${fmt(totalSpending)} = ${fmt(s.income - totalSpending)}\n${fmt(s.income - totalSpending)} − ${fmt(s.savings)} = ${fmt(leftover)} zeds`,
          operation: "subtraction",
        },
        {
          id: 3,
          question: "How much more do you spend on Needs than on Wants?",
          answer: needsMoreThanWants,
          hint: `Subtract the smaller from the bigger: ${fmt(s.needs)} − ${fmt(s.wants)}`,
          steps: `${fmt(s.needs)} − ${fmt(s.wants)} = ${fmt(needsMoreThanWants)} zeds`,
          operation: "subtraction",
        },
        {
          id: 4,
          question: `If you cut your Wants by ${fmt(s.cutAmount)} zeds, what is your new Wants total?`,
          answer: newWants,
          hint: `Take your Wants and subtract the cut: ${fmt(s.wants)} − ${fmt(s.cutAmount)}`,
          steps: `${fmt(s.wants)} − ${fmt(s.cutAmount)} = ${fmt(newWants)} zeds`,
          operation: "subtraction",
        },
        {
          id: 5,
          question: `If you move those ${fmt(s.cutAmount)} zeds to Savings, what is your new Savings?`,
          answer: newSavings,
          hint: `Add the cut amount to your current Savings: ${fmt(s.savings)} + ${fmt(s.cutAmount)}`,
          steps: `${fmt(s.savings)} + ${fmt(s.cutAmount)} = ${fmt(newSavings)} zeds`,
          operation: "addition",
        },
        {
          id: 6,
          question: "Split your Needs equally across 4 weeks. How much per week?",
          answer: needsPerWeek,
          hint: `Divide your Needs by 4: ${fmt(s.needs)} ÷ 4`,
          steps: `${fmt(s.needs)} ÷ 4 = ${fmt(needsPerWeek)} zeds`,
          operation: "division",
          tolerance: 5,
        },
        /*{
          id: 6,
          question: `You want to save ${fmt(s.saveGoal)} zeds. How much more do you need?`,
          answer: moreToSave,
          hint: `Subtract what you already have from your goal: ${fmt(s.saveGoal)} − ${fmt(s.savings)}`,
          steps: `${fmt(s.saveGoal)} − ${fmt(s.savings)} = ${fmt(moreToSave)} zeds`,
          operation: "subtraction",
        },*/
        {
          id: 7,
          question: `If you earn ${fmt(s.income)} for 3 months, how much total income?`,
          answer: threeMonths,
          hint: `Multiply your monthly income by 3: ${fmt(s.income)} × 3`,
          steps: `${fmt(s.income)} × 3 = ${fmt(threeMonths)} zeds`,
          operation: "multiplication",
        },
        {
          id: 8,
          //question: `You want to save ${fmt(s.saveGoal)} zeds. How much more do you need?`,
          //question: `If you want to save ${fmt(s.saveGoal)} zeds instead of 1,500 zeds, how many more do you need?`,
          question: `If you want to save ${fmt(s.saveGoal)} zeds, how many more do you need?`,
          answer: moreToSave,
          hint: `Subtract what you already have from your goal: ${fmt(s.saveGoal)} − ${fmt(s.savings)}`,
          steps: `${fmt(s.saveGoal)} − ${fmt(s.savings)} = ${fmt(moreToSave)} zeds`,
          operation: "subtraction",
        },
        /*{
          id: 8,
          question: "Split your Needs equally across 4 weeks. How much per week?",
          answer: needsPerWeek,
          hint: `Divide your Needs by 4: ${fmt(s.needs)} ÷ 4`,
          steps: `${fmt(s.needs)} ÷ 4 = ${fmt(needsPerWeek)} zeds`,
          operation: "division",
          tolerance: 5,
        },*/
      ];
    }

    const OP_ICONS = { addition: "➕", subtraction: "➖", multiplication: "✖️", division: "➗" };

    // Zedville_FinHero_Point_Values_v1.pdf §2 "The Approach System" - same
    // tier table as app/Services/FinheroApproachSystem.php (the server is
    // the actual source of truth for points; this mirrors it client-side
    // only to drive which hint/attempts-left UI to show).
    const APPROACH_TIERS = [
      { maxWrongAttempts: 2,        points: 3, hint: "none" },
      { maxWrongAttempts: 2,        points: 2, hint: "light" },
      { maxWrongAttempts: 2,        points: 1, hint: "full" },
      { maxWrongAttempts: Infinity, points: 0, hint: "full" },
    ];

    function redactFinalStep(stepsText) {
      const lines = stepsText.split("\n");
      const last = lines[lines.length - 1].replace(/=\s*[\d,]+(\s*zeds)?\s*$/, "= ?");
      return [...lines.slice(0, -1), last].join("\n");
    }

    // ───────────────────────────────────────────────────────────────
    // STATE
    // ───────────────────────────────────────────────────────────────
    const state = {
      salary: null,
      scale: null,
      questions: [],
      // Per-question state
      qState: {},  // { [id]: { tier: 0, wrongInTier: 0, status: null, earned: null, input: '' } }
      scores: {},  // { [id]: pts }
      firstTryCount: 0,
    };

    // ───────────────────────────────────────────────────────────────
    // DOM REFS
    // ───────────────────────────────────────────────────────────────
    const $ = (id) => document.getElementById(id);

    // ───────────────────────────────────────────────────────────────
    // INITIALIZE
    // ───────────────────────────────────────────────────────────────
    (async function init() {
      state.salary    = await getStudentSalary();
      state.scale     = getScale(state.salary);
      state.questions = buildQuestions(state.scale);

      // Initialize per-question state
      state.questions.forEach(q => {
        state.qState[q.id] = { tier: 0, wrongInTier: 0, status: null, earned: null, input: "" };
      });

      // Apply scale CSS variables to document
      document.documentElement.style.setProperty("--scale-color",  state.scale.color);
      document.documentElement.style.setProperty("--scale-bg",     state.scale.bg);
      document.documentElement.style.setProperty("--scale-border", state.scale.border);

      buildIntroBank();
      buildBankSticky();
      buildQuestionsUI();

      $("loading-screen").classList.add("hidden");
      $("intro-screen").classList.remove("hidden");
    })();

    $("start-button").addEventListener("click", () => {
      $("intro-screen").classList.add("hidden");
      $("game-screen").classList.remove("hidden");
    });

    // ───────────────────────────────────────────────────────────────
    // BUILD INTRO BANK STATEMENT
    // ───────────────────────────────────────────────────────────────
    function buildIntroBank() {
      const s = state.scale;
      $("intro-bank-title").textContent =
        `${s.emoji} Your Bank Statement — ${s.label} (${state.salary.toLocaleString()} zeds/month)`;

      const items = [
        { label: "Income",  val: s.income,  icon: "💰" },
        { label: "Needs",   val: s.needs,   icon: "🏠" },
        { label: "Wants",   val: s.wants,   icon: "🎮" },
        { label: "Savings", val: s.savings, icon: "🏦" },
      ];

      $("intro-bank-grid").innerHTML = items.map(item => `
        <div class="bank-tile">
          <span class="bank-tile-icon">${item.icon}</span>
          <div>
            <div class="bank-tile-label">${item.label}</div>
            <div class="bank-tile-value">${item.val.toLocaleString()}</div>
          </div>
        </div>
      `).join("");
    }

    // ───────────────────────────────────────────────────────────────
    // BUILD STICKY BANK BAR
    // ───────────────────────────────────────────────────────────────
    function buildBankSticky() {
      const s = state.scale;
      const items = [
        { label: "Income",  val: s.income,  icon: "💰" },
        { label: "Needs",   val: s.needs,   icon: "🏠" },
        { label: "Wants",   val: s.wants,   icon: "🎮" },
        { label: "Savings", val: s.savings, icon: "🏦" },
      ];
      $("bank-sticky").innerHTML = items.map(item => `
        <div class="bank-item">
          <span class="bank-item-icon">${item.icon}</span>
          <span class="bank-item-label">${item.label}:</span>
          <span class="bank-item-value">${item.val.toLocaleString()}</span>
        </div>
      `).join("");
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
        card.innerHTML = `
          <div class="question-header">
            <div class="question-number">${q.id}</div>
            <div class="question-text">${q.question}</div>
            <div class="question-op">${OP_ICONS[q.operation]}</div>
          </div>
          <div class="msg-hint hidden"></div>
          <div class="msg-revealed hidden"></div>
          <div class="msg-correct hidden"></div>
          <div class="msg-wrong hidden">Not quite right, try again! ✨</div>
          <div class="input-row">
            <div class="input-wrapper">
              <input type="text" inputmode="numeric" placeholder="Type your answer..." />
              <span class="currency-suffix">zeds</span>
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
          const cleaned = e.target.value.replace(/[^0-9,.-]/g, "");
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
      const num = parseInt(qs.input.replace(/,/g, ""), 10);
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
                source_key: 'task_2_question_' + qid,
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
        const ptsText = qs.earned > 0 ? ` +${qs.earned} Finhero Badge Point${qs.earned === 1 ? '' : 's'}!` : '';
        corrEl.innerHTML = `
          <span class="msg-correct-icon">🎉</span>
          <span>${q.answer.toLocaleString()} zeds — Correct!${ptsText}</span>
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
    const TASK2_TOTAL_POINTS = 24;

    function updateProgress() {
      const completedCount = Object.keys(state.scores).length;
      const totalPoints    = Object.values(state.scores).reduce((a, b) => a + b, 0);

      $("stat-points").textContent = `🏆 Finhero Badge Points: ${totalPoints} / ${TASK2_TOTAL_POINTS}`;
      $("stat-done").textContent   = `✅ ${completedCount} / 8 done`;
      $("progress-fill").style.width = ((completedCount / 8) * 100) + "%";
    }

    // ───────────────────────────────────────────────────────────────
    // CHECK ALL DONE — show completion
    // ───────────────────────────────────────────────────────────────
    function checkAllDone() {
      const completedCount = Object.keys(state.scores).length;
      if (completedCount !== 8) return;

      const totalPoints = Object.values(state.scores).reduce((a, b) => a + b, 0);
      let message;
      if (totalPoints === TASK2_TOTAL_POINTS) message = "Perfect score! You nailed every question on the first try! 🌟";
      else if (totalPoints >= 18)              message = "Great work! You've got a strong handle on your budget numbers!";
      else if (totalPoints >= 1)               message = "Good effort! Review the hints and steps to strengthen your budget skills.";
      else                                     message = "Don't worry — now you know how to break down a budget! Practice makes perfect.";

      $("completion-text").textContent = message;
      $("final-points").textContent    = totalPoints;
      $("final-firsttry").textContent  = state.firstTryCount;
      $("completion").classList.remove("hidden");
    }
  </script>
  @endpush