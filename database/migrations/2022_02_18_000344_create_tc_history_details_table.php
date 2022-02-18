<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTcHistoryDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tc_history_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('history_id');
            $table->date('fecha');
            $table->decimal('tc_compra',8,2);
            $table->decimal('tc_venta',8,2);
            $table->timestamps();

            $table->foreign('history_id')->references('id')->on('tc_histories');
        });

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tc_history_details');
    }
}
