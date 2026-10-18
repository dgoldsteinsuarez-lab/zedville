@extends('layouts.profile')

@section('title', 'Classify Your Salary!')

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
      max-width: 640px;
      margin: 0 auto;
      padding: 24px 16px;
    }

    .hidden { display: none !important; }

    /* ─── Loading screen ──────────────────────────────────────────── */
    #loading-emoji {
      font-size: 48px;
      margin-bottom: 16px;
      animation: pulse 1.5s infinite;
      text-align: center;
    }

    #loading-text {
      color: #0D9488;
      font-weight: 700;
      font-size: 18px;
      text-align: center;
    }

    /* ─── Intro screen ────────────────────────────────────────────── */
    .intro-card {
      max-width: 560px;
      background: white;
      border-radius: 24px;
      padding: 40px 36px;
      box-shadow: 0 20px 60px rgba(13, 148, 136, 0.12), 0 0 0 1px rgba(13, 148, 136, 0.06);
      text-align: center;
    }

    .intro-icon {
      font-size: 56px;
      margin-bottom: 12px;
    }

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

    .intro-instructions {
      background: #F0FDFA;
      border-radius: 16px;
      padding: 20px 24px;
      margin-bottom: 24px;
      text-align: left;
    }

    .intro-instructions-title {
      font-weight: 700;
      color: #0D9488;
      margin: 0 0 12px;
      font-size: 15px;
    }

    .intro-instructions p {
      color: #334155;
      font-size: 14px;
      line-height: 1.6;
      margin: 0 0 8px;
    }

    .intro-instructions p:last-child { margin-bottom: 0; }

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

    /* ─── Main game header ────────────────────────────────────────── */
    .header { text-align: center; margin-bottom: 20px; }

    .game-title {
      font-family: 'Fredoka One', cursive;
      font-size: 24px;
      color: #0F172A;
      margin: 0 0 4px;
    }

    .game-subtitle {
      color: #64748B;
      font-size: 14px;
      margin: 0;
    }

    /* ─── Status badges ───────────────────────────────────────────── */
    .status-row {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    .status-pill {
      background: white;
      border-radius: 10px;
      padding: 6px 14px;
      font-size: 13px;
      font-weight: 700;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .status-approach { color: #0D9488; }
    .status-points { color: #CA8A04; }
    .status-attempts { color: #DC2626; }

    /* ─── Salary card (draggable) ─────────────────────────────────── */
    .salary-wrapper {
      display: flex;
      justify-content: center;
      margin-bottom: 24px;
    }

    .salary-card {
      background: linear-gradient(135deg, #14B8A6, #0D9488);
      color: white;
      border-radius: 18px;
      padding: 18px 36px;
      font-size: 22px;
      font-weight: 900;
      cursor: grab;
      box-shadow: 0 8px 24px rgba(13, 148, 136, 0.25);
      transition: all 0.25s cubic-bezier(.4, 0, .2, 1);
      animation: float 3s ease-in-out infinite;
      user-select: none;
      text-align: center;
      position: relative;
    }

    .salary-card.dragging {
      background: linear-gradient(135deg, #0D9488, #0F766E);
      box-shadow: 0 12px 40px rgba(13, 148, 136, 0.4), 0 0 0 3px rgba(13, 148, 136, 0.2);
      transform: scale(1.05);
      animation: none;
    }

    .salary-card-hint {
      font-size: 14px;
      font-weight: 600;
      opacity: 0.8;
      margin-bottom: 2px;
    }

    /* ─── Feedback message ────────────────────────────────────────── */
    .feedback {
      text-align: center;
      margin-bottom: 16px;
      padding: 10px 20px;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 700;
    }

    .feedback.success { background: #D1FAE5; color: #065F46; animation: pop 0.35s; }
    .feedback.error { background: #FEE2E2; color: #991B1B; animation: shake 0.4s; }
    .feedback.hint { background: #FEF9C3; color: #92400E; animation: pop 0.35s; }

    /* ─── Income levels (drop zones) ──────────────────────────────── */
    .levels {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .level {
      background: white;
      border-radius: 16px;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      gap: 14px;
      cursor: default;
      border: 3px solid transparent;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
      transition: all 0.2s ease;
      position: relative;
      overflow: hidden;
    }

    .level.dragging-mode { cursor: pointer; }

    .level.hovered {
      border-color: var(--level-border);
      background: var(--level-bg-hover);
      box-shadow: 0 4px 16px var(--level-shadow);
    }

    .level.correct {
      background: var(--level-bg);
      border-color: var(--level-color);
      box-shadow: 0 4px 20px var(--level-shadow-strong);
      animation: pop 0.4s;
    }

    .level.wrong {
      background: #FEE2E2;
      border-color: #FCA5A5;
    }

    .level.glow {
      animation: glowPulse 1.5s infinite;
    }

    .level-emoji { font-size: 28px; line-height: 1; }

    .level-info { flex: 1; }

    .level-label {
      font-weight: 800;
      font-size: 16px;
      color: var(--level-color);
    }

    .level-term {
      font-size: 13px;
      color: #64748B;
      font-weight: 600;
    }

    .level-range-hint {
      font-size: 12px;
      color: var(--level-color);
      font-weight: 700;
      margin-top: 2px;
      animation: pop 0.3s;
      display: none;
    }

    .level.show-range .level-range-hint,
    .level.glow .level-range-hint,
    .level.correct .level-range-hint {
      display: block;
    }

    .level-range {
      font-size: 14px;
      color: #94A3B8;
      font-weight: 700;
      min-width: 90px;
      text-align: right;
      opacity: 0.4;
    }

    .level.show-range .level-range,
    .level.glow .level-range {
      color: var(--level-color);
      opacity: 1;
    }

    .level.correct .level-range {
      display: none;
    }

    .level-correct-badge {
      color: var(--level-color);
      font-size: 16px;
      font-weight: 700;
      min-width: 90px;
      text-align: right;
      display: none;
    }

    .level.correct .level-correct-badge {
      display: block;
    }

    .level-wrong-mark {
      position: absolute;
      right: 16px;
      font-size: 18px;
      opacity: 0.5;
      display: none;
    }

    .level.wrong:not(.correct) .level-wrong-mark {
      display: block;
    }

    /* ─── Completion screen ───────────────────────────────────────── */
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
      padding: 24px 28px;
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
      margin: 0 0 16px;
      max-width: 380px;
    }

    .completion-text strong { font-weight: 800; }

    .completion-stats {
      display: flex;
      justify-content: center;
      gap: 16px;
      flex-wrap: wrap;
    }

    .stat-box {
      background: #F0FDFA;
      border-radius: 12px;
      padding: 10px 18px;
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
      font-size: 28px;
      font-weight: 900;
      color: #0D9488;
    }

    .stat-box.attempts .stat-value { color: #334155; }

    /* ─── Animations ──────────────────────────────────────────────── */
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

    @keyframes pop {
      0% { transform: scale(0.8); opacity: 0; }
      50% { transform: scale(1.08); }
      100% { transform: scale(1); opacity: 1; }
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-6px); }
    }

    @keyframes confetti {
      0% { transform: translateY(0) rotate(0); opacity: 1; }
      100% { transform: translateY(-120px) rotate(360deg); opacity: 0; }
    }

    @keyframes glowPulse {
      0%, 100% { box-shadow: 0 0 8px rgba(13, 148, 136, 0.3); }
      50% { box-shadow: 0 0 24px rgba(13, 148, 136, 0.6); }
    }
  </style>
  @endpush
  <div class="flex items-center justify-between pb-6">
    <h1 class="text-xl font-bold whitespace-nowrap">
        Task 1
    </h1>

    <a href="{{ url()->previous() }}"
       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700">
        ← Back
    </a>
</div>
 <!-- ─── Loading screen ─────────────────────────────────────────── -->
  <div id="loading-screen" class="screen">
    <div>
      <div id="loading-emoji">💰</div>
      <p id="loading-text">Loading your salary...</p>
    </div>
  </div>
  <!-- ─── Intro screen ───────────────────────────────────────────── -->
  <div id="intro-screen" class="screen hidden">
    <div class="intro-card">
      <div class="intro-icon">🏦</div>
      <h1 class="intro-title">Classify Your Salary!</h1>
      <p class="intro-description">
        You just got your monthly bank statement. Before we dive into the numbers, let's figure out where your income sits on the scale.
      </p>
      <div class="intro-instructions">
        <p class="intro-instructions-title">What You'll Do:</p>
        <p>👉 You'll see your salary as a draggable card. Drag it (or tap it, then tap a level) into the income category where it belongs.</p>
        <p>👉 Each income level has a range — find the one that fits your number!</p>
      </div>
      <div class="intro-points-note">
        <p><strong>Finhero Badge Points: up to 3</strong> — get it right on your first try for 3 points. Each wrong try lowers the hint tier and the points available: 2 on your second approach, 1 on your third. After that, no more points, but no limit on tries either.</p>
      </div>
      <button class="start-button" id="start-button">Let's Go! 🚀</button>
    </div>
  </div>

  <!-- ─── Main game screen ───────────────────────────────────────── -->
  <div id="game-screen" class="hidden">
    <div class="container">
      <div class="header">
        <h1 class="game-title">Task 1: Classify Your Salary</h1>
        <p class="game-subtitle">Drag your salary into the correct income level</p>
      </div>

      <div class="status-row">
        <div class="status-pill status-approach" id="status-approach">Approach 1 of 4</div>
        <div class="status-pill status-points" id="status-points">🏆 Finhero Badge Points: 3</div>
        <div class="status-pill status-attempts" id="status-attempts">Attempts left: 1</div>
      </div>

      <!-- Salary card -->
      <div class="salary-wrapper" id="salary-wrapper">
        <div class="salary-card" id="salary-card" draggable="true">
          <div class="salary-card-hint" id="salary-hint">💰 Drag me! (or tap me first)</div>
          <div id="salary-amount">— zeds/month</div>
        </div>
      </div>

      <!-- Feedback -->
      <div class="feedback hidden" id="feedback"></div>
      <div class="feedback hidden" id="extra-feedback"></div>

      <!-- Income levels (drop zones) — populated by JS -->
      <div class="levels" id="levels"></div>

      <!-- Completion screen -->
      <div class="completion hidden" id="completion">
        <div class="confetti-row">
          <span class="confetti-emoji">🎉</span>
          <span class="confetti-emoji">⭐</span>
          <span class="confetti-emoji">💰</span>
          <span class="confetti-emoji">🏆</span>
          <span class="confetti-emoji">✨</span>
        </div>
        <div class="completion-card">
          <div class="completion-icon">🏦</div>
          <h2 class="completion-title">Salary Classified!</h2>
          <p class="completion-text" id="completion-text"></p>
          <div class="completion-stats">
            <div class="stat-box">
              <div class="stat-label">Finhero Badge Points</div>
              <div class="stat-value" id="stat-points">0</div>
            </div>
            <div class="stat-box attempts">
              <div class="stat-label">Total Attempts</div>
              <div class="stat-value" id="stat-attempts">0</div>
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
    const INCOME_LEVELS = [
      { id: "low",       label: "Low Income",          term: "Entry-Level / Minimum Wage", range: "1,500 – 2,500 zeds", min: 1500, max: 2500,     emoji: "🟢", color: "#059669", bg: "#D1FAE5", bgHover: "#A7F3D0", border: "#6EE7B7" },
      { id: "lower-mid", label: "Lower-Middle Income", term: "Junior / Early Career",      range: "2,500 – 3,500 zeds", min: 2500, max: 3500,     emoji: "🔵", color: "#2563EB", bg: "#DBEAFE", bgHover: "#BFDBFE", border: "#93C5FD" },
      { id: "middle",    label: "Middle Income",       term: "Mid-Level / Skilled Worker", range: "3,500 – 5,000 zeds", min: 3500, max: 5000,     emoji: "🟡", color: "#CA8A04", bg: "#FEF9C3", bgHover: "#FEF08A", border: "#FDE047" },
      { id: "upper-mid", label: "Upper-Middle Income", term: "Senior / Specialist",        range: "5,000 – 7,500 zeds", min: 5000, max: 7500,     emoji: "🟠", color: "#EA580C", bg: "#FED7AA", bgHover: "#FDBA74", border: "#FB923C" },
      { id: "high",      label: "High Income",         term: "Manager / Executive",        range: "7,500+ zeds",        min: 7500, max: Infinity, emoji: "🟣", color: "#7C3AED", bg: "#EDE9FE", bgHover: "#DDD6FE", border: "#C4B5FD" },
    ];

    // Zedville_FinHero_Point_Values_v1.pdf §2 "The Approach System".
    // maxAttempts here counts WRONG attempts allowed before escalating to
    // the next tier (doc: "2 wrong attempts" per tier, not 2 total tries).
    const APPROACHES = [
      { maxAttempts: 2,        points: 3, hint: null },
      { maxAttempts: 2,        points: 2, hint: "salary_ranges" },
      { maxAttempts: 2,        points: 1, hint: "correct_glow" },
      { maxAttempts: Infinity, points: 0, hint: "correct_glow" },
    ];

    function getCorrectLevel(salary) {
      return INCOME_LEVELS.find(l => salary >= l.min && salary < l.max) || INCOME_LEVELS[INCOME_LEVELS.length - 1];
    }

    // ╔════════════════════════════════════════════════════════════╗
    // ║  🔌 IT TEAM: REPLACE THIS WITH A REAL API CALL             ║
    // ║                                                            ║
    // ║  Example:                                                  ║
    // ║    async function getStudentSalary() {                     ║
    // ║      const r = await fetch('/api/current-student/salary'); ║
    // ║      const d = await r.json();                             ║
    // ║      return d.salary;                                      ║
    // ║    }                                                       ║
    // ╚════════════════════════════════════════════════════════════╝
    async function getStudentSalary() {
      //return 4000;
      return {{ $salary }};
    }

    // ───────────────────────────────────────────────────────────────
    // STATE
    // ───────────────────────────────────────────────────────────────
    const state = {
      salary: null,
      approach: 0,
      attempts: 0,
      totalAttempts: 0,
      completed: false,
      earnedPoints: 0,
      dragging: false,
      hoveredLevel: null,
      wrongIds: [],
      correctLevel: null,
    };

    // ───────────────────────────────────────────────────────────────
    // DOM REFS
    // ───────────────────────────────────────────────────────────────
    const $ = (id) => document.getElementById(id);
    const loadingScreen = $("loading-screen");
    const introScreen   = $("intro-screen");
    const gameScreen    = $("game-screen");
    const startButton   = $("start-button");
    const salaryCard    = $("salary-card");
    const salaryAmount  = $("salary-amount");
    const salaryHint    = $("salary-hint");
    const salaryWrapper = $("salary-wrapper");
    const feedbackEl    = $("feedback");
    const levelsEl      = $("levels");
    const completionEl  = $("completion");

    // ───────────────────────────────────────────────────────────────
    // INITIALIZE
    // ───────────────────────────────────────────────────────────────
    (async function init() {
      state.salary = await getStudentSalary();
      state.correctLevel = getCorrectLevel(state.salary);
      salaryAmount.textContent = state.salary.toLocaleString() + " zeds/month";
      loadingScreen.classList.add("hidden");
      introScreen.classList.remove("hidden");
    })();

    startButton.addEventListener("click", () => {
      introScreen.classList.add("hidden");
      gameScreen.classList.remove("hidden");
      buildLevels();
      updateStatus();
    });

    // ───────────────────────────────────────────────────────────────
    // BUILD LEVELS (drop zones)
    // ───────────────────────────────────────────────────────────────
    function buildLevels() {
      levelsEl.innerHTML = "";
      INCOME_LEVELS.forEach(level => {
        const el = document.createElement("div");
        el.className = "level";
        el.dataset.id = level.id;
        el.style.setProperty("--level-color",        level.color);
        el.style.setProperty("--level-bg",           level.bg);
        el.style.setProperty("--level-bg-hover",     level.bgHover);
        el.style.setProperty("--level-border",       level.border);
        el.style.setProperty("--level-shadow",       level.color + "22");
        el.style.setProperty("--level-shadow-strong",level.color + "33");

        el.innerHTML = `
          <div class="level-emoji">${level.emoji}</div>
          <div class="level-info">
            <div class="level-label">${level.label}</div>
            <div class="level-term">${level.term}</div>
            <div class="level-range-hint">${level.range}</div>
          </div>
          <div class="level-range">${level.range}</div>
          <div class="level-correct-badge">✅ Correct!</div>
          <div class="level-wrong-mark">❌</div>
        `;

        // Drag-and-drop handlers
        el.addEventListener("dragover",  (e) => { e.preventDefault(); setHovered(level.id); });
        el.addEventListener("dragleave", () => setHovered(null));
        el.addEventListener("drop",      (e) => { e.preventDefault(); handleDrop(level.id); });
        // Tap-to-place handler (used after tapping the salary card on touch devices)
        el.addEventListener("click",     () => {
          if (state.completed || !state.dragging) return;
          handleDrop(level.id);
        });

        levelsEl.appendChild(el);
      });
    }

    // ───────────────────────────────────────────────────────────────
    // SALARY CARD INTERACTIONS
    // ───────────────────────────────────────────────────────────────
    salaryCard.addEventListener("dragstart", (e) => {
      state.dragging = true;
      hideFeedback();
      state.wrongIds = [];
      refreshLevels();
      e.dataTransfer.setData("text/plain", "salary");
      e.dataTransfer.effectAllowed = "move";
      salaryCard.classList.add("dragging");
      updateSalaryHint();
    });

    salaryCard.addEventListener("dragend", () => {
      state.dragging = false;
      salaryCard.classList.remove("dragging");
      setHovered(null);
      updateSalaryHint();
    });

    salaryCard.addEventListener("touchstart", () => {
      state.dragging = true;
      hideFeedback();
      state.wrongIds = [];
      refreshLevels();
      salaryCard.classList.add("dragging");
      updateSalaryHint();
    });

    // Tap salary card on desktop too — toggles "select" mode
    salaryCard.addEventListener("click", () => {
      if (state.completed) return;
      state.dragging = !state.dragging;
      salaryCard.classList.toggle("dragging", state.dragging);
      updateSalaryHint();
    });

    function updateSalaryHint() {
      salaryHint.textContent = state.dragging
        ? "Now tap a level below! 👇"
        : "💰 Drag me! (or tap me first)";
    }

    // ───────────────────────────────────────────────────────────────
    // CORE LOGIC
    // ───────────────────────────────────────────────────────────────
    function setHovered(id) {
      state.hoveredLevel = state.dragging ? id : null;
      refreshLevels();
    }

    function handleDrop(levelId) {
      if (state.completed) return;
      const correct = levelId === state.correctLevel.id;
      const currentApproach = APPROACHES[state.approach];

      if (correct) {
        const pts = currentApproach.points;
        state.earnedPoints  = pts;
        // Insert points only if first attempt correct
          if (pts > 0) {

            fetch("{{ route('finhero.save-points') }}", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                source_key: "{{ $activityKey }}_question_1",
                approach: state.approach
              })
            })
            .then(res => res.json())
            .then(data => {
              //console.log(data);
              if (data.success  === false) {
                  const extraFeedback = document.getElementById("extra-feedback");
                  extraFeedback.className = "feedback error";
                  extraFeedback.textContent = "You’ve already finished this task, but feel free to give it another go!";

              }
            });

          }
          // Insert points only if first attempt correct
        state.completed     = true;
        state.totalAttempts = state.totalAttempts; // no increment on success (matches JSX)
        const text = pts > 0
          ? `Correct! Your salary of ${state.salary.toLocaleString()} zeds falls under ${state.correctLevel.label} (${state.correctLevel.term}). You earned ${pts} Finhero Badge Point${pts === 1 ? '' : 's'}! 🎉`
          : `Correct! Your salary of ${state.salary.toLocaleString()} zeds falls under ${state.correctLevel.label} (${state.correctLevel.term}). 🎉`;
        showFeedback("success", text);
        state.dragging = false;
        salaryCard.classList.remove("dragging");
        salaryWrapper.classList.add("hidden");
        showCompletion();
      } else {
        state.attempts      = state.attempts + 1;
        state.totalAttempts = state.totalAttempts + 1;
        if (!state.wrongIds.includes(levelId)) state.wrongIds.push(levelId);

        if (state.attempts >= currentApproach.maxAttempts) {
          if (state.approach < APPROACHES.length - 1) {
            state.approach += 1;
            state.attempts = 0;
            showFeedback("hint", "Let's try again with a hint! ✨");
          } else {
            showFeedback("error", "Not quite right, try again! ✨");
          }
        } else {
          showFeedback("error", "Not quite right, try again! ✨");
        }
        state.dragging = false;
        salaryCard.classList.remove("dragging");
      }

      setHovered(null);
      refreshLevels();
      updateStatus();
      updateSalaryHint();
    }

    // ───────────────────────────────────────────────────────────────
    // RENDER HELPERS
    // ───────────────────────────────────────────────────────────────
    function refreshLevels() {
      const currentApproach = APPROACHES[state.approach];
      const showHint = currentApproach.hint === "salary_ranges";
      const showGlow = currentApproach.hint === "correct_glow";

      INCOME_LEVELS.forEach(level => {
        const el = levelsEl.querySelector(`.level[data-id="${level.id}"]`);
        if (!el) return;

        const isCorrect = state.completed && level.id === state.correctLevel.id;
        const isWrong   = state.wrongIds.includes(level.id);
        const isHovered = state.hoveredLevel === level.id && state.dragging;

        el.classList.toggle("correct",       isCorrect);
        el.classList.toggle("wrong",         isWrong);
        el.classList.toggle("hovered",       isHovered);
        el.classList.toggle("show-range",    showHint);
        el.classList.toggle("glow",          showGlow && level.id === state.correctLevel.id && !state.completed);
        el.classList.toggle("dragging-mode", state.dragging && !state.completed);
      });
    }

    function updateStatus() {
      const currentApproach = APPROACHES[state.approach];
      $("status-approach").textContent = `Approach ${state.approach + 1} of ${APPROACHES.length}`;
      $("status-points").textContent   = `🏆 Finhero Badge Points: ${state.completed ? state.earnedPoints : currentApproach.points}`;

      const attemptsEl = $("status-attempts");
      if (!state.completed && currentApproach.maxAttempts !== Infinity) {
        attemptsEl.classList.remove("hidden");
        attemptsEl.textContent = `Attempts left: ${currentApproach.maxAttempts - state.attempts}`;
      } else {
        attemptsEl.classList.add("hidden");
      }
    }

    function showFeedback(type, text) {
      feedbackEl.className = "feedback " + type;
      feedbackEl.textContent = text;
      // Force reflow so animation re-triggers each time
      void feedbackEl.offsetWidth;
    }

    function hideFeedback() {
      feedbackEl.className = "feedback hidden";
      feedbackEl.textContent = "";

      const extraFeedback = document.getElementById("extra-feedback");
      extraFeedback.className = "feedback hidden";
      extraFeedback.textContent = "";
    }

    function showCompletion() {
      const correctLevel = state.correctLevel;
      $("completion-text").innerHTML =
        `Your income of <strong>${state.salary.toLocaleString()} zeds</strong> is ` +
        `<span style="color:${correctLevel.color};font-weight:800;">${correctLevel.label}</span> ` +
        `(${correctLevel.term}).`;
      $("stat-points").textContent   = state.earnedPoints;
      $("stat-attempts").textContent = state.totalAttempts + 1;
      completionEl.classList.remove("hidden");
    }
  </script>
  @endpush