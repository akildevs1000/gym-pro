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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->unsignedBigInteger('expense_category_id')->default(0);
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->json('items')->nullable();
            $table->json('attachments')->nullable()->comment('Stores file names or URLs of attachments');
            $table->string('notes')->nullable();
            $table->decimal('sub_total', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('balance', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * 
     * type expense_category_id invoice_number created_at(date range)
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
