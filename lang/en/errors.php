<?php

return [
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
