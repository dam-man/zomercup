<?php

use App\Models\Sprint;
use App\Models\Timer;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app.frontend')] class extends Component {

	public string $element = '';

	public function getListeners(): array
	{
		return [
			"echo:update.timers,UpdateTimersEvent" => 'with',
		];
	}

	public function with(): array
	{
		$sprints = Sprint::query()
		                 ->with([
				                 'athlete_one',
				                 'athlete_two',
			                 ]
		                 )
		                 ->addSelect([
			                 'athlete_two_timer_time' => Timer::select('total')
			                                                  ->whereColumn('run_id', 'sprints.id')
			                                                  ->whereColumn('athlete_id', 'sprints.athlete_2')
			                                                  ->limit(1),
			                 'athlete_one_timer_time' => Timer::select('total')
			                                                  ->whereColumn('run_id', 'sprints.id')
			                                                  ->whereColumn('athlete_id', 'sprints.athlete_1')
			                                                  ->limit(1),
		                 ])
		                 ->whereElement($this->element)
		                 ->orderByDesc('id')
		                 ->get();

		return [
			'sprints' => $sprints,
		];
	}

}; ?>

<div>
	<flux:select variant="listbox" wire:model.live="element" clearable placeholder="Onderdeel" class="w-full md:w-96 mb-4">
		<flux:select.option value="SKEELER_SPRINT_1">Skeeleren Sprint 1</flux:select.option>
		<flux:select.option value="SKEELER_SPRINT_2">Skeeleren Sprint 2</flux:select.option>
		<flux:select.option value="RUNNING_SPRINT_1">Hardlopen Sprint 1</flux:select.option>
		<flux:select.option value="RUNNING_SPRINT_2">Hardlopen Sprint 2</flux:select.option>
	</flux:select>

	@foreach($sprints as $index => $sprint)
		<div class="w-full flex mt-3">
			<div class="w-13 h-13 mr-5 flex-shrink-0 border border-b-gray-600 dark:border-white rounded-md flex items-center justify-center text-xl font-semibold">
				{{$index+1}}
			</div>
			<div class="flex-1 text-sm">
				<div class="w-full h-6 mt-1">
					@if($sprint->athlete_one->name)
						<div class="float-right">
							@if($sprint->athlete_one_timer_time)
								<flux:badge size="sm" color="{{$sprint->athlete_one_timer_time < $sprint->athlete_two_timer_time ? 'lime' : 'grey'}}">
									{{ $sprint->athlete_one_timer_time ?number_format($sprint->athlete_one_timer_time, 2, '.', '') : null}}
								</flux:badge>
							@endif
						</div>
						{{$sprint->athlete_one->name}} <span class="text-xs">(Binnenbaan)</span>
					@else
						-/-
					@endif
				</div>
				<flux:separator class="mb-1 mt-1"/>
				<div class="w-full h-6 ">
					@if($sprint->athlete_one->name)
						<div class="float-right">
							@if($sprint->athlete_two_timer_time)
								<flux:badge size="sm" color="{{$sprint->athlete_two_timer_time < $sprint->athlete_one_timer_time ? 'lime' : 'grey'}}">
									{{ $sprint->athlete_two_timer_time ?number_format($sprint->athlete_two_timer_time, 2, '.', '') : null}}
								</flux:badge>
							@endif
						</div>
						{{$sprint->athlete_two->name}} <span class="text-xs">(Buitenbaan)</span>
					@else
						-/-
					@endif
				</div>
			</div>
		</div>
	@endforeach
</div>
