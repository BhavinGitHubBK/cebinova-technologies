<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_chats', function (Blueprint $table) {
            $table->id();
            $table->string('token', 40)->unique();
            $table->string('name');
            $table->string('mobile', 10);
            $table->string('question')->nullable();
            $table->json('messages');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_chats');
    }
};
