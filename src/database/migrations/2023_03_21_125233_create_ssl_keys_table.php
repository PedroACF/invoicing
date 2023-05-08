<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PedroACF\Invoicing\Models\SYS\SslKey;

class CreateSslKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_ssl_keys', function (Blueprint $table) {
            $table->id();
            $table->binary('content');
            $table->enum('type', SslKey::getEnumTypes());
            $table->boolean('enabled')->default(true);
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
        Schema::dropIfExists('sys_ssl_keys');
    }
}
