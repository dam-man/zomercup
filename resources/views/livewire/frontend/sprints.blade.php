<?php

use App\Models\Sprint;
use App\Models\Timer;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app.frontend')] class extends Component {

	public string $element = 'SKEELER_SPRINT_1';

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
{{--	<a href="{{ route('login') }}"--}}
{{--	   class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal"--}}
{{--	>--}}
{{--		Log in--}}
{{--	</a>--}}

	<flux:button href="{{ route('login') }}" class="w-full md:w-96 mb-4"  variant="primary">
		Inloggen
	</flux:button>

	<flux:select variant="listbox" wire:model.live="element" clearable placeholder="Onderdeel" class="w-full md:w-96 mb-4">
		<flux:select.option value="SKEELER_SPRINT_1">Skeeleren Sprint 1</flux:select.option>
		<flux:select.option value="SKEELER_SPRINT_2">Skeeleren Sprint 2</flux:select.option>
		<flux:select.option value="RUNNING_SPRINT_1">Hardlopen Sprint 1</flux:select.option>
		<flux:select.option value="RUNNING_SPRINT_2">Hardlopen Sprint 2</flux:select.option>
	</flux:select>

	@if($sprints->count() === 0)
		<div class="text-center text-gray-500 dark:text-gray-300">
			Geen sprints gevonden voor dit onderdeel. Selecteer de juiste sprint om de resultaten te bekijken.
		</div>
	@else
		@foreach($sprints as $index => $sprint)
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
							{{$sprint->athlete_one->name}} <span class="text-xs"></span>
						@else
							-/-
						@endif
					</div>
					<flux:separator class="mb-1 mt-1"/>
					<div class="w-full h-6 ">
						@if($sprint->athlete_two)
							<div class="float-right">
								@if($sprint->athlete_two_timer_time)
									<flux:badge size="sm" color="{{$sprint->athlete_two_timer_time < $sprint->athlete_one_timer_time ? 'lime' : 'grey'}}">
										{{ $sprint->athlete_two_timer_time ?number_format($sprint->athlete_two_timer_time, 2, '.', '') : null}}
									</flux:badge>
								@endif
							</div>
							{{$sprint->athlete_two->name}} <span class="text-xs"></span>
						@else
							-/-
						@endif
					</div>
				</div>
			</div>
		@endforeach
	@endif
</div>
