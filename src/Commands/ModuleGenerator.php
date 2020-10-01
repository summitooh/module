<?php

namespace Summitooh\Module\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Summitooh\Module\Contracts\Stub;

class ModuleGenerator extends Command
{
    use Stub;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->disk = Storage::disk('modules');
        $this->directories = ['image'];
        // find a way to check directories or list of modules
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->module = $this->argument('module');

        $this->build();
    }
}
