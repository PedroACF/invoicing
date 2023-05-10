<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCufdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sin_cufd', function (Blueprint $table) {
            $table->id();
            $table->text('cufd');
            $table->string('codigo_control');
            $table->dateTime('expired_date');
            $table->boolean('activo')->default(false);
            $table->integer('sale_point')->default(0);
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
        Schema::dropIfExists('sin_cufd');
    }
}
