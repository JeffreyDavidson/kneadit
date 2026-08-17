<?php

use App\Services\Analytics\VisitorIdentifier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $visitorIdentifier = resolve(VisitorIdentifier::class);

        DB::table('page_views')
            ->select(['id', 'session_id'])
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $pageView) use ($visitorIdentifier): void {
                $id = data_get($pageView, 'id');
                $sessionId = data_get($pageView, 'session_id');

                if ((! is_int($id) && ! is_string($id)) || ! is_string($sessionId)) {
                    return;
                }

                DB::table('page_views')
                    ->where('id', $id)
                    ->update(['session_id' => $visitorIdentifier->fromSessionId($sessionId)]);
            });

        Schema::table('page_views', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent']);
        });
    }
};
