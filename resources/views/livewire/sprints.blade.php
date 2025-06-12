<?php

use App\Events\LoadSprintEvent;
use App\Events\UpdateTimersEvent;
use App\Models\Sprint;
use App\Models\Timer;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {

	public int    $run     = 0;
	public string $element = '';

	public function getListeners(): array
	{
		return [
			"echo:update.timers,UpdateTimersEvent" => 'with',
		];
	}

	public function OpenRaceDialog($run): void
	{
		Storage::put('current.txt', json_encode(['run' => $run, 'element' => $this->element, 'category' => '']));

		$this->run = $run;

		broadcast(new LoadSprintEvent($run, $this->element));

		$this->modal('massa-start-dialog')->show();
	}

	public function massStart(): void
	{
		if (empty($this->element))
		{
			Flux::toast(
				variant: 'warning',
				heading: 'Selectie onjuist!',
				text: 'Selecteer een onderdeel/categorie om te starten.',
			);

			return;
		}

		$startTime = now();
		$start     = round(microtime(true) * 1000);

		Timer::query()
		     ->whereElement($this->element)
		     ->where('run_id', $this->run)
		     ->update(
			     [
				     'duration'   => null,
				     'total'      => null,
				     'start_time' => $startTime,
				     'end_time'   => null,
				     'end'        => null,
				     'start'      => $start,
			     ]
		     );

		$this->modal('massa-start-dialog')->close();

		broadcast(new UpdateTimersEvent);
	}

	public function with(): array
	{
		$sprints = Sprint::query()
		                 ->with(['athlete_one', 'athlete_two'])
		                 ->where('element', $this->element)
		                 ->orderBy('completed_at')
		                 ->orderBy('id')
		                 ->get();

		return [
			'sprints' => $sprints,
		];
	}
};

?>

<div>
{{--	<flux:radio.group wire:model.live="element" variant="segmented" class="mb-6">--}}
{{--		<flux:radio value="SKEELER_SPRINT_1" label="Skeeler 1"/>--}}
{{--		<flux:radio value="SKEELER_SPRINT_2" label="Skeeler 2"/>--}}
{{--		<flux:radio value="RUNNING_SPRINT_1" label="Running R1"/>--}}
{{--		<flux:radio value="RUNNING_SPRINT_2" label="Running R2"/>--}}
{{--	</flux:radio.group>--}}

	<flux:select variant="listbox" wire:model.live="element" clearable placeholder="Onderdeel" class="w-full md:w-96">
		<flux:select.option value="SKEELER_SPRINT_1">Skeeleren Sprint 1</flux:select.option>
		<flux:select.option value="SKEELER_SPRINT_2">Skeeleren Sprint 2</flux:select.option>
		<flux:select.option value="RUNNING_SPRINT_1">Hardlopen Sprint 1</flux:select.option>
		<flux:select.option value="RUNNING_SPRINT_2">Hardlopen Sprint 2</flux:select.option>
	</flux:select>

	@foreach($sprints as $sprint)
		<flux:card wire:click="OpenRaceDialog({{$sprint->id}})" class="hover:bg-zinc-50 dark:hover:bg-zinc-700 mb-3">
			<div class="w-full text-md text-center font-extrabold">
				<flux:badge size="sm" color="{{$sprint->completed_at ? 'red' : 'green'}}" class="mr-3">Inner Lane</flux:badge>
				@if($sprint->athlete_one)
					<flux:badge size="sm" color="{{$sprint->completed_at ? 'red' : 'green'}}">Start No. #{{ $sprint->athlete_one->start_no }}</flux:badge>
					<div class="mb-2">{{ $sprint->athlete_one->name }}</div>
				@else
					<div class="mb-2">-</div>
				@endif

				<flux:separator text="tegenstander" class="mb-6 mt-6"/>

				@if($sprint->athlete_two)
					<div class="mb-2">{{ $sprint->athlete_two->name }}</div>
				@else
					<div class="mb-2">-</div>
				@endif
				<flux:badge size="sm" color="{{$sprint->completed_at ? 'red' : 'indigo'}}" class="mr-3">Outer Lane</flux:badge>
				@if($sprint->athlete_two)
					<flux:badge size="sm" color="{{$sprint->completed_at ? 'red' : 'indigo'}}">
						Start No. #{{ $sprint->athlete_two->start_no }}
					</flux:badge>
				@endif
			</div>
		</flux:card>
	@endforeach

	<flux:modal name="massa-start-dialog" class="w-[22rem]">
		<div class="space-y-6">
			<div>
				<flux:heading size="lg">Start Timers?</flux:heading>
				<flux:text class="mt-2">
					<p>Als alle deelnemers klaar zijn, klik je op "Start Timing" om het onderdeel te starten."</p>
				</flux:text>
			</div>
			<div class="flex gap-2">
				<flux:spacer/>
				<flux:modal.close>
					<flux:button variant="danger">Annuleren</flux:button>
				</flux:modal.close>
				<flux:button wire:click="massStart" type="submit" class="!bg-green-600">
					<span class="text-white">Start Timing</span>
				</flux:button>
			</div>
		</div>
	</flux:modal>
</div>
