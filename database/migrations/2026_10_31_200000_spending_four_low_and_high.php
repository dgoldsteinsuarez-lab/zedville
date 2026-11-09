<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Daniel, 2026-10-31: "debes crear 6 más en low, para cubrir los 12 meses del
 * año. Debe cubrir 20% del salario o un poco más, para evitar que ahorren
 * mucho ... si añado un low spending y low spending 4, debe sumar esos 21%
 * del salario ... si es un High Spending 4, deben ser más del 20% del
 * salario. El de High Spending, lo puedo usar en Intermediate level para que
 * puedan estar en menos en su cuenta y pedir crédito. Es decir, solo eligen
 * una actividad de Spending 4." He chose to make Spending 4 the big part:
 * short Low lists (Ƶ 50-200) and Low Spending 4 plans of a whole month
 * (Ƶ 640-810); High Spending 4 on its own above 20% (Ƶ 1,100-1,600).
 *
 * classes.spending4: which Spending 4 a class gets (low | high).
 * wants_plans.kind: which Spending 4 a plan belongs to.
 */
return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('wants_plans', 'kind')) {
            Schema::table('wants_plans', fn (Blueprint $t) => $t->string('kind', 4)->default('low')->after('id'));
        }
        if (!Schema::hasColumn('classes', 'spending4')) {
            Schema::table('classes', fn (Blueprint $t) => $t->string('spending4', 4)->default('low'));
        }

        // ── the plans: forty Low Spending 4, twelve High Spending 4 ──
        DB::table('wants_plan_choices')->exists()
            ? DB::table('wants_plans')->update(['is_active' => false])
            : DB::table('wants_plans')->delete();
        foreach ([['low', self::low()], ['high', self::high()]] as [$kind, $plans]) {
            foreach ($plans as $i => [$image, $cost, $en, $es, $fr, $de]) {
                DB::table('wants_plans')->insert([
                    'kind' => $kind, 'name' => $en[0], 'description' => $en[1], 'cost' => $cost, 'times_per_month' => 1,
                    'image' => 'asset/front/images/wants-plans/' . $image . '.svg', 'is_active' => true, 'sort_order' => $i + 1,
                    'translations' => json_encode(['es' => ['name' => $es[0], 'description' => $es[1]], 'fr' => ['name' => $fr[0], 'description' => $fr[1]],
                        'de' => ['name' => $de[0], 'description' => $de[1]]], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }

        // ── six more Low Spending activities: one for every month of the year ──
        DB::table('activities')->where('title', 'Community Food Drive')->update(['budget' => 50]);
        $next = (int) DB::table('activities')->where('type', 'low')->max('number');
        foreach (self::lowActivities() as [$title, $mission, $destination, $budget, $list]) {
            if (DB::table('activities')->where('title', $title)->exists()) {
                continue;
            }
            $shops = collect($list)->map(fn ($line) => isset($line['group']) ? 'Supermarket'
                : ($line['store'] ?? DB::table('products')->whereIn('product_name', $line['products'])->value('type')))->unique()->implode(',');
            DB::table('activities')->insert(['type' => 'low', 'number' => ++$next, 'title' => $title, 'mission' => $mission,
                'shop' => $shops, 'destination' => $destination, 'shopping_list' => json_encode($list), 'budget' => $budget,
                'created_at' => now(), 'updated_at' => now()]);
        }
    }

    /** [title, mission, delivered to, budget, list] */
    private static function lowActivities(): array
    {
        return [
            ['Back to School', 'The new school year starts on Monday. Get the essentials, and nothing you will not use.', 'Your Own Home', 70, [
                ['qty' => 1, 'products' => ['School Backpack']], ['qty' => 1, 'products' => ['Writing Tools']],
                ['qty' => 1, 'products' => ['Math Tools']], ['qty' => 1, 'products' => ['Paper Products']]]],
            ['Racket Sports Day', 'Your class is organising a sports afternoon. Bring a tennis racket, a table tennis paddle and a volleyball, and choose well: cheap or competition quality?', 'Your Own Home', 150, [
                ['qty' => 1, 'products' => ['Beginner Tennis Racket', 'Advanced Tennis Racket']],
                ['qty' => 1, 'products' => ['Casual Table Tennis Paddle', 'Pro Table Tennis Paddle']],
                ['qty' => 1, 'products' => ['Standard Volleyball', 'Tournament Volleyball']]]],
            ['A Gift for Your Parents', "It's your parents' wedding anniversary. Choose one special gift and add a small extra.", 'Your Parents', 130, [
                ['qty' => 1, 'products' => ['Watch', 'Handbag', 'Sunglasses']], ['qty' => 1, 'products' => ['Premium Socks']]]],
            ['Cosy Winter', 'Winter is here and your room is cold. Make it warm and cosy, and get one warm piece of clothing.', 'Your Own Home', 200, [
                ['qty' => 1, 'products' => ['Throw Blanket']], ['qty' => 1, 'products' => ['Cushion']],
                ['qty' => 1, 'products' => ['Nice Sweater', 'Quality Jacket']]]],
            ['Beach Day', 'A day at the beach with friends: protect yourself from the sun and bring a ball to play with.', 'Your Own Home', 120, [
                ['qty' => 1, 'products' => ['Hats', 'Sunglasses']], ['qty' => 1, 'products' => ['Standard Volleyball', 'Tournament Volleyball']]]],
            ['Ride to School', 'The bus is expensive. Get something to ride to school on, and a backpack for your books.', 'Your Own Home', 120, [
                ['qty' => 1, 'products' => ['Basic Bicycle', 'Simple Skateboard', 'Fun Roller Skates']], ['qty' => 1, 'products' => ['School Backpack']]]],
        ];
    }

    /** Low Spending 4: a whole month's plan, Ƶ 640-810 - with a short Low Spending list it is at least 21% of the salary. */
    private static function low(): array
    {
        $cooking = fn ($image, $utensilCost, array $names, array $dishes, array $utensils) => [$image, 360 + 140 + $utensilCost + 80,
            [$names[0], "Eight classes with a chef, two a week, to cook {$dishes[0]} (Ƶ 360), the ingredients to practise at home (Ƶ 140), {$utensils[0]} (Ƶ $utensilCost) and a dinner for your family with what you learned (Ƶ 80)."],
            [$names[1], "Ocho clases con un chef, dos por semana, para cocinar {$dishes[1]} (Ƶ 360), los ingredientes para practicar en casa (Ƶ 140), {$utensils[1]} (Ƶ $utensilCost) y una cena para tu familia con lo aprendido (Ƶ 80)."],
            [$names[2], "Huit cours avec un chef, deux par semaine, pour cuisiner {$dishes[2]} (Ƶ 360), les ingrédients pour t'entraîner à la maison (Ƶ 140), {$utensils[2]} (Ƶ $utensilCost) et un dîner pour ta famille avec ce que tu as appris (Ƶ 80)."],
            [$names[3], "Acht Kurse mit einem Koch, zwei pro Woche, um {$dishes[3]} zu kochen (Ƶ 360), die Zutaten zum Üben zu Hause (Ƶ 140), {$utensils[3]} (Ƶ $utensilCost) und ein Abendessen für deine Familie mit dem, was du gelernt hast (Ƶ 80)."]];
        $dance = fn ($image, $gearCost, array $names, array $gear, array $event) => [$image, 280 + $gearCost + 180 + 60 + 90,
            [$names[0], "Eight classes, two a week (Ƶ 280), {$gear[0]} (Ƶ $gearCost), a weekend workshop with a dance star (Ƶ 180), {$event[0]} (Ƶ 60) and your outfit for the end-of-course show (Ƶ 90)."],
            [$names[1], "Ocho clases, dos por semana (Ƶ 280), {$gear[1]} (Ƶ $gearCost), un taller de fin de semana con una estrella del baile (Ƶ 180), {$event[1]} (Ƶ 60) y tu ropa para la actuación de final de curso (Ƶ 90)."],
            [$names[2], "Huit cours, deux par semaine (Ƶ 280), {$gear[2]} (Ƶ $gearCost), un stage d'un week-end avec une star de la danse (Ƶ 180), {$event[2]} (Ƶ 60) et ta tenue pour le spectacle de fin de cours (Ƶ 90)."],
            [$names[3], "Acht Kurse, zwei pro Woche (Ƶ 280), {$gear[3]} (Ƶ $gearCost), ein Wochenend-Workshop mit einem Tanzstar (Ƶ 180), {$event[3]} (Ƶ 60) und dein Outfit für die Abschlussshow (Ƶ 90)."]];
        // a long weekend: the city's attractions, then what it costs
        $city = function ($image, $name, array $costs, array $texts, bool $island = false, bool $ferry = false, bool $cabin = false) {
            [$travel, $stay, $meals, $activities] = $costs;
            $how = [$ferry ? 'Ferry' : 'Train', $ferry ? 'Ferri' : 'Tren', $ferry ? 'Ferry' : 'Train', $ferry ? 'Fähre' : 'Zug'];
            $where = $cabin ? ['three nights in a cabin', 'tres noches en una cabaña', 'trois nuits dans un chalet', 'drei Nächte in einer Hütte']
                : ['three nights in a hotel', 'tres noches de hotel', "trois nuits d'hôtel", 'drei Nächte im Hotel'];

            return [$image, $travel + $stay + $meals + $activities,
                [($island ? 'Long weekend on ' : 'Long weekend in ') . $name, "{$texts[0]} {$how[0]} Ƶ $travel, {$where[0]} Ƶ $stay, meals Ƶ $meals and activities Ƶ $activities."],
                ['Fin de semana largo en ' . $name, "{$texts[1]} {$how[1]} Ƶ $travel, {$where[1]} Ƶ $stay, comidas Ƶ $meals y actividades Ƶ $activities."],
                ['Long week-end à ' . $name, "{$texts[2]} {$how[2]} Ƶ $travel, {$where[2]} Ƶ $stay, repas Ƶ $meals et activités Ƶ $activities."],
                [($island ? 'Langes Wochenende auf ' : 'Langes Wochenende in ') . $name, "{$texts[3]} {$how[3]} Ƶ $travel, {$where[3]} Ƶ $stay, Essen Ƶ $meals und Aktivitäten Ƶ $activities."]];
        };
        $sneakers = ['sneakers for dancing', 'zapatillas para bailar', 'des baskets pour danser', 'Tanz-Sneaker'];

        return [
            $cooking('cooking-china', 120, ['Chinese cooking course', 'Curso de cocina china', 'Cours de cuisine chinoise', 'Chinesischer Kochkurs'],
                ['dumplings, noodles, stir-fry and fried rice', 'empanadillas chinas, fideos, salteados y arroz frito', 'raviolis, nouilles, sautés au wok et riz cantonais', 'Teigtaschen, Nudeln, Wok-Gerichte und gebratenen Reis'],
                ['a wok and a set of knives', 'un wok y un juego de cuchillos', 'un wok et un set de couteaux', 'ein Wok und ein Messerset']),
            $dance('dance-hiphop', 90, ['Hip hop course', 'Curso de hip hop', 'Cours de hip-hop', 'Hip-Hop-Kurs'], $sneakers,
                ['a dance battle', 'una batalla de baile', 'une battle de danse', 'ein Dance-Battle']),
            $city('city-smith', 'Smith City', [140, 340, 180, 130], [
                'The city of Adam Smith: watch traders bargain in the old Market Hall, then relax on a beautiful white-sand beach.',
                'La ciudad de Adam Smith: mira cómo regatean los comerciantes en el antiguo mercado y relájate en una preciosa playa de arena blanca.',
                "La ville d'Adam Smith : regarde les marchands négocier dans l'ancienne halle, puis détends-toi sur une magnifique plage de sable blanc.",
                'Die Stadt von Adam Smith: Sieh zu, wie Händler in der alten Markthalle feilschen, und entspann dich an einem traumhaften weißen Sandstrand.']),
            ['concert', 790, ['Pop singer concert', 'Two tickets, for you and a friend (Ƶ 320), the train (Ƶ 120), a night in a hotel (Ƶ 160), two tour T-shirts (Ƶ 80) and dinner before the show (Ƶ 110).'],
                ['Concierto de cantante pop', 'Dos entradas, para ti y un amigo o amiga (Ƶ 320), el tren (Ƶ 120), una noche de hotel (Ƶ 160), dos camisetas de la gira (Ƶ 80) y la cena antes del concierto (Ƶ 110).'],
                ["Concert d'une star de la pop", "Deux billets, pour toi et un ami ou une amie (Ƶ 320), le train (Ƶ 120), une nuit d'hôtel (Ƶ 160), deux t-shirts de la tournée (Ƶ 80) et le dîner avant le concert (Ƶ 110)."],
                ['Konzert eines Popstars', 'Zwei Tickets, für dich und einen Freund oder eine Freundin (Ƶ 320), der Zug (Ƶ 120), eine Nacht im Hotel (Ƶ 160), zwei Tour-T-Shirts (Ƶ 80) und das Abendessen vor dem Konzert (Ƶ 110).']],
            ['cinema', 650, ['Cinema month', 'The cinema every Saturday with popcorn and a soda (Ƶ 80), a film festival pass (Ƶ 150) and a weekend camp where you make your own short film (Ƶ 420).'],
                ['Mes de cine', 'Cine cada sábado con palomitas y refresco (Ƶ 80), un abono para el festival de cine (Ƶ 150) y un campamento de fin de semana para rodar tu propio cortometraje (Ƶ 420).'],
                ['Mois de cinéma', 'Le cinéma chaque samedi avec pop-corn et soda (Ƶ 80), un pass pour le festival de cinéma (Ƶ 150) et un stage d\'un week-end pour tourner ton propre court-métrage (Ƶ 420).'],
                ['Kinomonat', 'Jeden Samstag Kino mit Popcorn und Limo (Ƶ 80), ein Pass fürs Filmfestival (Ƶ 150) und ein Wochenendcamp, in dem du deinen eigenen Kurzfilm drehst (Ƶ 420).']],
            ['surf', 780, ['Surf camp', 'Five days at a surf camp with lessons and board (Ƶ 480), your own wetsuit (Ƶ 140), the train to the coast (Ƶ 90) and a dinner with the group (Ƶ 70).'],
                ['Campamento de surf', 'Cinco días en un campamento de surf con clases y tabla (Ƶ 480), tu propio neopreno (Ƶ 140), el tren a la costa (Ƶ 90) y una cena con el grupo (Ƶ 70).'],
                ['Stage de surf', "Cinq jours de stage de surf avec cours et planche (Ƶ 480), ta propre combinaison (Ƶ 140), le train jusqu'à la côte (Ƶ 90) et un dîner avec le groupe (Ƶ 70)."],
                ['Surfcamp', 'Fünf Tage im Surfcamp mit Kurs und Brett (Ƶ 480), dein eigener Neoprenanzug (Ƶ 140), der Zug an die Küste (Ƶ 90) und ein Abendessen mit der Gruppe (Ƶ 70).']],
            ['speaking', 660, ['Public speaking course', 'Eight sessions to lose your fear of speaking (Ƶ 360), a weekend workshop with a professional speaker (Ƶ 220) and a video of your best talk (Ƶ 80).'],
                ['Curso de oratoria', 'Ocho sesiones para perder el miedo a hablar en público (Ƶ 360), un taller de fin de semana con un orador profesional (Ƶ 220) y un vídeo de tu mejor discurso (Ƶ 80).'],
                ['Cours de prise de parole', 'Huit séances pour perdre la peur de parler en public (Ƶ 360), un stage d\'un week-end avec un orateur professionnel (Ƶ 220) et une vidéo de ton meilleur discours (Ƶ 80).'],
                ['Rhetorikkurs', 'Acht Einheiten, um die Angst vorm Reden zu verlieren (Ƶ 360), ein Wochenend-Workshop mit einem Profi-Redner (Ƶ 220) und ein Video deiner besten Rede (Ƶ 80).']],
            $cooking('cooking-india', 130, ['Indian cooking course', 'Curso de cocina india', 'Cours de cuisine indienne', 'Indischer Kochkurs'],
                ['curry, naan bread, dal and chai', 'curri, pan naan, dal y té chai', 'curry, pain naan, dal et chaï', 'Curry, Naan-Brot, Dal und Chai'],
                ['a spice box and a set of knives', 'una caja de especias y un juego de cuchillos', 'une boîte à épices et un set de couteaux', 'eine Gewürzdose und ein Messerset']),
            $dance('dance-salsa', 110, ['Salsa course', 'Curso de salsa', 'Cours de salsa', 'Salsakurs'],
                ['dance shoes', 'zapatos de baile', 'des chaussures de danse', 'Tanzschuhe'],
                ['a salsa night with a live band', 'una noche de salsa con banda en directo', 'une soirée salsa avec un groupe live', 'ein Salsaabend mit Liveband']),
            $city('city-keynes', 'Keynes Bay', [140, 330, 170, 140], [
                'Visit the Central Bank museum and its gold vault, then take a boat to the sea caves.',
                'Visita el museo del Banco Central y su cámara del oro, y después ve en barco a las cuevas marinas.',
                'Visite le musée de la Banque centrale et sa salle des coffres, puis pars en bateau voir les grottes marines.',
                'Besuch das Museum der Zentralbank mit seinem Goldtresor und fahr dann mit dem Boot zu den Meereshöhlen.']),
            ['horse', 700, ['Horse riding course', 'Eight riding lessons (Ƶ 400), boots and a helmet (Ƶ 150) and a day-long ride in the mountains (Ƶ 150).'],
                ['Curso de equitación', 'Ocho clases de equitación (Ƶ 400), botas y casco (Ƶ 150) y un día de ruta a caballo por la montaña (Ƶ 150).'],
                ["Stage d'équitation", "Huit cours d'équitation (Ƶ 400), des bottes et un casque (Ƶ 150) et une journée de randonnée à cheval en montagne (Ƶ 150)."],
                ['Reitkurs', 'Acht Reitstunden (Ƶ 400), Stiefel und Helm (Ƶ 150) und ein ganzer Tag Ausritt in den Bergen (Ƶ 150).']],
            ['gaming', 640, ['Games month with friends', 'Two escape rooms (Ƶ 60), two bowling nights (Ƶ 50), a karaoke night (Ƶ 30) and a weekend at a gaming convention with hotel (Ƶ 500).'],
                ['Mes de juegos con amigos', 'Dos escape rooms (Ƶ 60), dos noches de bolos (Ƶ 50), una noche de karaoke (Ƶ 30) y un fin de semana en una convención de videojuegos con hotel (Ƶ 500).'],
                ['Mois de jeux entre amis', 'Deux escape games (Ƶ 60), deux soirées bowling (Ƶ 50), une soirée karaoké (Ƶ 30) et un week-end dans un salon du jeu vidéo avec hôtel (Ƶ 500).'],
                ['Spielemonat mit Freunden', 'Zwei Escape Rooms (Ƶ 60), zwei Bowlingabende (Ƶ 50), ein Karaoke-Abend (Ƶ 30) und ein Wochenende auf einer Gaming-Messe mit Hotel (Ƶ 500).']],
            ['growth', 650, ['Personal development course', 'An online course on habits, motivation and time management (Ƶ 190) and a weekend in the countryside to plan your goals (Ƶ 460).'],
                ['Curso de desarrollo personal', 'Un curso online sobre hábitos, motivación y organización del tiempo (Ƶ 190) y un fin de semana en el campo para planificar tus metas (Ƶ 460).'],
                ['Cours de développement personnel', 'Un cours en ligne sur les habitudes, la motivation et la gestion du temps (Ƶ 190) et un week-end à la campagne pour planifier tes objectifs (Ƶ 460).'],
                ['Kurs für persönliche Entwicklung', 'Ein Online-Kurs über Gewohnheiten, Motivation und Zeitmanagement (Ƶ 190) und ein Wochenende auf dem Land, um deine Ziele zu planen (Ƶ 460).']],
            $cooking('cooking-peru', 140, ['Peruvian cooking course', 'Curso de cocina peruana', 'Cours de cuisine péruvienne', 'Peruanischer Kochkurs'],
                ['ceviche, lomo saltado, causa and alfajores', 'ceviche, lomo saltado, causa y alfajores', 'ceviche, lomo saltado, causa et alfajores', 'Ceviche, Lomo Saltado, Causa und Alfajores'],
                ['a citrus press and a set of knives', 'un exprimidor y un juego de cuchillos', 'un presse-agrumes et un set de couteaux', 'eine Zitruspresse und ein Messerset']),
            $dance('dance-breakdance', 100, ['Breakdance course', 'Curso de breakdance', 'Cours de breakdance', 'Breakdance-Kurs'],
                ['knee pads and gloves', 'rodilleras y guantes', 'des genouillères et des gants', 'Knieschoner und Handschuhe'],
                ['a breaking competition', 'una competición de breaking', 'une compétition de breaking', 'ein Breaking-Wettbewerb']),
            $city('city-ricardo', 'Ricardo Valley', [140, 330, 170, 120], [
                'See the river port where two countries trade wine and cloth, then cycle through the vineyards and swim in the river.',
                'Mira el puerto fluvial donde dos países intercambian vino y telas, y luego recorre los viñedos en bici y báñate en el río.',
                'Découvre le port fluvial où deux pays échangent du vin et du drap, puis traverse les vignes à vélo et baigne-toi dans la rivière.',
                'Sieh den Flusshafen, wo zwei Länder Wein und Stoff tauschen, dann radle durch die Weinberge und bade im Fluss.']),
            ['band', 700, ['Concert by a band', 'Two tickets (Ƶ 260), the train (Ƶ 120), a night in a hotel (Ƶ 160), a signed poster (Ƶ 80) and dinner (Ƶ 80).'],
                ['Concierto de un grupo musical', 'Dos entradas (Ƶ 260), el tren (Ƶ 120), una noche de hotel (Ƶ 160), un póster firmado (Ƶ 80) y la cena (Ƶ 80).'],
                ["Concert d'un groupe", "Deux billets (Ƶ 260), le train (Ƶ 120), une nuit d'hôtel (Ƶ 160), une affiche dédicacée (Ƶ 80) et le dîner (Ƶ 80)."],
                ['Konzert einer Band', 'Zwei Tickets (Ƶ 260), der Zug (Ƶ 120), eine Nacht im Hotel (Ƶ 160), ein signiertes Poster (Ƶ 80) und das Abendessen (Ƶ 80).']],
            ['sailing', 790, ['Sailing course', 'Eight sailing sessions on a small boat (Ƶ 620), your own life jacket (Ƶ 70) and a day watching the regatta from a boat (Ƶ 100).'],
                ['Curso de vela', 'Ocho sesiones de vela en un barco pequeño (Ƶ 620), tu propio chaleco salvavidas (Ƶ 70) y un día viendo la regata desde un barco (Ƶ 100).'],
                ['Stage de voile', 'Huit séances de voile sur un petit bateau (Ƶ 620), ton propre gilet de sauvetage (Ƶ 70) et une journée à suivre la régate depuis un bateau (Ƶ 100).'],
                ['Segelkurs', 'Acht Segeleinheiten auf einem kleinen Boot (Ƶ 620), deine eigene Rettungsweste (Ƶ 70) und ein Tag, an dem du die Regatta vom Boot aus siehst (Ƶ 100).']],
            ['coding', 720, ['Coding bootcamp: build an app', 'Two weekends of coding to build your first app (Ƶ 560), publishing it in the app store (Ƶ 100) and a year of online lessons (Ƶ 60).'],
                ['Bootcamp de programación: crea una app', 'Dos fines de semana programando tu primera app (Ƶ 560), publicarla en la tienda de aplicaciones (Ƶ 100) y un año de clases online (Ƶ 60).'],
                ['Bootcamp de programmation : crée une appli', 'Deux week-ends de code pour créer ta première appli (Ƶ 560), sa publication sur la boutique d\'applis (Ƶ 100) et un an de cours en ligne (Ƶ 60).'],
                ['Programmier-Bootcamp: eine App bauen', 'Zwei Wochenenden Programmieren für deine erste App (Ƶ 560), die Veröffentlichung im App-Store (Ƶ 100) und ein Jahr Online-Unterricht (Ƶ 60).']],
            ['themepark', 700, ['Theme park weekend', 'Two days at a big theme park (Ƶ 180), a night in its hotel (Ƶ 240), the fast pass to skip the queues (Ƶ 100), meals (Ƶ 120) and the train (Ƶ 60).'],
                ['Fin de semana en un parque temático', 'Dos días en un gran parque temático (Ƶ 180), una noche en su hotel (Ƶ 240), el pase rápido para no hacer colas (Ƶ 100), comidas (Ƶ 120) y el tren (Ƶ 60).'],
                ["Week-end au parc d'attractions", "Deux jours dans un grand parc d'attractions (Ƶ 180), une nuit à son hôtel (Ƶ 240), le pass coupe-file (Ƶ 100), les repas (Ƶ 120) et le train (Ƶ 60)."],
                ['Freizeitpark-Wochenende', 'Zwei Tage in einem großen Freizeitpark (Ƶ 180), eine Nacht im Parkhotel (Ƶ 240), der Fast-Pass ohne Anstehen (Ƶ 100), Essen (Ƶ 120) und der Zug (Ƶ 60).']],
            $cooking('cooking-mexico', 150, ['Mexican cooking course', 'Curso de cocina mexicana', 'Cours de cuisine mexicaine', 'Mexikanischer Kochkurs'],
                ['tacos, guacamole, enchiladas and churros', 'tacos, guacamole, enchiladas y churros', 'tacos, guacamole, enchiladas et churros', 'Tacos, Guacamole, Enchiladas und Churros'],
                ['a tortilla press and a set of knives', 'una prensa para tortillas y un juego de cuchillos', 'une presse à tortillas et un set de couteaux', 'eine Tortillapresse und ein Messerset']),
            $dance('dance-kpop', 120, ['K-pop dance course', 'Curso de baile K-pop', 'Cours de danse K-pop', 'K-Pop-Tanzkurs'], $sneakers,
                ['a K-pop dance party', 'una fiesta de baile K-pop', 'une soirée danse K-pop', 'eine K-Pop-Tanzparty']),
            $city('city-friedman', 'Friedman Lake', [140, 330, 170, 130], [
                'See how coins are made at the Mint, then go kayaking on a crystal-clear mountain lake.',
                'Mira cómo se fabrican las monedas en la Casa de la Moneda y luego sal en kayak por un lago de montaña de aguas cristalinas.',
                "Découvre comment on fabrique les pièces à l'Hôtel de la Monnaie, puis fais du kayak sur un lac de montagne aux eaux cristallines.",
                'Sieh in der Münzprägeanstalt, wie Münzen gemacht werden, und paddle dann im Kajak über einen glasklaren Bergsee.']),
            ['festival', 720, ['Music festival weekend', 'A three-day pass (Ƶ 280), camping (Ƶ 100), the train (Ƶ 120), food and drinks (Ƶ 160) and festival merch (Ƶ 60).'],
                ['Fin de semana de festival de música', 'Abono de tres días (Ƶ 280), camping (Ƶ 100), el tren (Ƶ 120), comida y bebida (Ƶ 160) y recuerdos del festival (Ƶ 60).'],
                ['Week-end de festival de musique', 'Un pass trois jours (Ƶ 280), le camping (Ƶ 100), le train (Ƶ 120), la nourriture et les boissons (Ƶ 160) et des souvenirs du festival (Ƶ 60).'],
                ['Musikfestival-Wochenende', 'Ein Drei-Tages-Pass (Ƶ 280), Camping (Ƶ 100), der Zug (Ƶ 120), Essen und Getränke (Ƶ 160) und Festival-Merch (Ƶ 60).']],
            ['trainer', 680, ['Personal trainer and gym', 'Eight sessions with a personal trainer (Ƶ 440), a month at the gym (Ƶ 60) and sports clothes and running shoes (Ƶ 180).'],
                ['Entrenador personal y gimnasio', 'Ocho sesiones con un entrenador personal (Ƶ 440), un mes de gimnasio (Ƶ 60) y ropa de deporte y zapatillas para correr (Ƶ 180).'],
                ['Coach sportif et salle de sport', 'Huit séances avec un coach sportif (Ƶ 440), un mois de salle de sport (Ƶ 60) et des vêtements de sport et des chaussures de course (Ƶ 180).'],
                ['Personal Trainer und Fitnessstudio', 'Acht Einheiten mit einem Personal Trainer (Ƶ 440), ein Monat Fitnessstudio (Ƶ 60) und Sportkleidung und Laufschuhe (Ƶ 180).']],
            ['photography', 680, ['Photography course', 'Eight photography classes (Ƶ 320), a camera rental for the month (Ƶ 180), a photo walk through the old town (Ƶ 50) and printing your best photos for an exhibition (Ƶ 130).'],
                ['Curso de fotografía', 'Ocho clases de fotografía (Ƶ 320), una cámara de alquiler durante el mes (Ƶ 180), una ruta fotográfica por el casco antiguo (Ƶ 50) y la impresión de tus mejores fotos para una exposición (Ƶ 130).'],
                ['Cours de photo', 'Huit cours de photo (Ƶ 320), un appareil photo loué pour le mois (Ƶ 180), une balade photo dans la vieille ville (Ƶ 50) et le tirage de tes meilleures photos pour une exposition (Ƶ 130).'],
                ['Fotokurs', 'Acht Fotokurse (Ƶ 320), eine Leihkamera für den Monat (Ƶ 180), ein Fotospaziergang durch die Altstadt (Ƶ 50) und der Druck deiner besten Fotos für eine Ausstellung (Ƶ 130).']],
            $cooking('cooking-spain', 160, ['Spanish cooking course', 'Curso de cocina española', 'Cours de cuisine espagnole', 'Spanischer Kochkurs'],
                ['paella, Spanish omelette, gazpacho and croquettes', 'paella, tortilla de patatas, gazpacho y croquetas', 'paella, tortilla, gaspacho et croquetas', 'Paella, Tortilla, Gazpacho und Croquetas'],
                ['a paella pan and a set of knives', 'una paellera y un juego de cuchillos', 'une poêle à paella et un set de couteaux', 'eine Paellapfanne und ein Messerset']),
            $dance('dance-afrobeats', 130, ['Afrobeats dance course', 'Curso de baile afrobeats', 'Cours de danse afrobeats', 'Afrobeats-Tanzkurs'], $sneakers,
                ['an Afrobeats party with a live DJ', 'una fiesta afrobeats con DJ en directo', 'une soirée afrobeats avec un DJ en live', 'eine Afrobeats-Party mit Live-DJ']),
            $city('city-hayek', 'Hayek Peaks', [150, 330, 170, 150], [
                'See the old stock exchange, where prices changed on a giant board, and take the cable car up to the snowy peaks.',
                'Visita la antigua bolsa, donde los precios cambiaban en un panel gigante, y sube en teleférico a las cumbres nevadas.',
                "Visite l'ancienne Bourse, où les prix changeaient sur un tableau géant, et monte en téléphérique jusqu'aux sommets enneigés.",
                'Besuch die alte Börse, wo sich die Kurse auf einer riesigen Tafel änderten, und fahr mit der Seilbahn zu den verschneiten Gipfeln.']),
            ['climbing', 700, ['Climbing course', 'Six sessions at the climbing gym (Ƶ 240), climbing shoes and a harness (Ƶ 180) and a weekend on real rock with a guide (Ƶ 280).'],
                ['Curso de escalada', 'Seis sesiones en el rocódromo (Ƶ 240), pies de gato y arnés (Ƶ 180) y un fin de semana en roca de verdad con un guía (Ƶ 280).'],
                ["Stage d'escalade", "Six séances en salle d'escalade (Ƶ 240), des chaussons d'escalade et un baudrier (Ƶ 180) et un week-end sur de vrais rochers avec un guide (Ƶ 280)."],
                ['Kletterkurs', 'Sechs Einheiten in der Kletterhalle (Ƶ 240), Kletterschuhe und ein Gurt (Ƶ 180) und ein Wochenende am echten Fels mit einem Bergführer (Ƶ 280).']],
            ['massage', 680, ['Wellness month', 'Four massages to beat the school stress (Ƶ 260), a day at the spa (Ƶ 180), eight yoga classes (Ƶ 160) and a healthy cooking workshop (Ƶ 80).'],
                ['Mes de bienestar', 'Cuatro masajes contra el estrés del colegio (Ƶ 260), un día de spa (Ƶ 180), ocho clases de yoga (Ƶ 160) y un taller de cocina saludable (Ƶ 80).'],
                ['Mois bien-être', "Quatre massages contre le stress de l'école (Ƶ 260), une journée au spa (Ƶ 180), huit cours de yoga (Ƶ 160) et un atelier de cuisine saine (Ƶ 80)."],
                ['Wellnessmonat', 'Vier Massagen gegen den Schulstress (Ƶ 260), ein Tag im Spa (Ƶ 180), acht Yogastunden (Ƶ 160) und ein Workshop für gesundes Kochen (Ƶ 80).']],
            ['iceskating', 780, ['Winter weekend in the snow', 'Ice skating, sledding and a snowshoe hike with a guide (Ƶ 200), two nights in a mountain hostel (Ƶ 270), equipment rental (Ƶ 100), meals (Ƶ 140) and the bus (Ƶ 70).'],
                ['Fin de semana en la nieve', 'Patinaje sobre hielo, trineo y una ruta con raquetas de nieve con un guía (Ƶ 200), dos noches en un albergue de montaña (Ƶ 270), alquiler de material (Ƶ 100), comidas (Ƶ 140) y el autobús (Ƶ 70).'],
                ['Week-end à la neige', 'Patinage, luge et une randonnée en raquettes avec un guide (Ƶ 200), deux nuits dans une auberge de montagne (Ƶ 270), la location du matériel (Ƶ 100), les repas (Ƶ 140) et le bus (Ƶ 70).'],
                ['Winterwochenende im Schnee', 'Eislaufen, Rodeln und eine Schneeschuhwanderung mit Guide (Ƶ 200), zwei Nächte in einer Berghütte (Ƶ 270), Leihausrüstung (Ƶ 100), Essen (Ƶ 140) und der Bus (Ƶ 70).']],
            $city('city-ostrom', 'Ostrom Forest', [140, 270, 200, 140], [
                'A village that shares its forest and fishing lake and looks after them together. Hike to the waterfall.',
                'Un pueblo que comparte su bosque y su lago de pesca y los cuida entre todos. Haz una ruta hasta la cascada.',
                "Un village qui partage sa forêt et son lac de pêche et les entretient ensemble. Randonnée jusqu'à la cascade.",
                'Ein Dorf, das seinen Wald und seinen Fischsee teilt und gemeinsam pflegt. Wandere zum Wasserfall.'], false, false, true),
            ['football', 680, ['Football month at the stadium', 'Two home matches with a friend (Ƶ 280), the new team shirt (Ƶ 90), an away match with the train (Ƶ 260) and the stadium tour (Ƶ 50).'],
                ['Mes de fútbol en el estadio', 'Dos partidos en casa con un amigo o amiga (Ƶ 280), la nueva camiseta del equipo (Ƶ 90), un partido fuera con el tren (Ƶ 260) y la visita al estadio (Ƶ 50).'],
                ['Mois de foot au stade', "Deux matchs à domicile avec un ami ou une amie (Ƶ 280), le nouveau maillot de l'équipe (Ƶ 90), un match à l'extérieur avec le train (Ƶ 260) et la visite du stade (Ƶ 50)."],
                ['Fußballmonat im Stadion', 'Zwei Heimspiele mit einem Freund oder einer Freundin (Ƶ 280), das neue Trikot (Ƶ 90), ein Auswärtsspiel mit Zugfahrt (Ƶ 260) und die Stadionführung (Ƶ 50).']],
            ['guitar', 640, ['Guitar lessons and your own guitar', 'Four lessons with a teacher (Ƶ 160), your own acoustic guitar (Ƶ 300), a small amplifier (Ƶ 120), a songbook (Ƶ 30) and an open-mic night (Ƶ 30).'],
                ['Clases de guitarra y tu propia guitarra', 'Cuatro clases con un profesor (Ƶ 160), tu propia guitarra acústica (Ƶ 300), un amplificador pequeño (Ƶ 120), un cancionero (Ƶ 30) y una noche de micrófono abierto (Ƶ 30).'],
                ['Cours de guitare et ta propre guitare', 'Quatre cours avec un professeur (Ƶ 160), ta propre guitare acoustique (Ƶ 300), un petit ampli (Ƶ 120), un recueil de chansons (Ƶ 30) et une soirée scène ouverte (Ƶ 30).'],
                ['Gitarrenunterricht und deine eigene Gitarre', 'Vier Stunden bei einem Lehrer (Ƶ 160), deine eigene Akustikgitarre (Ƶ 300), ein kleiner Verstärker (Ƶ 120), ein Liederbuch (Ƶ 30) und ein Open-Mic-Abend (Ƶ 30).']],
            ['cycling', 660, ['Cycling weekend', 'Two days cycling along the river with a guide (Ƶ 220), bike and helmet rental (Ƶ 100), a night in a guesthouse (Ƶ 140), meals (Ƶ 120) and cycling gloves and glasses (Ƶ 80).'],
                ['Fin de semana en bici', 'Dos días en bici junto al río con un guía (Ƶ 220), alquiler de bici y casco (Ƶ 100), una noche en una casa rural (Ƶ 140), comidas (Ƶ 120) y guantes y gafas de ciclismo (Ƶ 80).'],
                ['Week-end à vélo', "Deux jours à vélo le long de la rivière avec un guide (Ƶ 220), la location du vélo et du casque (Ƶ 100), une nuit en chambre d'hôtes (Ƶ 140), les repas (Ƶ 120) et des gants et des lunettes de vélo (Ƶ 80)."],
                ['Radwochenende', 'Zwei Tage Radfahren am Fluss mit Guide (Ƶ 220), Leihrad und Helm (Ƶ 100), eine Nacht in einer Pension (Ƶ 140), Essen (Ƶ 120) und Radhandschuhe und -brille (Ƶ 80).']],
            $city('city-samuelson', 'Samuelson Island', [180, 330, 170, 130], [
                'Watch the morning fish auction at the harbour, then go snorkelling in a bay full of colourful fish.',
                'Mira la subasta de pescado de la mañana en el puerto y luego haz esnórquel en una bahía llena de peces de colores.',
                'Assiste à la criée du matin au port, puis fais du snorkeling dans une baie pleine de poissons colorés.',
                'Erlebe am Morgen die Fischauktion im Hafen und geh dann in einer Bucht voller bunter Fische schnorcheln.'], true, true),
            ['theatre', 640, ['Theatre month', 'Two theatre plays (Ƶ 160), a comedy show (Ƶ 60) and a weekend acting workshop with a final performance (Ƶ 420).'],
                ['Mes de teatro', 'Dos obras de teatro (Ƶ 160), un espectáculo de comedia (Ƶ 60) y un taller de interpretación de fin de semana con actuación final (Ƶ 420).'],
                ['Mois de théâtre', "Deux pièces de théâtre (Ƶ 160), un spectacle d'humour (Ƶ 60) et un stage de théâtre d'un week-end avec une représentation finale (Ƶ 420)."],
                ['Theatermonat', 'Zwei Theaterstücke (Ƶ 160), eine Comedy-Show (Ƶ 60) und ein Wochenend-Schauspielworkshop mit Abschlussaufführung (Ƶ 420).']],
            ['kayak', 670, ['Kayak and camping weekend', 'Two days kayaking down the river with a guide (Ƶ 280), tent and sleeping bag rental (Ƶ 90), camping food (Ƶ 100), the bus (Ƶ 70) and a waterproof jacket (Ƶ 130).'],
                ['Fin de semana de kayak y acampada', 'Dos días bajando el río en kayak con un guía (Ƶ 280), alquiler de tienda y saco de dormir (Ƶ 90), comida de acampada (Ƶ 100), el autobús (Ƶ 70) y un chubasquero (Ƶ 130).'],
                ['Week-end kayak et camping', 'Deux jours à descendre la rivière en kayak avec un guide (Ƶ 280), la location de la tente et du sac de couchage (Ƶ 90), les repas au camping (Ƶ 100), le bus (Ƶ 70) et une veste imperméable (Ƶ 130).'],
                ['Kajak- und Campingwochenende', 'Zwei Tage im Kajak den Fluss hinunter mit Guide (Ƶ 280), Leihzelt und Schlafsack (Ƶ 90), Essen auf dem Campingplatz (Ƶ 100), der Bus (Ƶ 70) und eine Regenjacke (Ƶ 130).']],
            ['museum', 680, ['Science and museums month', 'A museum pass for the month (Ƶ 40), a weekend science camp (Ƶ 480), a night at the planetarium (Ƶ 30) and a telescope workshop (Ƶ 130).'],
                ['Mes de ciencia y museos', 'Un pase de museos para todo el mes (Ƶ 40), un campamento científico de fin de semana (Ƶ 480), una noche en el planetario (Ƶ 30) y un taller de telescopios (Ƶ 130).'],
                ['Mois sciences et musées', 'Un pass musées pour le mois (Ƶ 40), un stage scientifique d\'un week-end (Ƶ 480), une soirée au planétarium (Ƶ 30) et un atelier télescope (Ƶ 130).'],
                ['Wissenschafts- und Museumsmonat', 'Ein Museumspass für den Monat (Ƶ 40), ein Wissenschaftscamp am Wochenende (Ƶ 480), ein Abend im Planetarium (Ƶ 30) und ein Teleskop-Workshop (Ƶ 130).']],
        ];
    }

    /** High Spending 4: on its own more than 20% of the salary (Ƶ 1,100-1,600) - an Intermediate class may need credit. */
    private static function high(): array
    {
        $flight = fn ($image, $name, $days, array $costs, array $texts, bool $island = false) => [$image, array_sum($costs),
            ["Flight and $days days " . ($island ? 'on ' : 'in ') . $name, $texts[0]], ["Vuelo y $days días en $name", $texts[1]],
            ["Vol et $days jours à $name", $texts[2]], ["Flug und $days Tage " . ($island ? 'auf ' : 'in ') . $name, $texts[3]]];

        return [
            $flight('flight-galbraith', 'Galbraith Bay', 4, [380, 420, 200, 100], [
                'Walk the famous arcade full of luxury shops and watch dolphins from a boat. Return flight Ƶ 380, three nights in a seaside hotel Ƶ 420, meals Ƶ 200 and the boat trip Ƶ 100.',
                'Pasea por la famosa galería de tiendas de lujo y mira delfines desde un barco. Vuelo de ida y vuelta Ƶ 380, tres noches en un hotel junto al mar Ƶ 420, comidas Ƶ 200 y la excursión en barco Ƶ 100.',
                "Promène-toi dans la célèbre galerie de boutiques de luxe et regarde les dauphins depuis un bateau. Vol aller-retour Ƶ 380, trois nuits dans un hôtel en bord de mer Ƶ 420, repas Ƶ 200 et l'excursion en bateau Ƶ 100.",
                'Bummle durch die berühmte Passage voller Luxusläden und beobachte Delfine vom Boot aus. Hin- und Rückflug Ƶ 380, drei Nächte im Hotel am Meer Ƶ 420, Essen Ƶ 200 und die Bootstour Ƶ 100.']),
            ['staycation', 1300, ['Luxury staycation in Zedville', 'Three nights in a five-star suite in your own city: room service, spa and a dinner on the rooftop. No travelling, a lot of luxury. Suite Ƶ 960, spa Ƶ 180 and dinner Ƶ 160.'],
                ['Staycation de lujo en Zedville', 'Tres noches en una suite de cinco estrellas en tu propia ciudad: servicio de habitaciones, spa y una cena en la azotea. Sin viajar, pero con mucho lujo. Suite Ƶ 960, spa Ƶ 180 y cena Ƶ 160.'],
                ['Staycation de luxe à Zedville', 'Trois nuits dans une suite cinq étoiles dans ta propre ville : service en chambre, spa et un dîner sur le toit. Pas de voyage, beaucoup de luxe. Suite Ƶ 960, spa Ƶ 180 et dîner Ƶ 160.'],
                ['Luxus-Staycation in Zedville', 'Drei Nächte in einer Fünf-Sterne-Suite in deiner eigenen Stadt: Zimmerservice, Spa und ein Abendessen auf der Dachterrasse. Keine Reise, viel Luxus. Suite Ƶ 960, Spa Ƶ 180 und Abendessen Ƶ 160.']],
            ['ski', 1400, ['Ski week in Hayek Peaks', 'Five days of skiing: ski pass Ƶ 320, equipment rental Ƶ 180, lessons Ƶ 220 and a hotel at the slopes Ƶ 680.'],
                ['Semana de esquí en Hayek Peaks', 'Cinco días de esquí: forfait Ƶ 320, alquiler de material Ƶ 180, clases Ƶ 220 y un hotel a pie de pista Ƶ 680.'],
                ['Semaine de ski à Hayek Peaks', 'Cinq jours de ski : forfait Ƶ 320, location du matériel Ƶ 180, cours Ƶ 220 et un hôtel au pied des pistes Ƶ 680.'],
                ['Skiwoche in Hayek Peaks', 'Fünf Tage Skifahren: Skipass Ƶ 320, Leihausrüstung Ƶ 180, Skikurs Ƶ 220 und ein Hotel direkt an der Piste Ƶ 680.']],
            ['vip', 1150, ['VIP concert experience', 'Front-row tickets for you and a friend Ƶ 640, meeting the band backstage Ƶ 200, a night in a hotel Ƶ 180 and the train Ƶ 130.'],
                ['Concierto VIP', 'Entradas en primera fila para ti y un amigo o amiga Ƶ 640, conocer al grupo en el camerino Ƶ 200, una noche de hotel Ƶ 180 y el tren Ƶ 130.'],
                ['Concert en VIP', "Places au premier rang pour toi et un ami ou une amie Ƶ 640, la rencontre avec le groupe en coulisses Ƶ 200, une nuit d'hôtel Ƶ 180 et le train Ƶ 130."],
                ['VIP-Konzerterlebnis', 'Tickets in der ersten Reihe für dich und einen Freund oder eine Freundin Ƶ 640, Treffen mit der Band hinter der Bühne Ƶ 200, eine Nacht im Hotel Ƶ 180 und der Zug Ƶ 130.']],
            $flight('flight-schumpeter', 'Schumpeter Springs', 4, [400, 450, 200, 120], [
                'Visit the Museum of Inventions, where new ideas replace old ones, and relax in natural hot springs. Return flight Ƶ 400, three nights in a hotel Ƶ 450, meals Ƶ 200 and the springs Ƶ 120.',
                'Visita el Museo de los Inventos, donde las ideas nuevas sustituyen a las viejas, y relájate en aguas termales naturales. Vuelo de ida y vuelta Ƶ 400, tres noches de hotel Ƶ 450, comidas Ƶ 200 y las termas Ƶ 120.',
                "Visite le musée des Inventions, où les idées nouvelles remplacent les anciennes, et détends-toi dans des sources chaudes naturelles. Vol aller-retour Ƶ 400, trois nuits d'hôtel Ƶ 450, repas Ƶ 200 et les sources Ƶ 120.",
                'Besuch das Museum der Erfindungen, in dem neue Ideen alte ersetzen, und entspann dich in natürlichen heißen Quellen. Hin- und Rückflug Ƶ 400, drei Nächte im Hotel Ƶ 450, Essen Ƶ 200 und die Quellen Ƶ 120.']),
            ['resort', 1450, ['Weekend at a luxury resort', 'Two nights in a luxury hotel by the sea with a private beach, gourmet dinners and a spa. Room Ƶ 900, dinners Ƶ 320 and spa Ƶ 230.'],
                ['Fin de semana en un hotel de lujo', 'Dos noches en un hotel de lujo junto al mar con playa privada, cenas gourmet y spa. Habitación Ƶ 900, cenas Ƶ 320 y spa Ƶ 230.'],
                ['Week-end dans un hôtel de luxe', 'Deux nuits dans un hôtel de luxe en bord de mer avec plage privée, dîners gastronomiques et spa. Chambre Ƶ 900, dîners Ƶ 320 et spa Ƶ 230.'],
                ['Wochenende im Luxushotel', 'Zwei Nächte in einem Luxushotel am Meer mit Privatstrand, Gourmet-Abendessen und Spa. Zimmer Ƶ 900, Abendessen Ƶ 320 und Spa Ƶ 230.']],
            ['diving', 1300, ['Diving certificate on Samuelson Island', 'Five days: diving course and certificate Ƶ 540, equipment Ƶ 160, the ferry Ƶ 140 and a hotel by the beach Ƶ 460.'],
                ['Título de buceo en Samuelson Island', 'Cinco días: curso y título de buceo Ƶ 540, equipo Ƶ 160, el ferri Ƶ 140 y un hotel junto a la playa Ƶ 460.'],
                ['Brevet de plongée à Samuelson Island', 'Cinq jours : cours et brevet de plongée Ƶ 540, équipement Ƶ 160, le ferry Ƶ 140 et un hôtel près de la plage Ƶ 460.'],
                ['Tauchschein auf Samuelson Island', 'Fünf Tage: Tauchkurs und Tauchschein Ƶ 540, Ausrüstung Ƶ 160, die Fähre Ƶ 140 und ein Hotel am Strand Ƶ 460.']],
            ['festival-abroad', 1400, ['Music festival abroad', 'Return flight Ƶ 400, a three-day pass Ƶ 340, a hotel near the festival Ƶ 450 and food Ƶ 210.'],
                ['Festival de música en el extranjero', 'Vuelo de ida y vuelta Ƶ 400, abono de tres días Ƶ 340, un hotel cerca del festival Ƶ 450 y comida Ƶ 210.'],
                ['Festival de musique à l\'étranger', 'Vol aller-retour Ƶ 400, un pass trois jours Ƶ 340, un hôtel près du festival Ƶ 450 et les repas Ƶ 210.'],
                ['Musikfestival im Ausland', 'Hin- und Rückflug Ƶ 400, ein Drei-Tages-Pass Ƶ 340, ein Hotel in Festivalnähe Ƶ 450 und Essen Ƶ 210.']],
            $flight('flight-sen', 'Sen City', 4, [420, 430, 180, 120], [
                "Visit the famous public library and the city's free schools, then hike to a turquoise mountain lake. Return flight Ƶ 420, three nights in a hotel Ƶ 430, meals Ƶ 180 and a guide for the hike Ƶ 120.",
                'Visita la famosa biblioteca pública y las escuelas gratuitas de la ciudad, y luego sube a un lago de montaña de color turquesa. Vuelo de ida y vuelta Ƶ 420, tres noches de hotel Ƶ 430, comidas Ƶ 180 y un guía para la ruta Ƶ 120.',
                "Visite la célèbre bibliothèque publique et les écoles gratuites de la ville, puis monte jusqu'à un lac de montagne turquoise. Vol aller-retour Ƶ 420, trois nuits d'hôtel Ƶ 430, repas Ƶ 180 et un guide pour la randonnée Ƶ 120.",
                'Besuch die berühmte öffentliche Bibliothek und die kostenlosen Schulen der Stadt und wandere dann zu einem türkisblauen Bergsee. Hin- und Rückflug Ƶ 420, drei Nächte im Hotel Ƶ 430, Essen Ƶ 180 und ein Guide für die Wanderung Ƶ 120.']),
            ['cruise', 1600, ['Cruise along the coast', 'Four days on a cruise ship: cabin and all meals Ƶ 1,300, shows, the pool and one excursion on land Ƶ 300.'],
                ['Crucero por la costa', 'Cuatro días en un crucero: camarote y todas las comidas Ƶ 1.300, espectáculos, piscina y una excursión en tierra Ƶ 300.'],
                ['Croisière le long de la côte', 'Quatre jours sur un bateau de croisière : cabine et tous les repas Ƶ 1 300, spectacles, piscine et une excursion à terre Ƶ 300.'],
                ['Kreuzfahrt entlang der Küste', 'Vier Tage auf einem Kreuzfahrtschiff: Kabine und alle Mahlzeiten Ƶ 1.300, Shows, Pool und ein Landausflug Ƶ 300.']],
            $flight('flight-yunus', 'Yunus Island', 5, [450, 520, 250, 130], [
                'Meet the village bank that lends small amounts so people can start a business, and dive in the coral reef. Return flight Ƶ 450, four nights in a hotel Ƶ 520, meals Ƶ 250 and the diving trip Ƶ 130.',
                'Conoce el banco del pueblo que presta pequeñas cantidades para que la gente monte su negocio, y bucea en el arrecife de coral. Vuelo de ida y vuelta Ƶ 450, cuatro noches de hotel Ƶ 520, comidas Ƶ 250 y la excursión de buceo Ƶ 130.',
                'Découvre la banque du village qui prête de petites sommes pour que les gens créent leur entreprise, et plonge dans le récif de corail. Vol aller-retour Ƶ 450, quatre nuits d\'hôtel Ƶ 520, repas Ƶ 250 et la sortie plongée Ƶ 130.',
                'Lern die Dorfbank kennen, die kleine Beträge verleiht, damit Menschen ein Geschäft gründen können, und tauch am Korallenriff. Hin- und Rückflug Ƶ 450, vier Nächte im Hotel Ƶ 520, Essen Ƶ 250 und der Tauchausflug Ƶ 130.'], true),
            ['themepark-abroad', 1600, ['Giant theme park abroad', 'Return flight Ƶ 400, three days in a giant theme park with its hotel Ƶ 950 and meals Ƶ 250.'],
                ['Gran parque temático en el extranjero', 'Vuelo de ida y vuelta Ƶ 400, tres días en un parque temático gigante con su hotel Ƶ 950 y comidas Ƶ 250.'],
                ["Grand parc d'attractions à l'étranger", "Vol aller-retour Ƶ 400, trois jours dans un parc d'attractions géant avec son hôtel Ƶ 950 et les repas Ƶ 250."],
                ['Riesiger Freizeitpark im Ausland', 'Hin- und Rückflug Ƶ 400, drei Tage in einem riesigen Freizeitpark mit Parkhotel Ƶ 950 und Essen Ƶ 250.']],
        ];
    }

    public function down()
    {
        Schema::table('wants_plans', fn (Blueprint $t) => $t->dropColumn('kind'));
        Schema::table('classes', fn (Blueprint $t) => $t->dropColumn('spending4'));
    }
};
