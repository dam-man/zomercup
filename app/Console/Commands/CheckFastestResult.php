<?php

namespace App\Console\Commands;

use App\Helpers\FastestTimeCalculator;
use App\Models\Timer;
use Illuminate\Console\Command;

class CheckFastestResult extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'app:check-fastest-result';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
	//	(new FastestTimeCalculator)->calculate(4, 'SKEELER_SPRINT_2', 'M12', 0);

//		$timers = Timer::query()
//		               ->where('element', 'SKEELER_SPRINT_2')
//		               ->orderBy('total')
//		               ->get();
//
//		foreach ($timers as $key => $timer)
//		{
//			$this->info($key . ' - Processing timer: ' . $timer->id);
//		}
	}
}
