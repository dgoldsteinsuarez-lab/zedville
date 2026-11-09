<?php

// The frauds of the Intermediate (App\Services\FraudService): the call on screen, the fake email from the
// Education Finance Department, the fake page, what happened and the bank's help. Terms: phishing, vishing,
// number spoofing, the 'Hi Mum' scam, money mule; Take Five.
return [
    // the incoming call (partials/fraud-call)
    'call_incoming' => 'Incoming call',
    'call_answer' => 'Answer',
    'call_decline' => 'Decline',
    'call_choose' => 'What do you do?',
    'call_close' => 'Close',
    'callers' => [
        'bank_code' => 'Universal Bank · Security',
        'safe_account' => 'Universal Bank · Security',
        'tech_support' => 'Technical support',
        'family' => 'Unknown number',
    ],
    'lines' => [
        'bank_code' => ['Hello, this is Universal Bank\'s security department. We have stopped a payment of :charge with your card.', 'To cancel it, read me the code we have just sent you by SMS. Quickly, or the payment will go through.'],
        'safe_account' => ['Hello, this is Universal Bank\'s security department. Someone is trying to get into your account right now.', 'To protect your money, move it to this safe account: ZV00 1234 5678. Do it now, before they empty yours.'],
        'tech_support' => ['Hello, this is technical support. Your computer has a virus that is sending us your bank details.', 'Install the program I am sending you and we will remove it. It costs :repair, by card.'],
        'family' => ['Hi, it\'s me, your cousin! I lost my phone and this is my new number.', 'I have to pay something urgently and my card doesn\'t work. Can you transfer me some money? I\'ll pay you back tomorrow.'],
    ],
    'answers' => [
        'hang_up_call_bank' => 'I hang up and call the bank myself, on its official number',
        'give_code' => 'I read them the code to cancel the payment',
        'ask_then_give' => 'I ask for their name and, if they tell me, I give them the code',
        'hang_up_safe' => 'I hang up: my bank would never ask me to move my money',
        'move_all' => 'I move all my money to the safe account',
        'move_half' => 'I move only half of it, just in case',
        'hang_up_tech' => 'I hang up: I didn\'t ask anyone for help',
        'install' => 'I install the program so they remove the virus',
        'pay_fix' => 'I pay for the repair with my card',
        'call_usual' => 'Before anything, I call my cousin\'s usual number',
        'transfer' => 'I transfer the money: it\'s urgent',
        'ask_amount' => 'I ask how much they need and send it',
    ],

    // the fake email (mailbox_templates/fraud-*)
    'email_from' => 'From',
    'email_dept' => 'Education Finance Department',
    'email_report' => 'Report as fraud',
    'emails' => [
        'verify' => ['subject' => 'URGENT: your account will be blocked', 'hello' => 'Dear user,',
            'text' => 'We have detected a problem with your Zedville account. If you don\'t verify it within 24 hours, it will be blocked and you won\'t receive your salary.', 'button' => 'Verify my account'],
        'refund' => ['subject' => 'You have a refund of :refund', 'hello' => 'Dear student,',
            'text' => 'You have a refund of :refund waiting. Verify your account today to receive it: after that, it will be cancelled.', 'button' => 'Get my refund'],
        'prize' => ['subject' => 'Congratulations! You have won a mobile phone', 'hello' => 'Dear winner,',
            'text' => 'You have won a brand-new mobile phone in the Department\'s draw. To receive it, just pay :prize_fee for delivery within 48 hours.', 'button' => 'Pay the delivery'],
        'parcel' => ['subject' => 'Your school materials are on hold', 'hello' => 'Dear student,',
            'text' => 'Your box of school materials is being held. Pay :parcel_fee in fees today or it will be sent back.', 'button' => 'Pay the fees'],
        'job' => ['subject' => 'A job for students: earn :pay for each transfer', 'hello' => 'Hello!',
            'text' => 'The Department is looking for students to receive transfers in their account and send them on to other accounts. You keep :pay for each one. No experience needed.', 'button' => 'Sign me up'],
    ],

    // the fake page (fraud/fake)
    'fake_title' => 'Verify your account',
    'fake_text' => 'Press the button to finish. It only takes a second.',

    // what happened (the call's end, fraud/lesson)
    'safe_title' => 'Well spotted!',
    'safe_text' => 'It was a fraud, and you did the right thing: you earn a participation point.',
    'fell_title' => 'It was a fraud',
    'fell_text' => 'The scammers have taken :amount from you. Cover it with your Emergency Fund or a loan within 30 days, in “:activity”.',
    'go_resolve' => 'Cover it',
    'name' => 'Its name: :name.',
    'signs_title' => 'The signs',
    'good_title' => 'What to do',
    'names' => [
        'bank_code' => 'vishing, fraud by phone',
        'safe_account' => 'vishing, fraud by phone',
        'tech_support' => 'the tech support scam',
        'family' => 'the ‘Hi Mum’ scam',
        'verify' => 'phishing, fraud by email',
        'refund' => 'phishing, fraud by email',
        'prize' => 'phishing, fraud by email',
        'parcel' => 'phishing, fraud by email',
        'job' => 'being a money mule',
    ],
    'signs' => [
        'bank_code' => 'They ask you for a code and rush you. The number was the bank\'s, but a number can be faked (number spoofing).',
        'safe_account' => 'Nobody from the bank asks you to move your money. The number was the bank\'s, but a number can be faked (number spoofing).',
        'tech_support' => 'A call you didn\'t ask for, and they want you to install something and pay.',
        'family' => 'A new number and a hurry for money.',
        'verify' => 'The address isn\'t the Department\'s (a 1 instead of an l), they don\'t use your name and they rush you.',
        'refund' => 'Money you weren\'t expecting, a hurry and a link to “verify” your account.',
        'prize' => 'You have to pay to collect a prize.',
        'parcel' => 'A small payment through a link, in a hurry.',
        'job' => 'Easy money for moving other people\'s money.',
    ],
    'good' => [
        'bank_code' => 'Hang up and call the bank yourself, on its official number (:phone).',
        'safe_account' => 'Hang up. Your bank never asks you to move your money.',
        'tech_support' => 'Hang up.',
        'family' => 'Call their usual number, or ask something only that person would know.',
        'verify' => 'Don\'t press the link: report it as fraud. The Department never asks you to verify your account.',
        'refund' => 'Delete it and report it.',
        'prize' => 'Ignore it.',
        'parcel' => 'Don\'t press: check it on the official website.',
        'job' => 'Say no: it is being a money mule, and it is a crime.',
    ],
    'slogan' => 'Take Five: Stop, Challenge, Protect.',
    'back' => 'Back to the mailbox',

    // the bank's help (bank/bank-help)
    'bank_contact_title' => 'Universal Bank\'s phone number',
    'bank_contact_text' => 'Call us on :phone. We will never ask you for a code or a password, or to move your money.',
];
