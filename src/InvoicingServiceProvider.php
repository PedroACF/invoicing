<?php
namespace PedroACF\Invoicing;
use Illuminate\Support\ServiceProvider;
use PedroACF\Invoicing\Commands\SiatCheckSignature;
use PedroACF\Invoicing\Commands\SiatServicesTest;

class InvoicingServiceProvider extends ServiceProvider
{
    public function boot(){
        $this->publishes([
            __DIR__.'/config/siat_invoicing.php' => config_path('siat_invoicing.php')
        ]);
        $this->mergeConfigFrom(__DIR__.'/config/siat_invoicing.php', 'siat_invoicing');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->commands([
            SiatServicesTest::class,
            SiatCheckSignature::class
        ]);
    }

    public function register(){

    }
}
