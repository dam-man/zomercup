<?php

namespace App\Console\Commands;

use App\Events\LoadSprintEvent;
use Illuminate\Console\Command;

class StartSprint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sprint';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        broadcast(new LoadSprintEvent(2,'SKEELER_SPRINT_2'));
    }
}
