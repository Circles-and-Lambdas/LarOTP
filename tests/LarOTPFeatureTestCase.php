<?php

declare(strict_types=1);

namespace circlesandlambdas\larotp\tests;

use circlesandlambdas\larotp\LarOTPServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class LarOTPFeatureTestCase extends OrchestraTestCase{

    use RefreshDatabase;

    protected function setUp(): void{
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array{
        return[
            LarOTPServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        tap($app['config'], function ($config) {
            $config->set('database.default', 'testbench');
            $config->set('database.connections.testbench', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]);

            $config->set('larotp.key_length', 32);
            $config->set('larotp.digits', 6);
            $config->set('larotp.algo', 'sha1');
            $config->set('larotp.algoTOTP', 'sha512');
            $config->set('larotp.timestep', 30);
            $config->set('larotp.expirty_min', 10);
            
            $config->set('larotp.exempt_routes', []);
        });
    }

    protected function defineRoutes($router): void
    {
        // Define test routes
        $router->get('/login', function () {
            return 'Login Page';
        })->name('login');

        $router->get('/dashboard', function () {
            return 'Dashboard';
        })->name('dashboard')->middleware(['auth', 'otp.verify']);

        // OTP routes
        $router->get('/larotp/verify', function () {
            return 'OTP Verify';
        })->name('verify')->middleware('auth');

        $router->post('/larotp/verify-otp', function () {
            return 'OTP Verify Submit';
        })->name('verify.otp')->middleware('auth');
    }

    protected function setUpDatabase(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function createUser(array $attributes = []): \Illuminate\Foundation\Auth\User
    {
        return \App\Models\User::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'phone' => '0734567890',
        ], $attributes));
    }

    protected function getEnvironmentSetup($app){
        //
    }
}