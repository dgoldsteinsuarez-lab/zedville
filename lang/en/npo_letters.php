<?php

// The charities' thank-you letters for the monthly donation (App\Services\DonationService,
// mailbox_templates/npo-thanks). One story per donation, in turn: at the shelter a different animal
// writes each month; at the orphanage, a different child. :amount is the month's donation.
return [
    'what' => 'We are a charity, a non-profit organisation: everything we receive goes to helping others.',
    'next' => 'Your donation leaves your account every month. If you\'d like to help another charity, you can switch it in the bank.',
    'signed' => 'With love,',
    'animals' => [
        ['text' => 'Woof! I\'m Luna, a three-year-old dog. With your :amount this month I had food, a warm blanket and my vaccinations.', 'from' => 'Luna, a dog at the shelter'],
        ['text' => 'Miaow. I\'m Max, a cat who used to live on the street. Your :amount paid for my food, clean litter and a vet visit for my hurt paw.', 'from' => 'Max, a cat at the shelter'],
        ['text' => 'Hello, I\'m Coco, a very curious rabbit. With your :amount they bought fresh hay and a big hutch where I can hop around.', 'from' => 'Coco, a rabbit at the shelter'],
        ['text' => 'Tweet tweet! I\'m Kiwi, a budgie who arrived with no tail feathers. Thanks to your :amount I have seeds, fruit and a cage in the sun.', 'from' => 'Kiwi, a budgie at the shelter'],
        ['text' => 'I\'m Toby and I\'m twelve, so I\'m the grandad of the shelter. Your :amount paid for my joint medicine and a soft bed.', 'from' => 'Toby, a dog at the shelter'],
        ['text' => 'I\'m Mia and I had four kittens here at the shelter. With your :amount we all had food, and the little ones have had their first vaccinations.', 'from' => 'Mia, a cat at the shelter'],
        ['text' => 'I\'m Bruno, a tortoise someone left in a park. Your :amount paid for my vivarium with a heat lamp, which I need to stay healthy.', 'from' => 'Bruno, a tortoise at the shelter'],
        ['text' => 'Hi! I\'m Lola, a very chatty guinea pig. With your :amount I get fresh vegetables every day and a little wooden house to sleep in.', 'from' => 'Lola, a guinea pig at the shelter'],
        ['text' => 'I\'m Rocky and they found me lost in the woods. Your :amount paid for my microchip and collar, so if I get lost again, they\'ll find me.', 'from' => 'Rocky, a dog at the shelter'],
        ['text' => 'I\'m Pip, a hedgehog who was cold this winter. Thanks to your :amount they\'re looking after me until I\'m strong enough to go back to the woods.', 'from' => 'Pip, a hedgehog at the shelter'],
    ],
    'children' => [
        ['text' => 'My name is Leo and I\'m nine. With your :amount I could buy my school books and a dictionary all of my own.', 'from' => 'Leo, aged 9'],
        ['text' => 'I\'m Sara and I\'m eleven. Your :amount paid for my violin lessons, and on Friday I played in front of everyone for the first time.', 'from' => 'Sara, aged 11'],
        ['text' => 'I\'m Omar and I love football. With your :amount they bought me football boots in my size, and on Saturday I scored a goal.', 'from' => 'Omar, aged 10'],
        ['text' => 'My name is Nora and I\'m eight. Thanks to your :amount I went on a school trip to the science museum: we saw a dinosaur skeleton!', 'from' => 'Nora, aged 8'],
        ['text' => 'I\'m Iván and I couldn\'t see the board properly. Your :amount paid for my new glasses, and now I can read everything in class.', 'from' => 'Iván, aged 12'],
        ['text' => 'I\'m Aya and I love drawing. With your :amount I joined a painting class and got my first box of watercolours.', 'from' => 'Aya, aged 10'],
        ['text' => 'My name is Teo and I want to be a programmer. Your :amount paid for a computer course at the library, and I\'ve already made my first game.', 'from' => 'Teo, aged 13'],
        ['text' => 'I\'m Emma and I\'m seven. With your :amount I\'m learning to swim at the local pool, and I\'m not scared of the water any more!', 'from' => 'Emma, aged 7'],
        ['text' => 'I\'m Samuel and I play the keyboard. Thanks to your :amount I have piano lessons twice a week, and I\'m practising a song for the party.', 'from' => 'Samuel, aged 12'],
        ['text' => 'My name is Lina and I\'m nine. Your :amount got me a coat and boots for the winter, and I\'m not cold on my way to school any more.', 'from' => 'Lina, aged 9'],
    ],
    'elderly' => [
        ['text' => 'I\'m Rosa and I\'m eighty-two. With your :amount they bought tablets, and now I video-call my grandchildren every Sunday.', 'from' => 'Rosa'],
        ['text' => 'I\'m Antonio, the one who looks after the care home\'s vegetable garden. Your :amount paid for seeds and tools, and this month we ate our own tomatoes.', 'from' => 'Antonio'],
        ['text' => 'My name is Carmen and I love dancing. Thanks to your :amount we have a dance class on Thursdays, and my knees are grateful.', 'from' => 'Carmen'],
        ['text' => 'I\'m Pedro and I hadn\'t seen the sea for ten years. With your :amount we went on a trip to the beach, and I got my feet wet.', 'from' => 'Pedro'],
    ],
    'food' => [
        ['text' => 'We are the López family. With your :amount we had our shopping for the month: milk, rice, fruit and vegetables for all five of us.', 'from' => 'The López family'],
        ['text' => 'I\'m Marta, a volunteer at the food bank. Your :amount helped us make breakfast for 30 children before school.', 'from' => 'Marta, volunteer'],
        ['text' => 'I\'m Yusuf and I work in the warehouse. With your :amount we bought fresh fruit: it\'s what we get the least of and what families need the most.', 'from' => 'Yusuf, warehouse team'],
        ['text' => 'Hi, I\'m Daniela and I\'m ten. With your :amount my mum could make me a birthday cake, and I blew out the candles with my friends.', 'from' => 'Daniela, aged 10'],
    ],
    'education' => [
        ['text' => 'I\'m Ana, the librarian. With your :amount we bought twenty new books, and the adventure ones are all out on loan already.', 'from' => 'Ana, librarian'],
        ['text' => 'We are the Tuesday book club. Your :amount paid for this month\'s books and a snack to talk about them.', 'from' => 'The book club'],
        ['text' => 'I\'m Hugo and I do my homework at the library. With your :amount a teacher helps us with maths in the afternoons.', 'from' => 'Hugo, aged 11'],
        ['text' => 'It\'s Ana again, the librarian. Your :amount helped us buy a new computer so that everyone can study or look for a job.', 'from' => 'Ana, librarian'],
    ],
    'community' => [
        ['text' => 'We are the neighbourhood football team. With your :amount we have new shirts, and on Sunday we play our first match.', 'from' => 'The neighbourhood team'],
        ['text' => 'I\'m Lucía and I run the cooking workshop. Your :amount paid for the ingredients, and this month thirty neighbours learnt to cook healthy, cheap meals.', 'from' => 'Lucía, cooking workshop'],
        ['text' => 'I\'m Karim and I play the drums in the music room. Thanks to your :amount we repaired the instruments, and four bands rehearse there now.', 'from' => 'Karim, music room'],
        ['text' => 'We are the summer camp leaders. With your :amount, ten children who couldn\'t afford it will come this year.', 'from' => 'The camp leaders'],
    ],
    'environment' => [
        ['text' => 'We are the EcoFun team. With your :amount we planted fifteen trees in the park, and one has your name on a plaque.', 'from' => 'The EcoFun team'],
        ['text' => 'I\'m Nico, an EcoFun volunteer. Your :amount paid for gloves and bags, and on Saturday we pulled more than a hundred kilos of rubbish out of the river.', 'from' => 'Nico, volunteer'],
        ['text' => 'I\'m Valeria from EcoFun. With your :amount we put a solar panel on the school, which now uses less electricity.', 'from' => 'Valeria, EcoFun'],
        ['text' => 'It\'s the EcoFun team again. Thanks to your :amount we built an insect hotel for the bees in the park.', 'from' => 'The EcoFun team'],
    ],
];
