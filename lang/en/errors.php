<?php

return [
    '401' => [
        'title' => 'Please Sign In | :store',
        'heading' => 'Please sign in',
        'message' => 'You need to sign in to see this page.',
    ],
    '402' => [
        'title' => 'Payment Required | :store',
        'heading' => 'Payment required',
        'message' => 'This page needs an active plan or a completed payment before it can open.',
    ],
    '403' => [
        'title' => 'Off the Menu | :store',
        'heading' => 'That one is off the menu',
        'message' => "You don't have permission to view this page.",
    ],
    '419' => [
        'title' => 'Link Expired | :store',
        'heading' => 'This link has expired',
        'message' => 'Your session timed out, please go back and try again.',
    ],
    '429' => [
        'title' => 'Slow Down | :store',
        'heading' => 'Easy there, baker',
        'message' => "We've had too many requests from you in a short time. Please wait a moment and try again.",
    ],
    'back_to' => 'Back to :store',
    '404' => [
        'title' => 'Page Not Found | :store',
        'heading' => 'Nothing baking here',
        'message' => "The page you're looking for doesn't exist or may have been moved.",
        'back_to' => 'Back to :store',
    ],
    '500' => [
        'title' => 'Something Went Wrong | :app',
        'heading' => 'Something burned in the oven',
        'message' => "We hit an unexpected error. We've been notified and are working on it. Please try again in a moment.",
        'back' => 'Back to :app',
    ],
    '503' => [
        'title' => 'Be Right Back | :app',
        'heading' => 'Dough is rising',
        'message' => "We're doing some quick maintenance. We'll be back in just a moment — your bakery data is safe.",
    ],
];
