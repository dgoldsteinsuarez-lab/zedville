{{--
    ZEDVILLE — Engagement Badge
    View: Admin / Tutor Badge View (per student)

    INSTRUCTIONS FOR IT:
    1. Place this file in: resources/views/admin/badges/student.blade.php
    2. Extend your existing admin layout
    3. This page is accessed at: /admin/badges/student/{studentId}
    4. The studentId is passed from the route to this view
--}}

@extends('layouts.admin') {{-- TODO: replace with your admin layout name --}}

@section('title', 'Student Badges — Zedville Admin')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Student Badge Record</h1>
            <p class="text-gray-400 text-sm mt-1">Student ID: <span id="student-id-display" class="font-mono"></span></p>
        </div>
        <a href="javascript:history.back()" class="text-sm text-blue-500 hover:underline">← Back</a>
    </div>

    {{-- Loading --}}
    <div id="admin-loading" class="text-center py-16 text-gray-400">
        <p>Loading badge data...</p>
    </div>

    <div id="admin-content" class="hidden space-y-8">

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Current Monthly Badge --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Current Monthly Badge</div>
                <div class="flex items-center gap-4">
                    <div id="admin-monthly-emoji" class="text-5xl"></div>
                    <div>
                        <div id="admin-monthly-label" class="text-2xl font-bold text-gray-800"></div>
                        <div id="admin-monthly-breakdown" class="text-sm text-gray-400 mt-1"></div>
                    </div>
                </div>
                {{-- Override controls --}}
                <div class="mt-5 pt-5 border-t border-gray-100">
                    <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Admin Override</div>
                    <div class="flex gap-2 flex-wrap">
                        @foreach(['PLATINUM','GOLD','SILVER','BRONZE','NONE'] as $level)
                        <button
                            onclick="overrideBadge('{{ $level }}')"
                            class="px-3 py-1 rounded-full text-xs font-semibold border hover:opacity-80 transition"
                            data-level="{{ $level }}"
                            id="override-btn-{{ $level }}">
                            {{ $level }}
                        </button>
                        @endforeach
                        <button
                            onclick="removeOverride()"
                            class="px-3 py-1 rounded-full text-xs font-semibold border border-gray-300 text-gray-500 hover:bg-gray-50 transition">
                            Remove Override
                        </button>
                    </div>
                    <div id="override-status" class="text-xs text-orange-500 mt-2 hidden">★ This badge has been manually overridden.</div>
                </div>
            </div>

            {{-- Accumulated Badge --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Accumulated Badge (Year)</div>
                <div class="flex items-center gap-4 mb-4">
                    <div id="admin-acc-emoji" class="text-5xl"></div>
                    <div>
                        <div id="admin-acc-label" class="text-2xl font-bold text-gray-800"></div>
                        <div id="admin-acc-year" class="text-sm text-gray-400"></div>
                    </div>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3 mb-1">
                    <div id="admin-acc-bar" class="bg-indigo-500 h-3 rounded-full transition-all duration-700" style="width:0%"></div>
                </div>
                <div id="admin-acc-pts" class="text-xs text-gray-400 text-right"></div>
            </div>
        </div>

        {{-- Month-by-month History Table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-5">Month-by-Month History</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-400 uppercase tracking-widest border-b border-gray-100">
                            <th class="text-left pb-3 pr-4">Month</th>
                            <th class="text-center pb-3 pr-4">Required</th>
                            <th class="text-center pb-3 pr-4">Optional</th>
                            <th class="text-center pb-3 pr-4">Participation</th>
                            <th class="text-center pb-3 pr-4">Monthly Badge</th>
                            <th class="text-center pb-3 pr-4">Pts</th>
                            <th class="text-center pb-3">Accumulated</th>
                        </tr>
                    </thead>
                    <tbody id="admin-history-table">
                        {{-- Filled by JS --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Admin Tools --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Admin Tools</div>
            <div class="flex flex-wrap gap-3">
                <button onclick="recalculate()"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
                    Recalculate This Month
                </button>
            </div>
            <div id="admin-tool-message" class="text-xs text-green-600 mt-3 hidden"></div>
        </div>

    </div>{{-- end admin-content --}}
</div>

<script>
const MONTH_NAMES = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const BADGE_META = {
    PLATINUM: { label: 'Platinum', emoji: '🏅', color: '#E8E8E8', text: '#333' },
    GOLD:     { label: 'Gold',     emoji: '🥇', color: '#FFF8DC', text: '#7A5C00' },
    SILVER:   { label: 'Silver',   emoji: '🥈', color: '#F0F0F0', text: '#555' },
    BRONZE:   { label: 'Bronze',   emoji: '🥉', color: '#FDF0E6', text: '#7A3B00' },
    NONE:     { label: 'No Badge', emoji: '—',  color: '#FFE8E8', text: '#AA0000' },
};

// Get student ID from URL: /admin/badges/student/{id}
const studentId = window.location.pathname.split('/').pop();
let currentMonth = null;
let currentYear  = null;

async function loadData() {
    try {
        const res  = await fetch(`/admin/badges/student/${studentId}`);
        const data = await res.json();
        render(data);
    } catch(e) {
        document.getElementById('admin-loading').textContent = 'Failed to load data.';
    }
}

function render(data) {
    document.getElementById('student-id-display').textContent = data.student_id;

    const current = data.current_month;
    if (current) {
        currentMonth = current.month;
        currentYear  = current.year;

        const m = BADGE_META[current.monthly_badge];
        document.getElementById('admin-monthly-emoji').textContent = m.emoji;
        document.getElementById('admin-monthly-label').textContent = m.label;
        document.getElementById('admin-monthly-breakdown').textContent =
            `Required: ${current.required_completed}/${current.required_assigned} | ` +
            `Optional: ${current.optional_completed}/${current.optional_assigned} | ` +
            `On their own: ${current.own_completed} | ` +
            `Participation: ${current.participation_points}/15 pts`;

        if (current.is_overridden) {
            document.getElementById('override-status').classList.remove('hidden');
        }
    }

    // Accumulated
    const accMeta = BADGE_META[data.accumulated_badge || 'NONE'];
    document.getElementById('admin-acc-emoji').textContent = accMeta.emoji;
    document.getElementById('admin-acc-label').textContent = accMeta.label;
    document.getElementById('admin-acc-year').textContent  = data.academic_year;
    document.getElementById('admin-acc-pts').textContent   = `${data.accumulated_points} / 40 pts`;
    document.getElementById('admin-acc-bar').style.width   = `${Math.min(100, Math.round(data.accumulated_points / 40 * 100))}%`;

    // History table
    const tbody = document.getElementById('admin-history-table');
    tbody.innerHTML = '';
    (data.history || []).forEach(r => {
        const m   = BADGE_META[r.monthly_badge];
        const row = document.createElement('tr');
        row.className = 'border-b border-gray-50 hover:bg-gray-50';
        row.innerHTML = `
            <td class="py-3 pr-4 font-medium text-gray-700">${MONTH_NAMES[r.month-1]} ${r.year}</td>
            <td class="py-3 pr-4 text-center text-gray-600">${r.required_completed}/${r.required_assigned}</td>
            <td class="py-3 pr-4 text-center text-gray-600">${r.optional_completed}/${r.optional_assigned}${r.own_completed ? ` (+${r.own_completed} own)` : ''}</td>
            <td class="py-3 pr-4 text-center text-gray-600">${r.participation_points}</td>
            <td class="py-3 pr-4 text-center">
                <span class="px-2 py-1 rounded-full text-xs font-semibold"
                    style="background:${m.color};color:${m.text}">
                    ${m.emoji} ${m.label}${r.is_overridden ? ' ★' : ''}
                </span>
            </td>
            <td class="py-3 pr-4 text-center text-gray-500">${r.monthly_badge_points}</td>
            <td class="py-3 text-center text-gray-500">${r.accumulated_points}</td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById('admin-loading').classList.add('hidden');
    document.getElementById('admin-content').classList.remove('hidden');
}

async function overrideBadge(level) {
    if (!currentMonth) return alert('No current month data available.');
    if (!confirm(`Override this student's badge to ${level} for ${currentMonth}/${currentYear}?`)) return;

    const res = await fetch('/admin/badges/override', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ student_id: parseInt(studentId), month: currentMonth, year: currentYear, badge: level })
    });
    const data = await res.json();
    if (data.success) { location.reload(); }
    else { alert(data.error || 'Override failed.'); }
}

async function removeOverride() {
    if (!currentMonth) return;
    if (!confirm('Remove the override and revert to the calculated badge?')) return;

    const res = await fetch('/admin/badges/override/remove', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ student_id: parseInt(studentId), month: currentMonth, year: currentYear })
    });
    const data = await res.json();
    if (data.success) { location.reload(); }
}

async function recalculate() {
    if (!currentMonth) return;
    const res = await fetch('/admin/badges/recalculate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ student_id: parseInt(studentId), month: currentMonth, year: currentYear })
    });
    const data = await res.json();
    const msg = document.getElementById('admin-tool-message');
    msg.textContent = data.message;
    msg.classList.remove('hidden');
    setTimeout(() => location.reload(), 1500);
}

loadData();
</script>
@endsection
