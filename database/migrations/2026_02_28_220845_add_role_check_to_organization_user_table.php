<?php

use App\Enums\OrganizationRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $roles = implode("', '", OrganizationRole::values());
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE organization_user ADD CONSTRAINT organization_user_role_check CHECK (role IN ('{$roles}'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE organization_user DROP CONSTRAINT IF EXISTS organization_user_role_check');
        }
    }
};
