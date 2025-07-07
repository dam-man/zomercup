<?php

use App\Models\Athlete;
use App\Models\Timer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {

	#[Reactive]
	public int $id = 0;

	public ?Timer $timer;
	public int    $athlete_id = 0;
	public float  $total      = 0;
	public float  $points     = 0;
	public string $element    = '';

	private function validated()
	{
		return $this->validate([
			'athlete_id' => 'required|integer|exists:athletes,id',
			'element'    => [
				'required',
				'string',
				'max:255',
				Rule::unique('timers')
				    ->where(function ($query) {
					    return $query->where('athlete_id', $this->athlete_id);
				    })
				    ->ignore($this->id),
			],
			'points'     => 'nullable|numeric|min:0',
			'total'      => 'nullable|numeric|min:0',
		]);
	}

	protected function messages(): array
	{
		return [
			'athlete_id.required' => 'Deelnemer is verplicht.',
			'element.required'    => 'Onderdeel is verplicht.',
			'element.unique'      => 'Deelnemer heeft dit onderdeel al..',
			'total.numeric'       => 'Totaal tijd moet een getal zijn.',
			'total.min'           => 'Totaal tijd moet minimaal 0 zijn.',
			'points.numeric'      => 'Punten moeten een getal zijn.',
			'points.min'          => 'Punten moeten minimaal 0 zijn.',
		];
	}

	public function store(): void
	{
		$validated = $this->validated();

		try
		{
			Timer::create($validated);

			$this->modal('timer-form')->close();
			$this->redirect(route('timers.index'), navigate: true);

			Flux::toast(
				text: 'Toevoeging voltooid.',
				heading: 'Succes',
				variant: 'success',
			);
		}
		catch (\Exception $e)
		{
			Flux::toast(
				text: 'Er is iets mis gegaan bij het bijwerken van de tijd.',
				heading: 'Fout',
				variant: 'danger',
			);

			return;
		}
	}

	public function update()
	{
		$validated = $this->validated();

		try
		{
			$validated['total']  = str_replace(',', '.', $validated['total']);
			$validated['points'] = str_replace(',', '.', $validated['points']);

			$this->timer->update($validated);

			Flux::toast(
				text: 'Aanpassing voltooid.',
				heading: 'Succes',
				variant: 'success',
			);

			$this->modal('timer-form')->close();
			$this->redirect(route('timers.index'), navigate: true);
		}
		catch (\Exception $e)
		{
			Flux::toast(
				text: 'Er is iets mis gegaan bij het bijwerken van de tijd.',
				heading: 'Fout',
				variant: 'danger',
			);

			return;
		}
	}

	public function mount(): void
	{
		$this->timer = Timer::query()->whereId($this->id)->first();

		$this->athlete_id = $this->timer->athlete_id ?? 0;
		$this->total      = $this->timer->total ?? 0;
		$this->points     = $this->timer->points ?? 0;
		$this->element    = $this->timer->element ?? '';
	}

	public function with(): array
	{
		return [
			'timer'    => Timer::query()->with('athlete')->whereId($this->id)->first(),
			'athletes' => Athlete::orderBy('name')->get(),
		];
	}
};
?>

<div>
	<flux:heading size="lg">{{$id ? 'Bewerk: ' . $timer->element_text .' ('. $id.')' : 'Toevoegen'}}</flux:heading>

	@if($id && $timer)
		<flux:text class="mt-2 mb-8">{{$timer->athlete->name}}</flux:text>
	@else
		<flux:text class="mt-2 mb-8">Voeg een deelnemer toe</flux:text>
	@endif

	<form wire:submit="{{$id ? 'update' : 'store'}}">

		<div class="space-y-6">
			<flux:select variant="listbox" searchable wire:model="athlete_id" placeholder="Deelnemer" label="Deelnemer">
				@foreach($athletes as $athlete)
					<flux:select.option value="{{$athlete->id}}">{{$athlete->name}}</flux:select.option>
				@endforeach
			</flux:select>

			<flux:select variant="listbox" wire:model="element" placeholder="Onderdeel" label="Onderdeel">
				<flux:select.option value="CYCLING">Fietsen</flux:select.option>
				<flux:select.option value="RUNNING_3KM">Hardlopen</flux:select.option>
				<flux:select.option value="INLINE_SKATING">Inline Skeeleren</flux:select.option>
			</flux:select>

			<flux:input wire:model="total" label="Totaal Tijd in Seconden*" placeholder="Totaal Tijd in Seconden"/>
			<flux:input wire:model="points" label="Toegewezen Punten" placeholder="Toegewezen Punten"/>

			<flux:text class="mt-2 mb-8">* De totale tijd is ALTIJD in seconden!</flux:text>
		</div>

		<div class="flex mt-8">
			<flux:spacer/>
			<flux:button type="submit" class="!bg-green-600">
				{{$id ? 'Bijwerken' : 'Toevoegen'}}
			</flux:button>
		</div>

	</form>
</div>
