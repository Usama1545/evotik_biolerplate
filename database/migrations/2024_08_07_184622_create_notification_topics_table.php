<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_topics', function (Blueprint $table) {
            $table->id();
            $table->string('topic')->nullable();
            $table->string('type')->nullable();
            $table->string('model')->nullable();
            $table->string('action')->nullable();
            $table->boolean('is_active')->default(true);
            $table->longText('template')->nullable();
            $table->string('path')->nullable();
            $table->string('target_user_role')->nullable();
            $table->string('target_user_feature')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_topics');
    }
};
