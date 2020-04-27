<?php

namespace BotMan\Drivers\Vk\Providers;

use BotMan\Drivers\VK\VkDriver;
use Illuminate\Support\ServiceProvider;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Studio\Providers\StudioServiceProvider;

class VkServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (! $this->isRunningInBotManStudio()) {
            $this->loadDrivers();

            $this->publishes([
                __DIR__.'/../../stubs/vk.php' => config_path('botman/vk.php'),
            ]);

            $this->mergeConfigFrom(__DIR__.'/../../stubs/vk.php', 'botman.vk');
        }
    }

    protected function loadDrivers()
    {
        DriverManager::loadDriver(VkDriver::class);
    }

    protected function isRunningInBotManStudio()
    {
        return class_exists(StudioServiceProvider::class);
    }
}