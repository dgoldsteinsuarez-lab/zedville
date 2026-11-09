<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Spending 4 (Daniel, 2026-10-31: "así lo llamaremos, ya que pueden elegir de
 * cuatro"): descriptive names with a short description instead of the
 * finance puns ("No pongas esos nombres artificiales"), forty options so a
 * class sees different ones every month ("si son unos 12 meses ... debería
 * haber 40 opciones"), cooking and dance classes of many kinds, weekends in
 * cities named after economists ("Smith City, de Adam Smith"), each with
 * something about money and something in nature. A student may also save
 * the money, or do three low-budget plans instead of one.
 *
 * Ten options in each of four price levels; sort_order mixes them so the
 * months alternate (cooking, dance, cooking...).
 */
return new class extends Migration {
    public function up()
    {
        DB::statement('ALTER TABLE wants_plan_choices MODIFY wants_plan_id BIGINT UNSIGNED NULL');
        if (!Schema::hasColumn('wants_plan_choices', 'kind')) {
            Schema::table('wants_plan_choices', function (Blueprint $t) {
                $t->string('kind', 10)->default('plan')->after('wants_plan_id'); // plan | save | bundle
                $t->json('plan_ids')->nullable()->after('kind');
            });
        }
        if (!Schema::hasColumn('wants_plan_charges', 'wants_plan_id')) {
            Schema::table('wants_plan_charges', fn (Blueprint $t) => $t->unsignedBigInteger('wants_plan_id')->nullable()->after('choice_id'));
        }

        // the first twelve are replaced (switched off if someone already chose one)
        DB::table('wants_plan_choices')->exists()
            ? DB::table('wants_plans')->update(['is_active' => false])
            : DB::table('wants_plans')->delete();

        foreach (self::options() as [$sort, $image, $cost, $times, $en, $es, $fr, $de]) {
            DB::table('wants_plans')->insert([
                'name' => $en[0], 'description' => $en[1], 'cost' => $cost, 'times_per_month' => $times,
                'image' => 'asset/front/images/wants-plans/' . $image . '.svg', 'is_active' => true, 'sort_order' => $sort,
                'translations' => json_encode([
                    'es' => ['name' => $es[0], 'description' => $es[1]],
                    'fr' => ['name' => $fr[0], 'description' => $fr[1]],
                    'de' => ['name' => $de[0], 'description' => $de[1]],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    /** [order, picture, cost each time, times a month (1, 2 or 4), [name, description] in en, es, fr, de] */
    private static function options(): array
    {
        $train = ['Train, 2 nights in a hotel and meals.', 'Tren, 2 noches de hotel y comidas.', "Train, 2 nuits d'hôtel et repas.", 'Zug, 2 Nächte im Hotel und Essen.'];
        $city = fn ($sort, $image, $cost, $name, array $texts, ?array $travel = null) => [$sort, $image, $cost, 1,
            ["Weekend in $name", $texts[0] . ' ' . ($travel ?? $train)[0]],
            ["Fin de semana en $name", $texts[1] . ' ' . ($travel ?? $train)[1]],
            ["Week-end à $name", $texts[2] . ' ' . ($travel ?? $train)[2]],
            ["Wochenende in $name", $texts[3] . ' ' . ($travel ?? $train)[3]]];
        $cooking = fn ($sort, $image, $en, $es, $fr, $de, array $dishes) => [$sort, $image, 30, 4,
            ["$en cooking classes", 'Every Saturday with a chef: ' . $dishes[0] . '.'],
            ["Clases de cocina $es", 'Cada sábado con un chef: ' . $dishes[1] . '.'],
            ["Cours de cuisine $fr", 'Chaque samedi avec un chef : ' . $dishes[2] . '.'],
            ["$de Kochkurs", 'Jeden Samstag mit einem Koch: ' . $dishes[3] . '.']];
        $dance = fn ($sort, $image, array $names, array $texts) => [$sort, $image, 25, 4,
            [$names[0], $texts[0]], [$names[1], $texts[1]], [$names[2], $texts[2]], [$names[3], $texts[3]]];

        return [
            // ── up to Ƶ 72 a month ──
            [1, 'cinema', 18, 4, ['Cinema and popcorn', 'A film every Saturday, with popcorn and a soda.'],
                ['Cine y palomitas', 'Una película cada sábado, con palomitas y refresco.'],
                ['Cinéma et pop-corn', 'Un film chaque samedi, avec pop-corn et soda.'],
                ['Kino und Popcorn', 'Jeden Samstag ein Film, mit Popcorn und Limo.']],
            [2, 'bowling', 24, 2, ['Bowling with friends', 'Two bowling evenings: lane, shoes and a drink.'],
                ['Bolos con amigos', 'Dos tardes de bolos: pista, zapatos y una bebida.'],
                ['Bowling entre amis', 'Deux soirées de bowling : la piste, les chaussures et une boisson.'],
                ['Bowling mit Freunden', 'Zwei Bowlingabende: Bahn, Schuhe und ein Getränk.']],
            [3, 'subscriptions', 39, 1, ['Series, music and games', 'Three subscriptions for a month: series Ƶ 13, music Ƶ 11 and games Ƶ 15.'],
                ['Series, música y videojuegos', 'Tres suscripciones durante un mes: series Ƶ 13, música Ƶ 11 y videojuegos Ƶ 15.'],
                ['Séries, musique et jeux vidéo', 'Trois abonnements pour un mois : séries Ƶ 13, musique Ƶ 11 et jeux vidéo Ƶ 15.'],
                ['Serien, Musik und Spiele', 'Drei Abos für einen Monat: Serien Ƶ 13, Musik Ƶ 11 und Spiele Ƶ 15.']],
            [4, 'escaperoom', 28, 1, ['Escape room', 'One hour to get out of a themed room with four friends.'],
                ['Escape room', 'Una hora para salir de una sala temática con cuatro amigos.'],
                ['Escape game', "Une heure pour sortir d'une salle à thème avec quatre amis."],
                ['Escape Room', 'Eine Stunde, um mit vier Freunden aus einem Themenraum zu entkommen.']],
            [5, 'football', 65, 1, ['Football match at the stadium', 'A seat at a league match, a scarf and a snack at half-time.'],
                ['Partido de fútbol en el estadio', 'Una entrada para un partido de liga, una bufanda y algo de comer en el descanso.'],
                ['Match de foot au stade', 'Une place pour un match de championnat, une écharpe et un en-cas à la mi-temps.'],
                ['Fußballspiel im Stadion', 'Ein Platz bei einem Ligaspiel, ein Schal und ein Snack in der Halbzeit.']],
            [6, 'iceskating', 18, 2, ['Ice skating', 'Two afternoons at the ice rink, skates and hot chocolate included.'],
                ['Patinaje sobre hielo', 'Dos tardes en la pista de hielo, con patines y chocolate caliente.'],
                ['Patinoire', 'Deux après-midi à la patinoire, patins et chocolat chaud compris.'],
                ['Eislaufen', 'Zwei Nachmittage auf der Eisbahn, mit Schlittschuhen und heißer Schokolade.']],
            [7, 'pizza', 22, 2, ['Pizza night with friends', 'Twice this month, pizza with friends at an Italian restaurant.'],
                ['Noche de pizza con amigos', 'Dos veces este mes, pizza con tus amigos en un restaurante italiano.'],
                ['Soirée pizza entre amis', 'Deux fois ce mois-ci, une pizza avec tes amis dans un restaurant italien.'],
                ['Pizzaabend mit Freunden', 'Zweimal in diesem Monat Pizza mit Freunden in einem italienischen Restaurant.']],
            [8, 'museum', 35, 1, ['Museums and science pass', 'A monthly pass: the science museum, the art gallery and the planetarium.'],
                ['Pase de museos y ciencia', 'Un pase mensual: el museo de la ciencia, la galería de arte y el planetario.'],
                ['Pass musées et sciences', "Un pass mensuel : le musée des sciences, la galerie d'art et le planétarium."],
                ['Museums- und Wissenschaftspass', 'Ein Monatspass: Wissenschaftsmuseum, Kunstgalerie und Planetarium.']],
            [9, 'gaming', 12, 4, ['Gaming café', 'Every Saturday afternoon, games with friends at a gaming café.'],
                ['Sala de videojuegos', 'Cada sábado por la tarde, partidas con tus amigos en una sala de videojuegos.'],
                ['Café gaming', 'Chaque samedi après-midi, des parties entre amis dans un café gaming.'],
                ['Gaming-Café', 'Jeden Samstagnachmittag mit Freunden spielen, im Gaming-Café.']],
            [10, 'karaoke', 20, 2, ['Karaoke night', 'Two nights of karaoke with friends: a private room and drinks.'],
                ['Noche de karaoke', 'Dos noches de karaoke con amigos: sala privada y bebidas.'],
                ['Soirée karaoké', 'Deux soirées karaoké entre amis : une salle privée et des boissons.'],
                ['Karaoke-Abend', 'Zwei Karaoke-Abende mit Freunden: eigener Raum und Getränke.']],

            // ── Ƶ 100-120: cooking and dance, every Saturday ──
            $cooking(11, 'cooking-china', 'Chinese', 'china', 'chinoise', 'Chinesischer',
                ['dumplings, noodles, stir-fry and fried rice', 'empanadillas chinas, fideos, salteados y arroz frito', 'raviolis, nouilles, sautés au wok et riz cantonais', 'Teigtaschen, Nudeln, Wok-Gerichte und gebratener Reis']),
            $dance(12, 'dance-salsa', ['Salsa classes', 'Clases de salsa', 'Cours de salsa', 'Salsakurs'], [
                'Every Saturday at a dance school: basic steps, turns and dancing in pairs.',
                'Cada sábado en una escuela de baile: pasos básicos, vueltas y baile en pareja.',
                'Chaque samedi dans une école de danse : pas de base, tours et danse à deux.',
                'Jeden Samstag in der Tanzschule: Grundschritte, Drehungen und Paartanz.']),
            $cooking(13, 'cooking-india', 'Indian', 'india', 'indienne', 'Indischer',
                ['curry, naan bread, dal and chai', 'curri, pan naan, dal y té chai', 'curry, pain naan, dal et chaï', 'Curry, Naan-Brot, Dal und Chai']),
            $dance(14, 'dance-hiphop', ['Hip hop classes', 'Clases de hip hop', 'Cours de hip-hop', 'Hip-Hop-Kurs'], [
                'Every Saturday: grooves, choreographies and freestyle.',
                'Cada sábado: ritmo, coreografías y freestyle.',
                'Chaque samedi : grooves, chorégraphies et freestyle.',
                'Jeden Samstag: Grooves, Choreografien und Freestyle.']),
            $cooking(15, 'cooking-peru', 'Peruvian', 'peruana', 'péruvienne', 'Peruanischer',
                ['ceviche, lomo saltado, causa and alfajores', 'ceviche, lomo saltado, causa y alfajores', 'ceviche, lomo saltado, causa et alfajores', 'Ceviche, Lomo Saltado, Causa und Alfajores']),
            $dance(16, 'dance-breakdance', ['Breakdance classes', 'Clases de breakdance', 'Cours de breakdance', 'Breakdance-Kurs'], [
                'Every Saturday: toprock, footwork and your first freezes.',
                'Cada sábado: toprock, footwork y tus primeros freezes.',
                'Chaque samedi : toprock, footwork et tes premiers freezes.',
                'Jeden Samstag: Toprock, Footwork und deine ersten Freezes.']),
            $cooking(17, 'cooking-mexico', 'Mexican', 'mexicana', 'mexicaine', 'Mexikanischer',
                ['tacos, guacamole, enchiladas and churros', 'tacos, guacamole, enchiladas y churros', 'tacos, guacamole, enchiladas et churros', 'Tacos, Guacamole, Enchiladas und Churros']),
            $dance(18, 'dance-kpop', ['K-pop dance classes', 'Clases de baile K-pop', 'Cours de danse K-pop', 'K-Pop-Tanzkurs'], [
                'Every Saturday: learn the choreographies of the biggest K-pop hits.',
                'Cada sábado: aprende las coreografías de los grandes éxitos del K-pop.',
                'Chaque samedi : apprends les chorégraphies des grands tubes de K-pop.',
                'Jeden Samstag: Lern die Choreografien der größten K-Pop-Hits.']),
            $cooking(19, 'cooking-spain', 'Spanish', 'española', 'espagnole', 'Spanischer',
                ['paella, Spanish omelette, gazpacho and croquettes', 'paella, tortilla de patatas, gazpacho y croquetas', 'paella, tortilla, gaspacho et croquetas', 'Paella, Tortilla, Gazpacho und Croquetas']),
            $dance(20, 'dance-afrobeats', ['Afrobeats dance classes', 'Clases de baile afrobeats', 'Cours de danse afrobeats', 'Afrobeats-Tanzkurs'], [
                'Every Saturday: the moves of the African dance music that fills dance floors.',
                'Cada sábado: los pasos de la música africana que llena las pistas de baile.',
                'Chaque samedi : les pas de la musique africaine qui remplit les pistes de danse.',
                'Jeden Samstag: die Moves der afrikanischen Tanzmusik, die die Tanzflächen füllt.']),

            // ── Ƶ 130-220 ──
            [21, 'massage', 65, 2, ['Massage sessions', 'Two relaxing massages to take away the school stress.'],
                ['Sesiones de masaje', 'Dos masajes relajantes para quitarte el estrés del colegio.'],
                ['Séances de massage', "Deux massages relaxants pour évacuer le stress de l'école."],
                ['Massagen', 'Zwei entspannende Massagen gegen den Schulstress.']],
            [22, 'band', 170, 1, ['Concert by a band', 'A band live on stage: ticket Ƶ 120, a poster Ƶ 20 and transport Ƶ 30.'],
                ['Concierto de un grupo musical', 'Un grupo en directo: entrada Ƶ 120, un póster Ƶ 20 y el transporte Ƶ 30.'],
                ["Concert d'un groupe", 'Un groupe en concert : billet Ƶ 120, une affiche Ƶ 20 et le transport Ƶ 30.'],
                ['Konzert einer Band', 'Eine Band live: Ticket Ƶ 120, ein Poster Ƶ 20 und die Fahrt Ƶ 30.']],
            [23, 'guitar', 35, 4, ['Guitar lessons', 'A lesson with a teacher every Saturday, guitar on loan.'],
                ['Clases de guitarra', 'Una clase con un profesor cada sábado, con guitarra prestada.'],
                ['Cours de guitare', 'Un cours avec un professeur chaque samedi, guitare prêtée.'],
                ['Gitarrenunterricht', 'Jeden Samstag eine Stunde mit einem Lehrer, die Gitarre wird geliehen.']],
            [24, 'speaking', 45, 4, ['Public speaking course', 'Once a week: lose your fear of speaking and learn to present your ideas.'],
                ['Curso de oratoria', 'Una vez por semana: pierde el miedo a hablar en público y aprende a presentar tus ideas.'],
                ['Cours de prise de parole', 'Une fois par semaine : perds la peur de parler en public et apprends à présenter tes idées.'],
                ['Rhetorikkurs', 'Einmal pro Woche: Verlier die Angst vorm Reden und lern, deine Ideen zu präsentieren.']],
            [25, 'surf', 45, 4, ['Surf lessons', 'Every Saturday at the beach, board and wetsuit included.'],
                ['Clases de surf', 'Cada sábado en la playa, con tabla y neopreno.'],
                ['Cours de surf', 'Chaque samedi à la plage, planche et combinaison comprises.'],
                ['Surfkurs', 'Jeden Samstag am Strand, Brett und Neoprenanzug inklusive.']],
            [26, 'concert', 220, 1, ['Concert by a pop singer', 'A pop star at the stadium: ticket Ƶ 150, T-shirt Ƶ 35 and the train Ƶ 35.'],
                ['Concierto de cantante pop', 'Una estrella del pop en el estadio: entrada Ƶ 150, camiseta Ƶ 35 y el tren Ƶ 35.'],
                ["Concert d'une star de la pop", 'Une star de la pop au stade : billet Ƶ 150, t-shirt Ƶ 35 et le train Ƶ 35.'],
                ['Konzert eines Popstars', 'Ein Popstar im Stadion: Ticket Ƶ 150, T-Shirt Ƶ 35 und der Zug Ƶ 35.']],
            [27, 'growth', 190, 1, ['Personal development course', 'An online course on habits, motivation and time management, with a workbook.'],
                ['Curso de desarrollo personal', 'Un curso online sobre hábitos, motivación y organización del tiempo, con cuaderno de ejercicios.'],
                ['Cours de développement personnel', "Un cours en ligne sur les habitudes, la motivation et la gestion du temps, avec un cahier d'exercices."],
                ['Kurs für persönliche Entwicklung', 'Ein Online-Kurs über Gewohnheiten, Motivation und Zeitmanagement, mit Arbeitsheft.']],
            [28, 'horse', 40, 4, ['Horse riding lessons', 'Every Saturday at a riding school, helmet and boots included.'],
                ['Clases de equitación', 'Cada sábado en una escuela de equitación, con casco y botas.'],
                ["Cours d'équitation", 'Chaque samedi dans un centre équestre, casque et bottes compris.'],
                ['Reitstunden', 'Jeden Samstag in einer Reitschule, Helm und Stiefel inklusive.']],
            [29, 'trainer', 50, 4, ['Personal trainer', 'Every Saturday a trainer makes a plan just for you.'],
                ['Entrenador personal', 'Cada sábado, un entrenador prepara un plan solo para ti.'],
                ['Coach sportif', 'Chaque samedi, un coach prépare un programme rien que pour toi.'],
                ['Personal Trainer', 'Jeden Samstag macht ein Trainer einen Plan nur für dich.']],
            [30, 'coding', 210, 1, ['Coding course: build an app', 'A weekend course where you build your first mobile app.'],
                ['Curso de programación: crea una app', 'Un curso de fin de semana en el que creas tu primera app para el móvil.'],
                ['Cours de programmation : crée une appli', "Un stage d'un week-end où tu crées ta première appli mobile."],
                ['Programmierkurs: eine App bauen', 'Ein Wochenendkurs, in dem du deine erste Handy-App baust.']],

            // ── Ƶ 320 and more: weekends away ──
            $city(31, 'city-smith', 480, 'Smith City', [
                'The city of Adam Smith: watch traders bargain in the old Market Hall, then relax on a beautiful white-sand beach.',
                'La ciudad de Adam Smith: mira cómo regatean los comerciantes en el antiguo mercado y relájate en una preciosa playa de arena blanca.',
                "La ville d'Adam Smith : regarde les marchands négocier dans l'ancienne halle, puis détends-toi sur une magnifique plage de sable blanc.",
                'Die Stadt von Adam Smith: Sieh zu, wie Händler in der alten Markthalle feilschen, und entspann dich an einem traumhaften weißen Sandstrand.']),
            [32, 'festival', 350, 1, ['Music festival weekend', 'Three days of live music: pass Ƶ 220, campsite Ƶ 60 and food Ƶ 70.'],
                ['Fin de semana de festival de música', 'Tres días de música en directo: abono Ƶ 220, camping Ƶ 60 y comida Ƶ 70.'],
                ['Week-end de festival de musique', 'Trois jours de musique live : pass Ƶ 220, camping Ƶ 60 et repas Ƶ 70.'],
                ['Musikfestival-Wochenende', 'Drei Tage Livemusik: Festivalpass Ƶ 220, Campingplatz Ƶ 60 und Essen Ƶ 70.']],
            $city(33, 'city-keynes', 450, 'Keynes Bay', [
                'Visit the Central Bank museum and its gold vault, then take a boat to the sea caves.',
                'Visita el museo del Banco Central y su cámara del oro, y después ve en barco a las cuevas marinas.',
                'Visite le musée de la Banque centrale et sa salle des coffres, puis pars en bateau voir les grottes marines.',
                'Besuch das Museum der Zentralbank mit seinem Goldtresor und fahr dann mit dem Boot zu den Meereshöhlen.']),
            $city(34, 'city-hayek', 520, 'Hayek Peaks', [
                'See the old stock exchange, where prices changed on a giant board, and take the cable car up to the snowy peaks.',
                'Visita la antigua bolsa, donde los precios cambiaban en un panel gigante, y sube en teleférico a las cumbres nevadas.',
                "Visite l'ancienne Bourse, où les prix changeaient sur un tableau géant, et monte en téléphérique jusqu'aux sommets enneigés.",
                'Besuch die alte Börse, wo sich die Kurse auf einer riesigen Tafel änderten, und fahr mit der Seilbahn zu den verschneiten Gipfeln.']),
            [35, 'sailing', 80, 4, ['Sailing course', 'Four Saturdays at sea learning to sail a small boat, life jacket included.'],
                ['Curso de vela', 'Cuatro sábados en el mar aprendiendo a navegar en un velero pequeño, con chaleco salvavidas.'],
                ['Stage de voile', 'Quatre samedis en mer pour apprendre à barrer un petit voilier, gilet de sauvetage compris.'],
                ['Segelkurs', 'Vier Samstage auf dem Meer: Segeln lernen auf einem kleinen Boot, Rettungsweste inklusive.']],
            $city(36, 'city-ricardo', 420, 'Ricardo Valley', [
                'See the river port where two countries trade wine and cloth, then cycle through the vineyards and swim in the river.',
                'Mira el puerto fluvial donde dos países intercambian vino y telas, y luego recorre los viñedos en bici y báñate en el río.',
                'Découvre le port fluvial où deux pays échangent du vin et du drap, puis traverse les vignes à vélo et baigne-toi dans la rivière.',
                'Sieh den Flusshafen, wo zwei Länder Wein und Stoff tauschen, dann radle durch die Weinberge und bade im Fluss.']),
            $city(37, 'city-friedman', 400, 'Friedman Lake', [
                'See how coins are made at the Mint, then go kayaking on a crystal-clear mountain lake.',
                'Mira cómo se fabrican las monedas en la Casa de la Moneda y luego sal en kayak por un lago de montaña de aguas cristalinas.',
                "Découvre comment on fabrique les pièces à l'Hôtel de la Monnaie, puis fais du kayak sur un lac de montagne aux eaux cristallines.",
                'Sieh in der Münzprägeanstalt, wie Münzen gemacht werden, und paddle dann im Kajak über einen glasklaren Bergsee.']),
            [38, 'themepark', 340, 1, ['Theme park weekend', 'Two days at a big theme park with a night in its hotel: tickets, rides and meals.'],
                ['Fin de semana en un parque temático', 'Dos días en un gran parque temático con una noche en su hotel: entradas, atracciones y comidas.'],
                ["Week-end au parc d'attractions", "Deux jours dans un grand parc d'attractions avec une nuit à son hôtel : entrées, manèges et repas."],
                ['Freizeitpark-Wochenende', 'Zwei Tage in einem großen Freizeitpark mit einer Nacht im Parkhotel: Eintritt, Fahrgeschäfte und Essen.']],
            $city(39, 'city-ostrom', 380, 'Ostrom Forest', [
                'A village that shares its forest and fishing lake and looks after them together. Hike to the waterfall and sleep in a cabin.',
                'Un pueblo que comparte su bosque y su lago de pesca y los cuida entre todos. Haz una ruta hasta la cascada y duerme en una cabaña.',
                'Un village qui partage sa forêt et son lac de pêche et les entretient ensemble. Randonnée jusqu\'à la cascade et nuits dans un chalet.',
                'Ein Dorf, das seinen Wald und seinen Fischsee teilt und gemeinsam pflegt. Wandere zum Wasserfall und schlaf in einer Hütte.'],
                ['Train, 2 nights and meals.', 'Tren, 2 noches y comidas.', 'Train, 2 nuits et repas.', 'Zug, 2 Nächte und Essen.']),
            // an island: "on" / "auf"
            [40, 'city-samuelson', 560, 1,
                ['Weekend on Samuelson Island', 'Watch the morning fish auction at the harbour, then go snorkelling in a bay full of colourful fish. Ferry, 2 nights in a hotel and meals.'],
                ['Fin de semana en Samuelson Island', 'Mira la subasta de pescado de la mañana en el puerto y luego haz esnórquel en una bahía llena de peces de colores. Ferri, 2 noches de hotel y comidas.'],
                ['Week-end à Samuelson Island', "Assiste à la criée du matin au port, puis fais du snorkeling dans une baie pleine de poissons colorés. Ferry, 2 nuits d'hôtel et repas."],
                ['Wochenende auf Samuelson Island', 'Erlebe am Morgen die Fischauktion im Hafen und geh dann in einer Bucht voller bunter Fische schnorcheln. Fähre, 2 Nächte im Hotel und Essen.']],
        ];
    }

    public function down()
    {
        Schema::table('wants_plan_charges', fn (Blueprint $t) => $t->dropColumn('wants_plan_id'));
        Schema::table('wants_plan_choices', fn (Blueprint $t) => $t->dropColumn(['kind', 'plan_ids']));
    }
};
