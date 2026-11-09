<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Intermediate's Spending 4 plans, kind "mid" (Daniel, 2026-11-08: "for
 * intermediate, if we add the donation per month, adjust to 15% Spending 4"):
 * twelve plans of Ƶ 410-560 that, with a Low Spending activity of Ƶ 50-200,
 * come to about 15% of the salary (WantsPlanService::MID_TOTAL). Each one's
 * parts add up to its cost; the pictures are those of the Low plans like them.
 */
return new class extends Migration {
    private const PLANS = [
        ['Games nights with friends', 410, 'gaming.svg',
            'An escape room (Ƶ 60), a bowling night (Ƶ 50), a karaoke night (Ƶ 40), a new board game (Ƶ 60), a laser tag afternoon (Ƶ 70) and pizza for everyone (Ƶ 130).',
            'es' => ['Noches de juegos con amigos', 'Una sala de escape (Ƶ 60), una noche de bolos (Ƶ 50), una noche de karaoke (Ƶ 40), un juego de mesa nuevo (Ƶ 60), una tarde de láser tag (Ƶ 70) y pizza para todos (Ƶ 130).'],
            'fr' => ['Soirées jeux entre amis', 'Un escape game (Ƶ 60), une soirée bowling (Ƶ 50), une soirée karaoké (Ƶ 40), un nouveau jeu de société (Ƶ 60), un après-midi de laser game (Ƶ 70) et des pizzas pour tout le monde (Ƶ 130).'],
            'de' => ['Spieleabende mit Freunden', 'Ein Escape Room (Ƶ 60), ein Bowlingabend (Ƶ 50), ein Karaokeabend (Ƶ 40), ein neues Brettspiel (Ƶ 60), ein Nachmittag Lasertag (Ƶ 70) und Pizza für alle (Ƶ 130).']],
        ['Football day out', 420, 'football.svg',
            'A home match with a friend (Ƶ 140), the team scarf (Ƶ 30), a stadium tour (Ƶ 50), lunch at the stadium (Ƶ 60) and a five-a-side match with your friends (Ƶ 140).',
            'es' => ['Día de fútbol', 'Un partido en casa con un amigo (Ƶ 140), la bufanda del equipo (Ƶ 30), una visita al estadio (Ƶ 50), la comida en el estadio (Ƶ 60) y un partido de fútbol 5 con tus amigos (Ƶ 140).'],
            'fr' => ['Journée foot', 'Un match à domicile avec un ami (Ƶ 140), l\'écharpe de l\'équipe (Ƶ 30), une visite du stade (Ƶ 50), le déjeuner au stade (Ƶ 60) et un match de foot à cinq avec tes amis (Ƶ 140).'],
            'de' => ['Fußballtag', 'Ein Heimspiel mit einem Freund (Ƶ 140), der Schal des Teams (Ƶ 30), eine Stadionführung (Ƶ 50), Mittagessen im Stadion (Ƶ 60) und ein Kleinfeldspiel mit deinen Freunden (Ƶ 140).']],
        ['Cycling day trip', 430, 'cycling.svg',
            'A day cycling along the river with a guide (Ƶ 160), bike and helmet rental (Ƶ 80), a picnic (Ƶ 50), the train back (Ƶ 40) and a service for your own bike (Ƶ 100).',
            'es' => ['Excursión en bici', 'Un día en bici junto al río con guía (Ƶ 160), el alquiler de la bici y el casco (Ƶ 80), un pícnic (Ƶ 50), el tren de vuelta (Ƶ 40) y una revisión de tu propia bici (Ƶ 100).'],
            'fr' => ['Sortie à vélo', 'Une journée à vélo le long de la rivière avec un guide (Ƶ 160), la location du vélo et du casque (Ƶ 80), un pique-nique (Ƶ 50), le train du retour (Ƶ 40) et une révision de ton propre vélo (Ƶ 100).'],
            'de' => ['Radtour', 'Ein Tag mit dem Rad am Fluss entlang, mit Guide (Ƶ 160), Leihrad und Helm (Ƶ 80), ein Picknick (Ƶ 50), der Zug zurück (Ƶ 40) und eine Inspektion für dein eigenes Rad (Ƶ 100).']],
        ['Climbing taster', 440, 'climbing.svg',
            'Three sessions at the climbing gym (Ƶ 120), shoe and harness rental (Ƶ 40), an outdoor climbing day with a guide (Ƶ 220) and a chalk bag (Ƶ 60).',
            'es' => ['Iniciación a la escalada', 'Tres sesiones en el rocódromo (Ƶ 120), el alquiler de pies de gato y arnés (Ƶ 40), un día de escalada al aire libre con guía (Ƶ 220) y una bolsa de magnesio (Ƶ 60).'],
            'fr' => ['Initiation à l\'escalade', 'Trois séances en salle d\'escalade (Ƶ 120), la location des chaussons et du baudrier (Ƶ 40), une journée d\'escalade en plein air avec un guide (Ƶ 220) et un sac à magnésie (Ƶ 60).'],
            'de' => ['Schnupperklettern', 'Drei Einheiten in der Kletterhalle (Ƶ 120), Leihschuhe und Klettergurt (Ƶ 40), ein Klettertag draußen mit Guide (Ƶ 220) und ein Chalkbag (Ƶ 60).']],
        ['Dance taster month', 440, 'dance-salsa.svg',
            'Four dance classes, one a week (Ƶ 160), dance shoes (Ƶ 110) and a weekend workshop (Ƶ 170).',
            'es' => ['Mes de prueba de baile', 'Cuatro clases de baile, una por semana (Ƶ 160), zapatos de baile (Ƶ 110) y un taller de fin de semana (Ƶ 170).'],
            'fr' => ['Mois découverte de la danse', 'Quatre cours de danse, un par semaine (Ƶ 160), des chaussures de danse (Ƶ 110) et un stage d\'un week-end (Ƶ 170).'],
            'de' => ['Tanz-Schnuppermonat', 'Vier Tanzstunden, eine pro Woche (Ƶ 160), Tanzschuhe (Ƶ 110) und ein Wochenend-Workshop (Ƶ 170).']],
        ['Cinema afternoons', 450, 'cinema.svg',
            'Two Saturday films with popcorn and a soda (Ƶ 60), a film festival pass (Ƶ 150) and a one-day film-making workshop (Ƶ 240).',
            'es' => ['Tardes de cine', 'Dos películas en sábado con palomitas y refresco (Ƶ 60), un abono para el festival de cine (Ƶ 150) y un taller de un día para rodar un corto (Ƶ 240).'],
            'fr' => ['Après-midi cinéma', 'Deux films le samedi avec pop-corn et soda (Ƶ 60), un pass pour le festival de cinéma (Ƶ 150) et un atelier d\'une journée pour tourner un court-métrage (Ƶ 240).'],
            'de' => ['Kinonachmittage', 'Zwei Filme am Samstag mit Popcorn und Limo (Ƶ 60), ein Pass fürs Filmfestival (Ƶ 150) und ein Tagesworkshop, in dem du einen Kurzfilm drehst (Ƶ 240).']],
        ['Science museum and planetarium', 460, 'museum.svg',
            'A museum pass for the month (Ƶ 40), a planetarium show (Ƶ 30), a one-day science workshop (Ƶ 190) and a robotics kit to build at home (Ƶ 200).',
            'es' => ['Museo de ciencias y planetario', 'Un pase de museo para el mes (Ƶ 40), una sesión de planetario (Ƶ 30), un taller de ciencia de un día (Ƶ 190) y un kit de robótica para montar en casa (Ƶ 200).'],
            'fr' => ['Musée des sciences et planétarium', 'Un pass musée pour le mois (Ƶ 40), une séance au planétarium (Ƶ 30), un atelier scientifique d\'une journée (Ƶ 190) et un kit de robotique à monter chez toi (Ƶ 200).'],
            'de' => ['Wissenschaftsmuseum und Planetarium', 'Ein Museumspass für den Monat (Ƶ 40), eine Planetariumsshow (Ƶ 30), ein eintägiger Wissenschaftsworkshop (Ƶ 190) und ein Robotik-Bausatz für zu Hause (Ƶ 200).']],
        ['Taster cooking classes', 480, 'cooking-spain.svg',
            'Four cooking classes with a chef, one a week (Ƶ 320), an apron and a knife set (Ƶ 90) and the ingredients for a dinner for your family (Ƶ 70).',
            'es' => ['Clases de cocina para empezar', 'Cuatro clases de cocina con un chef, una por semana (Ƶ 320), un delantal y un juego de cuchillos (Ƶ 90) y los ingredientes para una cena con tu familia (Ƶ 70).'],
            'fr' => ['Cours de cuisine découverte', 'Quatre cours de cuisine avec un chef, un par semaine (Ƶ 320), un tablier et un set de couteaux (Ƶ 90) et les ingrédients d\'un dîner pour ta famille (Ƶ 70).'],
            'de' => ['Schnupperkochkurs', 'Vier Kochstunden mit einem Koch, eine pro Woche (Ƶ 320), eine Schürze und ein Messerset (Ƶ 90) und die Zutaten für ein Abendessen mit deiner Familie (Ƶ 70).']],
        ['Wellness weekend', 500, 'massage.svg',
            'Two massages (Ƶ 130), a day at the spa (Ƶ 180), a yoga class pass (Ƶ 120) and a smoothie every Saturday (Ƶ 70).',
            'es' => ['Fin de semana de bienestar', 'Dos masajes (Ƶ 130), un día en el spa (Ƶ 180), un bono de clases de yoga (Ƶ 120) y un batido cada sábado (Ƶ 70).'],
            'fr' => ['Week-end bien-être', 'Deux massages (Ƶ 130), une journée au spa (Ƶ 180), une carte de cours de yoga (Ƶ 120) et un smoothie chaque samedi (Ƶ 70).'],
            'de' => ['Wellness-Wochenende', 'Zwei Massagen (Ƶ 130), ein Tag im Spa (Ƶ 180), eine Karte für Yogakurse (Ƶ 120) und jeden Samstag ein Smoothie (Ƶ 70).']],
        ['Day at the theme park', 520, 'themepark.svg',
            'A day at a big theme park with a fast pass (Ƶ 280), the train there and back (Ƶ 90), food and drinks (Ƶ 80) and a photo and a souvenir (Ƶ 70).',
            'es' => ['Un día en el parque de atracciones', 'Un día en un gran parque de atracciones con pase rápido (Ƶ 280), el tren de ida y vuelta (Ƶ 90), comida y bebida (Ƶ 80) y una foto y un recuerdo (Ƶ 70).'],
            'fr' => ['Une journée au parc d\'attractions', 'Une journée dans un grand parc d\'attractions avec coupe-file (Ƶ 280), le train aller-retour (Ƶ 90), à manger et à boire (Ƶ 80) et une photo et un souvenir (Ƶ 70).'],
            'de' => ['Ein Tag im Freizeitpark', 'Ein Tag in einem großen Freizeitpark mit Fast Pass (Ƶ 280), der Zug hin und zurück (Ƶ 90), Essen und Getränke (Ƶ 80) und ein Foto und ein Andenken (Ƶ 70).']],
        ['Photography weekend', 540, 'photography.svg',
            'Two photography classes (Ƶ 80), a camera rental for the weekend (Ƶ 90), a photo walk with a guide (Ƶ 130) and a photo book of your best photos (Ƶ 240).',
            'es' => ['Fin de semana de fotografía', 'Dos clases de fotografía (Ƶ 80), el alquiler de una cámara para el fin de semana (Ƶ 90), un paseo fotográfico con guía (Ƶ 130) y un álbum impreso con tus mejores fotos (Ƶ 240).'],
            'fr' => ['Week-end photo', 'Deux cours de photo (Ƶ 80), la location d\'un appareil pour le week-end (Ƶ 90), une balade photo avec un guide (Ƶ 130) et un livre photo de tes meilleures images (Ƶ 240).'],
            'de' => ['Fotowochenende', 'Zwei Fotokurse (Ƶ 80), eine Leihkamera fürs Wochenende (Ƶ 90), ein Fotospaziergang mit Guide (Ƶ 130) und ein Fotobuch mit deinen besten Bildern (Ƶ 240).']],
        ['Concert with a friend', 560, 'band.svg',
            'Two tickets for a local band (Ƶ 180), the bus there and back (Ƶ 40), dinner before the show (Ƶ 80), a T-shirt (Ƶ 40) and a night in a youth hostel (Ƶ 220).',
            'es' => ['Concierto con un amigo', 'Dos entradas para un grupo local (Ƶ 180), el autobús de ida y vuelta (Ƶ 40), la cena antes del concierto (Ƶ 80), una camiseta (Ƶ 40) y una noche en un albergue juvenil (Ƶ 220).'],
            'fr' => ['Concert avec un ami', 'Deux billets pour un groupe local (Ƶ 180), le bus aller-retour (Ƶ 40), le dîner avant le concert (Ƶ 80), un T-shirt (Ƶ 40) et une nuit dans une auberge de jeunesse (Ƶ 220).'],
            'de' => ['Konzert mit einem Freund', 'Zwei Tickets für eine Band aus der Region (Ƶ 180), der Bus hin und zurück (Ƶ 40), Abendessen vor dem Konzert (Ƶ 80), ein T-Shirt (Ƶ 40) und eine Nacht in der Jugendherberge (Ƶ 220).']],
    ];

    public function up()
    {
        foreach (self::PLANS as $i => $plan) {
            [$name, $cost, $image, $description] = $plan;
            if (DB::table('wants_plans')->where('kind', 'mid')->where('name', $name)->exists()) {
                continue;
            }
            $translations = [];
            foreach (['es', 'fr', 'de'] as $lang) {
                $translations[$lang] = ['name' => $plan[$lang][0], 'description' => $plan[$lang][1]];
            }
            DB::table('wants_plans')->insert([
                'kind' => 'mid', 'name' => $name, 'description' => $description,
                'translations' => json_encode($translations, JSON_UNESCAPED_UNICODE),
                'cost' => $cost, 'times_per_month' => 1, 'image' => 'asset/front/images/wants-plans/' . $image,
                'is_active' => 1, 'sort_order' => $i + 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('wants_plans')->where('kind', 'mid')->whereIn('name', array_column(self::PLANS, 0))->delete();
    }
};
