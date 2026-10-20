<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ZEDVILLE — Spending Activity catalog
 *
 * Daniel, 2026-10-21: "did you find all the activities for Spending
 * activities?" They already existed - 10 "High Spending" + 10 "Low
 * Spending" activities, each with a real title/mission/requirements/
 * destination/shop - but only as a hardcoded JS array inside
 * resources/views/spendingActivities/{high,low}-spending-activities.blade.php,
 * invisible to and unmanageable from the admin area. The `activities`
 * table itself already existed (2026_10_10_000001_create_missing_live_
 * schema_tables.php) and is already read by SpendingActivitiesController's
 * shop-destination pages - it was just never seeded or connected to the
 * two listing pages. This migration seeds it for real, rather than
 * creating a new table.
 *
 * `shop` stores the real `products.type` string(s), comma-separated for
 * multi-store activities - corrected from the original catalog's shorter
 * display text ("BeSpirit Sport Shop" -> "BeSpirit - Sport Shop" etc.) so
 * "Start Shopping" resolves to real inventory. "Supermarket" is left as-is
 * (it routes to market-list.blade.php, a different mechanism, not a
 * Product::type lookup). "Comfort Zone" (3 High activities) is preserved
 * faithfully even though it has zero real products today - flagged to
 * Daniel rather than invented around (bugs.md, 2026-10-21).
 */
return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('activities', 'type')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->string('type')->after('activity_type_id');
                $table->text('requirements')->nullable()->after('mission');
            });
        }

        // min_zeds/max_zeds were NOT NULL - Low's real activities have no
        // per-activity budget (Low computes a flat 10%-of-salary cap
        // client-side instead), so its 10 rows need to leave these null.
        DB::statement('ALTER TABLE activities MODIFY min_zeds DECIMAL(10,2) NULL');
        DB::statement('ALTER TABLE activities MODIFY max_zeds DECIMAL(10,2) NULL');

        if (DB::table('activities')->count() > 0) {
            return;
        }

        $now = now();

        $high = [
            ['number' => 1, 'title' => 'Birthday Party Budget', 'mission' => "You're throwing a birthday party for your best friend! You need to buy food for 8–10 guests.", 'requirements' => 'Purchase at least 15–25 food items. Choose diet preference: Omnivore, Vegetarian, Pescatarian, or Vegan', 'min_items' => 15, 'max_items' => 25, 'destination' => 'Goes to a Friend', 'shop' => 'Supermarket', 'special' => 'Choose diet preference and buy variety of food categories'],
            ['number' => 2, 'title' => 'Digital Upgrade', 'mission' => 'Enhance your technology setup for better productivity and entertainment.', 'requirements' => 'Choose at least 4–6 tech items. MUST include at least ONE major device (Tablet, Laptop, or Phone)', 'min_items' => 4, 'max_items' => 6, 'destination' => 'Your Own Home', 'shop' => 'Tech Hub', 'special' => 'MUST include at least ONE major device (Tablet, Laptop, or Phone)'],
            ['number' => 3, 'title' => 'Community Food Drive', 'mission' => 'The local food bank helps hungry families in your community. They need donations to fill their shelves.', 'requirements' => 'Select 3 items per family for 4 families (12 items total). Each family needs balanced nutrition', 'min_items' => 12, 'max_items' => null, 'destination' => 'Food Bank / Community Center', 'shop' => 'Supermarket', 'special' => '3 items per family × 4 families = 12 minimum. Balance spending across families'],
            ['number' => 4, 'title' => 'Study Space Setup', 'mission' => 'Create the perfect study environment at home to boost your academic performance.', 'requirements' => 'Purchase AT LEAST 8–10 items total. MUST include furniture or major tech item', 'min_items' => 8, 'max_items' => 10, 'destination' => 'Your Own Home (Study Room)', 'shop' => 'Stationery Store,Tech Hub,Comfort Zone', 'special' => 'MUST include furniture (desk or chair) OR major tech item (tablet)'],
            ['number' => 5, 'title' => 'Music Program Donation', 'mission' => 'The local orphanage wants to start a music program for kids. Help them by buying musical instruments!', 'requirements' => 'Purchase at least 8–12 instruments. Mix budget-friendly AND quality instruments', 'min_items' => 8, 'max_items' => 12, 'destination' => 'Orphanage', 'shop' => 'Beats - Music Store', 'special' => 'Mix budget-friendly instruments (5–8) AND quality instruments (2–4)'],
            ['number' => 6, 'title' => 'New Sport Starter Kit', 'mission' => 'Choose a new sport to learn and buy EVERYTHING you need for a complete setup!', 'requirements' => 'Main equipment + protective gear + clothing (3–4) + accessories (2–3). MINIMUM: 6–10 items', 'min_items' => 6, 'max_items' => 10, 'destination' => 'Your Own Home (Closet)', 'shop' => 'BeSpirit - Sport Shop', 'special' => 'Complete kit: main equipment + protective gear + clothing + accessories'],
            ['number' => 7, 'title' => 'Room Makeover', 'mission' => 'Transform your living space into the perfect personal sanctuary.', 'requirements' => 'Transform at least 2–3 room zones. Minimum 6–10 items total', 'min_items' => 6, 'max_items' => 10, 'destination' => 'Your Own Home', 'shop' => 'Comfort Zone,BeU - BeLuxury', 'special' => 'Transform at least 2–3 room zones (comfort, style, fashion)'],
            ['number' => 8, 'title' => 'Closet Restock', 'mission' => 'Oh no! Thieves took all your clothes! You need to rebuild your wardrobe with the most important items.', 'requirements' => 'Buy NEEDS first (7–8 basic items), then WANTS (2–3 fashion items)', 'min_items' => 9, 'max_items' => 11, 'destination' => 'Your Own Home (Closet)', 'shop' => 'The Basics Co.,BeU - BeLuxury', 'special' => 'Buy NEEDS first (7–8 basic items), then WANTS (2–3 fashion items)'],
            ['number' => 9, 'title' => 'Computer Donation', 'mission' => 'The local orphanage wants to help children learn digital skills for their future, but they need a computer!', 'requirements' => '1 Computer (Laptop Standard) + peripherals (4–5) + accessories (2–3). MINIMUM: 7–9 items', 'min_items' => 7, 'max_items' => 9, 'destination' => 'Orphanage', 'shop' => 'Tech Hub', 'special' => 'MUST include 1 Computer (Laptop Standard) + peripherals + accessories'],
            ['number' => 10, 'title' => 'Phone Replacement Gift', 'mission' => "Your friend's phone was stolen and they need a replacement! You want to help them get back online.", 'requirements' => 'Replacement phone + essential accessories (3–5). MINIMUM: 4–6 items', 'min_items' => 4, 'max_items' => 6, 'destination' => 'A Friend', 'shop' => 'Tech Hub', 'special' => 'MUST include phone + charger + accessories'],
        ];

        $low = [
            ['number' => 1, 'title' => 'Accessories for Seniors', 'mission' => 'The senior care home needs accessories to help elderly people feel more comfortable and stylish!', 'requirements' => 'Purchase at least 3-4 items', 'destination' => 'Senior Home Care', 'shop' => 'Accessories'],
            ['number' => 2, 'title' => 'Gift for a Friend', 'mission' => "Find the perfect accessories that express your friend's personal style while staying within budget!", 'requirements' => 'Purchase 2-3 thoughtful items', 'destination' => 'A Friend', 'shop' => 'Accessories'],
            ['number' => 3, 'title' => 'Sports Equipment Donation', 'mission' => 'The orphanage needs sports equipment so kids can play and exercise!', 'requirements' => 'Purchase at least 8-12 items (mix of balls, clothing, and accessories)', 'destination' => 'Orphanage', 'shop' => 'BeSpirit - Sport Shop'],
            ['number' => 4, 'title' => 'Tech Gift for a Friend', 'mission' => "Help your friend level up their tech with essential accessories!", 'requirements' => 'Purchase at least 4-6 tech items', 'destination' => 'A Friend', 'shop' => 'Tech Hub'],
            ['number' => 5, 'title' => 'Library Supplies', 'mission' => "Support learning in your community! Select essential supplies for a local library's children's section.", 'requirements' => 'Purchase at least 8-10 different items or bundles', 'destination' => "Local Library Children's Section", 'shop' => 'Stationery Store'],
            ['number' => 6, 'title' => 'Shelter Hygiene Items', 'mission' => 'Help provide dignity and basic comfort to individuals in need at a local homeless shelter.', 'requirements' => 'Purchase at least 6-8 hygiene and personal care items', 'destination' => 'Homeless Shelter', 'shop' => 'Daily Essentials'],
            ['number' => 7, 'title' => 'Community Tech Items', 'mission' => "Provide shareable tech accessories for a community center's computer lab.", 'requirements' => 'Purchase at least 6-10 tech items', 'destination' => 'Community Center', 'shop' => 'Tech Hub'],
            ['number' => 8, 'title' => 'Clothing Donation', 'mission' => 'Support a community clothing drive by contributing basic, versatile clothing items.', 'requirements' => 'Purchase at least 20-30 clothing items', 'destination' => 'Community Center', 'shop' => 'The Basics Co.'],
            ['number' => 9, 'title' => 'Community Cleaning Tools', 'mission' => 'Help a high-traffic community area maintain cleanliness with durable cleaning supplies.', 'requirements' => 'Purchase at least 10-15 cleaning items', 'destination' => 'Community Center', 'shop' => 'Daily Essentials'],
            ['number' => 10, 'title' => 'Sports Balls Donation', 'mission' => 'Help upgrade the quality of shared balls available at a public park or youth recreation area.', 'requirements' => 'Purchase quality/competition level balls (minimum 4-6)', 'destination' => 'Orphanage', 'shop' => 'BeSpirit - Sport Shop'],
        ];

        foreach ($high as $row) {
            DB::table('activities')->insert($row + ['type' => 'high', 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach ($low as $row) {
            DB::table('activities')->insert($row + ['type' => 'low', 'min_zeds' => null, 'max_zeds' => null, 'min_items' => null, 'max_items' => null, 'special' => null, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down()
    {
        DB::table('activities')->whereIn('type', ['high', 'low'])->delete();

        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['type', 'requirements']);
        });
    }
};
