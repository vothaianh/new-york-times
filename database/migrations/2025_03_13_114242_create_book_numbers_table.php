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
        Schema::create('book_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('key')->comment('Type of book number (e.g., isbn10, isbn13)');
            $table->string('number', 20)->comment('The actual book number value');
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->timestamps();

            // Add unique constraint to prevent duplicate book numbers for the same book
            $table->unique(['key', 'number', 'book_id']);
            // Add index for faster lookups
            $table->index(['key', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_numbers');
    }
};
