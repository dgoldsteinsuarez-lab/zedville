<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The Money Factory's currency exchange (Daniel, 2026-11-02): each exchange a
 * student makes, and the easy activities that send them there - "los alumnos
 * deben venir aquí para cambiar Zed en dólares o euros ... dales una razón por
 * la que tengan que hacer esto. Prepara varias actividades de este tipo, con
 * una razón diferente y una divisa diferente". An admin assigns them to
 * classes in Engagement > Exchange money (none is assigned here).
 */
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('currency_exchanges')) {
            Schema::create('currency_exchanges', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->index();
                $t->unsignedBigInteger('sid')->nullable();
                $t->char('currency', 3);
                $t->decimal('zeds', 10, 2);
                $t->decimal('commission', 10, 2);
                $t->decimal('rate', 14, 6);
                $t->decimal('foreign_amount', 14, 2);
                $t->unsignedBigInteger('transaction_id')->nullable();
                $t->timestamps();
            });
        }
        foreach (self::activities() as [$title, $currency, $zeds, $reason]) {
            DB::table('easy_activities')->insertOrIgnore([
                'type' => 'currency_exchange', 'title' => $title, 'min_amount' => $zeds,
                'content' => json_encode(['currency' => $currency, 'reason' => $reason]),
                'sid' => null, 'created_by' => null, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    /** [title, currency, Zeds to exchange, why] - each a different reason and currency. */
    public static function activities(): array
    {
        return [
            ['Pocket money for New York', 'USD', 100, 'Your class is going on a school trip to New York. You need US dollars for the subway and a snack.'],
            ['A weekend in Paris', 'EUR', 150, 'Your family is spending a weekend in Paris. You want euros for a museum ticket and a crêpe.'],
            ['Football tournament in London', 'GBP', 120, 'Your football team is playing a tournament in London. You need pounds for the bus and lunch.'],
            ['Visiting a pen friend in Tokyo', 'JPY', 80, 'You are visiting your pen friend in Tokyo. You need yen for the train and a bowl of ramen.'],
            ['Ski day in the Swiss Alps', 'CHF', 100, 'Your class is going skiing in Switzerland for a day. You need Swiss francs for the ski pass.'],
            ['Summer camp in Canada', 'CAD', 90, 'You are going to a summer camp in Canada. You need Canadian dollars for the camp shop.'],
        ];
    }

    public function down()
    {
        $titles = array_column(self::activities(), 0);
        $ids = DB::table('easy_activities')->whereIn('title', $titles)->pluck('id');
        DB::table('mba_position')->whereIn('activity_key', $ids->map(fn ($id) => 'easy_' . $id))->delete();
        DB::table('calendar_events')->whereIn('title', array_map(fn ($t) => 'easy-' . $t, $titles))->delete();
        DB::table('easy_activities')->whereIn('id', $ids)->delete();
        Schema::dropIfExists('currency_exchanges');
    }
};
