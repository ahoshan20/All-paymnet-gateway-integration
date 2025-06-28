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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_customer_id')->unique()->index();
            $table->string('name')->nullable();
            $table->string('email')->unique()->index();
            $table->string('phone')->nullable();
            $table->json('address')->nullable();
            $table->json('metadata')->nullable();
            $table->date('created_at_stripe')->useCurrent()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
