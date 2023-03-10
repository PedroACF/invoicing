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
        Schema::create('siat_cufd', function (Blueprint $table) {
            $table->id();
            $table->text('cufd');
            $table->string('codigo_control');
            $table->string('direccion');
            $table->dateTime('expired_date');
            $table->boolean('activo')->default(false);
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
        Schema::dropIfExists('siat_cufd');
    }
}
