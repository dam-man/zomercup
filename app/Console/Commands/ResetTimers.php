<?php

namespace App\Console\Commands;

use App\Models\Sprint;
use App\Models\Timer;
use Illuminate\Console\Command;

class ResetTimers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset Timers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Resetting timers...');

		Timer::query()->update([
			'start_time' => null,
			'end_time' => null,
			'duration' => null,
			'start' => null,
			'end' => null,
			'total' => null,
		]);

	    Sprint::query()->update([
		    'completed_at' => null,
	    ]);

		$this->info('All timers have been reset successfully.');
    }
}
