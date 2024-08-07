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
        Schema::create('email_metas', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->string('message_id')->nullable();
            $table->boolean('is_open')->nullable()->default(false);
            $table->boolean('is_clicked')->nullable()->default(false);
            $table->boolean('is_bounced')->nullable()->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_metas');
    }
};
