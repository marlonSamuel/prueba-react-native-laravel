<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTcHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tc_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('variable_type_id');
            $table->integer('peticion');
            $table->date('inicio');
            $table->date('fin');
            $table->decimal('prom_tc_compra',8,2);
            $table->decimal('prom_tc_venta',8,2);
            $table->timestamps();

            $table->foreign('variable_type_id')->references('id')->on('tc_variable_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tc_histories');
    }
}
