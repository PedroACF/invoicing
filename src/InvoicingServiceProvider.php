<?php
namespace PedroACF\Invoicing;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use PedroACF\Invoicing\Commands\InvCheckSignature;
use PedroACF\Invoicing\Commands\InvServicesTest;
use PedroACF\Invoicing\Repositories\CodeRepository;
use PedroACF\Invoicing\Repositories\DataSyncRepository;
use PedroACF\Invoicing\Repositories\OperationRepository;
use PedroACF\Invoicing\Repositories\PurchaseSaleRepository;
use PedroACF\Invoicing\Repositories\SoapRepository;
use PedroACF\Invoicing\Services\CatalogService;
use PedroACF\Invoicing\Services\CodeService;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Services\InvoicingService;
use PedroACF\Invoicing\Services\KeyService;
use PedroACF\Invoicing\Services\OperationService;
use PedroACF\Invoicing\Services\TokenService;
use PedroACF\Invoicing\Utils\XmlSigner;

class InvoicingServiceProvider extends ServiceProvider
{
    public function boot(){
        $this->publishes([
            __DIR__.'/config/pacf_invoicing.php' => config_path('pacf_invoicing.php'),
            __DIR__.'/../public' => public_path('vendor/pacf_invoicing')
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
        $this->app->bind(XmlSigner::class, function(Application $app){
            return new XmlSigner($app->make(KeyService::class));
        });

        //Repositories
        $this->app->bind(SoapRepository::class, function(Application $app){
            return new SoapRepository($app->make(TokenService::class));
        });
        $this->app->bind(CodeRepository::class, function(Application $app){
            return new CodeRepository();
        });
        $this->app->bind(DataSyncRepository::class, function(Application $app){
            return new DataSyncRepository();
        });
        $this->app->bind(PurchaseSaleRepository::class, function(Application $app){
            return new PurchaseSaleRepository();
        });
        $this->app->bind(OperationRepository::class, function(){
            return new OperationRepository();
        });

        //Services
        $this->app->bind(CodeService::class, function(Application $app){
            return new CodeService(
                $app->make(CodeRepository::class),
                $app->make(ConfigService::class)
            );
        });
        $this->app->bind(CatalogService::class, function(Application $app){
            return new CatalogService(
                $app->make(DataSyncRepository::class),
                $app->make(ConfigService::class)
            );
        });
        $this->app->bind(InvoicingService::class, function(Application $app){
            return new InvoicingService(
                $app->make(PurchaseSaleRepository::class),
                $app->make(ConfigService::class),
                $app->make(CodeService::class)
            );
        });
        $this->app->bind(OperationService::class, function(Application $app){
            return new OperationService(
                $app->make(OperationRepository::class),
                $app->make(ConfigService::class),
                $app->make(CodeService::class)
            );
        });
//        $this->app->singleton(ConfigService::class, function(Application $app){
//            return new ConfigService();
//        });
    }
}
