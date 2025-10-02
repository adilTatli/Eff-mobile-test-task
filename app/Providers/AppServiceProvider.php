<?php

namespace App\Providers;

use App\Models\Status;
use App\Models\Task;
use App\Models\User;
use App\Policies\StatusPolicy;
use App\Policies\TaskPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Политика взаимодействия с пользователями
         */
        Gate::policy(User::class, UserPolicy::class);

        /**
         * Политика статусов задач
         */
        Gate::policy(Status::class, StatusPolicy::class);

        /**
         * Политика задач
         */
        Gate::policy(Task::class, TaskPolicy::class);
    }
}
