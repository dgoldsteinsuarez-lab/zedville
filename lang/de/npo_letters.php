<?php

// Dankesbriefe der Hilfsorganisationen für die monatliche Spende (App\Services\DonationService,
// mailbox_templates/npo-thanks). Eine Geschichte pro Spende, der Reihe nach: Im Tierheim schreibt jeden
// Monat ein anderes Tier, im Kinderheim ein anderes Kind. :amount ist die Spende des Monats.
return [
    'what' => 'Wir sind eine gemeinnützige Organisation: Alles, was wir bekommen, setzen wir ein, um anderen zu helfen.',
    'next' => 'Deine Spende geht jeden Monat von deinem Konto ab. Wenn du einer anderen Organisation helfen möchtest, kannst du sie in der Bank wechseln.',
    'signed' => 'Herzliche Grüße',
    'animals' => [
        ['text' => 'Wuff! Ich bin Luna, eine dreijährige Hündin. Mit deinen :amount hatte ich diesen Monat Futter, eine warme Decke und meine Impfungen.', 'from' => 'Luna, Hündin im Tierheim'],
        ['text' => 'Miau. Ich bin Max, ein Kater, der früher auf der Straße gelebt hat. Deine :amount haben mein Futter, frische Katzenstreu und einen Tierarztbesuch für meine verletzte Pfote bezahlt.', 'from' => 'Max, Kater im Tierheim'],
        ['text' => 'Hallo, ich bin Coco, ein sehr neugieriges Kaninchen. Mit deinen :amount haben sie frisches Heu und ein großes Gehege gekauft, in dem ich hoppeln kann.', 'from' => 'Coco, Kaninchen im Tierheim'],
        ['text' => 'Piep, piep! Ich bin Kiwi, ein Wellensittich, der ohne Schwanzfedern hier ankam. Dank deiner :amount habe ich Körner, Obst und einen Käfig in der Sonne.', 'from' => 'Kiwi, Wellensittich im Tierheim'],
        ['text' => 'Ich bin Toby und zwölf Jahre alt, also der Opa im Tierheim. Deine :amount haben meine Medizin für die Gelenke und ein weiches Körbchen bezahlt.', 'from' => 'Toby, Hund im Tierheim'],
        ['text' => 'Ich bin Mia und habe hier im Tierheim vier Kätzchen bekommen. Mit deinen :amount sind wir alle satt geworden, und die Kleinen haben ihre ersten Impfungen bekommen.', 'from' => 'Mia, Katze im Tierheim'],
        ['text' => 'Ich bin Bruno, eine Schildkröte, die jemand in einem Park ausgesetzt hat. Deine :amount haben mein Terrarium mit Wärmelampe bezahlt, die ich brauche, um gesund zu bleiben.', 'from' => 'Bruno, Schildkröte im Tierheim'],
        ['text' => 'Hallo! Ich bin Lola, ein sehr gesprächiges Meerschweinchen. Mit deinen :amount bekomme ich jeden Tag frisches Gemüse und habe ein kleines Holzhaus zum Schlafen.', 'from' => 'Lola, Meerschweinchen im Tierheim'],
        ['text' => 'Ich bin Rocky, und man hat mich verirrt im Wald gefunden. Deine :amount haben meinen Mikrochip und mein Halsband bezahlt: Wenn ich mich wieder verlaufe, findet man mich.', 'from' => 'Rocky, Hund im Tierheim'],
        ['text' => 'Ich bin Pip, ein Igel, dem diesen Winter kalt war. Dank deiner :amount werde ich gepflegt, bis ich stark genug bin, um zurück in den Wald zu gehen.', 'from' => 'Pip, Igel im Tierheim'],
    ],
    'children' => [
        ['text' => 'Ich heiße Leo und bin neun Jahre alt. Mit deinen :amount konnte ich meine Schulbücher und ein eigenes Wörterbuch kaufen.', 'from' => 'Leo, 9 Jahre'],
        ['text' => 'Ich bin Sara und elf Jahre alt. Deine :amount haben meinen Geigenunterricht bezahlt, und am Freitag habe ich zum ersten Mal vor allen gespielt.', 'from' => 'Sara, 11 Jahre'],
        ['text' => 'Ich bin Omar und liebe Fußball. Mit deinen :amount haben sie mir Fußballschuhe in meiner Größe gekauft, und am Samstag habe ich ein Tor geschossen.', 'from' => 'Omar, 10 Jahre'],
        ['text' => 'Ich heiße Nora und bin acht Jahre alt. Dank deiner :amount war ich mit meiner Klasse im Naturkundemuseum: Wir haben ein Dinosaurierskelett gesehen!', 'from' => 'Nora, 8 Jahre'],
        ['text' => 'Ich bin Iván und konnte die Tafel nicht gut sehen. Deine :amount haben meine neue Brille bezahlt, und jetzt kann ich im Unterricht alles lesen.', 'from' => 'Iván, 12 Jahre'],
        ['text' => 'Ich bin Aya und male sehr gern. Mit deinen :amount habe ich mich für einen Malkurs angemeldet und meinen ersten Aquarellkasten bekommen.', 'from' => 'Aya, 10 Jahre'],
        ['text' => 'Ich heiße Teo und möchte Programmierer werden. Deine :amount haben einen Computerkurs in der Bibliothek bezahlt, und ich habe schon mein erstes Spiel programmiert.', 'from' => 'Teo, 13 Jahre'],
        ['text' => 'Ich bin Emma und sieben Jahre alt. Mit deinen :amount lerne ich im Schwimmbad bei uns im Viertel schwimmen, und ich habe keine Angst mehr vor dem Wasser!', 'from' => 'Emma, 7 Jahre'],
        ['text' => 'Ich bin Samuel und spiele Keyboard. Dank deiner :amount habe ich zweimal pro Woche Klavierunterricht und übe ein Lied für das Fest.', 'from' => 'Samuel, 12 Jahre'],
        ['text' => 'Ich heiße Lina und bin neun Jahre alt. Mit deinen :amount habe ich einen Mantel und Stiefel für den Winter bekommen, und jetzt friere ich auf dem Schulweg nicht mehr.', 'from' => 'Lina, 9 Jahre'],
    ],
    'elderly' => [
        ['text' => 'Ich bin Rosa und 82 Jahre alt. Mit deinen :amount wurden Tablets gekauft, und jetzt mache ich jeden Sonntag Videoanrufe mit meinen Enkeln.', 'from' => 'Rosa'],
        ['text' => 'Ich bin Antonio und kümmere mich um den Gemüsegarten im Seniorenheim. Deine :amount haben Samen und Werkzeug bezahlt, und diesen Monat haben wir unsere eigenen Tomaten gegessen.', 'from' => 'Antonio'],
        ['text' => 'Ich heiße Carmen und tanze für mein Leben gern. Dank deiner :amount haben wir donnerstags einen Tanzkurs, und meine Knie danken es dir.', 'from' => 'Carmen'],
        ['text' => 'Ich bin Pedro und hatte das Meer seit zehn Jahren nicht gesehen. Mit deinen :amount haben wir einen Ausflug an den Strand gemacht, und ich habe meine Füße ins Wasser gehalten.', 'from' => 'Pedro'],
    ],
    'food' => [
        ['text' => 'Wir sind Familie López. Mit deinen :amount hatten wir den Einkauf für den ganzen Monat: Milch, Reis, Obst und Gemüse für uns fünf.', 'from' => 'Familie López'],
        ['text' => 'Ich bin Marta und helfe ehrenamtlich bei der Tafel. Mit deinen :amount konnten wir für 30 Kinder ein Frühstück vor der Schule zubereiten.', 'from' => 'Marta, Ehrenamtliche'],
        ['text' => 'Ich bin Yusuf und arbeite im Lager. Mit deinen :amount haben wir frisches Obst gekauft: Davon bekommen wir am wenigsten, und die Familien brauchen es am meisten.', 'from' => 'Yusuf, aus dem Lager'],
        ['text' => 'Hallo, ich bin Daniela und zehn Jahre alt. Mit deinen :amount konnte meine Mutter mir einen Geburtstagskuchen backen, und ich habe mit meinen Freunden die Kerzen ausgepustet.', 'from' => 'Daniela, 10 Jahre'],
    ],
    'education' => [
        ['text' => 'Ich bin Ana, die Bibliothekarin. Mit deinen :amount haben wir zwanzig neue Bücher gekauft, und die Abenteuerbücher sind schon alle ausgeliehen.', 'from' => 'Ana, Bibliothekarin'],
        ['text' => 'Wir sind der Lesekreis vom Dienstag. Deine :amount haben die Bücher dieses Monats bezahlt und einen Imbiss, bei dem wir darüber sprechen.', 'from' => 'Der Lesekreis'],
        ['text' => 'Ich bin Hugo und mache meine Hausaufgaben in der Bibliothek. Mit deinen :amount hilft uns nachmittags ein Lehrer in Mathe.', 'from' => 'Hugo, 11 Jahre'],
        ['text' => 'Hier ist noch einmal Ana, die Bibliothekarin. Deine :amount haben uns geholfen, einen neuen Computer zu kaufen, damit alle lernen oder eine Arbeit suchen können.', 'from' => 'Ana, Bibliothekarin'],
    ],
    'community' => [
        ['text' => 'Wir sind die Fußballmannschaft aus dem Viertel. Mit deinen :amount haben wir neue Trikots, und am Sonntag spielen wir unser erstes Spiel.', 'from' => 'Die Mannschaft aus dem Viertel'],
        ['text' => 'Ich bin Lucía und leite den Kochkurs. Deine :amount haben die Zutaten bezahlt, und diesen Monat haben dreißig Nachbarinnen und Nachbarn gelernt, gesund und günstig zu kochen.', 'from' => 'Lucía, Kochkurs'],
        ['text' => 'Ich bin Karim und spiele Schlagzeug im Musikraum. Dank deiner :amount haben wir die Instrumente reparieren lassen, und jetzt proben dort vier Bands.', 'from' => 'Karim, Musikraum'],
        ['text' => 'Wir betreuen das Ferienlager. Mit deinen :amount können dieses Jahr zehn Kinder mitfahren, die es sich sonst nicht leisten könnten.', 'from' => 'Das Team vom Ferienlager'],
    ],
    'environment' => [
        ['text' => 'Wir sind das Team von EcoFun. Mit deinen :amount haben wir fünfzehn Bäume im Park gepflanzt, und einer trägt deinen Namen auf einem Schild.', 'from' => 'Das EcoFun-Team'],
        ['text' => 'Ich bin Nico und helfe ehrenamtlich bei EcoFun. Deine :amount haben Handschuhe und Säcke bezahlt, und am Samstag haben wir über hundert Kilo Müll aus dem Fluss geholt.', 'from' => 'Nico, Ehrenamtlicher'],
        ['text' => 'Ich bin Valeria von EcoFun. Mit deinen :amount haben wir ein Solarmodul auf dem Schuldach angebracht, und die Schule verbraucht jetzt weniger Strom.', 'from' => 'Valeria, EcoFun'],
        ['text' => 'Hier ist noch einmal das EcoFun-Team. Dank deiner :amount haben wir ein Insektenhotel für die Bienen im Park gebaut.', 'from' => 'Das EcoFun-Team'],
    ],
];
