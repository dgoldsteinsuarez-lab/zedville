<?php

// The student page frame (layouts/profile.blade.php): top bar, account
// menu, notifications, alerts, the Mood Meter and the Daily Quiz pop-ups.
return [
    'page_title' => 'Zedville - Financial Skills for Your Future',

    // Account menu
    'last_login' => 'Last login: :when',
    'never' => 'Never',
    'account' => 'Account',
    'personal_information' => 'Personal Information',
    'personal_information_sub' => 'Update your personal details',
    'preferences' => 'Preferences',
    'app_settings' => 'App Settings',
    'app_settings_sub' => 'Language, theme, and display',
    'support' => 'Support',
    'download_app' => 'Download App',
    'not_available_now' => 'Not available now',
    'sign_out' => 'Sign Out',

    // Notifications
    'notifications' => 'Notifications',
    'new_count' => ':count new',
    'mark_all_read' => 'Mark all read',
    'mark_as_read' => 'Mark as read',
    'delete_notification' => 'Delete notification',
    'no_notifications' => 'No notifications yet. Real activity from your account will show up here.',
    'read' => 'Read',
    'unread' => 'Unread',
    'settings' => 'Settings',

    // Alerts
    'successful' => 'Successful',
    'error' => 'Error',
    'profile_updated' => 'Profile updated successfully.',
    'password_not_updated' => 'Password not updated.',

    // Tutor using their own citizen account
    'back' => 'Back',
    'finish' => 'Finish',
    'student_mode' => 'STUDENT MODE',
    'student_mode_note' => "You're viewing this as a Tutor — this is your own citizen account.",
    'switch_to_tutor' => 'Switch to Tutor Mode',

    'mood' => [
        'title' => 'Mood Meter',
        'question' => 'How are you feeling today?',
        'intro' => 'Your mood selection helps us understand how you feel, and this practice will help you build your emotional vocabulary and enhance your emotional wellness.',
        'furious' => 'Furious',
        'angry' => 'Angry',
        'nervous' => 'Nervous',
        'worried' => 'Worried',
        'excited' => 'Excited',
        'ecstatic' => 'Ecstatic',
        'happy' => 'Happy',
        'joyful' => 'Joyful',
        'lonely' => 'Lonely',
        'sad' => 'Sad',
        'hopeless' => 'Hopeless',
        'disappointed' => 'Disappointed',
        'calm' => 'Calm',
        'serene' => 'Serene',
        'at_ease' => 'At Ease',
        'content' => 'Content',
        'save' => 'Save',
        'select_a_mood' => 'Please select a mood.',
        'server_error' => 'Server error, please try again.',
        'save_failed' => 'Failed to save mood.',
    ],

    'quiz' => [
        'title' => 'Daily Quiz',
        'subtitle' => 'Test your financial knowledge',
        'loading' => 'Loading question...',
        'submit' => 'Submit Answer',
        'submitting' => 'Submitting...',
        'correct' => 'Correct! 🎉',
        'correct_text' => 'Great job! You got it right.',
        'wrong' => 'Not quite! 💡',
        'correct_answer_was' => 'The correct answer was:',
        'done' => 'Done',
    ],
];
