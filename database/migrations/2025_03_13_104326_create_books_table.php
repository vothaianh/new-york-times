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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('contributor')->nullable();
            $table->string('author');
            $table->string('contributor_note')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('publisher')->nullable();
            $table->json('isbns')->nullable();
            $table->json('ranks_history')->nullable();
            $table->json('reviews')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
