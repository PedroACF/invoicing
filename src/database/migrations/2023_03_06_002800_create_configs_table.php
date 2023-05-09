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
        Schema::create('sys_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key');//NIT, BUSINESS_NAME, PHONE, OFFICE, OFFICE_ADDRESS, server_time_diff, LAST_INVOICE_NUMBER
            $table->string('value')->nullable();
            $table->string('data_type')->default('string');
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
        Schema::dropIfExists('sys_configs');
    }
}
