<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PedroACF\Invoicing\Models\SYS\Package;

class CreatePackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_packages', function (Blueprint $table) {
            $table->id();
            $table->text("sales");
            $table->string("response_code")->nullable();
            $table->string("state")->default(Package::ENUM_PENDANT);
            $table->text("messages")->nullable();
            $table->string("reception_code")->nullable();
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
        Schema::dropIfExists('sys_packages');
    }
}
