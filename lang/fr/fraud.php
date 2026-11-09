<?php

// Les fraudes du niveau Intermédiaire (App\Services\FraudService) : l'appel à l'écran, le faux courriel du
// Département d'éducation financière, la fausse page, ce qui s'est passé et l'aide de la banque. Termes :
// hameçonnage (phishing), fraude au faux conseiller bancaire, usurpation de numéro (spoofing),
// escroquerie « Coucou maman », mule bancaire ; « Au moindre doute, raccrochons ! ».
return [
    // l'appel entrant (partials/fraud-call)
    'call_incoming' => 'Appel entrant',
    'call_answer' => 'Répondre',
    'call_decline' => 'Refuser',
    'call_choose' => 'Que fais-tu ?',
    'call_close' => 'Fermer',
    'callers' => [
        'bank_code' => 'Universal Bank · Sécurité',
        'safe_account' => 'Universal Bank · Sécurité',
        'tech_support' => 'Support technique',
        'family' => 'Numéro inconnu',
    ],
    'lines' => [
        'bank_code' => ['Bonjour, ici le service sécurité d\'Universal Bank. Nous avons bloqué un paiement de :charge avec votre carte.', 'Pour l\'annuler, lisez-moi le code que nous venons de vous envoyer par SMS. Vite, sinon le paiement passera.'],
        'safe_account' => ['Bonjour, ici le service sécurité d\'Universal Bank. Quelqu\'un essaie d\'entrer dans votre compte en ce moment.', 'Pour protéger votre argent, virez-le sur ce compte sécurisé : ZV00 1234 5678. Faites-le tout de suite, avant qu\'on vide le vôtre.'],
        'tech_support' => ['Bonjour, ici le support technique. Votre ordinateur a un virus qui nous envoie vos données bancaires.', 'Installez le programme que je vous envoie et nous l\'enlèverons. Ça coûte :repair, par carte.'],
        'family' => ['Coucou, c\'est moi, ton cousin ! J\'ai perdu mon téléphone et voici mon nouveau numéro.', 'Je dois payer quelque chose d\'urgent et ma carte ne marche pas. Tu peux me faire un virement ? Je te rembourse demain.'],
    ],
    'answers' => [
        'hang_up_call_bank' => 'Je raccroche et j\'appelle moi-même la banque, à son numéro officiel',
        'give_code' => 'Je lui lis le code pour annuler le paiement',
        'ask_then_give' => 'Je lui demande son nom et, s\'il me le dit, je lui donne le code',
        'hang_up_safe' => 'Je raccroche : ma banque ne me demanderait jamais de déplacer mon argent',
        'move_all' => 'Je vire tout mon argent sur le compte sécurisé',
        'move_half' => 'Je n\'en vire que la moitié, au cas où',
        'hang_up_tech' => 'Je raccroche : je n\'ai demandé d\'aide à personne',
        'install' => 'J\'installe le programme pour qu\'ils enlèvent le virus',
        'pay_fix' => 'Je paie la réparation avec ma carte',
        'call_usual' => 'Avant tout, j\'appelle mon cousin à son numéro habituel',
        'transfer' => 'Je lui fais le virement : c\'est urgent',
        'ask_amount' => 'Je lui demande combien il lui faut et je l\'envoie',
    ],

    // le faux courriel (mailbox_templates/fraud-*)
    'email_from' => 'De',
    'email_dept' => 'Département d\'éducation financière',
    'email_report' => 'Signaler comme fraude',
    'emails' => [
        'verify' => ['subject' => 'URGENT : votre compte va être bloqué', 'hello' => 'Cher utilisateur,',
            'text' => 'Nous avons détecté un problème sur votre compte Zedville. Si vous ne le vérifiez pas dans les 24 heures, il sera bloqué et vous ne toucherez pas votre salaire.', 'button' => 'Vérifier mon compte'],
        'refund' => ['subject' => 'Vous avez un remboursement de :refund', 'hello' => 'Cher élève,',
            'text' => 'Un remboursement de :refund vous attend. Vérifiez votre compte aujourd\'hui pour le recevoir : après, il sera annulé.', 'button' => 'Recevoir mon remboursement'],
        'prize' => ['subject' => 'Félicitations ! Vous avez gagné un téléphone', 'hello' => 'Cher gagnant,',
            'text' => 'Vous avez gagné un téléphone neuf au tirage au sort du Département. Pour le recevoir, payez seulement :prize_fee de frais d\'envoi dans les 48 heures.', 'button' => 'Payer l\'envoi'],
        'parcel' => ['subject' => 'Votre matériel scolaire est bloqué', 'hello' => 'Cher élève,',
            'text' => 'Votre colis de matériel scolaire est bloqué. Payez :parcel_fee de frais aujourd\'hui, sinon il sera renvoyé.', 'button' => 'Payer les frais'],
        'job' => ['subject' => 'Un job pour les élèves : gagnez :pay par virement', 'hello' => 'Bonjour !',
            'text' => 'Le Département cherche des élèves pour recevoir des virements sur leur compte et les renvoyer vers d\'autres comptes. Vous gardez :pay pour chacun. Aucune expérience nécessaire.', 'button' => 'Je m\'inscris'],
    ],

    // la fausse page (fraud/fake)
    'fake_title' => 'Vérifiez votre compte',
    'fake_text' => 'Appuyez sur le bouton pour terminer. Ça ne prend qu\'une seconde.',

    // ce qui s'est passé (la fin de l'appel, fraud/lesson)
    'safe_title' => 'Bien vu !',
    'safe_text' => 'C\'était une fraude et tu as eu le bon réflexe : tu gagnes un point de participation.',
    'fell_title' => 'C\'était une fraude',
    'fell_text' => 'Les escrocs t\'ont pris :amount. Couvre-le avec ton épargne de précaution ou avec un prêt sous 30 jours, dans « :activity ».',
    'go_resolve' => 'Le couvrir',
    'name' => 'Son nom : :name.',
    'signs_title' => 'Les signes',
    'good_title' => 'Ce qu\'il fallait faire',
    'names' => [
        'bank_code' => 'la fraude au faux conseiller bancaire',
        'safe_account' => 'la fraude au faux conseiller bancaire',
        'tech_support' => 'l\'arnaque au faux support technique',
        'family' => 'l\'escroquerie « Coucou maman »',
        'verify' => 'l\'hameçonnage (phishing), la fraude par courriel',
        'refund' => 'l\'hameçonnage (phishing), la fraude par courriel',
        'prize' => 'l\'hameçonnage (phishing), la fraude par courriel',
        'parcel' => 'l\'hameçonnage (phishing), la fraude par courriel',
        'job' => 'devenir une mule bancaire',
    ],
    'signs' => [
        'bank_code' => 'On te demande un code et on te presse. Le numéro était celui de la banque, mais un numéro peut être falsifié (usurpation de numéro ou spoofing).',
        'safe_account' => 'Personne à la banque ne te demande de déplacer ton argent. Le numéro était celui de la banque, mais un numéro peut être falsifié (usurpation de numéro ou spoofing).',
        'tech_support' => 'Un appel que tu n\'as pas demandé, et on veut que tu installes quelque chose et que tu paies.',
        'family' => 'Un nouveau numéro et une urgence d\'argent.',
        'verify' => 'L\'adresse n\'est pas celle du Département (un 1 à la place d\'un l), on ne dit pas ton nom et on te presse.',
        'refund' => 'De l\'argent que tu n\'attendais pas, de l\'urgence et un lien pour « vérifier » ton compte.',
        'prize' => 'Il faut payer pour recevoir un prix.',
        'parcel' => 'Un petit paiement par un lien, dans l\'urgence.',
        'job' => 'De l\'argent facile pour faire circuler l\'argent des autres.',
    ],
    'good' => [
        'bank_code' => 'Raccrocher et appeler toi-même la banque, à son numéro officiel (:phone).',
        'safe_account' => 'Raccrocher. Ta banque ne te demande jamais de déplacer ton argent.',
        'tech_support' => 'Raccrocher.',
        'family' => 'Appeler son numéro habituel ou lui poser une question que seule cette personne connaît.',
        'verify' => 'Ne pas cliquer sur le lien : le signaler comme fraude. Le Département ne te demande jamais de vérifier ton compte.',
        'refund' => 'Le supprimer et le signaler.',
        'prize' => 'L\'ignorer.',
        'parcel' => 'Ne pas cliquer : vérifier sur le site officiel.',
        'job' => 'Dire non : c\'est devenir une mule bancaire, et c\'est un délit.',
    ],
    'slogan' => 'Au moindre doute, raccrochons !',
    'back' => 'Retour à la boîte aux lettres',

    // l'aide de la banque (bank/bank-help)
    'bank_contact_title' => 'Le numéro d\'Universal Bank',
    'bank_contact_text' => 'Appelle-nous au :phone. Nous ne te demanderons jamais de code, de mot de passe, ni de déplacer ton argent.',
];
