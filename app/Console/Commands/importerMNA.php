<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImportMNAService;

class importerMNA extends Command
{
    private $importmnaService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:MnaXML';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mna xml importer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ImportMNAService $importmnaService)
    {
        parent::__construct();
        $this->importmnaService = $importmnaService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
	$this->importmnaService->mnaxmlparser("123");
    }
}
