<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImportFarService;

class importerFar extends Command
{

    private $importfarService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:FarXML';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Far xml importer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ImportFarService $importfarService)
    {
        parent::__construct();
	$this->importfarService = $importfarService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
	$this->importfarService->farxmlparser("123");
    }
}
