<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSignificantEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_significant_events', function (Blueprint $table) {
            $table->id();
            $table->integer('event_type_code');
            $table->text('description');
            $table->string('reception_code')->nullable();
            $table->string('event_cufd');
            $table->string('cufd')->nullable();
            $table->string('cafc')->nullable();// ==>=>=>=>=>=>>=
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime')->nullable();
            $table->string('state')->default('OPEN');
            $table->integer('sale_point')->default(0);
            $table->text('observations')->nullable();
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
        Schema::dropIfExists('sys_significant_events');
    }
}
