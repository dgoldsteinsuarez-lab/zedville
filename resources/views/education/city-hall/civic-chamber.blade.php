@extends('layouts.profile')

@section('title', 'Civic Chamber - Educational Finance Department')

@section('content')
    @push('styles')
        <link
            href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,500;0,600;1,400&family=DM+Sans:wght@400;500&display=swap"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
        <style>
            :root {
                --gold: #8A6D1D;
                --gold-light: rgba(138, 109, 29, 0.10);
                --gold-border: rgba(138, 109, 29, 0.30);
                --blue: #1D4ED8;
                --blue-light: rgba(29, 78, 216, 0.10);
                --blue-border: rgba(29, 78, 216, 0.28);
                --purple: #7C3AED;
                --purple-light: rgba(124, 58, 237, 0.10);
                --purple-border: rgba(124, 58, 237, 0.28);
                --green: #00A47D;
                --red: #DC2626;
                --glass: rgba(255, 255, 255, 0.92);
                --shadow: 0 4px 24px rgba(0, 0, 0, 0.09);
                --radius: 16px;
                --radius-sm: 10px;
            }

            .cc-layout {
                max-width: 1060px;
                margin: 0 auto;
                padding: 2rem 1.5rem 4rem;
                display: flex;
                flex-direction: column;
                gap: 1.25rem;
            }

            /* BREADCRUMB */
            .cc-breadcrumb {
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: 12px;
                color: #5C5C5C;
            }

            .cc-breadcrumb a {
                color: #5C5C5C;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 4px;
            }

            .cc-breadcrumb a:hover {
                color: var(--gold);
            }

            /* HEADER */
            .cc-header {
                background: var(--glass);
                border: 0.5px solid var(--gold-border);
                border-radius: var(--radius);
                padding: 1.25rem 1.5rem;
                box-shadow: var(--shadow);
                display: flex;
                align-items: center;
                justify-content: space-between;
                animation: fadeUp 0.4s ease both;
            }

            .cc-header-left {
                display: flex;
                align-items: center;
                gap: 14px;
            }

            .cc-icon {
                width: 48px;
                height: 48px;
                border-radius: 12px;
                background: var(--blue-light);
                border: 0.5px solid var(--blue-border);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 22px;
                color: var(--blue);
            }

            .cc-title {
                font-family: 'Lora', serif;
                font-size: 20px;
                font-weight: 600;
                color: #222222;
            }

            .cc-subtitle {
                font-size: 12px;
                color: #5C5C5C;
                margin-top: 2px;
            }

            .cc-back {
                font-size: 12px;
                font-weight: 500;
                color: #000;
                background: #fff;
                border: 1px solid #000;
                box-shadow: 0 3px 0 #000;
                border-radius: 30px;
                padding: 6px 14px;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 5px;
                transition: 0.2s;
            }

            .cc-back:hover {
                background: #fff;
                color: #000;
                transform: translateY(3px);
                box-shadow: 0 1px 0 #000;
            }

            /* TABS */
            .cc-tabs {
                display: flex;
                gap: 8px;
                background: var(--glass);
                border: 0.5px solid var(--gold-border);
                border-radius: var(--radius);
                padding: 6px;
                box-shadow: var(--shadow);
                animation: fadeUp 0.4s 0.07s ease both;
            }

            .cc-tab {
                flex: 1;
                padding: 10px 1rem;
                border-radius: 10px;
                border: none;
                font-family: 'DM Sans', sans-serif;
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                background: transparent;
                color: #5C5C5C;
            }

            .cc-tab.active-ref {
                background: var(--blue-light);
                color: var(--blue);
                border: 0.5px solid var(--blue-border);
            }

            .cc-tab.active-pet {
                background: var(--purple-light);
                color: var(--purple);
                border: 0.5px solid var(--purple-border);
            }

            .cc-tab:not(.active-ref):not(.active-pet):hover {
                background: rgba(0, 0, 0, 0.04);
                color: #222222;
            }

            .tab-count {
                font-size: 10px;
                font-weight: 500;
                padding: 1px 7px;
                border-radius: 8px;
            }

            .tc-blue {
                background: var(--blue-light);
                color: var(--blue);
            }

            .tc-purple {
                background: var(--purple-light);
                color: var(--purple);
            }

            /* SECTIONS */
            .cc-section {
                display: none;
                flex-direction: column;
                gap: 1rem;
                animation: fadeUp 0.35s ease both;
            }

            .cc-section.visible {
                display: flex;
            }

            .sec-label {
                font-size: 11px;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.08em;
                color: #999999;
            }

            /* REFERENDUM CARD */
            .ref-card {
                background: var(--glass);
                border: 0.5px solid var(--blue-border);
                border-radius: var(--radius);
                padding: 1.25rem 1.5rem;
                box-shadow: var(--shadow);
            }

            .ref-card.closed {
                border-color: rgba(0, 0, 0, 0.08);
                opacity: 0.72;
            }

            .ref-card-top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 1rem;
            }

            .ref-q {
                font-family: 'Lora', serif;
                font-size: 16px;
                font-weight: 500;
                color: #222222;
                line-height: 1.55;
                flex: 1;
            }

            .ref-status {
                font-size: 10px;
                font-weight: 500;
                padding: 3px 9px;
                border-radius: 10px;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .rs-open {
                background: var(--blue-light);
                color: var(--blue);
                border: 0.5px solid var(--blue-border);
            }

            .rs-closed {
                background: rgba(0, 0, 0, 0.05);
                color: #5C5C5C;
            }

            .rs-yes {
                background: rgba(0, 164, 125, 0.10);
                color: var(--green);
            }

            .rs-no {
                background: rgba(220, 38, 38, 0.10);
                color: var(--red);
            }

            .ref-bars {
                display: flex;
                flex-direction: column;
                gap: 8px;
                margin-bottom: 0.85rem;
            }

            .ref-bar-row {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .ref-bar-lbl {
                font-size: 12px;
                font-weight: 500;
                width: 28px;
                color: #5C5C5C;
            }

            .ref-bar-track {
                flex: 1;
                height: 10px;
                border-radius: 5px;
                background: rgba(0, 0, 0, 0.07);
                overflow: hidden;
            }

            .ref-bar-fill {
                height: 100%;
                border-radius: 5px;
                transition: width 0.8s cubic-bezier(.4, 0, .2, 1);
            }

            .fill-yes {
                background: var(--green);
            }

            .fill-no {
                background: var(--red);
            }

            .ref-bar-pct {
                font-size: 12px;
                font-weight: 500;
                color: #222222;
                width: 34px;
                text-align: right;
            }

            .ref-meta {
                font-size: 11px;
                color: #999999;
                margin-bottom: 0.85rem;
            }

            .ref-vote-row {
                display: flex;
                gap: 8px;
            }

            .vote-btn {
                flex: 1;
                padding: 9px 0;
                border-radius: var(--radius-sm);
                font-family: 'DM Sans', sans-serif;
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.15s;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                border: 0.5px solid;
            }

            .vote-btn-yes {
                background: rgba(0, 164, 125, 0.08);
                border-color: rgba(0, 164, 125, 0.30);
                color: var(--green);
            }

            .vote-btn-yes:hover {
                background: rgba(0, 164, 125, 0.16);
            }

            .vote-btn-no {
                background: rgba(220, 38, 38, 0.08);
                border-color: rgba(220, 38, 38, 0.30);
                color: var(--red);
            }

            .vote-btn-no:hover {
                background: rgba(220, 38, 38, 0.16);
            }

            .vote-btn:disabled {
                opacity: 0.45;
                cursor: not-allowed;
            }

            .vote-btn:not(:disabled):active {
                transform: scale(0.98);
            }

            .voted-msg {
                font-size: 12px;
                font-weight: 500;
                padding: 8px 12px;
                border-radius: 8px;
                background: var(--blue-light);
                border: 0.5px solid var(--blue-border);
                color: var(--blue);
                display: flex;
                align-items: center;
                gap: 6px;
            }

            /* PETITION CARD */
            .pet-card {
                background: var(--glass);
                border: 0.5px solid var(--purple-border);
                border-radius: var(--radius);
                padding: 1.25rem 1.5rem;
                box-shadow: var(--shadow);
            }

            .pet-card.approved {
                border-color: rgba(0, 164, 125, 0.30);
            }

            .pet-card.rejected {
                border-color: rgba(220, 38, 38, 0.20);
                opacity: 0.72;
            }

            .pet-card-top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 0.6rem;
            }

            .pet-title {
                font-family: 'Lora', serif;
                font-size: 15px;
                font-weight: 600;
                color: #222222;
                flex: 1;
            }

            .pet-status {
                font-size: 10px;
                font-weight: 500;
                padding: 3px 9px;
                border-radius: 10px;
                white-space: nowrap;
                flex-shrink: 0;
            }

            .ps-collecting {
                background: var(--purple-light);
                color: var(--purple);
                border: 0.5px solid var(--purple-border);
            }

            .ps-approved {
                background: rgba(0, 164, 125, 0.10);
                color: var(--green);
            }

            .ps-rejected {
                background: rgba(220, 38, 38, 0.10);
                color: var(--red);
            }

            .pet-desc {
                font-size: 13px;
                color: #5C5C5C;
                line-height: 1.6;
                margin-bottom: 0.85rem;
            }

            .pet-meta {
                font-size: 11px;
                color: #999999;
                margin-bottom: 0.85rem;
            }

            .pet-sig-bar {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 0.85rem;
            }

            .pet-sig-track {
                flex: 1;
                height: 6px;
                border-radius: 3px;
                background: rgba(0, 0, 0, 0.07);
                overflow: hidden;
            }

            .pet-sig-fill {
                height: 100%;
                border-radius: 3px;
                background: var(--purple);
                transition: width 0.8s cubic-bezier(.4, 0, .2, 1);
            }

            .pet-sig-count {
                font-size: 12px;
                font-weight: 500;
                color: var(--purple);
                white-space: nowrap;
            }

            .sign-btn {
                width: 100%;
                padding: 9px 0;
                border-radius: var(--radius-sm);
                font-family: 'DM Sans', sans-serif;
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.15s;
                background: var(--purple-light);
                border: 0.5px solid var(--purple-border);
                color: var(--purple);
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
            }

            .sign-btn:hover {
                background: rgba(124, 58, 237, 0.18);
            }

            .sign-btn:disabled {
                opacity: 0.45;
                cursor: not-allowed;
            }

            .signed-msg {
                font-size: 12px;
                font-weight: 500;
                padding: 8px 12px;
                border-radius: 8px;
                background: var(--purple-light);
                border: 0.5px solid var(--purple-border);
                color: var(--purple);
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .pet-feedback {
                font-size: 12px;
                color: #5C5C5C;
                font-style: italic;
                padding: 8px 12px;
                border-radius: 8px;
                background: rgba(0, 0, 0, 0.04);
                border-left: 2px solid var(--gold);
                margin-top: 8px;
            }

            /* NEW PETITION FORM */
            .new-pet-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 10px;
                border-radius: var(--radius-sm);
                border: 1px dashed var(--purple-border);
                background: transparent;
                color: var(--purple);
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.15s;
                font-family: 'DM Sans', sans-serif;
                width: 100%;
            }

            .new-pet-btn:hover {
                background: var(--purple-light);
                border-style: solid;
            }

            .new-pet-form {
                background: var(--glass);
                border: 0.5px solid var(--purple-border);
                border-radius: var(--radius);
                padding: 1.25rem 1.5rem;
                box-shadow: var(--shadow);
                display: none;
                flex-direction: column;
                gap: 1rem;
            }

            .new-pet-form.open {
                display: flex;
            }

            .form-title {
                font-family: 'Lora', serif;
                font-size: 15px;
                font-weight: 600;
                color: #222222;
            }

            .form-group {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }

            .form-label {
                font-size: 12px;
                font-weight: 500;
                color: #5C5C5C;
            }

            .form-input,
            .form-textarea {
                font-family: 'DM Sans', sans-serif;
                font-size: 13px;
                color: #222222;
                background: white;
                border: 0.5px solid rgba(0, 0, 0, 0.15);
                border-radius: 8px;
                padding: 9px 12px;
                transition: border-color 0.15s;
                width: 100%;
            }

            .form-input:focus,
            .form-textarea:focus {
                outline: none;
                border-color: var(--purple);
            }

            .form-textarea {
                resize: vertical;
                min-height: 90px;
            }

            .form-hint {
                font-size: 11px;
                color: #999999;
            }

            .form-actions {
                display: flex;
                gap: 8px;
            }

            .btn-submit {
                flex: 1;
                padding: 9px;
                border-radius: var(--radius-sm);
                font-family: 'DM Sans', sans-serif;
                font-size: 13px;
                font-weight: 500;
                background: var(--purple-light);
                border: 0.5px solid var(--purple-border);
                color: var(--purple);
                cursor: pointer;
                transition: background 0.15s;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
            }

            .btn-submit:hover {
                background: rgba(124, 58, 237, 0.18);
            }

            .btn-cancel {
                padding: 9px 18px;
                border-radius: var(--radius-sm);
                font-family: 'DM Sans', sans-serif;
                font-size: 13px;
                background: transparent;
                border: 0.5px solid rgba(0, 0, 0, 0.12);
                color: #5C5C5C;
                cursor: pointer;
            }

            .btn-cancel:hover {
                background: rgba(0, 0, 0, 0.05);
            }

            /* EMPTY STATE */
            .empty-state {
                text-align: center;
                padding: 2.5rem 1rem;
                color: #999999;
                font-size: 13px;
            }

            .empty-state i {
                font-size: 32px;
                display: block;
                margin-bottom: 10px;
                opacity: 0.4;
            }

            @keyframes fadeUp {
                from {
                    opacity: 0;
                    transform: translateY(14px)
                }

                to {
                    opacity: 1;
                    transform: translateY(0)
                }
            }

            @media (max-width: 600px) {
                .cc-header {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                }

                .ref-vote-row {
                    flex-direction: column;
                }
            }
        </style>
    @endpush
    <div class="cc-layout">

        <!-- BREADCRUMB -->
        <div class="cc-breadcrumb">
            <a href="{{ route('education.main-hall') }}"><i class="ti ti-building-community" aria-hidden="true"></i> City
                Hall</a>
            <span style="color:#D2DDDB;">›</span>
            <span>Civic Chamber</span>
        </div>

        <!-- PAGE HEADER -->
        <div class="cc-header">
            <div class="cc-header-left">
                <div class="cc-icon"><i class="ti ti-checkup-list" aria-hidden="true"></i></div>
                <div>
                    <div class="cc-title">Civic Chamber</div>
                    <div class="cc-subtitle">Vote on referendums · Sign or start petitions</div>
                </div>
            </div>
            <a href="{{ route('education.city-hall') }}" class="cc-back">
                <i class="ti ti-arrow-left" aria-hidden="true"></i> Back to City Hall
            </a>
        </div>

        <!-- CITY NAME / QUOTE PROPOSAL -->
        <div class="cc-header" id="city-proposal-card" style="flex-direction:column; align-items:stretch; gap:10px; {{ $topic ? '' : 'opacity:.9;' }}">
            <div class="cc-title" style="font-size:15px;">Propose a city name or quote</div>
            <div class="cc-subtitle">Your Tutor reviews it before it goes live in City Hall. One pending proposal of each type at a time.</div>
            <div style="display:flex; gap:16px; flex-wrap:wrap;">
                <form id="city-name-form" style="flex:1; min-width:220px;">
                    @csrf
                    <label class="text-sm" style="display:block; margin-bottom:4px;">City name</label>
                    @if ($myCityProposals->has('name'))
                        <div class="text-sm" style="color:#7A5C00;">Pending: "{{ $myCityProposals['name']->value }}"</div>
                    @else
                        <div style="display:flex; gap:8px;">
                            <input type="text" name="value" maxlength="255" required class="form-control" placeholder="New city name">
                            <button type="submit" class="btn btn-sm btn-primary">Propose</button>
                        </div>
                    @endif
                </form>
                <form id="city-quote-form" style="flex:1; min-width:220px;">
                    @csrf
                    <label class="text-sm" style="display:block; margin-bottom:4px;">City quote</label>
                    @if ($myCityProposals->has('quote'))
                        <div class="text-sm" style="color:#7A5C00;">Pending: "{{ $myCityProposals['quote']->value }}"</div>
                    @else
                        <div style="display:flex; gap:8px;">
                            <input type="text" name="value" maxlength="255" required class="form-control" placeholder="New city quote">
                            <button type="submit" class="btn btn-sm btn-primary">Propose</button>
                        </div>
                    @endif
                </form>
            </div>
            <div id="city-proposal-feedback" class="text-sm"></div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const topic = @json($topic);
                if (topic === 'city-name' || topic === 'city-quote') {
                    document.getElementById('city-proposal-card')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                function wireForm(formId, type) {
                    const form = document.getElementById(formId);
                    if (!form) return;
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const value = form.querySelector('input[name="value"]').value;
                        fetch('{{ route("education.city-proposal.create") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ type: type, value: value })
                        })
                            .then(r => r.json())
                            .then(data => {
                                document.getElementById('city-proposal-feedback').textContent = data.message;
                                if (data.success) setTimeout(() => location.reload(), 900);
                            });
                    });
                }
                wireForm('city-name-form', 'name');
                wireForm('city-quote-form', 'quote');
            });
        </script>

        <!-- TABS -->
        <div class="cc-tabs" role="tablist">
            <button class="cc-tab active-ref" id="tab-ref" onclick="switchTab('referendum')" role="tab"
                aria-selected="true">
                <i class="ti ti-checkup-list" aria-hidden="true"></i> Referendums
                <!-- IT TEAM: replace 1 with open referendum count from DB -->
                <span class="tab-count tc-blue">1</span>
            </button>
            <button class="cc-tab" id="tab-pet" onclick="switchTab('petition')" role="tab" aria-selected="false">
                <i class="ti ti-writing" aria-hidden="true"></i> Petitions
                <!-- IT TEAM: replace 2 with open petition count from DB -->
                <span class="tab-count tc-purple">2</span>
            </button>
        </div>

        <!-- ═══════════════════ REFERENDUM SECTION ═══════════════════ -->
        <div class="cc-section visible" id="section-referendum">

            <div class="sec-label">
                Open votes
            </div>

            @forelse($openReferendums as $referendum)

                @php

                    $yesVotes = \App\Models\ReferendumVote::where('referendum_id', $referendum->id)
                        ->where('vote', 'yes')
                        ->count();

                    $noVotes = \App\Models\ReferendumVote::where('referendum_id', $referendum->id)
                        ->where('vote', 'no')
                        ->count();

                    $totalVotes = $yesVotes + $noVotes;

                    $yesPercent = $totalVotes ? round(($yesVotes / $totalVotes) * 100) : 0;

                    $noPercent = $totalVotes ? round(($noVotes / $totalVotes) * 100) : 0;

                    $alreadyVoted = \App\Models\ReferendumVote::where('referendum_id', $referendum->id)
                        ->where('student_id', $studentId)
                        ->exists();

                @endphp

                <div class="ref-card">

                    <div class="ref-card-top">

                        <div class="ref-q">

                            {{ $referendum->question }}

                        </div>

                        <span class="ref-status rs-open">

                            Open

                        </span>

                    </div>

                    <div class="ref-bars">

                        <div class="ref-bar-row">

                            <span class="ref-bar-lbl">

                                Yes

                            </span>

                            <div class="ref-bar-track">

                                <div class="ref-bar-fill fill-yes" style="width:{{ $yesPercent }}%">

                                </div>

                            </div>

                            <span class="ref-bar-pct">

                                {{ $yesPercent }}%

                            </span>

                        </div>

                        <div class="ref-bar-row">

                            <span class="ref-bar-lbl">

                                No

                            </span>

                            <div class="ref-bar-track">

                                <div class="ref-bar-fill fill-no" style="width:{{ $noPercent }}%">

                                </div>

                            </div>

                            <span class="ref-bar-pct">

                                {{ $noPercent }}%

                            </span>

                        </div>

                    </div>

                    <div class="ref-meta">

                        {{ $totalVotes }} Votes

                        @if($referendum->end_date)

                            · Closes {{ \Carbon\Carbon::parse($referendum->end_date)->format('d M Y') }}

                        @endif

                    </div>

                    @if(!$alreadyVoted)

                        <div class="ref-vote-row">

                            <button class="vote-btn vote-btn-yes" onclick="castVote({{ $referendum->id }},'yes')">

                                <i class="ti ti-thumb-up"></i>

                                Vote Yes

                            </button>

                            <button class="vote-btn vote-btn-no" onclick="castVote({{ $referendum->id }},'no')">

                                <i class="ti ti-thumb-down"></i>

                                Vote No

                            </button>

                        </div>

                    @else

                        <div class="signed-msg">

                            <i class="ti ti-circle-check"></i>

                            You have already voted.

                        </div>

                    @endif

                </div>

            @empty

                <div class="empty-state">

                    <i class="ti ti-checkup-list"></i>

                    No open referendums right now.

                </div>

            @endforelse


            <div class="sec-label mt-4">

                Past Referendums

            </div>

            @forelse($closedReferendums as $referendum)

                @php

                    $yesVotes = \App\Models\ReferendumVote::where('referendum_id', $referendum->id)
                        ->where('vote', 'yes')
                        ->count();

                    $noVotes = \App\Models\ReferendumVote::where('referendum_id', $referendum->id)
                        ->where('vote', 'no')
                        ->count();

                    $totalVotes = $yesVotes + $noVotes;

                    $yesPercent = $totalVotes ? round(($yesVotes / $totalVotes) * 100) : 0;

                    $noPercent = $totalVotes ? round(($noVotes / $totalVotes) * 100) : 0;

                @endphp

                <div class="ref-card closed">

                    <div class="ref-card-top">

                        <div class="ref-q">

                            {{ $referendum->question }}

                        </div>

                        <span class="ref-status {{ $yesVotes >= $noVotes ? 'rs-yes' : 'rs-no' }}">

                            {{ $yesVotes >= $noVotes ? 'Yes Won' : 'No Won' }}

                        </span>

                    </div>

                    <div class="ref-bars">

                        <div class="ref-bar-row">

                            <span class="ref-bar-lbl">

                                Yes

                            </span>

                            <div class="ref-bar-track">

                                <div class="ref-bar-fill fill-yes" style="width:{{ $yesPercent }}%">

                                </div>

                            </div>

                            <span class="ref-bar-pct">

                                {{ $yesPercent }}%

                            </span>

                        </div>

                        <div class="ref-bar-row">

                            <span class="ref-bar-lbl">

                                No

                            </span>

                            <div class="ref-bar-track">

                                <div class="ref-bar-fill fill-no" style="width:{{ $noPercent }}%">

                                </div>

                            </div>

                            <span class="ref-bar-pct">

                                {{ $noPercent }}%

                            </span>

                        </div>

                    </div>

                    <div class="ref-meta">

                        {{ $totalVotes }} Votes · Closed

                    </div>

                </div>

            @empty

                <div class="empty-state">

                    <i class="ti ti-history"></i>

                    No previous referendums.

                </div>

            @endforelse

        </div><!-- /section-referendum -->

        <!-- ═══════════════════ PETITION SECTION ═══════════════════ -->
        <div class="cc-section" id="section-petition">

            <!-- START PETITION -->

            <button class="new-pet-btn" id="newPetBtn" onclick="togglePetitionForm()">

                <i class="ti ti-plus"></i>

                Start a new petition

            </button>


            <div class="new-pet-form" id="newPetForm">

                <div class="form-title">

                    Start a new petition

                </div>

                <div class="form-group">

                    <label class="form-label">

                        Petition Title

                    </label>

                    <input type="text" id="petTitle" class="form-input" maxlength="200">

                </div>

                <div class="form-group">

                    <label class="form-label">

                        Description

                    </label>

                    <textarea id="petDesc" class="form-textarea"></textarea>

                </div>

                <div class="form-group">

                    <label class="form-label">Visible to</label>

                    <label><input type="radio" name="petScope" value="class" checked> Just my class</label>
                    <label><input type="radio" name="petScope" value="grade"> My whole Grade</label>

                </div>

                <div class="form-actions">

                    <button class="btn-submit" onclick="submitPetition()">

                        Submit Petition

                    </button>

                    <button class="btn-cancel" onclick="togglePetitionForm()">

                        Cancel

                    </button>

                </div>

            </div>


            <div class="sec-label">

                Active Petitions

            </div>


            @forelse($activePetitions as $petition)

                @php

                    $signatureCount = \App\Models\PetitionSignature::where(
                        'petition_id',
                        $petition->id
                    )->count();

                    $totalStudents = \App\Models\User::count();

                    $percentage = $totalStudents
                        ? round(($signatureCount / $totalStudents) * 100)
                        : 0;

                    $alreadySigned = \App\Models\PetitionSignature::where(
                        'petition_id',
                        $petition->id
                    )
                        ->where(
                            'student_id',
                            $studentId
                        )
                        ->exists();

                    $isOwner = $petition->created_by == $studentId;

                @endphp


                <div class="pet-card">

                    <div class="pet-card-top">

                        <div class="pet-title">

                            {{ $petition->title }}

                        </div>

                        <span class="pet-status ps-collecting">

                            {{ ucfirst($petition->status) }}

                        </span>

                    </div>


                    <div class="pet-desc">

                        {{ $petition->description }}

                    </div>


                    <div class="pet-sig-bar">

                        <div class="pet-sig-track">

                            <div class="pet-sig-fill" style="width:{{ $percentage }}%">

                            </div>

                        </div>

                        <span class="pet-sig-count">

                            <i class="ti ti-pencil"></i>

                            {{ $signatureCount }}

                            Signatures

                        </span>

                    </div>


                    <div class="pet-meta">

                        @if($isOwner)

                            You started this petition

                        @else

                            Started

                            {{ $petition->created_at->diffForHumans() }}

                        @endif

                    </div>


                    @if($isOwner)

                        <div class="signed-msg" style="background:var(--gold-light);
                                 border-color:var(--gold-border);
                                 color:var(--gold);">

                            <i class="ti ti-star"></i>

                            This is your petition.

                        </div>

                    @elseif($alreadySigned)

                        <div class="signed-msg">

                            <i class="ti ti-circle-check"></i>

                            You already signed this petition.

                        </div>

                    @else

                        <button class="sign-btn" onclick="signPetition({{ $petition->id }})">

                            <i class="ti ti-pencil"></i>

                            Sign Petition

                        </button>

                    @endif

                </div>

            @empty

                <div class="empty-state">

                    <i class="ti ti-writing"></i>

                    No Active Petitions

                </div>

            @endforelse



            <div class="sec-label mt-4">

                Past Petitions

            </div>


            @forelse($pastPetitions as $petition)

                    <div class="pet-card {{ $petition->status }}">

                        <div class="pet-card-top">

                            <div class="pet-title">

                                {{ $petition->title }}

                            </div>

                            <span class="pet-status">

                                {{ ucfirst($petition->status) }}

                            </span>

                        </div>

                        <div class="pet-meta">

                            {{ \App\Models\PetitionSignature::where(
                    'petition_id',
                    $petition->id
                )->count() }}

                            Signatures

                        </div>

                        @if($petition->tutor_feedback)

                            <div class="pet-feedback">

                                <strong>

                                    Tutor:

                                </strong>

                                {{ $petition->tutor_feedback }}

                            </div>

                        @endif

                    </div>

            @empty

                <div class="empty-state">

                    <i class="ti ti-history"></i>

                    No Previous Petitions

                </div>

            @endforelse

        </div><!-- /section-petition -->

    </div><!-- /cc-layout -->

    <script>
        // ── TAB SWITCHER ──
        function switchTab(tab) {
            const isRef = tab === 'referendum';
            document.getElementById('section-referendum').classList.toggle('visible', isRef);
            document.getElementById('section-petition').classList.toggle('visible', !isRef);
            document.getElementById('tab-ref').classList.toggle('active-ref', isRef);
            document.getElementById('tab-pet').classList.toggle('active-pet', !isRef);
            document.getElementById('tab-ref').setAttribute('aria-selected', isRef);
            document.getElementById('tab-pet').setAttribute('aria-selected', !isRef);
        }

        // Auto-switch tab if URL has ?tab=petition
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'petition') switchTab('petition');

        // ── CAST VOTE ──
        function castVote(referendumId, vote) {
            if (!confirm('Vote ' + vote.toUpperCase() + ' on this referendum? You cannot change your vote afterwards.')) return;

            // IT TEAM: replace URL with Laravel route
            fetch('/education/referendum/' + referendumId + '/vote', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // IT TEAM: CSRF token must be in meta tag in Laravel layout
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ vote: vote })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        // Update bars
                        document.querySelector('#ref-card-' + referendumId + ' .fill-yes').style.width = data.yes_pct + '%';
                        document.querySelector('#ref-card-' + referendumId + ' .fill-no').style.width = data.no_pct + '%';
                        document.getElementById('yes-pct-' + referendumId).textContent = data.yes_pct + '%';
                        document.getElementById('no-pct-' + referendumId).textContent = data.no_pct + '%';
                        document.getElementById('ref-meta-' + referendumId).textContent = data.total_votes + ' students voted';
                        // Replace vote buttons with voted message
                        document.getElementById('vote-row-' + referendumId).outerHTML =
                            '<div class="voted-msg"><i class="ti ti-circle-check" aria-hidden="true"></i> You have already voted on this referendum</div>';
                    } else {
                        alert(data.message || 'Something went wrong. Please try again.');
                    }
                })
                .catch(() => alert('Connection error. Please try again.'));
        }

        // ── SIGN PETITION ──
        function signPetition(petitionId) {
            if (!confirm('Sign this petition? Your name will not be shown — only the total count.')) return;

            // IT TEAM: replace URL with Laravel route
            fetch('/education/petition/' + petitionId + '/sign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('sig-count-' + petitionId).textContent = data.signature_count;
                        document.getElementById('sign-btn-' + petitionId).outerHTML =
                            '<div class="signed-msg"><i class="ti ti-circle-check" aria-hidden="true"></i> You have signed this petition</div>';
                    } else {
                        alert(data.message || 'Something went wrong. Please try again.');
                    }
                })
                .catch(() => alert('Connection error. Please try again.'));
        }

        // ── PETITION FORM ──
        function togglePetitionForm() {
            const form = document.getElementById('newPetForm');
            const btn = document.getElementById('newPetBtn');
            const isOpen = form.classList.toggle('open');
            btn.style.display = isOpen ? 'none' : 'flex';
            if (isOpen) document.getElementById('petTitle').focus();
        }

        function submitPetition() {
            const title = document.getElementById('petTitle').value.trim();
            const desc = document.getElementById('petDesc').value.trim();
            if (!title) { alert('Please write a title for your petition.'); return; }
            if (!desc) { alert('Please write a description for your petition.'); return; }
            const scope = document.querySelector('input[name="petScope"]:checked').value;

            // IT TEAM: replace URL with Laravel route
            fetch('/education/petition/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ title: title, description: desc, scope: scope })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('Your petition has been submitted. Your tutor will review it shortly.');
                        document.getElementById('petTitle').value = '';
                        document.getElementById('petDesc').value = '';
                        togglePetitionForm();
                    } else {
                        alert(data.message || 'Something went wrong. Please try again.');
                    }
                })
                .catch(() => alert('Connection error. Please try again.'));
        }
    </script>
@endsection