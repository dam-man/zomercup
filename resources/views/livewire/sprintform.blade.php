<?php

use App\Models\Athlete;
use App\Models\Sprint;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {

	#[Reactive]
	public int $id = 0;

	public ?Sprint $sprint;

	public int|null $athlete_1 = null;
	public int|null $athlete_2 = null;
	public string   $element   = '';
	public bool     $isCouple  = false;

	public function mount(): void
	{
		$this->sprint = Sprint::query()->find($this->id);

		$this->athlete_1 = $this->sprint->athlete_1 ?? null;
		$this->athlete_2 = $this->sprint->athlete_2 ?? null;
		$this->element   = $this->sprint->element ?? '';
	}

	public function store()
	{
		$validated = $this->validated();

		$this->sprint = Sprint::create($validated);

		Flux::toast(
			text: 'Sprint is toegevoegd',
			heading: 'Succes',
			variant: 'success',
		);

		$this->modal('sprint-form')->close();
		$this->redirect(route('sprint.overview'), navigate: true);
	}

	public function update(): void
	{
		$validated = $this->validated();

		$this->sprint->update($validated);

		$this->sprint->completed_at = null;
		$this->sprint->save();

		Flux::toast(
			text: 'Sprint is bijgewerkt',
			heading: 'Succes',
			variant: 'success',
		);

		$this->modal('sprint-form')->close();
		$this->redirect(route('sprint.overview'), navigate: true);
	}

	private function validated()
	{
		return $this->validate([
			'athlete_1' => ['required_without:athlete_2', 'integer', 'nullable'],
			'athlete_2' => ['required_without:athlete_1', 'integer', 'nullable'],
			'element'   => ['required', 'string'],
		]);
	}

	public function with(): array
	{
		return [
			'athletes' => Athlete::all(),
		];
	}

}; ?>

<div>
	<div class="mb-8">
		<flux:heading size="lg" class="mb-8">{{$id ? 'Bewerk ' . $sprint->element_text . ' - Run: ' . $sprint->id : 'Toevoegen'}}</flux:heading>

		<form wire:submit="{{$id ? 'update' : 'store'}}">

			<div class="space-y-6">

				<flux:select variant="listbox" wire:model="element" placeholder="Onderdeel" label="Onderdeel">
					<flux:select.option value="SKEELER_SPRINT_1">Skeeleren Sprint 1</flux:select.option>
					<flux:select.option value="SKEELER_SPRINT_2">Skeeleren Sprint 2</flux:select.option>
					<flux:select.option value="RUNNING_SPRINT_1">Hardlopen Sprint 1</flux:select.option>
					<flux:select.option value="RUNNING_SPRINT_2">Hardlopen Sprint 2</flux:select.option>
				</flux:select>

				<flux:select variant="listbox" searchable wire:model="athlete_1" placeholder="Binnenbaan" label="Binnenbaan">
					@foreach($athletes as $athlete)
						<flux:select.option value="{{$athlete->id}}">{{$athlete->name}} ({{$athlete->start_no}})</flux:select.option>
					@endforeach
				</flux:select>

				<flux:select variant="listbox" searchable wire:model="athlete_2" placeholder="Buitenbaan" label="Buitenbaan">
					@foreach($athletes as $athlete)
						<flux:select.option value="{{$athlete->id}}">{{$athlete->name}} ({{$athlete->start_no}})</flux:select.option>
					@endforeach
				</flux:select>

			</div>

			<div class="flex mt-8">
				<flux:spacer/>
				<flux:button type="submit" class="!bg-green-600">
					{{$id ? 'Bijwerken' : 'Toevoegen'}}
				</flux:button>
			</div>

		</form>
	</div>
</div>
