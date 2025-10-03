<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Application;

class AppServiceProviderTest extends TestCase
{
    public function test_app_service_provider_can_be_instantiated()
    {
        $app = $this->app;
        $provider = new AppServiceProvider($app);

        $this->assertInstanceOf(AppServiceProvider::class, $provider);
    }

    public function test_register_method_exists_and_is_callable()
    {
        $provider = new AppServiceProvider($this->app);

        $this->assertTrue(method_exists($provider, 'register'));
        $this->assertTrue(is_callable([$provider, 'register']));
    }

    public function test_boot_method_exists_and_is_callable()
    {
        $provider = new AppServiceProvider($this->app);

        $this->assertTrue(method_exists($provider, 'boot'));
        $this->assertTrue(is_callable([$provider, 'boot']));
    }

    public function test_register_method_can_be_called_without_errors()
    {
        $provider = new AppServiceProvider($this->app);

        $this->expectNotToPerformAssertions();
        $provider->register();
    }

    public function test_boot_method_can_be_called_without_errors()
    {
        $provider = new AppServiceProvider($this->app);

        $this->expectNotToPerformAssertions();
        $provider->boot();
    }

    public function test_provider_is_registered_in_application()
    {
        $registeredProviders = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(AppServiceProvider::class, $registeredProviders);
    }

    public function test_provider_extends_service_provider()
    {
        $provider = new AppServiceProvider($this->app);

        $this->assertInstanceOf(\Illuminate\Support\ServiceProvider::class, $provider);
    }
}
