<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Comment;
use App\Policies\PostPolicy;
use App\Policies\CommentPolicy;
use App\Providers\AuthServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_service_provider_can_be_instantiated()
    {
        $provider = new AuthServiceProvider($this->app);

        $this->assertInstanceOf(AuthServiceProvider::class, $provider);
    }

    public function test_policies_property_contains_correct_mappings()
    {
        $provider = new AuthServiceProvider($this->app);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('policies');
        $property->setAccessible(true);
        $policies = $property->getValue($provider);

        $this->assertIsArray($policies);
        $this->assertArrayHasKey(Post::class, $policies);
        $this->assertArrayHasKey(Comment::class, $policies);
        $this->assertEquals(PostPolicy::class, $policies[Post::class]);
        $this->assertEquals(CommentPolicy::class, $policies[Comment::class]);
    }

    public function test_boot_method_registers_policies()
    {

        Gate::policies([]);

        $provider = new AuthServiceProvider($this->app);
        $provider->boot();

        $this->assertInstanceOf(PostPolicy::class, Gate::getPolicyFor(Post::class));
        $this->assertInstanceOf(CommentPolicy::class, Gate::getPolicyFor(Comment::class));
    }

    public function test_register_method_exists_and_is_callable()
    {
        $provider = new AuthServiceProvider($this->app);

        $this->assertTrue(method_exists($provider, 'register'));
        $this->assertTrue(is_callable([$provider, 'register']));
    }

    public function test_boot_method_exists_and_is_callable()
    {
        $provider = new AuthServiceProvider($this->app);

        $this->assertTrue(method_exists($provider, 'boot'));
        $this->assertTrue(is_callable([$provider, 'boot']));
    }

    public function test_register_method_can_be_called_without_errors()
    {
        $provider = new AuthServiceProvider($this->app);

        $this->expectNotToPerformAssertions();
        $provider->register();
    }

    public function test_provider_extends_auth_service_provider()
    {
        $provider = new AuthServiceProvider($this->app);

        $this->assertInstanceOf(\Illuminate\Foundation\Support\Providers\AuthServiceProvider::class, $provider);
    }

    public function test_provider_is_registered_in_application()
    {
        $registeredProviders = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(AuthServiceProvider::class, $registeredProviders);
    }

    public function test_policies_are_automatically_registered_on_boot()
    {

        $this->assertNotNull(Gate::getPolicyFor(Post::class));
        $this->assertNotNull(Gate::getPolicyFor(Comment::class));
    }

    public function test_policy_mappings_count()
    {
        $provider = new AuthServiceProvider($this->app);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('policies');
        $property->setAccessible(true);
        $policies = $property->getValue($provider);


        $this->assertCount(2, $policies);
    }

    public function test_policy_classes_exist()
    {
        $provider = new AuthServiceProvider($this->app);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('policies');
        $property->setAccessible(true);
        $policies = $property->getValue($provider);

        foreach ($policies as $model => $policy) {
            $this->assertTrue(class_exists($model), "Model class {$model} does not exist");
            $this->assertTrue(class_exists($policy), "Policy class {$policy} does not exist");
        }
    }
}
