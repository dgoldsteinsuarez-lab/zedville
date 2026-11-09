<?php

// The log-in page (resources/views/auth/login.blade.php), which is also the
// home page - Daniel chose design 3c on 2026-10-25: the skyline under a
// bright sky, "Hello Citizen, Welcome to Zedville!" (2026-10-26), and
// "What is Zedville?" for schools below.
return [
    'title' => 'Log in · Zedville',
    'what_is' => 'What is Zedville?',
    'language' => 'Language',
    'hello' => 'Hello Citizen,',
    'welcome' => 'Welcome to Zedville!',
    'intro' => 'The sun is shining and your city is busy. Log in and make your next choice.',
    'skyline' => 'The Zedville skyline: the Courthouse, City Hall and the Library',

    'email' => 'Email',
    'email_placeholder' => 'you@school.org',
    'password' => 'Password',
    'password_placeholder' => 'Your password',
    'show_password' => 'Show password',
    'hide_password' => 'Hide password',
    'remember' => 'Remember me',
    'forgot' => 'Forgot password?',
    'submit' => 'Log in',
    'new' => 'New to Zedville?',
    'create_account' => 'Create an account',

    'about' => [
        'title' => 'A virtual city where students learn to manage real-life money.',
        'lead' => 'Zedville is a <b>financial literacy platform for schools</b>. Every student becomes a citizen of a virtual city: they earn a <b>monthly salary in Zeds</b>, open a bank account, pay rent, school fees and bills, shop, save, invest and take part in city life. <b>No real money is ever used</b>: mistakes cost nothing, and the lessons last a lifetime.',
        'students' => [
            'label' => 'For students',
            'title' => 'Learn by living it',
            'items' => [
                'Plan a monthly budget with your own salary.',
                'Tell needs from wants and shop with your head.',
                'Save for emergencies and for your goals.',
                'Understand bank statements, interest and investing.',
                'Vote, make proposals and donate to your community.',
            ],
        ],
        'teachers' => [
            'label' => 'For teachers',
            'title' => 'Guide every step',
            'items' => [
                'Assign activities and follow each student\'s progress.',
                'A curriculum by level, from Rookie to Advance Economist.',
                'Financial literacy practice in the style of PISA.',
                'Autopilot keeps the city running over school holidays.',
            ],
        ],
        'schools' => [
            'label' => 'For schools',
            'title' => 'Built for the classroom',
            'items' => [
                'A ready-made way to bring financial education into the timetable.',
                'Available in English, Spanish, French and German.',
                'Safe and private: citizen IDs, nicknames and no real money.',
                'End-of-year exams as evidence of learning.',
            ],
        ],
        'steps' => [
            ['Sign up', 'Register with your school and get your Citizen ID.'],
            ['Become a citizen', 'Open your bank account and set up your salary payment.'],
            ['Live your month', 'Pay your bills, shop, save and make decisions.'],
            ['Grow real skills', 'Every choice teaches you how adult life works.'],
        ],
        'band' => 'When you turn 18, you\'ll already know how to handle money, pay bills and make smart financial decisions.',
        'bring' => 'Bring Zedville to your school',
        'login' => 'Log in ↑',
        'footer' => 'Financial skills for your future',
        'register' => 'Register',
        'contact' => 'Contact',
        'home' => 'Home',
    ],
];
