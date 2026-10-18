<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Real "Wants" items from Daniel's pricing doc (page 6), used by
 * BankController::banks_penalty() to simulate forced purchases when a
 * student doesn't spend enough of their salary by month-end (bugs.md,
 * penalty discussion 2026-10-18). sid is null - "Fix for all schools"
 * (Admin Structure Proposal doc §7.1), matching the Supermarket catalog.
 *
 * Three items from the doc (Sports gear, Art Supplies, Phone Accessories)
 * were struck through in the source sheet - treated as removed/invalid,
 * not seeded.
 */
return new class extends Migration {
    public function up()
    {
        $items = [
            ['Movie Ticket', 12],
            ['New AAA Game (PS5/Xbox/PC)', 60],
            ['Indie/Medium-budget Games', 20],
            ['Mobile Games (Premium)', 5],
            ['Fast Food', 15],
            ['Comics', 15],
            ['Concert Ticket', 100],
            ['Sports Court Booking', 50],
            ['Premium Streaming Subscription', 15],
            ['Entertainment Subscription', 12],
            ['Soft drinks', 3],
            ['Sweets and Snacks', 6],
            ['Board games', 35],
            ['Event Ticket', 45],
            ['Birthday Party Supplies', 40],
            ['Restaurant', 50],
            ['Social Club Membership', 150],
            ['Hobby equipment', 50],
            ['Donation', 25],
            ['Gift', 40],
            ['Arcade Visit', 20],
            ['Barber Shop/Salon Visit', 35],
            ['Skincare Products', 40],
        ];

        $now = now();

        foreach ($items as [$name, $price]) {
            $exists = DB::table('penalty_wants_items')->where('item_name', $name)->exists();

            if (!$exists) {
                DB::table('penalty_wants_items')->insert([
                    'sid' => null,
                    'item_name' => $name,
                    'price' => $price,
                    'category' => 'Wants',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('penalty_wants_items')->where('category', 'Wants')->delete();
    }
};
