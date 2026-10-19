<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Daniel, 2026-10-19: the `npos` table was empty locally - "I know the IT
 * guys did it, could I find this in hostinger?" Yes: the real 7 NGOs were
 * seeded directly into the production database on Hostinger, never
 * committed as a migration/seeder here (same pattern as the avatars table
 * and the City Mall catalog). Daniel pulled the production data via
 * phpMyAdmin's Export; this migration replays it here.
 *
 * One deliberate difference from the production dump: production's
 * `image` values are upload-style filenames with a unix-timestamp prefix
 * (e.g. "1773142879_animal-shelter.png", written by
 * NpoController::add_npo()'s `time() . '_' . $file->getClientOriginalName()`
 * pattern) pointing at files in `public/uploads/npos/` - which only exist
 * on the live server, not in this repo. The exact same 7 NGOs already had
 * plain-named images committed at `public/asset/front/images/npos/` (e.g.
 * "animal-shelter.png") - likely the original source files before they
 * were uploaded through the admin form. Copied those into
 * `public/uploads/npos/` (where the view actually reads from) and used
 * their plain filenames instead of production's timestamped ones, so this
 * works without needing the production image files themselves.
 */
return new class extends Migration
{
    public function up()
    {
        $npos = [
            ['id' => 1, 'category' => 'Animals', 'name' => 'Animal Shelter', 'slug' => 'animal-shelter', 'content' => 'The City Animal Rescue Center gives abandoned pets a second chance at finding a loving home. Help feed and care for dogs, cats, and other animals by donating food, blankets, toys, or contributing to their medical care.', 'image' => 'animal-shelter.png', 'account_number' => '1234-5678-9012', 'bank_name' => 'Animal Shelter', 'sort_order' => 1],
            ['id' => 2, 'category' => 'Elderly', 'name' => 'Senior Care Home', 'slug' => 'senior-care-home', 'content' => 'Our Senior Living Community is home to elderly neighbors who could use some extra love and support. Brighten their days with personal care items, books, puzzles, or help fund special activities that bring joy to their lives.', 'image' => 'senior-care-home.png', 'account_number' => '2345-6789-0123', 'bank_name' => 'Senior Care Home', 'sort_order' => 2],
            ['id' => 3, 'category' => 'Children', 'name' => "Children's Orphanage", 'slug' => 'childrens-orphanage', 'content' => "The Sunshine Children's Home provides a safe, nurturing place for kids who need it most. Give them hope for a brighter future with clothing, school supplies, toys, or support their education and dreams.", 'image' => 'children-orphanage.png', 'account_number' => '3456-7890-1234', 'bank_name' => "Children's Orphanage", 'sort_order' => 3],
            ['id' => 4, 'category' => 'Food', 'name' => 'Food Bank', 'slug' => 'food-bank', 'content' => 'The Community Food Bank ensures no family in our community goes to bed hungry. Stock their shelves with canned goods, hygiene products, baby essentials, or help provide fresh, healthy meals.', 'image' => 'food-bank.png', 'account_number' => '4567-8901-2345', 'bank_name' => 'Food Bank', 'sort_order' => 4],
            ['id' => 5, 'category' => 'Education', 'name' => 'Local Library', 'slug' => 'local-library', 'content' => 'Our local library opens doors to knowledge and opportunity for everyone. Share the gift of reading and learning by donating books, computers, or supporting programs that help people of all ages discover new worlds.', 'image' => 'local-library.png', 'account_number' => ' 5678-9012-3456', 'bank_name' => 'Local Library', 'sort_order' => 5],
            ['id' => 6, 'category' => 'Community', 'name' => 'Community Center', 'slug' => 'community-center', 'content' => 'The Community Center brings our neighbors together through classes, activities, and programs that strengthen our community. Your donations help create spaces where everyone belongs.', 'image' => 'community-center.png', 'account_number' => '6789-0123-4567', 'bank_name' => 'Community Center', 'sort_order' => 6],
            ['id' => 7, 'category' => 'Environment', 'name' => 'EcoFun', 'slug' => 'ecofun', 'content' => 'EcoFun teaches us how to protect our planet for future generations. Join the movement by donating gardening supplies, reusable items, educational materials, or supporting local environmental projects.', 'image' => 'ecofun.png', 'account_number' => '7890-1234-5678', 'bank_name' => 'EcoFun', 'sort_order' => 7],
        ];

        foreach ($npos as $npo) {
            if (!DB::table('npos')->where('slug', $npo['slug'])->exists()) {
                DB::table('npos')->insert($npo + [
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down()
    {
        DB::table('npos')->whereIn('slug', [
            'animal-shelter', 'senior-care-home', 'childrens-orphanage',
            'food-bank', 'local-library', 'community-center', 'ecofun',
        ])->delete();
    }
};
