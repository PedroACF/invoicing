<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_sale_details', function (Blueprint $table) {
            $table->id();

            $table->string('code');//producto local
            $table->string('description');//producto local
            $table->integer('quantity');
            $table->decimal("unit_price", 10, 2);
            $table->decimal("discount_amount", 10, 2)->default(0.0);
            $table->decimal("sub_amount", 10, 2);
            $table->string('serial_number')->nullable();
            $table->string('imei_number')->nullable();
            //SIN RELATIONS
            $table->string('activity_code');
            $table->string('product_code');//producto
            $table->string('measurement_unit_code');


            $table->unsignedBigInteger('sale_id');
            $table->foreign('sale_id')->references('id')->on('sys_sales');
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
        Schema::dropIfExists('sys_sale_details');
    }
}
