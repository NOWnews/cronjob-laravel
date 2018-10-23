<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImportCNYESService;

class importerCNYES extends Command
{
    private $importcnyesService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:CnyesXML';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cnyes xml importer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ImportCNYESService $importcnyesService)
    {
        parent::__construct();
	$this->importcnyesService = $importcnyesService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
	$this->importcnyesService->cnyesxmlparser("123");
    }
}
