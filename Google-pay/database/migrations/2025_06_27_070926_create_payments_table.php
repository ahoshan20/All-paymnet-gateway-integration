<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_payment_intent_id')->unique();
            $table->string('stripe_customer_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('usd');
            $table->string('status');
            $table->string('payment_method')->nullable();
            $table->json('metadata')->nullable();
            $table->date('paid_at')->useCurrent()->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
