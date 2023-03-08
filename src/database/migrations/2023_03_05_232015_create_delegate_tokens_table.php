<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDelegateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    //4007190
    public function up()
    {
        Schema::create('siat_delegate_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('nit');
            $table->integer('sucursal');//
            $table->text('token');
            $table->dateTime('fecha_expiracion');
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
        Schema::dropIfExists('siat_delegate_tokens');
    }
}
