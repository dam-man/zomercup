<?php

namespace App\Console\Commands;

use App\Models\Athlete;
use App\Models\Point;
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
	public function handle(): void
	{
		// Getting all unique categories from athletes
		$categories = Athlete::query()->pluck('category')->unique()->toArray();

		$elements = [
			'RUNNING_SPRINT',
			'SKEELER_SPRINT',
			'CYCLING',
		];

		foreach ($elements as $element)
		{
			$this->getFastestResults($element);
		}

		foreach ($categories as $category)
		{
			foreach ($elements as $element)
			{
				$this->updatePointsByCategory($category, $element);
			}
		}

		$this->updatePointsTables();
	}

	public function updatePointsTables(): void
	{
		Point::query()->delete();

		$athletes = Athlete::query()->get();

		foreach ($athletes as $atlete)
		{
			$timers = Timer::query()
			               ->where('athlete_id', $atlete->id)
			               ->where('points', '>', 0)
			               ->orderByDesc('points')
			               ->take(3)
			               ->get();

			if ($timers)
			{
				foreach ($timers as $timer)
				{
					Point::query()
					     ->create([
						     'athlete_id' => $atlete->id,
						     'timer_id'   => $timer->id,
						     'points'     => $timer->points,
					     ]);
				}
			}
		}
	}

	public function updatePointsByCategory($category, $element = 'SKEELER_SPRINT'): void
	{
		Timer::query()
		     ->whereRelation('athlete', 'category', $category)
		     ->whereLike('element', $element . '%')
		     ->update(
			     ['points' => 0]
		     );

		$timers = Timer::query()
		               ->whereRelation('athlete', 'category', $category)
		               ->where('fastest', 1)
		               ->whereLike('element', $element . '%')
		               ->orderBy('total')
		               ->get();

		foreach ($timers as $index => $timer)
		{
			$timer->points = $this->getPointsForIndex($index);
			$timer->update();
		}
	}

	public function getFastestResults($element): void
	{
		$athletes = Athlete::query()->get();

		foreach ($athletes as $atlete)
		{
			Timer::query()
			     ->where('athlete_id', $atlete->id)
			     ->whereLike('element', $element . '%')
			     ->update(['fastest' => 0]);

			$timer = Timer::query()
			              ->where('athlete_id', $atlete->id)
			              ->whereLike('element', $element . '%')
			              ->orderBy('total')
			              ->first();

			if ($timer)
			{
				$timer->fastest = true;
				$timer->save();
			}
		}
	}

	public function getPointsForIndex($index): int|string
	{
		$points = [
			'10.1', '9', '8', '7', '6', '5', '4', '3', '2', '1',
		];

		return $points[$index] ?? 0;
	}

}
