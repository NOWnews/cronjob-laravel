<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImportNOWService;

class importerNOW extends Command
{
    private $importnowService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:NowXML';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'NOWnews subweb xml importer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ImportNOWService $importnowService)
    {
        parent::__construct();
	$this->importnowService = $importnowService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
	$this->importnowService->nowxmlparser("123");
    }
}
