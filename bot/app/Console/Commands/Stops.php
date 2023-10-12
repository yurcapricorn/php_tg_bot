<?php

namespace App\Console\Commands;

use App\Http\Controllers\StopsController;
use Illuminate\Console\Command;

class Stops extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stops:clear {secid?}';

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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $secid = $this->argument('secid');
        (new StopsController())->clear($secid);
    }
}
