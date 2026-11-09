<?php

namespace App\Http\Controllers;

use App\Models\Street;
use App\Support\CityStreetLabels;
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
 * 2026-10-22: Daniel supplied the renders - a top view split into his 9
 * divisions (clickable areas on the overview) and one 3D view per division
 * (shown on the district page, with that division's buildings listed below).
 */
class CityController extends Controller
{
    /**
     * Daniel's 9 map divisions (2026-10-22, "ZED_Infra - 3D View - WHOLE D
     * DEVISION"), numbered as on his drawing. 'image' is that division's 3D
     * render (ZED_Infra - 3D View - 1D ... 9D) in public/asset/front/images/city.
     * 'area' is where the division sits on the top-view overview image, in
     * percent (left, top, width, height) - traced from the lines he drew.
     */
    public static function districts(): array
    {
        return self::localized('districts', [
            ['key' => 'senior-quarter', 'number' => 1, 'name' => 'Senior Care Quarter', 'area' => [0, 0, 27.25, 30.7]],
            ['key' => 'financial-square', 'number' => 2, 'name' => 'Financial Square', 'area' => [27.25, 0, 29, 30.7]],
            ['key' => 'market-energy', 'number' => 3, 'name' => 'Market & Energy District', 'area' => [56.25, 0, 43.75, 48.3]],
            ['key' => 'community-care', 'number' => 4, 'name' => 'Community Care', 'area' => [65, 48.3, 35, 30.3]],
            ['key' => 'civic-plaza', 'number' => 5, 'name' => 'Civic Plaza', 'area' => [20, 30.7, 36.25, 17.9]],
            ['key' => 'residential-quarter', 'number' => 6, 'name' => 'Residential Quarter', 'area' => [0, 30.7, 20, 18.4]],
            ['key' => 'garden-homes', 'number' => 7, 'name' => 'Garden Homes', 'area' => [0, 49.1, 16.75, 50.9]],
            ['key' => 'education-hub', 'number' => 8, 'name' => 'Education Hub', 'area' => [16.75, 48.6, 48.25, 51.4]],
            ['key' => 'future-developments', 'number' => 9, 'name' => 'Future Developments', 'area' => [65, 78.6, 35, 21.4]],
        ]);
    }

    /** The 3D render of one division. */
    public static function districtImage(array $district): string
    {
        return asset('asset/front/images/city/district-' . $district['number'] . '.jpg');
    }

    /**
     * Each division's render: its size, and the empty white rows at its top
     * and bottom, cut off on screen so the picture can be bigger (the page
     * fits the screen, Daniel 2026-10-28) - [width, height, top, bottom].
     */
    private const DISTRICT_PICTURES = [
        1 => [2400, 1323, 24, 9],
        2 => [2400, 1472, 23, 8],
        3 => [2400, 1480, 24, 11],
        4 => [2400, 1421, 24, 10],
        5 => [2400, 1441, 24, 9],
        6 => [2400, 1515, 23, 14],
        7 => [2400, 1560, 26, 19],
        8 => [2400, 1468, 23, 5],
        9 => [2400, 1323, 23, 24],
    ];

    public static function districtPicture(array $district): array
    {
        [$width, $height, $cropTop, $cropBottom] = self::DISTRICT_PICTURES[$district['number']];

        return ['url' => self::districtImage($district)] + compact('width', 'height', 'cropTop', 'cropBottom');
    }

    /**
     * Every building, in the division whose render shows it. A building
     * without a page yet ('route' => null) shows as "Coming soon". Buildings
     * from the "City and Map" doc that no render shows yet are listed under
     * Future Developments. NPO buildings open their donation page.
     */
    public static function buildings(): array
    {
        return self::localized('buildings', [
            // 1. Senior Care Quarter
            ['key' => 'senior-care-home', 'name' => 'Senior Care Home', 'district' => 'senior-quarter', 'npo' => true, 'route' => self::npoRoute('senior-care-home'), 'spots' => ['overview' => [9.5, 8.5, 11.5, 7], 'district' => [62, 27, 21, 28]]],
            ['key' => 'family-homes', 'name' => 'Family Homes', 'district' => 'senior-quarter', 'route' => null],
            ['key' => 'tennis-courts', 'name' => 'Tennis & Basketball Courts', 'district' => 'senior-quarter', 'route' => null],

            // 2. Financial Square
            ['key' => 'bank', 'name' => 'Universal Bank', 'district' => 'financial-square', 'route' => route('bank.index'), 'spots' => ['overview' => [40, 20.5, 6, 7.5], 'district' => [42, 57, 16, 19]]],
            ['key' => 'money-factory', 'name' => 'Money Factory', 'district' => 'financial-square', 'route' => route('education.money-factory')],
            ['key' => 'courthouse', 'name' => 'Courthouse', 'district' => 'financial-square', 'route' => null],
            ['key' => 'city-mall', 'name' => 'City Mall', 'district' => 'financial-square', 'route' => route('education.city-mall'), 'spots' => ['overview' => [41, 8.5, 9, 7.5], 'district' => [58, 42, 19, 22]]],
            ['key' => 'community-center', 'name' => 'Community Center', 'district' => 'financial-square', 'npo' => true, 'route' => self::npoRoute('community-center'), 'spots' => ['overview' => [30.5, 10.5, 3.5, 6], 'district' => [37, 32, 11, 12]]],
            ['key' => 'agora', 'name' => 'Agora', 'district' => 'financial-square', 'route' => null],
            ['key' => 'news-center', 'name' => 'Broadcasting Center', 'district' => 'financial-square', 'route' => null],
            ['key' => 'bakery', 'name' => 'Bakery', 'district' => 'financial-square', 'route' => null],

            // 3. Market & Energy District
            ['key' => 'efd', 'name' => 'Educational Finance Department', 'district' => 'market-energy', 'route' => route('education.educational_finance_department'), 'spots' => ['overview' => [61.8, 22.6, 3.2, 4.4], 'district' => [29.5, 15, 7, 24]]],
            ['key' => 'library', 'name' => 'Library', 'district' => 'market-energy', 'npo' => true, 'route' => self::npoRoute('local-library'), 'spots' => ['overview' => [56.8, 39.3, 2.6, 4.8], 'district' => [7, 34, 9, 12]]],
            ['key' => 'supermarket', 'name' => 'Supermarket', 'district' => 'market-energy', 'route' => route('supermarket.market-list'), 'spots' => ['overview' => [69.4, 39, 5.2, 5.2], 'district' => [25, 51, 11, 12]]],
            ['key' => 'restaurants', 'name' => 'Restaurants', 'district' => 'market-energy', 'route' => null],
            ['key' => 'car-dealership', 'name' => 'Car Dealership / Auto Repair', 'district' => 'market-energy', 'route' => null],
            ['key' => 'sports-center', 'name' => 'Sports Center', 'district' => 'market-energy', 'route' => null],
            ['key' => 'power-plant', 'name' => 'Power Plant, Solar & Wind Farm', 'district' => 'market-energy', 'route' => null],

            // 4. Community Care
            ['key' => 'orphanage', 'name' => "Children's Orphanage", 'district' => 'community-care', 'npo' => true, 'route' => self::npoRoute('childrens-orphanage'), 'spots' => ['overview' => [67.3, 51.8, 3.9, 16.5], 'district' => [19, 9, 19, 24]]],
            ['key' => 'donation-center', 'name' => 'Donation Center', 'district' => 'community-care', 'route' => route('education.npos')],
            ['key' => 'apartments', 'name' => 'Apartment Blocks', 'district' => 'community-care', 'route' => null],
            ['key' => 'playground', 'name' => 'Playground', 'district' => 'community-care', 'route' => null],

            // 5. Civic Plaza
            ['key' => 'city-hall', 'name' => 'City Hall', 'district' => 'civic-plaza', 'route' => route('education.city-hall'), 'spots' => ['overview' => [43.5, 34.5, 7.5, 8.5], 'district' => [56, 45, 28, 30]]],
            ['key' => 'police-station', 'name' => 'Police Station', 'district' => 'civic-plaza', 'route' => null],
            ['key' => 'fire-station', 'name' => 'Fire Station', 'district' => 'civic-plaza', 'route' => null],
            ['key' => 'central-park', 'name' => 'Central Park', 'district' => 'civic-plaza', 'route' => null],
            ['key' => 'post-office', 'name' => 'Post Office (City Mailbox)', 'district' => 'civic-plaza', 'route' => route('profile.mailbox')],

            // 6. Residential Quarter
            ['key' => 'student-houses', 'name' => "Students' Houses", 'district' => 'residential-quarter', 'route' => null],
            ['key' => 'parks', 'name' => 'Parks', 'district' => 'residential-quarter', 'route' => null],

            // 7. Garden Homes
            ['key' => 'garden-houses', 'name' => 'Houses with Pool Garden', 'district' => 'garden-homes', 'route' => null],
            ['key' => 'gas-station', 'name' => 'Gas Station', 'district' => 'garden-homes', 'route' => null],
            ['key' => 'camp', 'name' => 'Camping Cabins', 'district' => 'garden-homes', 'route' => null],

            // 8. Education Hub
            ['key' => 'school', 'name' => 'School', 'district' => 'education-hub', 'route' => null],
            ['key' => 'aqua-park', 'name' => 'Aqua Park', 'district' => 'education-hub', 'route' => null],
            ['key' => 'bus-station', 'name' => 'Bus Station', 'district' => 'education-hub', 'route' => null],

            // 9. Future Developments - planned in the "City and Map" doc, not built yet
            ['key' => 'vacant-lots', 'name' => 'Vacant Lots', 'district' => 'future-developments', 'route' => null],
            ['key' => 'hospital', 'name' => 'Hospital', 'district' => 'future-developments', 'route' => null],
            ['key' => 'farmers-market', 'name' => 'Farmers Market', 'district' => 'future-developments', 'route' => null],
            ['key' => 'small-business-district', 'name' => 'Small Business District', 'district' => 'future-developments', 'route' => null],
            ['key' => 'wall-of-inspiration', 'name' => 'Wall of Inspiration, Voice and Art', 'district' => 'future-developments', 'route' => null],
            ['key' => 'recycling-center', 'name' => 'Recycling Center', 'district' => 'future-developments', 'route' => null],
            ['key' => 'water-treatment', 'name' => 'Water Treatment Plant / Utilities Office', 'district' => 'future-developments', 'route' => null],
        ]);
    }

    /**
     * Clickable buildings on a city image ('overview' or the district key):
     * buildings with a page of their own and a drawn 'spots' area (left, top,
     * width, height in % of that image). An NPO switched off has no page, so
     * it isn't clickable either.
     */
    public static function buildingSpots(string $view): array
    {
        return collect(self::buildings())
            ->filter(fn ($b) => $b['route'] && isset($b['spots']) && ($view === 'overview' || $b['district'] === $view))
            ->map(fn ($b) => ['name' => $b['name'], 'route' => $b['route'], 'npo' => $b['npo'] ?? false, 'area' => $b['spots'][$view === 'overview' ? 'overview' : 'district']])
            ->values()
            ->all();
    }

    /**
     * The buildings you can go into, each with its district - listed on the
     * overview before the districts (Daniel, 2026-11-02: "también debe haber una
     * lista de los edificios importantes, es decir, en los que se puede entrar,
     * porque actualmente se accede a ellos cuando entras al barrio").
     */
    public static function importantBuildings(): array
    {
        $districts = collect(self::districts())->keyBy('key');

        return collect(self::buildings())
            ->filter(fn ($b) => $b['route'])
            ->map(fn ($b) => $b + ['district_name' => $districts[$b['district']]['name'], 'district_number' => $districts[$b['district']]['number']])
            ->values()
            ->all();
    }

    /** An NPO building opens its donation page - only while that NPO is switched on. */
    private static function npoRoute(string $slug): ?string
    {
        return \App\Models\Npo::where('slug', $slug)->where('status', 1)->exists()
            ? route('education.npos.donate', $slug)
            : null;
    }

    /**
     * Names in the student's language (lang/<language>/city.php, by key);
     * a building added here without a translation keeps its English name.
     */
    private static function localized(string $group, array $items): array
    {
        return array_map(function (array $item) use ($group) {
            $key = "city.{$group}.{$item['key']}";
            $item['name'] = \Illuminate\Support\Facades\Lang::has($key) ? __($key) : $item['name'];

            return $item;
        }, $items);
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

        // Daniel, 2026-10-20: also reachable directly from the overview,
        // not just from the Residential Quarter district page (which
        // keeps its own copy too - this is an additional entry point, not
        // a replacement).
        $streets = Street::orderBy('sort_order')->get();

        return view('education.city.index', [
            'myAddress' => $myAddress,
            'districts' => self::districts(),
            'importantBuildings' => self::importantBuildings(),
            'basketTrend' => $basketTrend,
            'streets' => $streets,
            'streetLabels' => self::streetLabels('overview'),
            'buildingSpots' => self::buildingSpots('overview'),
        ]);
    }

    /**
     * Street names to draw on a city image ('overview' or a district
     * number), each with its Street so the label opens the street's bio.
     */
    public static function streetLabels(string|int $view): array
    {
        $streets = Street::all()->keyBy('name');

        return collect(CityStreetLabels::for($view))
            ->filter(fn ($row) => $streets->has($row[0]))
            ->map(fn ($row) => ['street' => $streets[$row[0]], 'x' => $row[1], 'y' => $row[2], 'angle' => $row[3]])
            ->values()
            ->all();
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
            'streetLabels' => self::streetLabels($current['number']),
            'buildingSpots' => self::buildingSpots($district),
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
