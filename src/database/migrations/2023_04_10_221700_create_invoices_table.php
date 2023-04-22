<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PedroACF\Invoicing\Models\Invoice;
use Illuminate\Support\Facades\DB;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');

        Schema::create('siat_invoices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->bigInteger('number');
            $table->string("cuf");
            $table->string("document");
            $table->string("client_name");
            $table->dateTime("emission_date");
            $table->decimal("amount", 10, 2);
            $table->binary('content');
            $table->enum('state', Invoice::getEnumTypes())->default(Invoice::ENUM_PENDANT);//VALIDA, RECHAZADA, PENDIENTE (DE ENVIO)
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();
        });

        //DB::statement('ALTER TABLE siat_invoices ALTER COLUMN id SET DEFAULT uuid_generate_v4();');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
