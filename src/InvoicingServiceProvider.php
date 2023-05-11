<?php
namespace PedroACF\Invoicing;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PedroACF\Invoicing\Commands\InvCheckSignature;
use PedroACF\Invoicing\Commands\InvServicesTest;
use PedroACF\Invoicing\Repositories\CodeRepository;
use PedroACF\Invoicing\Repositories\SoapRepository;
use PedroACF\Invoicing\Services\CodeService;
use PedroACF\Invoicing\Services\KeyService;
use PedroACF\Invoicing\Services\TokenService;

class InvoicingServiceProvider extends ServiceProvider
{
    public function boot(){
        $this->publishes([
            __DIR__.'/config/pacf_invoicing.php' => config_path('pacf_invoicing.php')
        ]);
        $this->mergeConfigFrom(__DIR__.'/config/pacf_invoicing.php', 'pacf_invoicing');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->commands([
            InvServicesTest::class,
            InvCheckSignature::class
        ]);
    }

    public function register(){
        $this->app->singleton(TokenService::class, function(Application $app){
            return new TokenService();
        });
        $this->app->singleton(KeyService::class, function(Application $app){
            return new KeyService();
        });

        //Repositories
        $this->app->bind(SoapRepository::class, function(Application $app){
            return new SoapRepository($app->make(TokenService::class));
        });

        $this->app->bind(CodeRepository::class, function(Application $app){
            return new CodeRepository();
        });

        //Services
        $this->app->bind(CodeService::class, function(Application $app){
            return new CodeService($app->make(CodeRepository::class));
        });
//        $this->app->singleton(ConfigService::class, function(Application $app){
//            return new ConfigService();
//        });
    }
}
