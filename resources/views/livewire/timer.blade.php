<?php

use App\Models\Athlete;
use App\Models\Timer;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {

	use WithPagination;

	public int $id        = 0;
	public int $athleteId = 0;

	public function removeRecord($id): void
	{
		try
		{
			Timer::query()->whereId($id)->delete();

			Flux::toast(
				text: 'Tijd verwijderd',
				heading: 'Succes',
				variant: 'success',
			);
		}
		catch (\Exception $e)
		{
			Flux::toast(
				text: 'Er is iets mis gegaan bij het verwijderen van de tijd.',
				heading: 'Fout',
				variant: 'danger',
			);
		}
	}

	public function editRecord($id): void
	{
		$this->id = $id;

		$this->modal('timer-form')->show();
	}

	public function addRecord(): void
	{
		$this->id = 0;

		$this->modal('timer-form')->show();
	}

	public function calculate()
	{
		Artisan::call('app:check-fastest-result');
	}

	public function with(): array
	{
		$athleteId = $this->athleteId ?? 0;

		$timers = Timer::query()
		               ->with('athlete')
		               ->when($athleteId > 0, function ($query) use ($athleteId) {
			               $query->where('athlete_id', $athleteId);
		               })
		               ->paginate();

		return [
			'timers'   => $timers,
			'athletes' => Athlete::query()->orderBy('name')->get(),
		];
	}

};
?>

<div>
	<flux:heading size="lg" class="mb-8">Tijdswaarnemingen</flux:heading>

	<div class="flex gap-3 items-center mb-3">
		<flux:select variant="listbox" wire:model.live="athleteId" searchable clearable placeholder="Deelnemer" class="w-full md:w-96">
			@foreach($athletes as $athlete)
				<flux:select.option value="{{$athlete->id}}">{{$athlete->name}}</flux:select.option>
			@endforeach
		</flux:select>
		<flux:button wire:click="addRecord" icon="plus" variant="primary" class="!bg-green-600 text-white">Toevoegen</flux:button>
		<flux:button wire:click="calculate" icon="plus" variant="primary" class="!bg-blue-600 text-white">Update Klassement</flux:button>
	</div>

	<flux:table :paginate="$timers">
		<flux:table.columns>
			<flux:table.column>Naam</flux:table.column>
			<flux:table.column>
				Category
			</flux:table.column>
			<flux:table.column>Element</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Tijd</div>
			</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Snelste</div>
			</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Punten</div>
			</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Datum</div>
			</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Acties</div>
			</flux:table.column>
		</flux:table.columns>
		<flux:table.rows>
			@foreach($timers as $timer)
				<flux:table.row wire:click="editRecord({{$timer->id}})" class="cursor-pointer">
					<flux:table.cell>
						{{ $timer->athlete->name }}
					</flux:table.cell>
					<flux:table.cell>
						{{ $timer->athlete->category }}
					</flux:table.cell>
					<flux:table.cell>
						{{ $timer->element_text }}
					</flux:table.cell>
					<flux:table.cell>
						<div class="w-full text-center">
							{{ $timer->duration_text }}
						</div>
					</flux:table.cell>
					<flux:table.cell>
						<div class="w-full text-center">
							@if($timer->fastest)
								<flux:badge color="green" size="sm" inset="top bottom">Y</flux:badge>
							@else
								<flux:badge color="red" size="sm" inset="top bottom">N</flux:badge>
							@endif
						</div>
					</flux:table.cell>
					<flux:table.cell>
						<div class="w-full text-center">
							{{ $timer->points > 0 ? $timer->points : '-' }}
						</div>
					</flux:table.cell>
					<flux:table.cell>
						<div class="w-full text-center">
							{{ $timer->created_at->format('d-m-Y H:i') }}
						</div>
					</flux:table.cell>
					<flux:table.cell variant="strong">
						<div class="w-full text-center">
							<flux:button wire:click.stop="editRecord({{$timer->id}})" icon="pencil" class="mr-1"/>
							<flux:button
									wire:click.stop="removeRecord({{$timer->id}})"
									wire:confirm="Weet je dit zeker? Dit kan namelijk niet ongedaan gemaakt worden." icon="trash"
							/>
						</div>
					</flux:table.cell>
				</flux:table.row>
			@endforeach
		</flux:table.rows>
	</flux:table>

	<flux:modal name="timer-form" variant="flyout" class="space-y-6">
		<livewire:timer-form :id="$id" :key="$id"/>
	</flux:modal>
</div>
