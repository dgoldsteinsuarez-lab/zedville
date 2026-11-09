<?php

// Cartas de agradecimiento de las ONG por la donación mensual (App\Services\DonationService,
// mailbox_templates/npo-thanks). Una historia por donación, por turnos: en el refugio escribe cada
// mes un animal distinto; en el orfanato, un niño o una niña distintos. :amount es la donación del mes.
return [
    'what' => 'Somos una ONG, una organización sin ánimo de lucro: todo lo que recibimos lo usamos para ayudar.',
    'next' => 'Tu donación sale de tu cuenta cada mes. Si quieres ayudar a otra ONG, puedes cambiarla en el banco.',
    'signed' => 'Con cariño,',
    'animals' => [
        ['text' => '¡Guau! Soy Luna, una perra de tres años. Con tus :amount de este mes he tenido comida, una manta calentita y mis vacunas.', 'from' => 'Luna, perra del refugio'],
        ['text' => 'Miau. Soy Max, un gato que vivía en la calle. Tus :amount han pagado mi pienso, arena limpia y la visita al veterinario por mi pata herida.', 'from' => 'Max, gato del refugio'],
        ['text' => 'Hola, soy Coco, un conejo muy curioso. Con tus :amount han comprado heno fresco y una jaula grande en la que puedo saltar.', 'from' => 'Coco, conejo del refugio'],
        ['text' => '¡Pío, pío! Soy Kiwi, un periquito que llegó sin plumas en la cola. Gracias a tus :amount tengo semillas, fruta y una jaula al sol.', 'from' => 'Kiwi, periquito del refugio'],
        ['text' => 'Soy Toby y tengo doce años: soy el abuelo del refugio. Tus :amount han pagado mi medicina para las articulaciones y una cama blandita.', 'from' => 'Toby, perro del refugio'],
        ['text' => 'Soy Mía y aquí, en el refugio, tuve cuatro gatitos. Con tus :amount hemos comido todos, y los pequeños ya tienen sus primeras vacunas.', 'from' => 'Mía, gata del refugio'],
        ['text' => 'Soy Bruno, una tortuga que alguien abandonó en un parque. Tus :amount han pagado mi terrario con lámpara de calor, que necesito para estar sano.', 'from' => 'Bruno, tortuga del refugio'],
        ['text' => '¡Hola! Soy Lola, una cobaya muy parlanchina. Con tus :amount tengo verdura fresca todos los días y una casita de madera para dormir.', 'from' => 'Lola, cobaya del refugio'],
        ['text' => 'Soy Rocky y me encontraron perdido en el bosque. Tus :amount han pagado mi microchip y mi collar: si me vuelvo a perder, me encontrarán.', 'from' => 'Rocky, perro del refugio'],
        ['text' => 'Soy Pip, un erizo que pasaba frío este invierno. Gracias a tus :amount me cuidan hasta que esté fuerte para volver al bosque.', 'from' => 'Pip, erizo del refugio'],
    ],
    'children' => [
        ['text' => 'Me llamo Leo y tengo nueve años. Con tus :amount he podido comprar los libros del colegio y un diccionario solo para mí.', 'from' => 'Leo, 9 años'],
        ['text' => 'Soy Sara y tengo once años. Tus :amount han pagado mis clases de violín, y el viernes toqué por primera vez delante de todos.', 'from' => 'Sara, 11 años'],
        ['text' => 'Soy Omar y me encanta el fútbol. Con tus :amount me han comprado unas botas de mi talla, y el sábado marqué un gol.', 'from' => 'Omar, 10 años'],
        ['text' => 'Me llamo Nora y tengo ocho años. Gracias a tus :amount fui de excursión con mi clase al museo de ciencias: ¡vimos el esqueleto de un dinosaurio!', 'from' => 'Nora, 8 años'],
        ['text' => 'Soy Iván y no veía bien la pizarra. Tus :amount han pagado mis gafas nuevas, y ahora en clase lo leo todo.', 'from' => 'Iván, 12 años'],
        ['text' => 'Soy Aya y me encanta dibujar. Con tus :amount me he apuntado a un taller de pintura y tengo mi primera caja de acuarelas.', 'from' => 'Aya, 10 años'],
        ['text' => 'Me llamo Teo y quiero ser programador. Tus :amount han pagado un curso de informática en la biblioteca, y ya he hecho mi primer videojuego.', 'from' => 'Teo, 13 años'],
        ['text' => 'Soy Emma y tengo siete años. Con tus :amount aprendo a nadar en la piscina del barrio, ¡y ya no me da miedo el agua!', 'from' => 'Emma, 7 años'],
        ['text' => 'Soy Samuel y toco el teclado. Gracias a tus :amount voy a clases de piano dos veces por semana y estoy preparando una canción para la fiesta.', 'from' => 'Samuel, 12 años'],
        ['text' => 'Me llamo Lina y tengo nueve años. Tus :amount me han dado un abrigo y unas botas para el invierno, y ya no paso frío de camino al colegio.', 'from' => 'Lina, 9 años'],
    ],
    'elderly' => [
        ['text' => 'Soy Rosa y tengo ochenta y dos años. Con tus :amount han comprado tabletas, y ahora hago videollamadas con mis nietos todos los domingos.', 'from' => 'Rosa'],
        ['text' => 'Soy Antonio, el que cuida el huerto de la residencia. Tus :amount han pagado semillas y herramientas, y este mes hemos comido nuestros propios tomates.', 'from' => 'Antonio'],
        ['text' => 'Me llamo Carmen y me encanta bailar. Gracias a tus :amount tenemos clase de baile los jueves, y mis rodillas te lo agradecen.', 'from' => 'Carmen'],
        ['text' => 'Soy Pedro y llevaba diez años sin ver el mar. Con tus :amount fuimos de excursión a la playa, y me mojé los pies.', 'from' => 'Pedro'],
    ],
    'food' => [
        ['text' => 'Somos la familia López. Con tus :amount hemos tenido la compra del mes: leche, arroz, fruta y verdura para los cinco.', 'from' => 'La familia López'],
        ['text' => 'Soy Marta, voluntaria del banco de alimentos. Tus :amount han servido para preparar el desayuno de 30 niños antes del colegio.', 'from' => 'Marta, voluntaria'],
        ['text' => 'Soy Yusuf y trabajo en el almacén. Con tus :amount hemos comprado fruta fresca: es lo que menos nos llega y lo que más necesitan las familias.', 'from' => 'Yusuf, del almacén'],
        ['text' => 'Hola, soy Daniela y tengo diez años. Con tus :amount mi madre pudo hacerme una tarta de cumpleaños, y soplé las velas con mis amigos.', 'from' => 'Daniela, 10 años'],
    ],
    'education' => [
        ['text' => 'Soy Ana, la bibliotecaria. Con tus :amount hemos comprado veinte libros nuevos, y los de aventuras ya están todos prestados.', 'from' => 'Ana, bibliotecaria'],
        ['text' => 'Somos el club de lectura de los martes. Tus :amount han pagado los libros de este mes y una merienda para comentarlos.', 'from' => 'El club de lectura'],
        ['text' => 'Soy Hugo y hago los deberes en la biblioteca. Con tus :amount, por las tardes un profesor nos ayuda con las matemáticas.', 'from' => 'Hugo, 11 años'],
        ['text' => 'Soy Ana otra vez, la bibliotecaria. Tus :amount nos han ayudado a comprar un ordenador nuevo para que todo el mundo pueda estudiar o buscar trabajo.', 'from' => 'Ana, bibliotecaria'],
    ],
    'community' => [
        ['text' => 'Somos el equipo de fútbol del barrio. Con tus :amount tenemos camisetas nuevas, y el domingo jugamos nuestro primer partido.', 'from' => 'El equipo del barrio'],
        ['text' => 'Soy Lucía y doy el taller de cocina. Tus :amount han pagado los ingredientes, y este mes treinta vecinos han aprendido a cocinar sano y barato.', 'from' => 'Lucía, taller de cocina'],
        ['text' => 'Soy Karim y toco la batería en la sala de música. Gracias a tus :amount hemos arreglado los instrumentos, y ya ensayan cuatro grupos.', 'from' => 'Karim, sala de música'],
        ['text' => 'Somos los monitores del campamento de verano. Con tus :amount, este año vendrán diez niños que no podían pagarlo.', 'from' => 'Los monitores del campamento'],
    ],
    'environment' => [
        ['text' => 'Somos el equipo de EcoFun. Con tus :amount hemos plantado quince árboles en el parque, y uno lleva tu nombre en una placa.', 'from' => 'El equipo de EcoFun'],
        ['text' => 'Soy Nico, voluntario de EcoFun. Tus :amount han pagado guantes y bolsas, y el sábado sacamos del río más de cien kilos de basura.', 'from' => 'Nico, voluntario'],
        ['text' => 'Soy Valeria, de EcoFun. Con tus :amount hemos puesto un panel solar en el colegio, que ahora gasta menos luz.', 'from' => 'Valeria, de EcoFun'],
        ['text' => 'Somos otra vez el equipo de EcoFun. Gracias a tus :amount hemos construido un hotel de insectos para las abejas del parque.', 'from' => 'El equipo de EcoFun'],
    ],
];
