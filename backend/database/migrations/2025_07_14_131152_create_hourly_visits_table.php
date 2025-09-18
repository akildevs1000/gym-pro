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
        Schema::create('hourly_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('hour');
            $table->unsignedInteger('visitors');
            $table->timestamps();
            $table->unique('hour');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hourly_visits');
    }
};
