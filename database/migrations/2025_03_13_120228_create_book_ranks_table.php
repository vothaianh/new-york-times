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
        Schema::create('book_ranks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->string('primary_isbn10')->nullable();
            $table->string('primary_isbn13')->nullable();
            $table->integer('rank')->nullable();
            $table->string('list_name')->nullable();
            $table->string('display_name')->nullable();
            $table->date('published_date')->nullable();
            $table->date('bestsellers_date')->nullable();
            $table->integer('weeks_on_list')->default(0);
            $table->integer('ranks_last_week')->nullable();
            $table->boolean('asterisk')->default(false);
            $table->boolean('dagger')->default(false);
            $table->timestamps();

            // Add index for faster lookups
            $table->index(['book_id', 'list_name', 'published_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_ranks');
    }
};
