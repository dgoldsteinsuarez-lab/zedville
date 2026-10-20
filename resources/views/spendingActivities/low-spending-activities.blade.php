@extends('layouts.profile')

@section('title', 'Low Spending Activities')

@section('content')
  @push('styles')
    <style>
        

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .header .subtitle {
            color: #666;
            font-size: 1.1em;
        }

        .budget-info {
            background: #f8f9fa;
            border-left: 5px solid #667eea;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }

        .budget-info h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .budget-info ul {
            margin-left: 20px;
            color: #555;
            line-height: 1.8;
        }

        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .activity-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .activity-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
        }

        .activity-number {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 0.9em;
        }

        .activity-title {
            font-size: 1.3em;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .activity-mission {
            color: #666;
            font-size: 0.95em;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .activity-requirements {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 10px;
            font-size: 0.9em;
        }

        .activity-requirements strong {
            color: #333;
        }

        .activity-destination {
            background: #e8f5e9;
            padding: 10px;
            border-radius: 6px;
            font-size: 0.9em;
            color: #2e7d32;
            font-weight: 500;
        }

        .activity-destination::before {
            content: "📍 ";
        }

        .budget-limit {
            background: #fff3e0;
            padding: 8px 12px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 0.85em;
            color: #e65100;
            font-weight: 600;
        }

        .rules-section {
            background: #f5f5f5;
            padding: 30px;
            border-radius: 12px;
            margin-top: 40px;
        }

        .rules-section h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .rule-item {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .rule-item strong {
            color: #333;
            display: block;
            margin-bottom: 8px;
        }

        .rule-item p {
            color: #666;
            line-height: 1.6;
        }

        .shop-link {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 10px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .shop-link:hover {
            background: #5568d3;
        }
    </style>
  @endpush
  <div class="flex flex-col items-start justify-between pb-6 space-y-4 lg:items-center lg:space-y-0 lg:flex-row">
    <h1 class="text-xl font-bold whitespace-nowrap ">Low Spending Activities</h1>
  </div>
    <div class="grid grid-cols-1 gap-5 mt-6" x-data="{openEmergencyModal: false, emerFundmodal: false}">
        <div class="themeTabspills">
            <div class="w-full">
                <div class="header">
                    <h1>💰 Low Spending Activities</h1>
                    <p class="subtitle">Budget-Friendly Community Contributions</p>
                </div>

                <div class="budget-info">
                    <h3>📊 Spending Rules</h3>
                    <ul>
                        <li><strong>Budget Limit:</strong> Spend up to 10% of your monthly salary (max <span class="cap-zeds">...</span> zeds)</li>
                        <li><strong>Warning Alert:</strong> If you exceed 10%, you'll receive a budget alert to remove items</li>
                        <li><strong>Assignment:</strong> Activities are assigned sequentially (Activity 1 in month 1, Activity 2 in month 2, etc.)</li>
                        <li><strong>Delivery:</strong> Each activity has a pre-set delivery destination (automatically configured)</li>
                    </ul>
                </div>

                <div class="activities-grid" id="activitiesGrid">
                    <!-- Activities will be generated by JavaScript -->
                </div>

                <div class="rules-section">
                    <h2>🔧 Implementation Rules for IT Team</h2>
                    
                    <div class="rule-item">
                        <strong>1. Assignment Logic</strong>
                        <p>Activities are assigned sequentially on the first Friday of each month (Salary Day). Student receives Activity 1 in their first month, Activity 2 in their second month, continuing through Activity 10, then cycling back to Activity 1.</p>
                    </div>

                    <div class="rule-item">
                        <strong>2. Budget Enforcement</strong>
                        <p>System must calculate in real-time: (Cart Total / Monthly Salary) × 100. If result exceeds 10%, display warning: "BUDGET ALERT: Oops! You've gone over the spending limit for this activity. Remove some items to get back on track!"</p>
                    </div>

                    <div class="rule-item">
                        <strong>3. Wants Spending Target (20-30%)</strong>
                        <p>Goal: Students should spend 20-30% of their salary on "wants" category overall. Low Spending Activities contribute up to 10% of this target. Monitor total "wants" spending throughout the month.</p>
                    </div>

                    <div class="rule-item">
                        <strong>4. Automatic Intervention (25th of Month)</strong>
                        <p>If by the 25th of the month, student's total "wants" spending is below 20%, system automatically assigns a High Discretionary Activity. If still below 20% by the 26th, assign "List of Wants" activity.</p>
                    </div>

                    <div class="rule-item">
                        <strong>5. Delivery Address Automation</strong>
                        <p>When student completes purchase, delivery address is automatically set based on activity number (see activity cards for specific destinations). No manual selection needed for assigned activities.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endsection
@push('scripts')
    <script>
        // ╔════════════════════════════════════════════════════════════════════════╗
        // ║  🔌 IT TEAM: CONNECT THIS FUNCTION TO YOUR PLATFORM                ║
        // ║                                                                      ║
        // ║  Replace the inside of this function so it returns the student's    ║
        // ║  current salary from your platform.                                 ║
        // ║                                                                      ║
        // ║  EXAMPLE:                                                           ║
        // ║    const response = await fetch('/api/current-student/salary');      ║
        // ║    const data = await response.json();                              ║
        // ║    return data.salary;                                              ║
        // ╚════════════════════════════════════════════════════════════════════════╝
        async function getStudentSalary() {
            // ⚠️ IT TEAM: Replace the line below with your actual salary source.
            //    This placeholder keeps the page working before connection.
            return 3953;
        }

        // Daniel, 2026-10-21: "did you find all the activities for Spending
        // activities?" - the 10 Low activities are now a real DB catalog
        // (app/Models/Activity.php). Previously this page showed all 10
        // unconditionally with no selection and a dead "Go to shop" link
        // (href="#") - it now shows only the one activity actually assigned
        // to this student's class (EFDController::low_spending_activities()),
        // brought up to the same "show the one assigned activity, with a
        // real shop link" pattern already working on the High page.
        const activity = {!! $activity ? $activity->toJson() : 'null' !!};

        const shopRoutes = {
            "Accessories": "{{ url('/spending-activities/spending-tracker/accessories') }}?activity_id={{ $activity->id ?? '' }}",
            "BeSpirit - Sport Shop": "{{ url('/spending-activities/spending-tracker/bespirit-sport-shop') }}?activity_id={{ $activity->id ?? '' }}",
            "Tech Hub": "{{ url('/spending-activities/spending-tracker/tech-hub') }}?activity_id={{ $activity->id ?? '' }}",
            "Stationery Store": "{{ url('/spending-activities/spending-tracker/stationery-store') }}?activity_id={{ $activity->id ?? '' }}",
            "Daily Essentials": "{{ url('/spending-activities/spending-tracker/daily-essentials') }}?activity_id={{ $activity->id ?? '' }}",
            "The Basics Co.": "{{ url('/spending-activities/spending-tracker/basicco') }}?activity_id={{ $activity->id ?? '' }}",
        };

        // Generate the one assigned activity's card with dynamic salary
        async function generateActivities() {
            const salary = await getStudentSalary();
            const capZeds = Math.round(salary * 0.10);

            const grid = document.getElementById('activitiesGrid');

            if (!activity) {
                grid.innerHTML = '<p>No activity assigned</p>';
                return;
            }

            const card = document.createElement('div');
            card.className = 'activity-card';

            const shopUrl = shopRoutes[activity.shop] || '#';

            card.innerHTML = `
                <div class="activity-number">Activity ${activity.number}</div>
                <h3 class="activity-title">${activity.title}</h3>
                <p class="activity-mission">${activity.mission}</p>
                <div class="activity-requirements"><strong>Requirements:</strong> ${activity.requirements}</div>
                <div class="activity-destination">Delivery: ${activity.destination}</div>
                <div class="budget-limit">💰 Budget: Up to 10% of salary (max ${capZeds} zeds)</div>
                <a href="${shopUrl}" class="shop-link">Go to ${activity.shop} →</a>
            `;

            grid.appendChild(card);

            // Update the spending rules section
            document.querySelectorAll('.cap-zeds').forEach(el => {
                el.textContent = capZeds;
            });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', generateActivities);
    </script>
@endpush