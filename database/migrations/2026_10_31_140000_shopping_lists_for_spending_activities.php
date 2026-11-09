<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-31: "el alumno al final va a cliquear sin mirar hasta
 * llegar a la cantidad mínima. Me gustaría que la cantidad sea menos y más
 * selectiva, por ejemplo, en supermercado, buscar 3 de proteínas, carbos,
 * etc. en una tienda, buscar unos pocos artículos específicos."
 *
 * A Spending Activity becomes a short shopping list with a budget: each line
 * is a number of different products from a food group (Supermarket), from a
 * set of named products, or from one shop. Orders remember the activity and
 * the products, so a list bought in two shops adds up.
 */
return new class extends Migration {
    /** The supermarket's food groups, by product name (every diet section). */
    private const FOOD_GROUPS = [
        'protein' => ['Hamburger', 'Chicken', 'Beef', 'Fish', 'Seafood', 'Eggs', 'Fish Burger', 'Tofu', 'Lentils', 'Chickpeas',
            'Mixed Nuts', 'Plant-based Hamburger Patties', 'Vegetarian Hamburger'],
        'carbs' => ['Rice', 'Pasta', 'Pasta (eggfree)', 'Bread', 'Bread and Toasts', 'Bread and Toasts (vegan)', 'Cereals', 'Potatoes', 'Potato'],
        'vegetables' => ['Frozen Vegetables', 'Seasonal Vegetables', 'Mixed Vegetables (frozen)', 'Spinach', 'Lettuce', 'Tomatoes', 'Tomato'],
        'fruit' => ['Mixed National Fruits', 'Mixed International Fruits'],
        'dairy' => ['Yogurt', 'Cheese', 'Milk', 'Plant-based Yogurt', 'Plant-based Cheese', 'Plant-based Milk'],
        'fats' => ['Cooking Oil'],
    ];

    /** Comfort Zone had no products, so Study Space Setup and Room Makeover couldn't be done. */
    private const COMFORT_ZONE = [
        ['Cushion', 15, 'Wants'], ['Wall Poster', 12, 'Wants'], ['Indoor Plant', 20, 'Wants'], ['Bedside Lamp', 25, 'Wants'],
        ['Storage Boxes', 18, 'Needs-Variable'], ['Throw Blanket', 30, 'Wants'], ['Curtains', 35, 'Wants'], ['Rug', 60, 'Wants'],
        ['Bean Bag', 70, 'Wants'], ['Desk Chair', 90, 'Needs-Variable'],
    ];

    /** number => [budget, list] - each list fits its budget only with some thought. */
    private function lists(): array
    {
        return [
            1 => [55, [['qty' => 3, 'group' => 'protein'], ['qty' => 2, 'group' => 'carbs'], ['qty' => 2, 'group' => 'fruit'], ['qty' => 1, 'group' => 'dairy']]],
            2 => [500, [
                ['qty' => 1, 'products' => ['Take it Easy Phone', 'Never give Up Phone', 'Only me Phone', 'Tablet Premium', 'Laptop Standard', 'Laptop Elite']],
                ['qty' => 1, 'products' => ['Bluetooth Headphones']],
                ['qty' => 1, 'products' => ['Phone Charger', 'Laptop Charger']],
            ]],
            3 => [35, [['qty' => 2, 'group' => 'protein'], ['qty' => 2, 'group' => 'carbs'], ['qty' => 2, 'group' => 'vegetables']]],
            4 => [130, [
                ['qty' => 1, 'products' => ['Writing Tools']], ['qty' => 1, 'products' => ['Paper Products']],
                ['qty' => 1, 'products' => ['Organization Supplies']], ['qty' => 1, 'products' => ['Desk Chair']],
            ]],
            5 => [350, [
                ['qty' => 2, 'products' => ['Ukelele', 'Violin Starter', 'Keyboard Beginner', 'Flute Beginner', 'Trumpet Beginner', 'Drum Kit Beginner']],
                ['qty' => 1, 'products' => ['Acoustic Guitar', 'Acoustic Guitar Pro', 'Electric Guitar', 'Electric Guitar Pro']],
            ]],
            6 => [100, [
                ['qty' => 1, 'products' => ['Basic Soccer Shoes', 'Professional Soccer Shoes']],
                ['qty' => 1, 'products' => ['Practice Football', 'Official Football']],
            ]],
            7 => [110, [
                ['qty' => 1, 'products' => ['Cushion', 'Throw Blanket', 'Bean Bag']],
                ['qty' => 1, 'products' => ['Indoor Plant', 'Wall Poster', 'Curtains']],
                ['qty' => 1, 'products' => ['Bedside Lamp']],
            ]],
            8 => [80, [
                ['qty' => 1, 'products' => ['Shirt']], ['qty' => 1, 'products' => ['Trousers']], ['qty' => 1, 'products' => ['Shoes']],
                ['qty' => 1, 'store' => 'BeU - BeLuxury'],
            ]],
            9 => [550, [['qty' => 1, 'products' => ['Laptop Standard']], ['qty' => 1, 'products' => ['Laptop Charger']], ['qty' => 1, 'products' => ['Printer']]]],
            10 => [380, [
                ['qty' => 1, 'products' => ['Take it Easy Phone', 'Never give Up Phone', 'Only me Phone']],
                ['qty' => 1, 'products' => ['Phone Charger']], ['qty' => 1, 'products' => ['Bluetooth Headphones']],
            ]],
        ];
    }

    public function up()
    {
        if (!Schema::hasColumn('supermarkets', 'food_group')) {
            Schema::table('supermarkets', fn (Blueprint $t) => $t->string('food_group', 20)->nullable()->after('category'));
        }
        if (!Schema::hasColumn('activities', 'shopping_list')) {
            Schema::table('activities', function (Blueprint $t) {
                $t->json('shopping_list')->nullable()->after('max_items');
                $t->decimal('budget', 10, 2)->nullable()->after('shopping_list');
                $t->json('translations')->nullable()->after('budget');
            });
        }
        if (!Schema::hasColumn('orders', 'activity_id')) {
            Schema::table('orders', fn (Blueprint $t) => $t->unsignedBigInteger('activity_id')->nullable()->index()->after('status'));
        }
        if (!Schema::hasColumn('order_items', 'product_id')) {
            Schema::table('order_items', function (Blueprint $t) {
                $t->unsignedBigInteger('product_id')->nullable()->after('order_id');
                $t->string('catalog', 20)->nullable()->after('product_id');
            });
        }

        foreach (self::FOOD_GROUPS as $group => $names) {
            DB::table('supermarkets')->whereIn('product_name', $names)->update(['food_group' => $group]);
        }

        if (!DB::table('products')->where('type', 'Comfort Zone')->exists()) {
            foreach (self::COMFORT_ZONE as [$name, $price, $goods]) {
                DB::table('products')->insert(['product_name' => $name, 'type' => 'Comfort Zone', 'category' => 'Home & Comfort',
                    'goods_type' => $goods, 'price' => $price, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        foreach ($this->lists() as $number => [$budget, $list]) {
            $shops = collect($list)->map(fn ($line) => isset($line['group']) ? 'Supermarket'
                : ($line['store'] ?? DB::table('products')->whereIn('product_name', $line['products'])->value('type')))->unique()->implode(',');
            DB::table('activities')->where('type', 'high')->where('number', $number)
                ->update(['shopping_list' => json_encode($list), 'budget' => $budget, 'shop' => $shops]);
        }
        // a sport the catalog can really equip
        DB::table('activities')->where('type', 'high')->where('number', 6)->update(['mission' =>
            'You want to start playing football with your friends this season. Get the two things you can\'t play without.']);
    }

    public function down()
    {
        Schema::table('supermarkets', fn (Blueprint $t) => $t->dropColumn('food_group'));
        Schema::table('activities', fn (Blueprint $t) => $t->dropColumn(['shopping_list', 'budget', 'translations']));
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn('activity_id'));
        Schema::table('order_items', fn (Blueprint $t) => $t->dropColumn(['product_id', 'catalog']));
    }
};
