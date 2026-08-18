<?php

use App\Services\Settings\TenantSettingCipher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $cipher = resolve(TenantSettingCipher::class);

        DB::table('settings')
            ->whereIn('key', TenantSettingCipher::sensitiveKeys())
            ->orderBy('id')
            ->each(function (object $setting) use ($cipher): void {
                if (! isset($setting->id, $setting->key) || ! is_int($setting->id) || ! is_string($setting->key)) {
                    return;
                }

                $encrypted = $cipher->encrypt($setting->key, $setting->value ?? null);

                if ($encrypted === ($setting->value ?? null)) {
                    return;
                }

                DB::table('settings')
                    ->where('id', $setting->id)
                    ->update(['value' => $encrypted]);
            });
    }
};
