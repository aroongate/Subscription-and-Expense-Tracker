<?php

use App\Enums\SubscriptionBillingCycle;
use App\Enums\SubscriptionStatus;
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
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('vendor')->nullable();
            $table->bigInteger('amount_minor');
            $table->char('currency_code', 3);
            $table->decimal('exchange_rate', 12, 6)->default(1);
            $table->bigInteger('amount_base_minor');
            $table->string('billing_cycle');
            $table->date('next_charge_at');
            $table->string('status')->default(SubscriptionStatus::Active->value);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'next_charge_at']);
        });

        $driver = DB::connection()->getDriverName();
        $cycles = implode("', '", SubscriptionBillingCycle::values());
        $statuses = implode("', '", SubscriptionStatus::values());

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_billing_cycle_check CHECK (billing_cycle IN ('{$cycles}'))");
            DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_status_check CHECK (status IN ('{$statuses}'))");
            DB::statement('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_amount_minor_check CHECK (amount_minor > 0)');
            DB::statement('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_amount_base_minor_check CHECK (amount_base_minor > 0)');
            DB::statement('ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_exchange_rate_check CHECK (exchange_rate > 0)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
