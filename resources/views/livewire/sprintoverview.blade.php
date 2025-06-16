<?php

use App\Models\Athlete;
use App\Models\Sprint;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('components.layouts.app')] class extends Component {

	use WithPagination;

	public int    $id       = 0;
	public string $filter   = '';
	public string $category = '';

	public function edit($id): void
	{
		$this->id = $id;

		$this->modal('sprint-form')->show();
	}

	public function add(): void
	{
		$this->id = 0;

		$this->modal('sprint-form')->show();
	}

	public function removeRecord($id)
	{
		Sprint::query()->whereId($id)->delete();

		Flux::toast(
			text: 'Sprint Verwijderd',
			heading: 'Succes',
			variant: 'success',
		);
	}

	public function with(): array
	{
		$categories = Athlete::query()
		                     ->where('category', '!=', '')
		                     ->distinct()
		                     ->pluck('category')
		                     ->toArray();

		$sprints = Sprint::query()
		                 ->with(['athlete_one', 'athlete_two'])
		                 ->when($this->filter, function ($query) {
			                 $query->where('element', $this->filter);
		                 })
		                 ->when($this->category, function ($query) {
			                 $query->whereRelation('athlete_one', 'category', $this->category);
		                 })
		                 ->orderByDesc('id')
		                 ->paginate(25);

		return [
			'sprints'    => $sprints,
			'categories' => $categories,
		];
	}

};
?>

<div>
	<div class="w-full md:w-36 mb-5">
		<flux:button wire:click="add" size="sm" icon="plus" variant="primary">Toevoegen</flux:button>
	</div>

	<div class="flex flex-wrap gap-4 mb-4">
		<flux:select variant="listbox" wire:model.live="filter" clearable placeholder="Onderdeel" class="w-full md:w-96">
			<flux:select.option value="SKEELER_SPRINT_1">Skeeleren Sprint 1</flux:select.option>
			<flux:select.option value="SKEELER_SPRINT_2">Skeeleren Sprint 2</flux:select.option>
			<flux:select.option value="RUNNING_SPRINT_1">Hardlopen Sprint 1</flux:select.option>
			<flux:select.option value="RUNNING_SPRINT_2">Hardlopen Sprint 2</flux:select.option>
		</flux:select>

		<flux:select variant="listbox" wire:model.live="category" clearable placeholder="Category" class="w-full md:w-96">
			@foreach($categories as $category)
				<flux:select.option value="{{$category}}">{{$category}}</flux:select.option>
			@endforeach
		</flux:select>
	</div>

	<flux:table :paginate="$sprints">
		<flux:table.columns>
			<flux:table.column>Run</flux:table.column>
			<flux:table.column>Element</flux:table.column>
			<flux:table.column>Binnenbaan</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Category</div>
			</flux:table.column>
			<flux:table.column>Buitenbaan</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Category</div>
			</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Voltooid</div>
			</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Aangemaakt</div>
			</flux:table.column>
			<flux:table.column>
				<div class="w-full text-center">Acties</div>
			</flux:table.column>
		</flux:table.columns>
		<flux:table.rows>
			@foreach($sprints as $sprint)
				<flux:table.row wire:click="edit({{$sprint->id}})" class="cursor-pointer">
					<flux:table.cell class="w-2">
						{{$sprint->id}}
					</flux:table.cell>
					<flux:table.cell>
						@if($sprint->element_text)
							{{$sprint->element_text}}
						@endif
					</flux:table.cell>
					<flux:table.cell>
						{{$sprint->athlete_one->name ?? ''}}
					</flux:table.cell>
					<flux:table.cell>
						<div class="w-full text-center">
							@if($sprint->athlete_one)
								<flux:badge color="green" size="sm" inset="top bottom">{{$sprint->athlete_one->category ?? ''}}</flux:badge>
							@endif
						</div>
					</flux:table.cell>
					<flux:table.cell>
						{{$sprint->athlete_two->name ?? ''}}
					</flux:table.cell>
					<flux:table.cell>
						<div class="w-full text-center">
							@if($sprint->athlete_two)
								<flux:badge color="green" size="sm" inset="top bottom">{{$sprint->athlete_two->category ?? ''}}</flux:badge>
							@endif
						</div>
					</flux:table.cell>
					<flux:table.cell variant="strong">
						<div class="w-full text-center">
							{{ !empty($sprint->completed_at) ? $sprint->completed_at : '-' }}
						</div>
					</flux:table.cell>
					<flux:table.cell variant="strong">
						<div class="w-full text-center">
							{{ $sprint->created_at->format('d-m-Y H:i') }}
						</div>
					</flux:table.cell>
					<flux:table.cell variant="strong">
						<div class="w-full text-center">
							<flux:button wire:click.stop="removeRecord({{$sprint->id}})" wire:confirm="Weet je dit zeker?" icon="trash"/>
						</div>
					</flux:table.cell>
				</flux:table.row>
			@endforeach
		</flux:table.rows>
	</flux:table>

	<flux:modal name="sprint-form" variant="flyout" class="space-y-6">
		<livewire:sprintform :id="$id" :key="$id"/>
	</flux:modal>
</div>
