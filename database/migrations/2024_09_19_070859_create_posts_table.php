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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Owning User
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            // Post Content
            $table->string("title")->nullable(false);
            $table->text("content")->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
