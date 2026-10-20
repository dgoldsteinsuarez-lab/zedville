<?php

namespace App\Http\Controllers;

use App\Models\Street;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * City Map, Phase 1/1b (2026-10-19 - "City and Map" doc). Two-level map:
 * one overview image with the 9 districts the doc itself groups buildings
 * into, then one closer-in image per district with that district's own
 * buildings - instead of one overview image carrying ~25 tiny hotspots.
 * Same clip-path-polygon hotspot technique as education/city-mall.blade.php
 * either way; this only changes how many images/levels there are.
 *
 * Street plaques live on the Residential Quarter district page, not the
 * overview - streets are residential by definition (bugs.md's "never
 * live in the Bank" rule).
 *
 * The real hotspot polygon coordinates can only be hand-fitted once
 * Daniel supplies the actual exported images (angled/isometric, not
 * top-down). Until then this renders a plain clickable directory instead
 * of positioned hotspots, so the routing/data layer is fully testable now.
 */
class CityController extends Controller
{
    public static function districts(): array
    {
        return [
            ['key' => 'residential-quarter', 'name' => 'Residential Quarter'],
            ['key' => 'civic-plaza', 'name' => 'Civic Plaza'],
            ['key' => 'education-hub', 'name' => 'Education Hub'],
            ['key' => 'main-city-facilities', 'name' => 'Main City Facilities'],
            ['key' => 'financial-square', 'name' => 'Financial Square'],
            ['key' => 'community-care', 'name' => 'Community Care'],
            ['key' => 'public-services', 'name' => 'Public Services'],
            ['key' => 'industrial-utilities', 'name' => 'Industrial and Utilities Zone'],
            ['key' => 'recreation-development', 'name' => 'Recreation and Development Spaces'],
        ];
    }

    /**
     * The 7 buildings that already have a real page. Every other building
     * in the doc stays visible with no route - "Coming soon" - until
     * Daniel builds its real page; adding one later is a one-line change
     * here, not a new map.
     */
    public static function buildings(): array
    {
        return [
            // A. Residential Quarter
            ['key' => 'student-houses', 'name' => "Students' Houses", 'district' => 'residential-quarter', 'route' => null],
            ['key' => 'parks', 'name' => 'Parks', 'district' => 'residential-quarter', 'route' => null],

            // B. Civic Plaza
            ['key' => 'city-hall', 'name' => 'City Hall', 'district' => 'civic-plaza', 'route' => route('education.city-hall')],
            ['key' => 'efd', 'name' => 'Educational Finance Department', 'district' => 'civic-plaza', 'route' => route('education.educational_finance_department')],
            ['key' => 'post-office', 'name' => 'Post Office (City Mailbox)', 'district' => 'civic-plaza', 'route' => route('profile.mailbox')],
            ['key' => 'news-center', 'name' => 'News Center', 'district' => 'civic-plaza', 'route' => null],
            ['key' => 'bus-station', 'name' => 'Bus Station', 'district' => 'civic-plaza', 'route' => null],

            // C. Education Hub
            ['key' => 'school', 'name' => 'School', 'district' => 'education-hub', 'route' => null],
            ['key' => 'wall-of-inspiration', 'name' => 'Wall of Inspiration, Voice and Art', 'district' => 'education-hub', 'route' => null],

            // D. Main City Facilities
            ['key' => 'city-mall', 'name' => 'City Mall', 'district' => 'main-city-facilities', 'route' => route('education.city-mall')],
            ['key' => 'supermarket', 'name' => 'Supermarket', 'district' => 'main-city-facilities', 'route' => route('supermarket.market-list')],
            ['key' => 'restaurants', 'name' => 'Restaurants', 'district' => 'main-city-facilities', 'route' => null],
            ['key' => 'farmers-market', 'name' => 'Farmers Market', 'district' => 'main-city-facilities', 'route' => null],
            ['key' => 'car-dealership', 'name' => 'Car Dealership / Auto Repair', 'district' => 'main-city-facilities', 'route' => null],

            // E. Financial Square
            ['key' => 'bank', 'name' => 'Universal Bank', 'district' => 'financial-square', 'route' => route('bank.index')],
            ['key' => 'small-business-district', 'name' => 'Small Business District', 'district' => 'financial-square', 'route' => null],
            ['key' => 'money-factory', 'name' => 'Money Factory', 'district' => 'financial-square', 'route' => null],

            // F. Community Care
            ['key' => 'hospital', 'name' => 'Hospital', 'district' => 'community-care', 'route' => null],
            ['key' => 'donation-center', 'name' => 'Donation Center', 'district' => 'community-care', 'route' => route('education.npos')],

            // G. Public Services
            ['key' => 'courthouse', 'name' => 'Courthouse', 'district' => 'public-services', 'route' => null],
            ['key' => 'fire-station', 'name' => 'Fire Station', 'district' => 'public-services', 'route' => null],
            ['key' => 'police-station', 'name' => 'Police Station', 'district' => 'public-services', 'route' => null],
            ['key' => 'repair-shop', 'name' => 'Repair Shop', 'district' => 'public-services', 'route' => null],

            // H. Industrial and Utilities Zone
            ['key' => 'water-treatment', 'name' => 'Water Treatment Plant / Utilities Office', 'district' => 'industrial-utilities', 'route' => null],
            ['key' => 'power-plant', 'name' => 'Power Plant / Electrical Grid', 'district' => 'industrial-utilities', 'route' => null],
            ['key' => 'gas-station', 'name' => 'Gas Station', 'district' => 'industrial-utilities', 'route' => null],
            ['key' => 'recycling-center', 'name' => 'Recycling Center', 'district' => 'industrial-utilities', 'route' => null],

            // I. Recreation and Development Spaces
            ['key' => 'vacant-lots', 'name' => 'Vacant Lots', 'district' => 'recreation-development', 'route' => null],
        ];
    }

    public function index(): View
    {
        $user = Auth::user();
        $myAddress = $this->myAddress($user);

        $basketTrend = \App\Services\ConsumerSurveySummaryService::cityBasketTrend($user);
        foreach ($basketTrend as $i => &$row) {
            $prev = $basketTrend[$i + 1]['avgBasketCost'] ?? null;
            $row['pctChange'] = ($prev && $prev > 0)
                ? round((($row['avgBasketCost'] - $prev) / $prev) * 100, 1)
                : null;
        }
        unset($row);

        return view('education.city.index', [
            'myAddress' => $myAddress,
            'districts' => self::districts(),
            'basketTrend' => $basketTrend,
        ]);
    }

    public function district(string $district): View
    {
        $districts = collect(self::districts());
        $current = $districts->firstWhere('key', $district);

        if (!$current) {
            abort(404);
        }

        $user = Auth::user();
        $buildings = collect(self::buildings())->where('district', $district)->values()->all();

        $streets = $district === 'residential-quarter'
            ? Street::orderBy('sort_order')->get()
            : collect();

        return view('education.city.district', [
            'district' => $current,
            'buildings' => $buildings,
            'streets' => $streets,
            'myAddress' => $this->myAddress($user),
        ]);
    }

    private function myAddress($user)
    {
        return $user->isStudent()
            ? \App\Services\AddressService::assignForStudent($user)?->load('street')
            : null;
    }
}
