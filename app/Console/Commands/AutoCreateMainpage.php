<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoCreateMainpageService;

class AutoCreateMainpage extends Command
{
    private $autoCreateMainpageService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AutoCreateMainpageService $service)
    {
        parent::__construct();
	$this->autoCreateMainpageService = $service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
	$this->autoCreateMainpageService->createMainpage();
    }
}
