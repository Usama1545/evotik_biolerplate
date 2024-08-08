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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->nullable()->onDelete('cascade');
            $table->string('title');
            $table->string('uid')->nullable();
            $table->text('description');
            $table->string('status')->default('open');
            $table->string('priority')->nullable();
            $table->string('department')->nullable();
            $table->date('opening_date');
            $table->date('closing_date')->nullable();
            $table->foreignId('closed_by')->references('id')->on('users')->nullable()->onDelete('cascade');
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
