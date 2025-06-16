<?php

namespace App\Helpers;

use App\Models\Sprint;
use App\Models\Timer;

class FastestTimeCalculator
{
	public function calculate($athleteId, $element, $category = '', $run = 0): void
	{
		if ( ! $athleteId || ! $element)
		{
			return;
		}

		$element = str_replace(['_1', '_2'], '', $element);

		Timer::query()
		     ->where('athlete_id', $athleteId)
		     ->whereLike('element', $element . '%')
		     ->update(['fastest' => 0]);

		$fastestTimer = Timer::query()
		                     ->where('athlete_id', $athleteId)
		                     ->whereLike('element', $element . '%')
		                     ->whereNotNull('total')
		                     ->orderBy('total', 'asc')
		                     ->first();

		if ($fastestTimer)
		{
			$fastestTimer->update(['fastest' => 1]);
		}

		if ($run != 0)
		{
			$timers = Timer::query()
			               ->whereRunId($run)
			               ->whereNull('total')
			               ->count();

			if ($timers == 0)
			{
				Sprint::query()
				      ->whereId($run)
				      ->update([
						  'completed_at' => now()
				      ]);
			}
		}
	}
}