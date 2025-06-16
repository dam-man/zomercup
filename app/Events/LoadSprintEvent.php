<?php

namespace App\Events;

use App\Models\Sprint;
use App\Models\Timer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LoadSprintEvent implements ShouldBroadcastNow
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public int    $id;
	public string $element = '';

	public function __construct($id, $element)
	{
		$this->id      = $id;
		$this->element = $element;
	}

	public function broadcastOn(): array
	{
		$sprint = Sprint::query()
		                ->whereId($this->id)
		                ->first();

		$sprint->completed_at = null;
		$sprint->save();

		if ($sprint->athlete_1)
		{
			Timer::updateOrCreate(
				[
					'run_id'     => $this->id,
					'athlete_id' => $sprint->athlete_1,
				],
				[
					'element'    => $this->element,
					'lane'       => 'Inner Lane',
					'start'      => null,
					'end'        => null,
					'total'      => null,
					'start_time' => null,
					'end_time'   => null,
					'fastest'    => 0,
					'points'     => 0,
					'duration'   => 0,
				]
			);
		}

		if ($sprint->athlete_2)
		{
			Timer::updateOrCreate(
				[
					'run_id'     => $this->id,
					'athlete_id' => $sprint->athlete_2,
				],
				[
					'element'    => $this->element,
					'lane'       => 'Outer Lane',
					'start'      => null,
					'end'        => null,
					'total'      => null,
					'start_time' => null,
					'end_time'   => null,
					'fastest'    => 0,
					'points'     => 0,
					'duration'   => 0,
				]
			);
		}

		return [
			new Channel('load-sprint'),
		];
	}
}
