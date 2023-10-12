<?php

namespace App\Console\Commands;

use App\Http\Controllers\GorbiloController;
use Illuminate\Console\Command;

class quikbot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bot:run';

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
//        (new BotController())->run();
        (new GorbiloController())->run();
        return null;
    }
}
