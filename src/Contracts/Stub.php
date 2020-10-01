<?php

namespace Summitooh\Module\Contracts;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait Stub
{

    protected $directories;

    protected $disk;

    protected $module;

    protected $modulePath;

    protected $mainModulePath;


    protected function isModule($module)
    {
        return in_array($module, $this->directories);
    }

    protected function build()
    {
        $this->buildModuleDirectories();
        $this->composer();
        $this->serviceProvider();
        $this->command();
        $this->controller();
        $this->request();
        $this->event();
        $this->observer();
        $this->model();
        $this->jobs();
        $this->config();
        $this->migrate();
        $this->routes();
        $this->view();
    }

    protected function buildModuleDirectories()
    {
        $module  = $this->module;

        if ($this->isModule($module)) {
            $this->info('Module is already exists.');
            exit();
        }

        $source =  strtolower($module) . '/src/';
        $this->disk->makeDirectory($source);
        $this->disk->makeDirectory($source . 'app/Commands');
        $this->disk->makeDirectory($source . 'app/Jobs');
        $this->disk->makeDirectory($source . 'app/Events');
        $this->disk->makeDirectory($source . 'app/Observers');
        $this->disk->makeDirectory($source . 'app/Http');
        $this->disk->makeDirectory($source . 'app/Http/Controllers');
        $this->disk->makeDirectory($source . 'app/Http/Requests');
        $this->disk->makeDirectory($source . 'config');
        $this->disk->makeDirectory($source . 'database/migrations');
        $this->disk->makeDirectory($source . 'database/seeds');
        $this->disk->makeDirectory($source . 'routes');
        $this->disk->makeDirectory($source . 'views');

        $this->info("{$this->module} module directory created successfuly.");
    }

    protected function getStub($type)
    {
        if (App::environment(['production', 'staging'])) {
            $vendorPath = 'vendor/summitooh/';
        } else {
            $vendorPath = 'modules/summitooh/';
        }

        return File::get(base_path($vendorPath . "module/src/Stubs/$type.stub"));
    }

    protected function composer()
    {
        $this->info('This will create a module package. Please provide the details for the module.');
        $description = $this->ask('Description');
        $name = $this->ask('Author');
        $email = $this->ask('Email');

        $composerTemplate = str_replace(
            [
                '{{moduleName}}',
                '{{moduleNameSingularLowerCase}}',
                '{{description}}',
                '{{name}}',
                '{{email}}',
            ],
            [
                $this->module,
                strtolower($this->module),
                $description,
                $name,
                $email
            ],
            $this->getStub('Composer')
        );

        $this->createStubToFile("../composer.json", $composerTemplate, true);
    }

    protected function model()
    {
        $modelTemplate = str_replace(
            [
                '{{moduleName}}',
                '{{moduleNamePluralLowerCase}}',
            ],
            [
                $this->module,
                strtolower(Str::plural($this->module)),
            ],
            $this->getStub('Model')
        );

        $this->createStubToFile("app/{$this->module}.php", $modelTemplate);
    }

    protected function config()
    {
        $moduleTemplate = str_replace(
            ['{{moduleName}}'],
            [$this->module],
            $this->getStub('Config')
        );

        $this->createStubToFile("config/" . strtolower($this->module) . ".php", $moduleTemplate);
    }


    protected function routes()
    {
        $routeTemplate = str_replace(
            ['{{moduleNameSingularLowerCase}}', '{{moduleName}}'],
            [strtolower(Str::kebab($this->module)), $this->module],
            $this->getStub('Route')
        );

        $this->createStubToFile("routes/web.php", $routeTemplate);
    }

    protected function controller()
    {
        $controllerTemplate = str_replace(
            [
                '{{moduleName}}',
                '{{moduleNamePluralLowerCase}}',
                '{{moduleNameSingularLowerCase}}'
            ],
            [
                $this->module,
                strtolower(Str::plural($this->module)),
                strtolower($this->module)
            ],
            $this->getStub('Controller')
        );

        $this->createStubToFile("app/Http/Controllers/{$this->module}Controller.php", $controllerTemplate);
    }

    protected function request()
    {
        $requestTemplate = str_replace(
            ['{{moduleName}}'],
            [$this->module],
            $this->getStub('Request')
        );

        $this->createStubToFile("app/Http/Requests/{$this->module}Request.php", $requestTemplate);
    }

    protected function observer()
    {
        $observerTemplate = str_replace(
            [
                '{{moduleName}}',
                '{{moduleNameSingularLowerCase}}'
            ],
            [
                $this->module,
                strtolower($this->module)
            ],
            $this->getStub('Observer')
        );

        $this->createStubToFile("app/Observers/{$this->module}Observer.php", $observerTemplate);
    }

    protected function serviceProvider()
    {
        $serviceProviderTemplate = str_replace(
            [
                '{{moduleName}}',
                '{{moduleNamePluralLowerCase}}',
                '{{moduleNameSingularLowerCase}}'
            ],
            [
                $this->module,
                strtolower(Str::plural($this->module)),
                strtolower($this->module)
            ],
            $this->getStub('ServiceProvider')
        );

        $this->createStubToFile("{$this->module}ServiceProvider.php", $serviceProviderTemplate);
    }

    protected function seed()
    {
        $this->info('Creating seeder file.');
        $seederTemplate = str_replace(
            [
                '{{moduleName}}'
            ],
            [
                $this->module
            ],
            $this->getStub('Seed')
        );

        $this->createStubToFile("database/seeds/{$this->module}TableSeeder.php", $seederTemplate);
    }

    protected function migrate()
    {

        $module = strtolower($this->module);
        $migrationPath = 'modules/summitooh/' . $module . '/src/database/migrations';
        $modulePlural = Str::plural($module);

        $this->info('Creating a migration scripts.');
        Artisan::call("make:migration create_{$module}_table --path={$migrationPath} --create={$modulePlural}");

        $this->seed();

        $this->info('Migration script created.');
    }

    protected function event()
    {
        $observerEvents = ['Creating', 'Created', 'Saving', 'Saved', 'Updating', 'Updated'];

        foreach ($observerEvents as $events) {
            $eventTemplate = str_replace(
                [
                    '{{className}}',
                    '{{moduleName}}',
                ],
                [
                    $this->module . $events,
                    $this->module,
                ],
                $this->getStub('Event')
            );
            $this->createStubToFile("app/Events/{$this->module}{$events}.php", $eventTemplate);
        }
    }

    protected function eventServiceProvider()
    {
        $hasEventServiceProvider = $this->anticipate('Do you wish to create event service provider (Yes or No)?', ['Yes', 'No']);

        if (strtolower($hasEventServiceProvider) === 'yes') {

            $eventServiceProviderTemplate = str_replace(
                [
                    '{{moduleName}}',
                ],
                [
                    $this->module,
                ],
                $this->getStub('EventServiceProvider')
            );

            $this->createStubToFile("{$this->module}EventServiceProvider.php", $eventServiceProviderTemplate);

            $this->eventSubscriber();
        }
    }

    protected function eventSubscriber()
    {
        $eventSubscriberTemplate = str_replace(
            [
                '{{moduleName}}',
            ],
            [
                $this->module,
            ],
            $this->getStub('EventSubscriber')
        );

        $this->createStubToFile("app/Listeners/{$this->module}EventSubscriber.php", $eventSubscriberTemplate);
    }

    protected function command()
    {
        $commandTemplate = str_replace(
            [
                '{{moduleName}}',
                '{{moduleNameSingularLowerCase}}'
            ],
            [
                $this->module,
                strtolower($this->module),
            ],
            $this->getStub('Command')
        );

        $this->createStubToFile("app/Commands/{$this->module}Command.php", $commandTemplate);
    }

    protected function jobs()
    {

        $jobTemplate = str_replace(
            [
                '{{moduleName}}',
                '{{moduleNameSingularLowerCase}}'
            ],
            [
                $this->module,
                strtolower($this->module),
            ],
            $this->getStub('Job')
        );

        $this->createStubToFile("app/Jobs/{$this->module}Job.php", $jobTemplate);
    }

    protected function view()
    {

        $viewTemplate = 'Welcome to ' . $this->module;

        $this->createStubToFile("views/index.blade.php", $viewTemplate);
    }

    protected function createStubToFile($file, $template, $mainDirectory = false)
    {
        $path =  $this->module . '//src//';

        $this->disk->put(
            $path . $file,
            $template
        );

        $this->info("$file created successfuly.");
    }

    protected function createToPath($file, $template)
    {
        $this->disk->put(
            $file,
            $template
        );

        $this->info("{$this->disk->getAdapter()->getPathPrefix()}{$file} created successfuly");
    }
}
