<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImportCNAService;

class importerCNA extends Command
{
    private $importcnaService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:CnaXML';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cna xml importer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ImportCNAService $importcnaService)
    {
        parent::__construct();
	$this->importcnaService = $importcnaService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
	$this->importcnaService->cnaxmlparser("123");
    }
}
