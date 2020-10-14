<?php

namespace App\Providers;

use App\Contracts\SubjectContract;
use App\Contracts\UserContract;
use App\Repositories\SubjectRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    protected $repositories = [
        SubjectContract::class => SubjectRepository::class,
        UserContract::class => UserRepository::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
