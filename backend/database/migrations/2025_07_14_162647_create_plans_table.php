<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Plan name: e.g., "Monthly Basic"
            $table->text('description')->nullable();         // Detailed description
            $table->decimal('price', 10, 2);                 // Price in your currency
            $table->integer('duration_in_days');            // Duration in days (e.g., 30 for monthly)
            $table->json('features')->nullable();            // Optional: JSON list of features
            $table->timestamps();                            // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
};
