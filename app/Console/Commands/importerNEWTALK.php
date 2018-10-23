<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImportNEWTALKService;

class importerNEWTALK extends Command
{
    private $importnewtalkService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:NewtalkXML';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Newtalk xml importer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ImportNEWTALKService $importnewtalkService)
    {
        parent::__construct();
	$this->importnewtalkService = $importnewtalkService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
	$this->importnewtalkService->newtalkxmlparser("123");
    }
}
