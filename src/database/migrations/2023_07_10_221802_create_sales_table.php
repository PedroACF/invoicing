<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PedroACF\Invoicing\Models\SYS\Sale;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');
        Schema::create('sys_sales', function (Blueprint $table) {
            $table->id();
            $table->uuid('sec_id')->default(DB::raw('uuid_generate_v4()'));

            //INVOICE DATA
            $table->string('cuf')->nullable();
            $table->string('cufd')->nullable();
            $table->bigInteger('invoice_number');//invoice
            $table->dateTime('emission_date')->nullable();
            $table->string('cafc')->nullable();
            $table->decimal("total_amount", 10, 2);
            $table->decimal("iva_total_amount", 10, 2);
            $table->decimal("gift_card_amount", 10, 2)->nullable();
            $table->decimal("extra_discount_amount", 10, 2)->nullable();
            $table->smallInteger("exception_code")->default(0);//or 1
            $table->decimal("exchange_rate", 12, 6)->default(1.0);
            $table->decimal("currency_total_amount", 10, 2);
            //CLIENT DATA
            $table->string('document_number');
            $table->string('document_complement')->nullable();
            $table->string('buyer_name');
            $table->boolean("mail_sent")->default(false);
            $table->boolean("phone_sent")->default(false);
            //SIN RELATIONS
            $table->string('document_type_code');
            $table->string('emission_type_code');
            $table->string('payment_method_code');
            $table->string('sector_doc_type_code');
            $table->string('sale_point_code');
            $table->string("currency_code");

            //SIN SERVICE
            $table->string('reception_code')->nullable();
            $table->string('cancel_code')->nullable();//anulacion
            $table->string('cancel_reason')->nullable();//anulacion
            $table->binary('signed_invoice')->nullable();
            //MY TABLES RELATIONS
            $table->unsignedBigInteger('buyer_id');
            $table->foreign('buyer_id')->references('id')->on('sys_buyers');
            $table->string('user_creation');
            //OTHERS
            $table->text('observations')->nullable();
            $table->enum('state', Sale::getEnumTypes())->default(Sale::ENUM_PENDANT);//VALIDA, RECHAZADA, PENDIENTE (DE ENVIO)

            $table->unsignedBigInteger('significant_event_id')->nullable();
            $table->foreign('significant_event_id')->references('id')->on('sys_significant_events');

            $table->boolean("test")->default(true);
            $table->timestamps();
        });

        DB::statement('CREATE SEQUENCE IF NOT EXISTS sys_sales_invoice_number');
        DB::statement('ALTER TABLE sys_sales ALTER COLUMN invoice_number SET NOT NULL');
        DB::statement("ALTER TABLE sys_sales ALTER COLUMN invoice_number SET DEFAULT nextval('sys_sales_invoice_number')");
        DB::statement('ALTER SEQUENCE sys_sales_invoice_number OWNED BY sys_sales.invoice_number');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sys_sales');
        DB::statement('DROP SEQUENCE IF EXISTS sys_sales_invoice_number');
    }
}
