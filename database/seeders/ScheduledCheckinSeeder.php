<?php

namespace Database\Seeders;

use App\Models\ScheduledCheckin;
use Illuminate\Database\Seeder;

class ScheduledCheckinSeeder extends Seeder
{
    public function run(): void
    {
        $checkins = [
            [
                'name' => 'Welcome!',
                'days_after_signup' => 1,
                'subject' => 'Welcome to KneadIt!',
                'body' => 'Welcome aboard! We\'re thrilled to have you. Let us know if you need any help getting started.',
                'is_active' => true,
            ],
            [
                'name' => 'Getting started?',
                'days_after_signup' => 3,
                'subject' => 'Getting started with KneadIt?',
                'body' => 'It\'s been a few days since you signed up. Have you had a chance to explore your dashboard? Here are some tips to get you started.',
                'is_active' => true,
            ],
            [
                'name' => 'How\'s your first week?',
                'days_after_signup' => 7,
                'subject' => 'How\'s your first week with KneadIt?',
                'body' => 'You\'ve been with us for a week now! We\'d love to hear how things are going. Any questions or feedback?',
                'is_active' => true,
            ],
            [
                'name' => 'Two weeks in!',
                'days_after_signup' => 14,
                'subject' => 'Two weeks in — how\'s it going?',
                'body' => 'You\'re two weeks into your KneadIt journey. By now you should be getting the hang of things. Need any help optimizing your setup?',
                'is_active' => true,
            ],
        ];

        foreach ($checkins as $checkin) {
            ScheduledCheckin::firstOrCreate(
                ['days_after_signup' => $checkin['days_after_signup']],
                $checkin
            );
        }
    }
}
