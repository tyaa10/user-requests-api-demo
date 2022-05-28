<?php

namespace App\Providers;

use App\Repositories\Interfaces\IRequestsDao;
use App\Repositories\RequestsEloquentDao;
use Illuminate\Support\ServiceProvider;

class RequestsRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        /* $this->app->bind('IRequestsDao', function () {
            return new RequestsEloquentDao();
        }); */
        $this->app->bind(IRequestsDao::class, RequestsEloquentDao::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
