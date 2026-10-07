<?php

use App\Enums\Staff\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A tenant user that is created without a role gets the least privileged one.
     * Existing rows keep the role they have.
     *
     * On SQLite, changing a default rebuilds the table. The users table has no CHECK
     * constraint to lose, and its unique email index is recreated.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default(UserRole::Staff->value)->change();
        });
    }
};
