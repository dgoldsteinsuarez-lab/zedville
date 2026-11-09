<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The plan of the month (Daniel, 2026-10-31: "quiero que los alumnos elijan
 * cada mes, de cuatro opciones que van a hacer durante este mes y darles ya
 * el coste de esta actividad, puede ser recurrente, como ir al cine cada fin
 * de semana ... Saldría después de la pregunta del día y solo una vez al
 * mes, en un recuadro grande, con la explicación y una imagen").
 *
 * wants_plans: the catalog (an admin can change it); cost is per time,
 * times_per_month 1 (once), 2 (every other Saturday) or 4 (every Saturday).
 * wants_plan_choices: what each student chose for a month.
 * wants_plan_charges: the payments of that choice, each on its day.
 */
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('wants_plans')) {
            Schema::create('wants_plans', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->text('description');
                $t->json('translations')->nullable();
                $t->decimal('cost', 10, 2);
                $t->unsignedTinyInteger('times_per_month')->default(1);
                $t->string('image')->nullable();
                $t->boolean('is_active')->default(true);
                $t->unsignedInteger('sort_order')->default(0);
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('wants_plan_choices')) {
            Schema::create('wants_plan_choices', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('wants_plan_id');
                $t->char('month', 7);
                $t->json('offered')->nullable();
                $t->decimal('total', 10, 2)->default(0);
                $t->timestamps();
                $t->unique(['user_id', 'month']);
            });
        }
        if (!Schema::hasTable('wants_plan_charges')) {
            Schema::create('wants_plan_charges', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('choice_id');
                $t->unsignedBigInteger('user_id');
                $t->date('due_date');
                $t->decimal('amount', 10, 2);
                $t->string('status', 10)->default('pending'); // pending | charged | waiting (skipped until 2026-11-02)
                $t->unsignedBigInteger('transaction_id')->nullable();
                $t->timestamps();
                $t->index(['status', 'due_date']);
            });
        }

        if (DB::table('wants_plans')->exists()) {
            return;
        }
        foreach (self::plans() as $i => [$image, $cost, $times, $en, $es, $fr, $de]) {
            DB::table('wants_plans')->insert([
                'name' => $en[0], 'description' => $en[1], 'cost' => $cost, 'times_per_month' => $times,
                'image' => 'asset/front/images/wants-plans/' . $image . '.svg', 'is_active' => true, 'sort_order' => $i + 1,
                'translations' => json_encode([
                    'es' => ['name' => $es[0], 'description' => $es[1]],
                    'fr' => ['name' => $fr[0], 'description' => $fr[1]],
                    'de' => ['name' => $de[0], 'description' => $de[1]],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    /** [image, cost per time, times a month, [name, description] in en, es, fr, de] - real prices, broken down. */
    private static function plans(): array
    {
        return [
            ['cinema', 20, 4,
                ['Popcorn Standing Order', 'The cinema every weekend: a film ticket, a big popcorn and a soda. Every Saturday the same amount goes out, just like a standing order.'],
                ['Palomitas domiciliadas', 'Cine todos los fines de semana: entrada, palomitas grandes y un refresco. Cada sábado sale la misma cantidad, como un recibo domiciliado.'],
                ['Prélèvement pop-corn', 'Le cinéma chaque week-end : une place, un grand pop-corn et un soda. Chaque samedi, le même montant part, comme un prélèvement automatique.'],
                ['Popcorn-Dauerauftrag', 'Jedes Wochenende ins Kino: Ticket, großes Popcorn und eine Limo. Jeden Samstag geht derselbe Betrag ab, wie bei einem Dauerauftrag.']],
            ['concert', 220, 1,
                ['High-Yield Concert', 'Your favourite band live at the stadium: ticket Ƶ 150, the tour T-shirt Ƶ 35 and the train there and back Ƶ 35. A night you will remember for years.'],
                ['Concierto de alto rendimiento', 'Tu grupo favorito en directo en el estadio: entrada Ƶ 150, la camiseta de la gira Ƶ 35 y el tren de ida y vuelta Ƶ 35. Una noche que recordarás durante años.'],
                ['Concert à haut rendement', 'Ton groupe préféré en concert au stade : billet Ƶ 150, le t-shirt de la tournée Ƶ 35 et le train aller-retour Ƶ 35. Une soirée dont tu te souviendras pendant des années.'],
                ['Konzert mit Höchstrendite', 'Deine Lieblingsband live im Stadion: Ticket Ƶ 150, das Tour-T-Shirt Ƶ 35 und die Zugfahrt hin und zurück Ƶ 35. Ein Abend, an den du dich jahrelang erinnerst.']],
            ['trip', 500, 1,
                ['Compound Interest Getaway', 'A weekend in another city: train Ƶ 120, two nights in a hotel Ƶ 220, meals Ƶ 120 and a museum Ƶ 40. Expensive, but the memories grow with interest.'],
                ['Escapada a interés compuesto', 'Un fin de semana en otra ciudad: tren Ƶ 120, dos noches de hotel Ƶ 220, comidas Ƶ 120 y un museo Ƶ 40. Caro, pero los recuerdos crecen con intereses.'],
                ['Escapade à intérêts composés', "Un week-end dans une autre ville : train Ƶ 120, deux nuits d'hôtel Ƶ 220, repas Ƶ 120 et un musée Ƶ 40. C'est cher, mais les souvenirs rapportent des intérêts."],
                ['Zinseszins-Kurztrip', 'Ein Wochenende in einer anderen Stadt: Zug Ƶ 120, zwei Nächte im Hotel Ƶ 220, Essen Ƶ 120 und ein Museum Ƶ 40. Teuer, aber die Erinnerungen wachsen mit Zinsen.']],
            ['massage', 60, 2,
                ['Anti-Stress Bond', 'Exams, homework, tests... Two relaxing massage sessions this month to take the school stress off your shoulders.'],
                ['Bono antiestrés', 'Exámenes, deberes, controles... Dos sesiones de masaje relajante este mes para quitarte de encima el estrés del colegio.'],
                ['Obligation anti-stress', "Examens, devoirs, contrôles... Deux séances de massage relaxant ce mois-ci pour enlever le stress de l'école de tes épaules."],
                ['Anti-Stress-Anleihe', 'Prüfungen, Hausaufgaben, Tests ... Zwei entspannende Massagen in diesem Monat, damit der Schulstress von deinen Schultern fällt.']],
            ['cooking', 40, 4,
                ['Slow-Cooked Investment', 'A cooking class every week with a real chef: pasta today, curry next week. A skill that saves you money for the rest of your life.'],
                ['Inversión a fuego lento', 'Una clase de cocina cada semana con un chef de verdad: hoy pasta, la semana que viene curry. Una habilidad que te ahorrará dinero toda la vida.'],
                ['Investissement à feu doux', "Un cours de cuisine chaque semaine avec un vrai chef : des pâtes aujourd'hui, un curry la semaine prochaine. Un savoir-faire qui te fera économiser toute ta vie."],
                ['Geldanlage auf kleiner Flamme', 'Jede Woche ein Kochkurs mit einem echten Koch: heute Pasta, nächste Woche Curry. Eine Fähigkeit, die dir dein Leben lang Geld spart.']],
            ['growth', 190, 1,
                ['Growth Stock: You', 'An online course on habits, motivation and time management, with a workbook. Investing in yourself is the investment nobody can take away from you.'],
                ['Acción de crecimiento: tú', 'Un curso online sobre hábitos, motivación y organización del tiempo, con cuaderno de ejercicios. Invertir en ti es la inversión que nadie te puede quitar.'],
                ['Valeur de croissance : toi', "Un cours en ligne sur les habitudes, la motivation et la gestion du temps, avec un cahier d'exercices. Investir en toi, c'est l'investissement que personne ne peut te prendre."],
                ['Wachstumswert: du', 'Ein Online-Kurs über Gewohnheiten, Motivation und Zeitmanagement, mit Arbeitsheft. In dich selbst zu investieren ist die Investition, die dir niemand nehmen kann.']],
            ['speaking', 45, 4,
                ['Speech Dividends', 'A public speaking course, once a week: lose your fear of the stage and learn to present your ideas. It pays off in every presentation.'],
                ['Dividendos de la palabra', 'Un curso de oratoria, una vez por semana: pierde el miedo al escenario y aprende a presentar tus ideas. Se nota en cada exposición.'],
                ['Dividendes de la parole', 'Un cours de prise de parole en public, une fois par semaine : perds la peur de la scène et apprends à présenter tes idées. Ça rapporte à chaque exposé.'],
                ['Rede-Dividende', 'Ein Rhetorikkurs, einmal pro Woche: Verlier die Angst vor der Bühne und lern, deine Ideen zu präsentieren. Das zahlt sich bei jedem Referat aus.']],
            ['trainer', 55, 4,
                ['Muscle Mutual Fund', 'A personal trainer every week who makes a plan just for you. Small deposits of effort, big returns in energy.'],
                ['Fondo de inversión muscular', 'Un entrenador personal cada semana que te prepara un plan solo para ti. Pequeños depósitos de esfuerzo, grandes rendimientos de energía.'],
                ['Fonds commun de muscu', 'Un coach sportif chaque semaine qui te prépare un programme rien que pour toi. De petits dépôts d\'effort, de gros rendements en énergie.'],
                ['Muskel-Investmentfonds', 'Jede Woche ein Personal Trainer, der einen Plan nur für dich macht. Kleine Einzahlungen an Mühe, große Rendite an Energie.']],
            ['themepark', 110, 1,
                ['Stock Market Roller Coaster', 'A day at the theme park with friends: entrance Ƶ 65, lunch Ƶ 25 and the bus Ƶ 20. Ups and downs guaranteed, just like the stock market.'],
                ['Montaña rusa bursátil', 'Un día en el parque de atracciones con tus amigos: entrada Ƶ 65, comida Ƶ 25 y autobús Ƶ 20. Subidas y bajadas aseguradas, como en la bolsa.'],
                ['Montagnes russes boursières', "Une journée au parc d'attractions avec tes amis : entrée Ƶ 65, déjeuner Ƶ 25 et bus Ƶ 20. Des hauts et des bas garantis, comme en bourse."],
                ['Börsen-Achterbahn', 'Ein Tag im Freizeitpark mit Freunden: Eintritt Ƶ 65, Mittagessen Ƶ 25 und Bus Ƶ 20. Höhen und Tiefen garantiert, wie an der Börse.']],
            ['restaurant', 45, 2,
                ['Dinner, Tax Included', 'Twice this month, dinner at a nice restaurant with your friends: starter, main course and dessert. The price on the menu already includes the tax.'],
                ['Cena con IVA incluido', 'Dos veces este mes, cena en un buen restaurante con tus amigos: entrante, plato principal y postre. El precio de la carta ya incluye el IVA.'],
                ['Dîner TVA comprise', 'Deux fois ce mois-ci, un dîner dans un bon restaurant avec tes amis : entrée, plat et dessert. Le prix sur la carte comprend déjà la TVA.'],
                ['Abendessen inklusive MwSt.', 'Zweimal in diesem Monat ein Abendessen in einem schönen Restaurant mit deinen Freunden: Vorspeise, Hauptgang und Nachtisch. Der Preis auf der Karte enthält schon die Mehrwertsteuer.']],
            ['subscriptions', 39, 1,
                ['Subscription Snowball', 'Films, music and games: three subscriptions for Ƶ 13, Ƶ 11 and Ƶ 15. Each one looks small; together they roll like a snowball every month.'],
                ['Bola de nieve de suscripciones', 'Películas, música y videojuegos: tres suscripciones de Ƶ 13, Ƶ 11 y Ƶ 15. Cada una parece poco; juntas crecen como una bola de nieve cada mes.'],
                ["Boule de neige d'abonnements", 'Films, musique et jeux vidéo : trois abonnements à Ƶ 13, Ƶ 11 et Ƶ 15. Chacun semble petit ; ensemble, ils font boule de neige chaque mois.'],
                ['Abo-Schneeball', 'Filme, Musik und Spiele: drei Abos für Ƶ 13, Ƶ 11 und Ƶ 15. Jedes wirkt klein, zusammen rollen sie jeden Monat wie ein Schneeball.']],
            ['dance', 30, 4,
                ['Interest-Free Dance Classes', 'Hip-hop or salsa, once a week at a dance school. You pay every week, but the fun comes with zero interest.'],
                ['Clases de baile sin intereses', 'Hip-hop o salsa, una vez por semana en una escuela de baile. Pagas cada semana, pero la diversión llega sin intereses.'],
                ['Cours de danse sans intérêts', 'Hip-hop ou salsa, une fois par semaine dans une école de danse. Tu paies chaque semaine, mais le plaisir arrive sans intérêts.'],
                ['Tanzkurs ohne Zinsen', 'Hip-Hop oder Salsa, einmal pro Woche in einer Tanzschule. Du zahlst jede Woche, aber der Spaß kommt ganz ohne Zinsen.']],
        ];
    }

    public function down()
    {
        Schema::dropIfExists('wants_plan_charges');
        Schema::dropIfExists('wants_plan_choices');
        Schema::dropIfExists('wants_plans');
    }
};
