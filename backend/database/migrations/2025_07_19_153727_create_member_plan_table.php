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
        Schema::create('member_plan', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');

            // Additional subscription details
            $table->date('plan_start_date')->nullable();
            $table->date('plan_end_date')->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps(); // Useful for created_at and updated_at tracking
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_plan');
    }
};
