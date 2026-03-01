<?php

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
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->bigInteger('amount_minor');
            $table->char('currency_code', 3);
            $table->decimal('exchange_rate', 12, 6)->default(1);
            $table->bigInteger('amount_base_minor');
            $table->date('spent_at');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'spent_at']);
            $table->index(['organization_id', 'category_id']);
        });

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE expenses ADD CONSTRAINT expenses_amount_minor_check CHECK (amount_minor > 0)');
            DB::statement('ALTER TABLE expenses ADD CONSTRAINT expenses_amount_base_minor_check CHECK (amount_base_minor > 0)');
            DB::statement('ALTER TABLE expenses ADD CONSTRAINT expenses_exchange_rate_check CHECK (exchange_rate > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
