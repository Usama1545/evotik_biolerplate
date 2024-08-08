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
        if (!Schema::hasTable('email_subscriptions')) {
            Schema::create('email_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->integer('optout')->nullable();
                $table->integer('opens')->nullable();
                $table->integer('clicks')->nullable();
                $table->boolean('is_subscribed')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_subscriptions');
    }
};
