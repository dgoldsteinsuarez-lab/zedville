<?php

// Lettres de remerciement des associations pour le don mensuel (App\Services\DonationService,
// mailbox_templates/npo-thanks). Une histoire par don, à tour de rôle : au refuge, un animal différent
// écrit chaque mois ; à l'orphelinat, un enfant différent. :amount est le don du mois.
return [
    'what' => 'Nous sommes une association à but non lucratif : tout ce que nous recevons sert à aider les autres.',
    'next' => 'Ton don part de ton compte chaque mois. Si tu veux aider une autre association, tu peux en changer à la banque.',
    'signed' => 'Affectueusement,',
    'animals' => [
        ['text' => 'Wouf ! Je suis Luna, une chienne de trois ans. Grâce à tes :amount ce mois-ci, j\'ai eu à manger, une couverture bien chaude et mes vaccins.', 'from' => 'Luna, chienne du refuge'],
        ['text' => 'Miaou. Je suis Max, un chat qui vivait dans la rue. Tes :amount ont payé mes croquettes, une litière propre et une visite chez le vétérinaire pour ma patte blessée.', 'from' => 'Max, chat du refuge'],
        ['text' => 'Bonjour, je suis Coco, un lapin très curieux. Avec tes :amount, ils ont acheté du foin frais et un grand enclos où je peux sauter.', 'from' => 'Coco, lapin du refuge'],
        ['text' => 'Cui-cui ! Je suis Kiwi, une perruche arrivée sans plumes à la queue. Grâce à tes :amount, j\'ai des graines, des fruits et une cage au soleil.', 'from' => 'Kiwi, perruche du refuge'],
        ['text' => 'Je suis Toby et j\'ai douze ans : je suis le papy du refuge. Tes :amount ont payé mon médicament pour les articulations et un panier bien moelleux.', 'from' => 'Toby, chien du refuge'],
        ['text' => 'Je suis Mia et j\'ai eu quatre chatons ici, au refuge. Avec tes :amount, nous avons tous mangé, et les petits ont reçu leurs premiers vaccins.', 'from' => 'Mia, chatte du refuge'],
        ['text' => 'Je suis Bruno, une tortue que quelqu\'un a abandonnée dans un parc. Tes :amount ont payé mon terrarium avec une lampe chauffante, dont j\'ai besoin pour rester en bonne santé.', 'from' => 'Bruno, tortue du refuge'],
        ['text' => 'Coucou ! Je suis Lola, un cochon d\'Inde très bavard. Avec tes :amount, j\'ai des légumes frais tous les jours et une petite maison en bois pour dormir.', 'from' => 'Lola, cochon d\'Inde du refuge'],
        ['text' => 'Je suis Rocky et on m\'a trouvé perdu dans la forêt. Tes :amount ont payé ma puce électronique et mon collier : si je me perds encore, on me retrouvera.', 'from' => 'Rocky, chien du refuge'],
        ['text' => 'Je suis Pip, un hérisson qui avait froid cet hiver. Grâce à tes :amount, on me soigne jusqu\'à ce que je sois assez fort pour retourner dans la forêt.', 'from' => 'Pip, hérisson du refuge'],
    ],
    'children' => [
        ['text' => 'Je m\'appelle Leo et j\'ai neuf ans. Avec tes :amount, j\'ai pu acheter mes livres d\'école et un dictionnaire rien qu\'à moi.', 'from' => 'Leo, 9 ans'],
        ['text' => 'Je suis Sara et j\'ai onze ans. Tes :amount ont payé mes cours de violon, et vendredi j\'ai joué devant tout le monde pour la première fois.', 'from' => 'Sara, 11 ans'],
        ['text' => 'Je suis Omar et j\'adore le foot. Avec tes :amount, on m\'a acheté des chaussures de foot à ma taille, et samedi j\'ai marqué un but.', 'from' => 'Omar, 10 ans'],
        ['text' => 'Je m\'appelle Nora et j\'ai huit ans. Grâce à tes :amount, je suis allée au musée des sciences avec ma classe : on a vu un squelette de dinosaure !', 'from' => 'Nora, 8 ans'],
        ['text' => 'Je suis Iván et je ne voyais pas bien le tableau. Tes :amount ont payé mes nouvelles lunettes, et maintenant je lis tout en classe.', 'from' => 'Iván, 12 ans'],
        ['text' => 'Je suis Aya et j\'adore dessiner. Avec tes :amount, je me suis inscrite à un atelier de peinture et j\'ai ma première boîte d\'aquarelle.', 'from' => 'Aya, 10 ans'],
        ['text' => 'Je m\'appelle Teo et je veux devenir programmeur. Tes :amount ont payé un cours d\'informatique à la bibliothèque, et j\'ai déjà créé mon premier jeu.', 'from' => 'Teo, 13 ans'],
        ['text' => 'Je suis Emma et j\'ai sept ans. Avec tes :amount, j\'apprends à nager à la piscine du quartier, et je n\'ai plus peur de l\'eau !', 'from' => 'Emma, 7 ans'],
        ['text' => 'Je suis Samuel et je joue du clavier. Grâce à tes :amount, j\'ai des cours de piano deux fois par semaine, et je prépare une chanson pour la fête.', 'from' => 'Samuel, 12 ans'],
        ['text' => 'Je m\'appelle Lina et j\'ai neuf ans. Tes :amount m\'ont offert un manteau et des bottes pour l\'hiver, et je n\'ai plus froid en allant à l\'école.', 'from' => 'Lina, 9 ans'],
    ],
    'elderly' => [
        ['text' => 'Je suis Rosa et j\'ai quatre-vingt-deux ans. Avec tes :amount, ils ont acheté des tablettes, et maintenant j\'appelle mes petits-enfants en visio tous les dimanches.', 'from' => 'Rosa'],
        ['text' => 'Je suis Antonio, celui qui s\'occupe du potager de la maison de retraite. Tes :amount ont payé des graines et des outils, et ce mois-ci nous avons mangé nos propres tomates.', 'from' => 'Antonio'],
        ['text' => 'Je m\'appelle Carmen et j\'adore danser. Grâce à tes :amount, nous avons un cours de danse le jeudi, et mes genoux te remercient.', 'from' => 'Carmen'],
        ['text' => 'Je suis Pedro et je n\'avais pas vu la mer depuis dix ans. Avec tes :amount, nous sommes allés à la plage, et je me suis mouillé les pieds.', 'from' => 'Pedro'],
    ],
    'food' => [
        ['text' => 'Nous sommes la famille López. Avec tes :amount, nous avons eu les courses du mois : du lait, du riz, des fruits et des légumes pour nous cinq.', 'from' => 'La famille López'],
        ['text' => 'Je suis Marta, bénévole à la banque alimentaire. Tes :amount nous ont permis de préparer le petit-déjeuner de 30 enfants avant l\'école.', 'from' => 'Marta, bénévole'],
        ['text' => 'Je suis Yusuf et je travaille à l\'entrepôt. Avec tes :amount, nous avons acheté des fruits frais : c\'est ce que nous recevons le moins et ce dont les familles ont le plus besoin.', 'from' => 'Yusuf, de l\'entrepôt'],
        ['text' => 'Coucou, je suis Daniela et j\'ai dix ans. Avec tes :amount, ma mère a pu me faire un gâteau d\'anniversaire, et j\'ai soufflé les bougies avec mes amis.', 'from' => 'Daniela, 10 ans'],
    ],
    'education' => [
        ['text' => 'Je suis Ana, la bibliothécaire. Avec tes :amount, nous avons acheté vingt nouveaux livres, et ceux d\'aventure sont déjà tous empruntés.', 'from' => 'Ana, bibliothécaire'],
        ['text' => 'Nous sommes le club de lecture du mardi. Tes :amount ont payé les livres du mois et un goûter pour en parler.', 'from' => 'Le club de lecture'],
        ['text' => 'Je suis Hugo et je fais mes devoirs à la bibliothèque. Avec tes :amount, un professeur nous aide en maths l\'après-midi.', 'from' => 'Hugo, 11 ans'],
        ['text' => 'C\'est encore Ana, la bibliothécaire. Tes :amount nous ont aidés à acheter un nouvel ordinateur pour que tout le monde puisse étudier ou chercher un emploi.', 'from' => 'Ana, bibliothécaire'],
    ],
    'community' => [
        ['text' => 'Nous sommes l\'équipe de foot du quartier. Avec tes :amount, nous avons de nouveaux maillots, et dimanche nous jouons notre premier match.', 'from' => 'L\'équipe du quartier'],
        ['text' => 'Je suis Lucía et j\'anime l\'atelier cuisine. Tes :amount ont payé les ingrédients, et ce mois-ci trente voisins ont appris à cuisiner sain et pas cher.', 'from' => 'Lucía, atelier cuisine'],
        ['text' => 'Je suis Karim et je joue de la batterie dans la salle de musique. Grâce à tes :amount, nous avons réparé les instruments, et quatre groupes y répètent maintenant.', 'from' => 'Karim, salle de musique'],
        ['text' => 'Nous sommes les animateurs de la colonie de vacances. Avec tes :amount, dix enfants qui ne pouvaient pas la payer viendront cette année.', 'from' => 'Les animateurs de la colonie'],
    ],
    'environment' => [
        ['text' => 'Nous sommes l\'équipe d\'EcoFun. Avec tes :amount, nous avons planté quinze arbres dans le parc, et l\'un d\'eux porte ton nom sur une plaque.', 'from' => 'L\'équipe d\'EcoFun'],
        ['text' => 'Je suis Nico, bénévole chez EcoFun. Tes :amount ont payé des gants et des sacs, et samedi nous avons sorti plus de cent kilos de déchets de la rivière.', 'from' => 'Nico, bénévole'],
        ['text' => 'Je suis Valeria, d\'EcoFun. Avec tes :amount, nous avons installé un panneau solaire sur l\'école, qui consomme maintenant moins d\'électricité.', 'from' => 'Valeria, EcoFun'],
        ['text' => 'C\'est encore l\'équipe d\'EcoFun. Grâce à tes :amount, nous avons construit un hôtel à insectes pour les abeilles du parc.', 'from' => 'L\'équipe d\'EcoFun'],
    ],
];
