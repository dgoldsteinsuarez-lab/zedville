<?php

// Shops: the supermarket, its shopping list (market-list) and the spending
// trackers - in the city and in the Spending Activity. Product names come
// from the catalogue tables. Prices are shown in Z (zeds).
return [
    'diets' => [
        'omnivore' => 'Omnivore',
        'vegetarian' => 'Vegetarian',
        'pescatarian' => 'Pescatarian',
        'vegan' => 'Vegan',
    ],
    'supermarket' => 'Supermarket',
    'secure_supermarket' => 'Secure Student Supermarket',
    'shopping_portal' => 'Secure Student Shopping Portal',
    'store' => 'Store',
    'no_bank_account' => 'No bank account opened',
    'bank_required_title' => '⚠ Bank Account Required',
    'bank_required_text' => 'Please open your bank account to start shopping.',
    'open_bank_account' => 'Open Bank Account',
    'back' => 'Back',

    // Payment result
    'payment_successful' => 'Payment Successful!',
    'transaction_complete' => 'Transaction complete.',
    'payment_done' => 'Payment of <strong>:amount</strong> successful.<br>Remaining Balance: <strong>:balance</strong>',
    'payment_error' => 'Payment error. Please try again.',
    'close' => 'Close',

    // Products and cart
    'products' => 'Products',
    'list_view' => '☰ List',
    'grid_view' => '⸬ Grid',
    'your_cart' => 'Your Cart',
    'cart_empty' => 'Cart is empty',
    'items_in_cart' => 'Items in cart',
    'task' => 'Task',
    'total' => 'Total',
    'qty' => 'Qty: :n',
    'remove_item' => 'Remove Item',
    'max_items' => 'Maximum :max items allowed!',

    // Checkout
    'secure_checkout' => 'Secure Checkout',
    'shipping_method' => 'Shipping Method',
    'ship_self' => 'My Own Address (Default)',
    'ship_discretionary' => 'Current Discretionary "Month"',
    'ship_classmate' => 'Gift to Classmate',
    'ship_npo' => 'Donate to NPO',
    'delivery_address' => 'Delivery Address',
    'own_address' => '123 Main Dorm, Room 456<br>Campus City, 12345',
    'discretionary_address' => '<strong>Community Garden Project</strong><br><small>Discretionary Activity Month</small>',
    'select_classmate' => 'Select Classmate',
    'select_npo' => 'Select NPO',
    'student_name' => 'Student Name',
    'card_number' => 'Card Number',
    'expiry' => 'Expiry',
    'expiry_placeholder' => 'MM/YY',
    'cvv' => 'CVV',
    'enter_pin' => 'Enter 4-Digit PIN',
    'forgot_pin' => 'Forgot your PIN?',
    'view_pin' => 'Click here to view your PIN',
    'pay_now' => 'PAY NOW',
    'enter_pin_alert' => 'Please enter a 4-digit PIN',

    // PIN pop-up
    'your_pin' => 'Your PIN',
    'debit_card_pin' => 'Debit Card PIN',
    'pin_security_title' => '📌 Important PIN Security Information:',
    'pin_security' => [
        'Never share your PIN with anyone',
        'Use your PIN for ATM withdrawals and card purchases',
        'Cover the keypad when entering your PIN',
        'Change your PIN regularly for security',
        'Contact us immediately if compromised',
    ],

    // Spending Activity budget checks
    'spend_more' => '⬆️ Spend more. Minimum is :min Z. You have :spent Z.',
    'over_limit' => '🚨 BUDGET ALERT: Over limit! Maximum is :max Z. You have :spent Z.',
    'looking_good' => '✅ Looking good! :items items — :spent Z — within range (:min–:max Z)',
    'min_spend_error' => '❌ Minimum spend is :min Z. You have :spent Z.',
    'max_spend_error' => '❌ Maximum spend is :max Z. You have :spent Z.',
    'cannot_checkout' => '⚠️ Cannot checkout — Requirements not met:',

    // Shopping list (market-list)
    'market_list_title' => 'Supermarket Market List',
    'market_list_hint' => 'Click a shelf to start shopping',
    'complete_survey_title' => 'Complete Your Food Survey',
    'complete_survey_text' => 'Please complete the food preference survey to access your personalized supermarket categories.',
    'take_survey' => 'Take Survey',

    // Shopping basket (substitute goods)
    'basket' => [
        'title' => 'My Shopping Basket',
        'question_1' => 'Want to change something in your monthly shopping basket?',
        'question_2' => 'Do you know what substitute goods are?',
        'intro' => 'They\'re products that can stand in for each other because they do the same job - like chicken and tofu, or butter and margarine. Here you can swap any item in your basket for a substitute. Pick one that gives you about the same value for a similar (or even better!) price. Your basket changes straight away.',
        'loading' => 'Loading your basket…',
        'empty' => 'Complete your monthly Consumer Profile survey to build a shopping basket.',
        'out_of_stock' => 'Out of stock',
        'swap' => 'Swap',
        'swap_now' => 'Swap now',
        'total' => 'Basket total:',
        'challenge_one' => 'Shop alert! 1 item in your basket is out of stock this month. Swap it for a substitute to finish the activity.',
        'challenge_many' => 'Shop alert! :count items in your basket are out of stock this month. Swap each one for a substitute to finish the activity.',
        'how_it_works' => 'How does it work?',
        'replace' => 'Swap',
        'pick_substitute' => 'Pick a substitute from your diet section that does the same job.',
        'no_options' => 'No other items available to swap into right now.',
        'select_substitute' => 'Choose a substitute…',
        'confirm' => 'Swap it',
        'cancel' => 'Cancel',
        'could_not_swap' => 'Could not swap that item.',
        'went_wrong' => 'Something went wrong. Please try again.',
        'done_title' => 'Well done! Activity completed',
        'done_text' => 'You found a substitute for every out-of-stock item. Smart shopping!',
        'engagement_point' => '+1 Engagement point',
        'great' => 'Great!',
    ],

    // Messages from the server
    'server' => [
        'no_bank_account' => 'Bank account not found.',
        'invalid_pin' => 'Invalid PIN.',
        'product_unavailable' => 'One or more products are no longer available.',
        'insufficient_balance' => 'Insufficient balance. Your available balance is :balance Z. Required amount is :required Z.',
        'order_placed' => 'Order placed successfully.',
        'order_failed' => 'Unable to place order.',
        'no_basket' => 'No shopping basket found.',
        'not_in_basket' => 'That item is not in your basket.',
        'already_in_basket' => 'That item is already in your basket.',
        'also_unavailable' => 'That item is currently unavailable too.',
        'not_in_diet' => 'That item is not part of your diet catalog.',
    ],
];
