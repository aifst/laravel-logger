<?php

namespace  Aifst\Logger;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Class LoggerServiceProvider
 * @package Aifst\Logger
 */
class LoggerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }

    /**
     * Publish migrations and config
     */
    protected function registerPublishables(): void
    {
        $this->publishes([
            __DIR__.'/../config/logger.php' => config_path('logger.php'),
        ], 'config');

        if (! class_exists('CreateLoggerTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_logger_table.php.stub' =>
                    database_path('migrations/'.date('Y_m_d_His', time()).'_create_logger_table.php'),
            ], 'migrations');
        }
    }
}
