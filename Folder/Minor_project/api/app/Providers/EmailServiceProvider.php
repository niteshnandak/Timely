<?php
 
namespace App\Providers;
 
use Illuminate\Support\ServiceProvider;
use App\Services\EmailService;
 
class EmailServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(EmailService::class, function ($app) {
            return new EmailService();
        });
    }
}