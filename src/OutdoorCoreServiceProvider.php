<?php

namespace Summitooh\Module;


use Illuminate\Support\ServiceProvider;

class OutdoorCoreServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadCommands();
        $this->loadSettings();
    }

    public function register()
    {
        //
    }

    public function loadSettings()
    {
        //
    }

    public function loadCommands()
    {
        $this->commands([
            \Summitooh\Core\Commands\ModuleGenerator::class,
        ]);
    }
}
