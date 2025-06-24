<?php

use App\Models\Sprint;
use App\Models\Timer;
use Livewire\Volt\Component;

new class extends Component {

	public string $element  = 'SKEELER_SPRINT_1';
	public string $category = 'M1112';

	public function getListeners(): array
	{
		return [
			"echo:update.timers,UpdateTimersEvent" => 'with',
		];
	}

	public function with(): array
	{
		$category = $this->category;

		if (in_array($this->element, ['SKEELER_SPRINT_1', 'SKEELER_SPRINT_2', 'RUNNING_SPRINT_1', 'RUNNING_SPRINT_2']))
		{
			$results = Sprint::query()
			                 ->with([
					                 'athlete_one',
					                 'athlete_two',
				                 ]
			                 )
			                 ->where(function ($query) use ($category) {
				                 $query->whereRelation('athlete_one', 'category', $category)
				                       ->orWhereRelation('athlete_two', 'category', $category);
			                 })
			                 ->addSelect([
				                 'athlete_two_timer_time' => Timer::select('total')
				                                                  ->whereColumn('run_id', 'sprints.id')
				                                                  ->whereColumn('athlete_id', 'sprints.athlete_2')
				                                                  ->limit(1),
				                 'athlete_one_timer_time' => Timer::select('total')
				                                                  ->whereColumn('run_id', 'sprints.id')
				                                                  ->whereColumn('athlete_id', 'sprints.athlete_1')
				                                                  ->limit(1),
				                 'athlete_two_points'     => Timer::select('points')
				                                                  ->whereColumn('run_id', 'sprints.id')
				                                                  ->whereColumn('athlete_id', 'sprints.athlete_2')
				                                                  ->limit(1),
				                 'athlete_one_points'     => Timer::select('points')
				                                                  ->whereColumn('run_id', 'sprints.id')
				                                                  ->whereColumn('athlete_id', 'sprints.athlete_1')
				                                                  ->limit(1),
			                 ])
			                 ->whereElement($this->element)
			                 ->orderByDesc('id')
			                 ->get();
		}
		else
		{
			$results = Timer::query()
			                ->with('athlete')
			                ->whereElement($this->element)
			                ->whereRelation('athlete', 'category', $category)
			                ->orderByDesc('points')
			                ->get();
		}

		return [
			'results' => $results,
		];
	}

}; ?>

<div>

	<flux:select variant="listbox" wire:model.live="element" clearable placeholder="Onderdeel" class="w-full md:w-96 mb-4">
		<flux:select.option value="SKEELER_SPRINT_1">Skeeleren Sprint 1</flux:select.option>
		<flux:select.option value="SKEELER_SPRINT_2">Skeeleren Sprint 2</flux:select.option>
		<flux:select.option value="RUNNING_SPRINT_1">Hardlopen Sprint 1</flux:select.option>
		<flux:select.option value="RUNNING_SPRINT_2">Hardlopen Sprint 2</flux:select.option>
		<flux:select.option value="INLINE_SKATING">Inline Skating</flux:select.option>
		<flux:select.option value="RUNNING_3KM">Hardlopen</flux:select.option>
		<flux:select.option value="CYCLING">Fietsen</flux:select.option>
	</flux:select>

	<flux:select variant="listbox" wire:model.live="category" placeholder="Category" class="mb-3" clearable>
		<flux:select.option value="M0910">Meisjes 2009-2010</flux:select.option>
		<flux:select.option value="M1112">Meisjes 2011-2012</flux:select.option>
		<flux:select.option value="M1314">Meisjes 2013-2014</flux:select.option>
		<flux:select.option value="M1618">Meisjes 2016-2018</flux:select.option>
		<flux:select.option value="J2012">Jongens 2012</flux:select.option>
		<flux:select.option value="J1314">Jongens 2013-2014</flux:select.option>
		<flux:select.option value="J1518">Jongens 2015-2018</flux:select.option>
	</flux:select>


	@if($results->count() === 0)
		<div class="text-center text-gray-500 dark:text-gray-300">
			Geen sprints gevonden voor dit onderdeel. Selecteer de juiste sprint om de resultaten te bekijken.
		</div>
	@else
		@if(in_array($element, ['SKEELER_SPRINT_1', 'SKEELER_SPRINT_2', 'RUNNING_SPRINT_1', 'RUNNING_SPRINT_2']))
			@foreach($results as $index => $sprint)
				<div class="w-full flex mt-3">
					<div class="w-10 h-10 mt-3 mr-4 flex-shrink-0 {{$sprint->completed_at ? 'bg-green-800' : ''}} border border-b-gray-600 dark:border-white rounded-md flex items-center justify-center text-lg font-semibold">
						{{str_pad($index+1, 2, '0', STR_PAD_LEFT)}}
					</div>
					<div class="flex-1 text-sm">
						<div class="w-full h-6 mt-1">
							@if($sprint->athlete_one)
								<div class="float-right">
									@if($sprint->athlete_one_timer_time)
										<flux:badge size="sm" color="{{$sprint->athlete_one_timer_time < $sprint->athlete_two_timer_time ? 'green' : 'grey'}}">
											{{ $sprint->athlete_one_timer_time ?number_format($sprint->athlete_one_timer_time, 2, '.', '') : null}}
										</flux:badge>
									@endif
								</div>
								{{$sprint->athlete_one->name}}
								@if($sprint->athlete_one_points)
									<span class="text-xs text-green-700">+ {{$sprint->athlete_one_points}} punten</span>
								@endif
							@else
								-/-
							@endif
						</div>
						<flux:separator class="mb-1 mt-1"/>
						<div class="w-full h-6 ">
							@if($sprint->athlete_two)
								<div class="float-right">
									@if($sprint->athlete_two_timer_time)
										<flux:badge size="sm" color="{{$sprint->athlete_two_timer_time < $sprint->athlete_one_timer_time ? 'green' : 'grey'}}">
											{{ $sprint->athlete_two_timer_time ?number_format($sprint->athlete_two_timer_time, 2, '.', '') : null}}
										</flux:badge>
									@endif
								</div>
								{{$sprint->athlete_two->name}}
								@if($sprint->athlete_two_points)
									<span class="text-xs text-green-700">+ {{$sprint->athlete_two_points}} punten</span>
								@endif
							@else
								-/-
							@endif
						</div>
					</div>
				</div>
			@endforeach
		@else
			@foreach($results as $index => $result)
				<div class="w-full flex mt-3 border border-gray-600 p-3 rounded-md items-center">
					<div class="ml-3 mr-4 flex-shrink-0 rounded-md flex items-center justify-center text-lg font-semibold">
						{{$index+1}}
					</div>
					<div class="flex-1">
						<div class="float-right font-bold">{{$result->points}}</div>
						{{$result->athlete->name}}
					</div>
				</div>
			@endforeach
		@endif
	@endif

</div>
