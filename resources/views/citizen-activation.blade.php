@extends('layouts.profile')

@section('title', 'Dashboard')

@section('content')
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<style>
  /* ============================================================
     ZEDVILLE — CITIZEN ACTIVATION
     Typography rationale (for IT team):
       - Nunito 700/800: headings. Rounded letterforms tested well
         with 12–16 year olds; feels approachable, not childish.
       - Inter 400/500: body. Open letterforms, high x-height,
         excellent on screens at 17px.
       - Base size: 17px body (research-backed for teen screen reading)
       - Line-height: 1.75 for body (generous leading aids comprehension)
       - Never below 14px anywhere on screen.
  ============================================================ */

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --green-dark:   #085041;
    --green-mid:    #0F6E56;
    --green:        #1D9E75;
    --green-light:  #5DCAA5;
    --green-pale:   #E1F5EE;
    --green-border: #9FE1CB;
    --text-primary: #1A1A1A;
    --text-secondary: #4A5568;
    --text-muted:   #718096;
    --bg-page:      #F7F9F8;
    --bg-white:     #FFFFFF;
    --bg-surface:   #F0F4F2;
    --border:       #D1E4DC;
    --radius-sm:    8px;
    --radius-md:    12px;
    --radius-lg:    16px;
    --radius-xl:    24px;
  }

 

  /* ---- Layout ---- */
  .page-shell {
    max-width: 680px;
    margin: 0 auto;
    padding: 2rem 1.25rem 4rem;
  }

  /* ---- Progress bar ---- */
  .progress-header {
    margin-bottom: 2rem;
  }
  .progress-label {
    font-family: 'Inter', sans-serif;
    font-size: 0.76rem;
    font-weight: 600;
    color: var(--text-muted);
    letter-spacing: 0.07em;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .progress-track {
    display: flex;
    gap: 4px;
  }
  .progress-seg {
    height: 5px;
    flex: 1;
    border-radius: 3px;
    background: var(--border);
    transition: background 0.3s;
  }
  .progress-seg.done  { background: var(--green); }
  .progress-seg.active { background: var(--green-light); }

  /* ---- Step card ---- */
  .step {
    display: none;
    background: var(--bg-white);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    margin-bottom: 0;
  }
  .step.active { display: block; }

  .step-header {
    padding: 1.5rem 1.75rem 1.25rem;
    border-bottom: 1px solid var(--border);
  }
  .step-eyebrow {
    font-size: 0.76rem;
    font-weight: 600;
    color: var(--green-mid);
    letter-spacing: 0.07em;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
  }
  .step-title {
    font-family: 'Nunito', sans-serif;
    font-size: 1.55rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.25;
    margin-bottom: 0.4rem;
  }
  .step-subtitle {
    font-size: 0.94rem;
    color: var(--text-secondary);
    line-height: 1.6;
  }
  .step-subtitle .term {
    color: var(--green-mid);
    font-weight: 600;
  }

  /* ---- Step body ---- */
  .step-body {
    padding: 1.5rem 1.75rem;
  }

  /* ---- Narrative box ---- */
  .narrative {
    border-left: 4px solid var(--green);
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    background: var(--green-pale);
    padding: 1rem 1.25rem;
    font-size: 0.97rem;
    line-height: 1.8;
    color: var(--green-dark);
    margin-bottom: 1.25rem;
  }
  .narrative strong { font-weight: 600; }

  /* ---- Definition callout ---- */
  .definition {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 0.875rem 1.1rem;
    font-size: 0.88rem;
    color: var(--text-secondary);
    line-height: 1.65;
    margin-bottom: 1.25rem;
    display: flex;
    gap: 0.6rem;
    align-items: flex-start;
  }
  .definition-icon {
    font-size: 1.1rem;
    margin-top: 1px;
    flex-shrink: 0;
  }
  .definition strong { color: var(--text-primary); font-weight: 600; }

  /* ---- Info highlight ---- */
  .info-box {
    background: var(--green-pale);
    border: 1px solid var(--green-border);
    border-radius: var(--radius-md);
    padding: 1rem 1.25rem;
    font-size: 0.94rem;
    color: var(--green-dark);
    line-height: 1.7;
    margin-bottom: 1.25rem;
  }
  .info-box .info-row {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 0.2rem;
  }
  .info-box .info-row:last-child { margin-bottom: 0; }
  .info-box .info-label { font-weight: 600; min-width: 130px; }

  /* ---- Forms ---- */
  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
  }
  .form-grid.single { grid-template-columns: 1fr; }
  .field-group { display: flex; flex-direction: column; gap: 4px; }
  .field-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-secondary);
  }
  .field-input {
    font-family: 'Inter', sans-serif;
    font-size: 0.97rem;
    padding: 0.6rem 0.875rem;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--bg-white);
    color: var(--text-primary);
    outline: none;
    transition: border-color 0.2s;
  }
  .field-input:focus { border-color: var(--green); }
  .field-input.readonly {
    background: var(--bg-surface);
    color: var(--text-muted);
  }
  .field-textarea {
    font-family: 'Inter', sans-serif;
    font-size: 0.97rem;
    padding: 0.6rem 0.875rem;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    background: var(--bg-white);
    color: var(--text-primary);
    resize: vertical;
    min-height: 80px;
    outline: none;
  }
  .field-textarea:focus { border-color: var(--green); }

  /* ---- Checkbox rows ---- */
  .checkbox-group { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1.25rem; }
  .check-row {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    background: var(--bg-white);
  }
  .check-row:hover { border-color: var(--green-light); background: var(--green-pale); }
  .check-row.checked { border-color: var(--green); background: var(--green-pale); }
  .check-row.required { opacity: 0.7; cursor: default; }
  .check-box {
    width: 18px; height: 18px;
    border-radius: 4px;
    border: 1.5px solid var(--border);
    background: var(--bg-white);
    flex-shrink: 0;
    margin-top: 2px;
    display: flex; align-items: center; justify-content: center;
  }
  .check-row.checked .check-box, .check-row.required .check-box {
    background: var(--green); border-color: var(--green);
  }
  .check-box svg { display: none; }
  .check-row.checked .check-box svg,
  .check-row.required .check-box svg { display: block; }
  .check-text { flex: 1; font-size: 0.94rem; line-height: 1.5; }
  .check-text .check-title { font-weight: 600; color: var(--text-primary); }
  .check-text .check-desc  { font-size: 0.82rem; color: var(--text-muted); margin-top: 1px; }
  .check-text .required-tag {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 700;
    background: var(--green);
    color: #fff;
    border-radius: 4px;
    padding: 1px 6px;
    margin-left: 6px;
    vertical-align: middle;
  }

  /* ---- Survey cards ---- */
  .survey-options { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1.25rem; }
  .survey-card {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.875rem 1.1rem;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
    background: var(--bg-white);
  }
  .survey-card:hover { border-color: var(--green-light); background: var(--green-pale); }
  .survey-card.selected { border-color: var(--green); background: var(--green-pale); }
  .survey-icon { font-size: 1.3rem; flex-shrink: 0; }
  .survey-label { font-size: 0.97rem; font-weight: 600; color: var(--text-primary); }
  .survey-desc  { font-size: 0.85rem; color: var(--text-muted); margin-top: 1px; }
  .survey-check {
    margin-left: auto; width: 20px; height: 20px;
    border-radius: 50%; border: 1.5px solid var(--border);
    background: var(--bg-white); flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
  }
  .survey-card.selected .survey-check {
    background: var(--green); border-color: var(--green);
  }
  .survey-card.selected .survey-check::after {
    content: ''; width: 7px; height: 7px; border-radius: 50%; background: #fff;
  }

  /* ---- Legal block ---- */
  .legal-block {
    border-left: 3px solid var(--border);
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
    padding: 0.875rem 1rem;
    font-size: 0.85rem;
    font-style: italic;
    color: var(--text-secondary);
    line-height: 1.7;
    margin-bottom: 1.25rem;
    background: var(--bg-surface);
  }

  /* ---- Buttons ---- */
  .btn-row {
    display: flex; gap: 0.75rem; align-items: center;
    margin-top: 1.25rem;
    flex-wrap: wrap;
  }
  .btn-primary {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--green-mid);
    color: #fff;
    font-family: 'Inter', sans-serif;
    font-size: 0.97rem;
    font-weight: 600;
    padding: 0.7rem 1.5rem;
    border: none;
    border-radius: var(--radius-xl);
    cursor: pointer;
    transition: background 0.2s, transform 0.1s;
    text-decoration: none;
  }
  .btn-primary:hover  { background: var(--green-dark); }
  .btn-primary:active { transform: scale(0.98); }
  .btn-secondary {
    display: inline-flex; align-items: center; gap: 6px;
    background: #fff;
    color: #000;
    font-family: 'Inter', sans-serif;
    font-size: 0.9rem;
    font-weight: 500;
    padding: 0.65rem 1.1rem;
    border: 1px solid #000;
    border-radius: 30px;
    box-shadow: 0 3px 0 #000;
    cursor: pointer;
    transition: 0.2s;
  }
  .btn-secondary:hover { background: #fff; color: #000; transform: translateY(3px); box-shadow: 0 1px 0 #000; }

  /* ---- Completion screen ---- */
  .completion-banner {
    background: var(--green-pale);
    border: 1px solid var(--green-border);
    border-radius: var(--radius-md);
    padding: 1.5rem;
    text-align: center;
    margin-bottom: 1.5rem;
  }
  .completion-banner .big {
    font-family: 'Nunito', sans-serif;
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--green-dark);
    margin-bottom: 0.4rem;
  }
  .completion-banner .sub {
    font-size: 0.94rem;
    color: var(--green-mid);
    line-height: 1.6;
  }

  /* ---- Citizen Diary ---- */
  .diary-heading {
    font-family: 'Nunito', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.875rem;
    display: flex; align-items: center; gap: 0.5rem;
  }
  .diary-entry {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    padding: 1rem 1.25rem;
    margin-bottom: 0.75rem;
  }
  .diary-date {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--green-mid);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 0.4rem;
  }
  .diary-text {
    font-size: 0.94rem;
    color: var(--text-primary);
    line-height: 1.75;
  }
  .diary-text .term {
    color: var(--green-mid);
    font-weight: 600;
    text-decoration: underline;
    text-decoration-style: dotted;
    text-underline-offset: 3px;
  }

  /* ---- Spending snapshot ---- */
  .snapshot-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.55rem 0;
    border-bottom: 1px solid var(--border);
    font-size: 0.94rem;
  }
  .snapshot-row:last-child { border-bottom: none; }
  .snapshot-cat {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--green-mid);
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 0.75rem 0 0.3rem;
  }
  .snapshot-total {
    background: var(--green-mid);
    color: #fff;
    border-radius: var(--radius-md);
    padding: 0.875rem 1.25rem;
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 0.875rem;
  }
  .snapshot-total .label { font-size: 0.9rem; opacity: 0.85; }
  .snapshot-total .amount {
    font-family: 'Nunito', sans-serif;
    font-size: 1.4rem;
    font-weight: 800;
  }

  /* ---- Responsive ---- */
  @media (max-width: 540px) {
    html { font-size: 16px; }
    .form-grid { grid-template-columns: 1fr; }
    .step-title { font-size: 1.3rem; }
    .page-shell { padding: 1rem 1rem 3rem; }
  }
</style>
@endpush
<!-- <div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap ">Citizen Activation</h1>
</div> -->
<div class="grid grid-cols-1 gap-5">
<div class="page-shell">

  <!-- Progress indicator (shared across all steps) -->
  <div class="progress-header" id="progressHeader">
    <div class="progress-label">
      <span id="progressText">Citizen Activation — Step 1 of 6</span>
      <span id="progressPct">17%</span>
    </div>
    <div class="progress-track" id="progressTrack">
      <div class="progress-seg active" id="seg1"></div>
      <div class="progress-seg" id="seg2"></div>
      <div class="progress-seg" id="seg3"></div>
      <div class="progress-seg" id="seg4"></div>
      <div class="progress-seg" id="seg5"></div>
      <div class="progress-seg" id="seg6"></div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════
       STEP 1 — Welcome
  ══════════════════════════════════════════════════════════ -->
  <div class="step active" id="step1">
    <div class="step-header">
      <div class="step-eyebrow">Step 1 of 6 — Welcome</div>
      <div class="step-title">Welcome to Zedville, <span id="studentName">dan</span>!</div>
      <div class="step-subtitle">
        Your <span class="term">Citizen ID</span> is ready — this takes about 5 minutes to complete
      </div>
    </div>
    <div class="step-body">
      <div class="narrative">
        You are now officially a Zedville citizen. <strong>Everything here is virtual</strong> — the city, the money, the bills. You will earn a monthly <strong>salary in Zeds</strong> (our virtual currency), open a bank account, pay bills, and make real spending decisions — just like adult life.<br><br>
        Nothing costs real money. This is your space to learn by doing. Complete the steps below to activate your account and unlock the city.
      </div>
      <div class="info-box">
        <div class="info-row"><span class="info-label">Your Citizen ID</span><span>ZV-2026-0076</span></div>
        <div class="info-row"><span class="info-label">Your role</span><span>Zedville resident &amp; student</span></div>
        <div class="info-row"><span class="info-label">Time to complete</span><span>About 5 minutes</span></div>
      </div>
      <div class="btn-row">
        <button class="btn-primary" onclick="goTo(2)">Start activation →</button>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════
       STEP 2 — Open bank account
  ══════════════════════════════════════════════════════════ -->
  <div class="step" id="step2">
    <div class="step-header">
      <div class="step-eyebrow">Step 2 of 6 — Bank account</div>
      <div class="step-title">Open your city bank account</div>
      <div class="step-subtitle">
        In banking, a <span class="term">current account</span> is used for everyday money — receiving your salary and paying bills. Open yours at Universal Bank now.
      </div>
    </div>
    <div class="step-body">
      <div class="narrative">
        Every Zedville citizen banks at <strong>Universal Bank</strong> — the city's official bank. This is where your monthly salary in Zeds will land and where your expenses will be paid from. It only takes 60 seconds to open.
      </div>
      <div class="form-grid">
        <div class="field-group">
          <label class="field-label">Full name</label>
          <input class="field-input" type="text" value="dan" placeholder="Your name">
        </div>
        <div class="field-group">
          <label class="field-label">Citizen ID (auto-filled)</label>
          <input class="field-input readonly" type="text" value="ZV-2026-0076" readonly>
        </div>
      </div>
      <div class="form-grid single">
        <div class="field-group">
          <label class="field-label">Email address</label>
          <input class="field-input" type="email" value="1@testjune.com" placeholder="Your email">
        </div>
      </div>
      <div class="form-grid single">
        <div class="field-group">
          <label class="field-label">Home address</label>
          <textarea class="field-textarea" placeholder="Home address…"></textarea>
        </div>
      </div>
      <div class="checkbox-group" style="margin-top:0.5rem">
        <div class="check-row checked" onclick="toggleCheck(this)">
          <div class="check-box">
            <svg width="11" height="9" viewBox="0 0 11 9" fill="none"><path d="M1 4.5L4 7.5L10 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <span class="check-text">I agree to receive account statements via email</span>
        </div>
        <div class="check-row" onclick="toggleCheck(this)">
          <div class="check-box">
            <svg width="11" height="9" viewBox="0 0 11 9" fill="none"><path d="M1 4.5L4 7.5L10 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <span class="check-text">I agree to the Terms &amp; Conditions</span>
        </div>
      </div>
      <div class="btn-row">
        <button class="btn-primary" onclick="goTo(3)">Submit application →</button>
        <button class="btn-secondary" onclick="goTo(1)">← Back</button>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════
       STEP 3 — Direct deposit (salary)
  ══════════════════════════════════════════════════════════ -->
  <div class="step" id="step3">
    <div class="step-header">
      <div class="step-eyebrow">Step 3 of 6 — Salary setup</div>
      <div class="step-title">Get paid — set up your salary</div>
      <div class="step-subtitle">
        Authorise a <span class="term">direct deposit</span> so your monthly Zeds salary goes straight into your Universal Bank account on payday
      </div>
    </div>
    <div class="step-body">
      <div class="narrative">
        A <strong>direct deposit</strong> means the city automatically transfers your salary into your bank account on payday — no action needed from you each month. This is how most workers receive their wages in real life. You are authorising 100% of your salary to be deposited directly.
      </div>
      <div class="definition">
        <span class="definition-icon">💡</span>
        <div>
          <strong>Financial literacy:</strong> A <strong>direct deposit authorisation</strong> is a signed instruction from you to your employer, giving them permission to transfer your wages electronically into your bank account. It is faster and safer than a paper cheque.
        </div>
      </div>
      <div class="info-box">
        <div class="info-row"><span class="info-label">Account holder</span><span>dan</span></div>
        <div class="info-row"><span class="info-label">Bank</span><span>Universal Bank</span></div>
        <div class="info-row"><span class="info-label">Account number</span><span>123000-326474</span></div>
        <div class="info-row"><span class="info-label">Deposit amount</span><span>100% of monthly salary</span></div>
      </div>
      <div class="legal-block">
        "I hereby authorise Zedville City Hall to initiate electronic deposits of my salary into the designated bank account. I understand I may cancel this authorisation at any time. I confirm that the bank information provided is accurate."
      </div>
      <div class="checkbox-group">
        <div class="check-row checked required">
          <div class="check-box">
            <svg width="11" height="9" viewBox="0 0 11 9" fill="none"><path d="M1 4.5L4 7.5L10 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div class="check-text">
            <span class="check-title">Deposit 100% of my salary into the account above</span>
          </div>
        </div>
      </div>
      <div class="btn-row">
        <button class="btn-primary" onclick="goTo(4)">Authorise direct deposit →</button>
        <button class="btn-secondary" onclick="goTo(2)">← Back</button>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════
       STEP 4 — Auto-debit (bills)
  ══════════════════════════════════════════════════════════ -->
  <div class="step" id="step4">
    <div class="step-header">
      <div class="step-eyebrow">Step 4 of 6 — Bill payments</div>
      <div class="step-title">Pay your bills automatically</div>
      <div class="step-subtitle">
        Set up <span class="term">auto-debit</span> so your monthly services are paid on time — every month, without doing anything
      </div>
    </div>
    <div class="step-body">
      <div class="narrative">
        An <strong>auto-debit</strong> (also called a direct debit) is an instruction that allows a company to pull an agreed amount from your bank account on a set date each month. Most adults use auto-debits for regular bills — rent, utilities, subscriptions — so they never miss a payment or get a late fee.
      </div>
      <div class="definition">
        <span class="definition-icon">💡</span>
        <div>
          <strong>Financial literacy:</strong> <strong>Auto-debit</strong> and <strong>direct deposit</strong> are different. Direct deposit = money comes <em>into</em> your account (salary). Auto-debit = money goes <em>out</em> of your account (bills). Both are automatic.
        </div>
      </div>
      <div class="checkbox-group">
        <div class="check-row required">
          <div class="check-box">
            <svg width="11" height="9" viewBox="0 0 11 9" fill="none"><path d="M1 4.5L4 7.5L10 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div class="check-text">
            <span class="check-title">School Fees — Ƶ1,000/month <span class="required-tag">Required</span></span>
            <div class="check-desc">Account no.: 9119646157249480</div>
          </div>
        </div>
        <div class="check-row" onclick="toggleCheck(this)">
          <div class="check-box">
            <svg width="11" height="9" viewBox="0 0 11 9" fill="none"><path d="M1 4.5L4 7.5L10 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div class="check-text">
            <span class="check-title">Internet Service — ABC Communications</span>
            <div class="check-desc">Account no.: 4514991365852758</div>
          </div>
        </div>
        <div class="check-row" onclick="toggleCheck(this)">
          <div class="check-box">
            <svg width="11" height="9" viewBox="0 0 11 9" fill="none"><path d="M1 4.5L4 7.5L10 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div class="check-text">
            <span class="check-title">Electricity — ABC Electric Utility Co.</span>
            <div class="check-desc">Account no.: 3680336953774408</div>
          </div>
        </div>
        <div class="check-row" onclick="toggleCheck(this)">
          <div class="check-box">
            <svg width="11" height="9" viewBox="0 0 11 9" fill="none"><path d="M1 4.5L4 7.5L10 1" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
          <div class="check-text">
            <span class="check-title">Water — ABC Water Utility Co.</span>
            <div class="check-desc">Account no.: 9111200383967906</div>
          </div>
        </div>
      </div>
      <div class="legal-block">
        "I hereby authorise ABC Group Companies to initiate debit entries to my bank account for the bill payments of the services I have selected above. This authorisation can be cancelled at any time by notifying the respective company in writing."
      </div>
      <div class="btn-row">
        <button class="btn-primary" onclick="goTo(5)">Confirm &amp; activate auto-pay →</button>
        <button class="btn-secondary" onclick="goTo(3)">← Back</button>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════
       STEP 5 — Consumer profile survey
  ══════════════════════════════════════════════════════════ -->
  <div class="step" id="step5">
    <div class="step-header">
      <div class="step-eyebrow">Step 5 of 6 — Consumer profile</div>
      <div class="step-title">What kind of spender are you?</div>
      <div class="step-subtitle">
        Answer a short <span class="term">consumer profile</span> survey — your answers will set your personal monthly expenses inside Zedville
      </div>
    </div>
    <div class="step-body">
      <div class="narrative">
        In real life, your <strong>consumer profile</strong> is shaped by your lifestyle choices — what you eat, how you travel, what you do for fun. Your answers here will generate your personal monthly expenses inside Zedville. Choose honestly — this becomes your financial reality in the city.
      </div>
      <p style="font-size:0.88rem;font-weight:600;color:var(--text-secondary);margin-bottom:0.75rem;text-transform:uppercase;letter-spacing:0.05em">What is your diet?</p>
      <div class="survey-options" id="dietOptions">
        <div class="survey-card selected" onclick="selectSurvey('dietOptions', this)">
          <div>
            <div class="survey-label">🍖 Omnivore</div>
            <div class="survey-desc">You eat everything</div>
          </div>
          <div class="survey-check"></div>
        </div>
        <div class="survey-card" onclick="selectSurvey('dietOptions', this)">
          <div>
            <div class="survey-label">🥚 Vegetarian</div>
            <div class="survey-desc">No meat, but eggs, milk, cheese, etc.</div>
          </div>
          <div class="survey-check"></div>
        </div>
        <div class="survey-card" onclick="selectSurvey('dietOptions', this)">
          <div>
            <div class="survey-label">🐟 Pescatarian</div>
            <div class="survey-desc">No meat, but fish, eggs, dairy, etc.</div>
          </div>
          <div class="survey-check"></div>
        </div>
        <div class="survey-card" onclick="selectSurvey('dietOptions', this)">
          <div>
            <div class="survey-label">🌱 Vegan</div>
            <div class="survey-desc">No meat, no fish, no animal products</div>
          </div>
          <div class="survey-check"></div>
        </div>
      </div>
      <div class="btn-row">
        <button class="btn-primary" onclick="goTo(6)">Complete &amp; see my profile →</button>
        <button class="btn-secondary" onclick="goTo(4)">← Back</button>
      </div>
    </div>
  </div>

  <!-- ══════════════════════════════════════════════════════════
       STEP 6 — Activation complete + Citizen Diary
  ══════════════════════════════════════════════════════════ -->
  <div class="step" id="step6">
    <div class="step-header">
      <div class="step-eyebrow">Step 6 of 6 — Complete</div>
      <div class="step-title">You are all set up, <span id="studentName2">dan</span>!</div>
      <div class="step-subtitle">
        Your <span class="term">Citizen Diary</span> below is a plain-English record of everything you just activated
      </div>
    </div>
    <div class="step-body">

      <div class="completion-banner">
        <div class="big">🎉 Citizen activation complete</div>
        <div class="sub">Your Zedville account is fully live. Your first salary in Zeds arrives at the start of next month.</div>
      </div>

      <!-- Spending snapshot -->
      <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--radius-md);padding:1rem 1.25rem;margin-bottom:1.5rem">
        <p style="font-size:0.82rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:0.75rem">Your monthly spending snapshot</p>
        <div class="snapshot-cat">🍖 Food &amp; Groceries</div>
        <div class="snapshot-row"><span>Weekly groceries (Pescatarian)</span><span>Ƶ 210.11</span></div>
        <div class="snapshot-row"><span>School lunch</span><span>Ƶ 20.00</span></div>
        <div class="snapshot-cat">🚌 Getting around</div>
        <div class="snapshot-row"><span>Travel to school</span><span>Ƶ 80.00</span></div>
        <div class="snapshot-cat">🎮 Fun &amp; Free time</div>
        <div class="snapshot-row"><span>Activities &amp; hobbies</span><span>Ƶ 75.00</span></div>
        <div class="snapshot-row"><span>Eating out</span><span>Ƶ 65.00</span></div>
        <div class="snapshot-total">
          <span class="label">Total monthly expenses</span>
          <span class="amount">Ƶ 450.11</span>
        </div>
      </div>

      <!-- Citizen Diary -->
      <div class="diary-heading">📖 Your Citizen Diary</div>

      <div class="diary-entry">
        <div class="diary-date">Today — account setup</div>
        <div class="diary-text">
          You opened a <span class="term">current account</span> at Universal Bank (account no. 123000-326474). A <span class="term">current account</span> is the standard type of bank account used for everyday money — receiving income and paying bills.
        </div>
      </div>

      <div class="diary-entry">
        <div class="diary-date">Today — salary authorisation</div>
        <div class="diary-text">
          You signed a <span class="term">direct deposit authorisation</span>, instructing Zedville City Hall to transfer 100% of your monthly Zeds salary directly into your Universal Bank account on payday. This is how most workers receive wages in real life — faster and safer than a paper cheque.
        </div>
      </div>

      <div class="diary-entry">
        <div class="diary-date">Today — bill payments</div>
        <div class="diary-text">
          You activated <span class="term">auto-debit</span> for School Fees (Ƶ 1,000/month). <span class="term">Auto-debit</span> means the payment is pulled automatically from your account each month — you never need to remember to pay. Remember: <strong>direct deposit</strong> = money in; <strong>auto-debit</strong> = money out.
        </div>
      </div>

      <div class="diary-entry">
        <div class="diary-date">Today — consumer profile</div>
        <div class="diary-text">
          You completed your <span class="term">consumer profile</span> survey. A <span class="term">consumer profile</span> describes your spending habits — what you buy, how you travel, and how you live. Based on your answers, your monthly expenses have been calculated at <strong>Ƶ 450.11</strong> and will appear in your bank statement each month.
        </div>
      </div>

      <div class="btn-row" style="margin-top:1.5rem">
        <button class="btn-primary" onclick="alert('// IT TEAM: redirect to bank account dashboard')">Go to my bank account →</button>
      </div>

    </div>
  </div>

</div><!-- /page-shell -->

</div>

<script>
  var currentStep = 1;

  function goTo(n) {
    document.getElementById('step' + currentStep).classList.remove('active');
    document.getElementById('step' + n).classList.add('active');
    currentStep = n;
    updateProgress(n);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function updateProgress(n) {
    var total = 6;
    var pct = Math.round((n / total) * 100);
    document.getElementById('progressText').textContent = 'Citizen Activation — Step ' + n + ' of ' + total;
    document.getElementById('progressPct').textContent = pct + '%';
    for (var i = 1; i <= total; i++) {
      var seg = document.getElementById('seg' + i);
      seg.className = 'progress-seg';
      if (i < n)  seg.classList.add('done');
      if (i === n) seg.classList.add('active');
    }
  }

  function toggleCheck(row) {
    if (row.classList.contains('required')) return;
    row.classList.toggle('checked');
  }

  function selectSurvey(groupId, card) {
    var group = document.getElementById(groupId);
    group.querySelectorAll('.survey-card').forEach(function(c) {
      c.classList.remove('selected');
    });
    card.classList.add('selected');
  }
</script>
@endsection