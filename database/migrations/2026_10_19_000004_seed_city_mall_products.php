<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Real City Mall catalog from Daniel's pricing doc (pages 3-4), seeded for
 * the already-built clickable mall picture (resources/views/education/
 * city-mall.blade.php -> EFDController::spending_tracker()). `type` values
 * match EFDController's $storeMap exactly so the existing hotspots/routes
 * work immediately - not the doc's own store-name spelling in every case
 * (see notes below).
 *
 * Known gaps, not invented here:
 * - "Comfort Zone" (mall hotspot s6) has no matching store in the doc at
 *   all - left unseeded rather than guessing what it should sell.
 * - "Room Essentials" (a real store in the doc - Home Goods, Small
 *   Furniture, Store & Organization, Room Decor) has no mall hotspot or
 *   $storeMap entry pointing to it at all - left unseeded; needs a code
 *   change (new hotspot + route) before it can be reached, not just data.
 * - The doc's "The Basics Hub" (Personal Care + Household Supplies) is
 *   seeded under the code's existing "Daily Essentials" type, since
 *   that's the mall hotspot that actually exists for this category - the
 *   display name mismatch is a separate, smaller finding.
 * - A handful of Accessories items (Cane, Walker, Non Slip Mat, Hand Held
 *   Shower, Chair Cushion Lifter, Blood Pressure Monitor, Glucose
 *   Monitor) have no price in the doc - not seeded.
 */
return new class extends Migration {
    public function up()
    {
        $products = [
            'The Basics Co.' => ['Clothing', [
                ['Shirt', 3, 'Needs-Fixed'], ['Trousers', 10, 'Needs-Fixed'], ['Shoes', 15, 'Needs-Fixed'],
                ['Sweater', 8, 'Needs-Fixed'], ['Jacket', 15, 'Needs-Fixed'], ['Socks', 1, 'Needs-Fixed'],
            ]],
            'Stationery Store' => ['Stationery', [
                ['Writing Tools', 10, 'Needs-Fixed'], ['Paper Products', 7, 'Needs-Fixed'],
                ['Organization Supplies', 18, 'Needs-Fixed'], ['Math Tools', 20, 'Needs-Fixed'],
                ['Art Supplies', 18, 'Needs-Fixed'],
            ]],
            'Daily Essentials' => ['Personal Care & Household', [
                ['Toiletries', 20, 'Needs-Fixed'], ['Personal Care', 30, 'Needs-Fixed'], ['Towels', 20, 'Needs-Fixed'],
                ['Trash Bags', 6, 'Needs-Fixed'], ['Toilet Paper', 10, 'Needs-Fixed'], ['Paper Towels', 5, 'Needs-Fixed'],
                ['Soap', 5, 'Needs-Fixed'], ['All Purpose Cleaner', 6, 'Needs-Fixed'],
            ]],
            'Tech Hub' => ['Electronics', [
                ['Take it Easy Phone', 50, 'Wants'], ['Never give Up Phone', 300, 'Wants'],
                ['Only me Phone', 800, 'Needs-Variable'], ['Laptop Standard', 400, 'Wants'],
                ['Laptop Elite', 1200, 'Wants'], ['Tablet Premium', 400, 'Needs-Variable'],
                ['Printer', 100, 'Needs-Variable'], ['Desk Lamp', 30, 'Needs-Variable'],
                ['Phone Charger', 15, 'Needs-Variable'], ['Laptop Charger', 30, 'Wants'],
                ['Bluetooth Headphones', 50, 'Needs-Variable'],
            ]],
            'BeU - BeLuxury' => ['Luxury', [
                ['Fashion t-shirt', 50, 'Wants'], ['Fashion Trousers', 120, 'Wants'], ['Quality Shoes', 150, 'Wants'],
                ['Nice Sweater', 120, 'Wants'], ['Quality Jacket', 250, 'Wants'], ['Premium Socks', 10, 'Wants'],
            ]],
            'Accessories' => ['Accessories', [
                ['Sunglasses', 100, 'Wants'], ['Handbag', 100, 'Needs-Fixed'], ['School Backpack', 20, 'Wants'],
                ['Fashion Backpack', 100, 'Wants'], ['Watch', 100, 'Needs-Fixed'], ['Eyeglasses', 100, 'Wants'],
                ['Hats', 50, 'Wants'],
            ]],
            'BeSpirit - Sport Shop' => ['Sports', [
                ['Basic Soccer Shoes', 40, 'Wants'], ['Professional Soccer Shoes', 150, 'Wants'],
                ['Recreational Basketball', 10, 'Wants'], ['Competition Basketball', 70, 'Wants'],
                ['Standard Volleyball', 15, 'Wants'], ['Tournament Volleyball', 80, 'Wants'],
                ['Practice Football', 10, 'Wants'], ['Official Football', 80, 'Wants'],
                ['Beginner Tennis Racket', 20, 'Wants'], ['Advanced Tennis Racket', 100, 'Wants'],
                ['Casual Table Tennis Paddle', 10, 'Wants'], ['Pro Table Tennis Paddle', 50, 'Wants'],
                ['Basic Bicycle', 100, 'Wants'], ['Mountain Bicycle', 500, 'Wants'],
                ['Simple Skateboard', 50, 'Wants'], ['Trick Skateboard', 100, 'Wants'],
                ['Fun Roller Skates', 50, 'Wants'], ['Speed Roller Skates', 200, 'Wants'],
                ['Foam Surfboard', 200, 'Wants'], ['Fiberglass Surfboard', 400, 'Wants'],
                ['Training Boxing Gloves', 20, 'Wants'], ['Championship Boxing Gloves', 100, 'Wants'],
            ]],
            'Beats - Music Store' => ['Music Instruments', [
                ['Acoustic Guitar', 80, 'Wants'], ['Acoustic Guitar Pro', 300, 'Wants'],
                ['Electric Guitar', 120, 'Wants'], ['Electric Guitar Pro', 400, 'Wants'],
                ['Bass Guitar', 150, 'Wants'], ['Bass Guitar Pro', 400, 'Wants'],
                ['Violin Starter', 60, 'Wants'], ['Violin Classic', 300, 'Wants'],
                ['Ukelele', 30, 'Wants'], ['Ukelele Mahogany', 100, 'Wants'],
                ['Keyboard Beginner', 100, 'Wants'], ['Keyboard Pro', 300, 'Wants'],
                ['Flute Beginner', 200, 'Wants'], ['Flute Concert', 300, 'Wants'],
                ['Trumpet Beginner', 250, 'Wants'], ['Trumpet Concert', 400, 'Wants'],
                ['Drum Kit Beginner', 300, 'Wants'], ['Drum Kit Master', 500, 'Wants'],
            ]],
        ];

        foreach ($products as $type => [$category, $items]) {
            foreach ($items as [$name, $price, $goodsType]) {
                $exists = DB::table('products')->where('type', $type)->where('product_name', $name)->exists();

                if (!$exists) {
                    DB::table('products')->insert([
                        'sid' => null,
                        'product_name' => $name,
                        'type' => $type,
                        'category' => $category,
                        'goods_type' => $goodsType,
                        'price' => $price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down()
    {
        DB::table('products')->whereIn('type', [
            'The Basics Co.', 'Stationery Store', 'Daily Essentials', 'Tech Hub',
            'BeU - BeLuxury', 'Accessories', 'BeSpirit - Sport Shop', 'Beats - Music Store',
        ])->delete();
    }
};
