<?php

use App\Enums\CategoryType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('color', 7)->default('#3b82f6');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'type']);
            $table->unique(['organization_id', 'type', 'name']);
        });

        $driver = DB::connection()->getDriverName();
        $types = implode("', '", CategoryType::values());

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE categories ADD CONSTRAINT categories_type_check CHECK (type IN ('{$types}'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
