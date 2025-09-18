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
        Schema::create('payments', function (Blueprint $table) {

            $table->id();

            // link back to the member who paid
            $table->foreignId('member_id')
                ->constrained() // assumes “members” table
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained()
                ->cascadeOnDelete()->default(0);

            $table->decimal('total', 10, 2);
            $table->decimal('discount', 10, 2);
            $table->decimal('after_discount', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            $table->decimal('balance', 10, 2);

            // link to a lookup table of payment modes (cash, card, etc.)
            $table->foreignId('payment_mode_id')
                ->constrained('payment_modes');

            $table->string('payment_mode_ref')->nullable(); // cheque no., txn id, etc.

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
