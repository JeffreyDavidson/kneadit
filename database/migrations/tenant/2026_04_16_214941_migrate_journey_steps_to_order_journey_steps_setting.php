<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $pageContentRow = DB::table('settings')->where('key', 'page_content')->first();

        if (! $pageContentRow) {
            return;
        }

        $pageContent = json_decode($pageContentRow->value ?? '', true);

        if (! is_array($pageContent)) {
            return;
        }

        $journeySteps = $pageContent['order_confirmation']['journey_steps'] ?? null;

        if (! is_array($journeySteps) || $journeySteps === []) {
            return;
        }

        DB::table('settings')->updateOrInsert(
            ['key' => 'order_journey_steps'],
            ['value' => json_encode(array_values($journeySteps))],
        );

        unset($pageContent['order_confirmation']['journey_steps']);

        DB::table('settings')
            ->where('key', 'page_content')
            ->update(['value' => json_encode($pageContent)]);
    }
};
