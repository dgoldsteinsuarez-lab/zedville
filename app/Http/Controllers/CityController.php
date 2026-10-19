<?php

namespace App\Http\Controllers;

use App\Models\Street;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * City Map, Phase 1 (2026-10-19 - "City and Map" doc). One shared city
 * image (not per-Grade - Daniel only has one 3D model) with clickable
 * building hotspots and street plaques, same clip-path-polygon technique
 * as education/city-mall.blade.php and education/city-hall/city-hall.
 * blade.php.
 *
 * The real hotspot polygon coordinates can only be hand-fitted once
 * Daniel supplies the actual exported hero image (angled/isometric, not
 * top-down - he wants each building's unique architecture recognizable).
 * Until then this renders a plain clickable directory instead of
 * positioned hotspots, so the routing/data layer is fully testable now.
 */
class CityController extends Controller
{
    /**
     * The 7 buildings that already have a real page. Every other building
     * in the doc (Hospital, Courthouse, Police/Fire Station, Power Plant,
     * Water Treatment, Gas Station, Recycling Center, Farmers Market, Car
     * Dealership, 3 Restaurants, Small Business District, Money Factory,
     * Repair Shop, School, Wall of Inspiration, Vacant Lots) stays visible
     * with no route - "Coming soon" - until Daniel builds its real page;
     * adding one later is a one-line change here, not a new map.
     */
    public static function buildings(): array
    {
        return [
            ['key' => 'bank', 'name' => 'Universal Bank', 'route' => route('bank.index')],
            ['key' => 'city-mall', 'name' => 'City Mall', 'route' => route('education.city-mall')],
            ['key' => 'supermarket', 'name' => 'Supermarket', 'route' => route('supermarket.market-list')],
            ['key' => 'efd', 'name' => 'Educational Finance Department', 'route' => route('education.educational_finance_department')],
            ['key' => 'post-office', 'name' => 'Post Office (City Mailbox)', 'route' => route('profile.mailbox')],
            ['key' => 'donation-center', 'name' => 'Donation Center', 'route' => route('education.npos')],
            ['key' => 'city-hall', 'name' => 'City Hall', 'route' => route('education.city-hall')],

            ['key' => 'hospital', 'name' => 'Hospital', 'route' => null],
            ['key' => 'courthouse', 'name' => 'Courthouse', 'route' => null],
            ['key' => 'fire-station', 'name' => 'Fire Station', 'route' => null],
            ['key' => 'police-station', 'name' => 'Police Station', 'route' => null],
            ['key' => 'repair-shop', 'name' => 'Repair Shop', 'route' => null],
            ['key' => 'water-treatment', 'name' => 'Water Treatment Plant / Utilities Office', 'route' => null],
            ['key' => 'power-plant', 'name' => 'Power Plant / Electrical Grid', 'route' => null],
            ['key' => 'gas-station', 'name' => 'Gas Station', 'route' => null],
            ['key' => 'recycling-center', 'name' => 'Recycling Center', 'route' => null],
            ['key' => 'farmers-market', 'name' => 'Farmers Market', 'route' => null],
            ['key' => 'car-dealership', 'name' => 'Car Dealership / Auto Repair', 'route' => null],
            ['key' => 'restaurants', 'name' => 'Restaurants', 'route' => null],
            ['key' => 'small-business-district', 'name' => 'Small Business District', 'route' => null],
            ['key' => 'money-factory', 'name' => 'Money Factory', 'route' => null],
            ['key' => 'school', 'name' => 'School', 'route' => null],
            ['key' => 'wall-of-inspiration', 'name' => 'Wall of Inspiration, Voice and Art', 'route' => null],
            ['key' => 'vacant-lots', 'name' => 'Vacant Lots', 'route' => null],
        ];
    }

    public function index(): View
    {
        $user = Auth::user();
        $myAddress = $user->isStudent()
            ? \App\Services\AddressService::assignForStudent($user)?->load('street')
            : null;

        $streets = Street::orderBy('sort_order')->get();

        return view('education.city.index', [
            'user' => $user,
            'myAddress' => $myAddress,
            'buildings' => self::buildings(),
            'streets' => $streets,
        ]);
    }
}
