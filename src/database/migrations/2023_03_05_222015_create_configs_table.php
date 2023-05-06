<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('siat_configs', function (Blueprint $table) {
            $table->id();
            $table->string('nit');
            $table->string('business_name');
            $table->string('municipality');
            $table->string('phone');
            $table->integer('office')->default(0);//sucursales
            $table->string('office_address');
            $table->integer('sale_point');
            $table->integer('server_time_diff')->default(0);
            $table->bigInteger('last_invoice_number')->default(0);
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
        Schema::dropIfExists('siat_configs');
    }
}
