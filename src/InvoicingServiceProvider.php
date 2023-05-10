<?php
namespace PedroACF\Invoicing;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PedroACF\Invoicing\Commands\SiatCheckSignature;
use PedroACF\Invoicing\Commands\SiatServicesTest;
use PedroACF\Invoicing\Repositories\SoapRepository;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Services\KeyService;
use PedroACF\Invoicing\Services\TokenService;

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
        $this->app->singleton(TokenService::class, function(Application $app){
            return new TokenService();
        });
        $this->app->singleton(KeyService::class, function(Application $app){
            return new KeyService();
        });

        $this->app->bind(SoapRepository::class, function(Application $app){
            return new SoapRepository($app->make(TokenService::class));
        });
//        $this->app->singleton(ConfigService::class, function(Application $app){
//            return new ConfigService();
//        });
    }
}
