<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_sale_points', function (Blueprint $table) {
            $table->id();
            $table->integer('sin_code')->nullable();
            $table->integer('sale_point_type')->nullable();
            $table->string('name');
            $table->string('description');
            $table->string('state')->default('ACTIVE');
            $table->timestamps();
        });

        $salePointDefault = new \PedroACF\Invoicing\Models\SYS\SalePoint();
        $salePointDefault->sin_code = 0;
        $salePointDefault->name = 'DEFAULT';
        $salePointDefault->description = 'DEFAULT';
        $salePointDefault->save();

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_sale_points');
    }
}
