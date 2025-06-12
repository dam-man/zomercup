<?php

use App\Models\Athlete;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {

	use WithPagination;

	public int $id= 0;

	public function getListeners(): array
	{
		return [
			"echo:update.timers,UpdateTimersEvent" => 'update',
		];
	}

	public function edit($id): void
	{
		$this->id = $id;

		$this->modal('athlete-form')->show();
	}

	public function add(): void
	{
		$this->id = 0;

		$this->modal('athlete-form')->show();
	}
	
	public function with(): array
	{
		$athletes = Athlete::query()->withCount('elements as elements')->orderBy('name')->paginate(50);

		return [
			'athletes' => $athletes,
		];
	}

};
?>

<div>
	<div class="w-full md:w-36 mb-5">
		<flux:button wire:click="add" size="sm" icon="plus" variant="primary">Toevoegen</flux:button>
	</div>

	<flux:table :paginate="$athletes">
		<flux:table.columns>
			<flux:table.column>Naam</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Startnummer</div>
			</flux:table.column>
			<flux:table.column>Vereniging</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Category</div>
			</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Onderdelen</div>
			</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Aangemaakt</div>
			</flux:table.column>
		</flux:table.columns>
		<flux:table.rows>
			@foreach($athletes as $athlete)
				<flux:table.row wire:click="edit({{$athlete->id}})" class="cursor-pointer">
					<flux:table.cell>
						{{ $athlete->name }}
					</flux:table.cell>
					<flux:table.cell>
						<div class="w-full text-center">
							#{{ $athlete->start_no }}
						</div>
					</flux:table.cell>
					<flux:table.cell>{{ $athlete->club }}</flux:table.cell>
					<flux:table.cell>
						<div class="w-full text-center">
							<flux:badge color="green" size="sm" inset="top bottom">{{ $athlete->category }}</flux:badge>
						</div>
					</flux:table.cell>
					<flux:table.cell variant="strong">
						<div class="w-full text-center">
							{{ $athlete->elements }}
						</div>
					</flux:table.cell>
					<flux:table.cell variant="strong">
						<div class="w-full text-center">
							{{ $athlete->created_at->format('d-m-Y H:i') }}
						</div>
					</flux:table.cell>
				</flux:table.row>
			@endforeach
		</flux:table.rows>
	</flux:table>

	<flux:modal name="athlete-form" variant="flyout" class="space-y-6">
		<livewire:athleteform :id="$id" :key="$id" />
	</flux:modal>
</div>
