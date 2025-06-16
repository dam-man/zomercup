<?php

use App\Models\Athlete;
use App\Models\Sprint;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {

	#[Reactive]
	public int $id = 0;

	public ?Athlete $athlete;

	public string $name     = '';
	public string $start_no = '';
	public string $club     = '';
	public string $category = '';
	public array  $elements = [];

	public function mount(): void
	{
		$this->athlete = Athlete::query()->find($this->id);

		$this->name     = $this->athlete->name ?? '';
		$this->start_no = $this->athlete->start_no ?? '';
		$this->club     = $this->athlete->club ?? '';
		$this->category = $this->athlete->category ?? '';

		if ($this->athlete)
		{
			$this->athlete->load('elements');

			foreach ($this->athlete->elements as $timer)
			{
				if (in_array($timer->element, ['CYCLING', 'RUNNING_3KM', 'INLINE_SKATING']))
				{
					$this->elements[] = $timer->element;
				}
			}
		}
	}

	public function store(): void
	{
		$validated = $this->validated();

		$this->athlete = Athlete::create($validated);

		if (count($this->elements))
		{
			foreach ($this->elements as $element)
			{
				$this->athlete->elements()->create(['element' => $element]);
			}
		}

		Flux::toast(
			text: 'Deelnemer is toegevoegd',
			heading: 'Succes',
			variant: 'success',
		);

		$this->modal('athlete-form')->close();
		$this->redirect(route('athletes.index'), navigate: true);
	}

	public function update(): void
	{
		$validated = $this->validated();

		$this->athlete->update($validated);

		$this->athlete->elements()->delete();

		if ( ! in_array('INLINE_SKATING', $this->elements))
		{
			Sprint::query()
			      ->where('athlete_1', $this->athlete->id)
			      ->update(['athlete_1' => null]);

			Sprint::query()
			      ->where('athlete_2', $this->athlete->id)
			      ->update(['athlete_2' => null]);
		}

		if (count($this->elements))
		{
			foreach ($this->elements as $element)
			{
				$this->athlete->elements()->create(['element' => $element]);
			}
		}

		Flux::toast(
			text: 'Deelnemer is bijgewerkt',
			heading: 'Succes',
			variant: 'success',
		);

		$this->modal('athlete-form')->close();
		$this->redirect(route('athletes.index'), navigate: true);
	}

	private function validated()
	{
		return $this->validate([
			'name'     => 'required|min:3',
			'start_no' => ['required', 'int', Rule::unique('athletes')->ignore($this->id)],
			'club'     => 'required|string|max:255',
			'category' => 'required|string|max:255',
			'elements' => 'array',
		]);
	}

};
?>

<div>
	<div class="mb-8">
		<flux:heading size="lg" class="mb-8">{{$id ? 'Bewerk ' . $athlete->name .' ('.$athlete->id.')' : 'Deelnemer Toevoegen'}}</flux:heading>

		<form wire:submit="{{$id ? 'update' : 'store'}}">

			<div class="space-y-6">

				<flux:input wire:model="name" label="Naam" placeholder="Naam"/>
				<flux:input wire:model="start_no" label="Startnummer" placeholder="Startnummer"/>

				<flux:select variant="listbox" wire:model="category" placeholder="Category" label="Categorie">
					<flux:select.option value="M0910">Meisjes 2009-2010</flux:select.option>
					<flux:select.option value="M1112">Meisjes 2011-2012</flux:select.option>
					<flux:select.option value="M1314">Meisjes 2013-2014</flux:select.option>
					<flux:select.option value="M1618">Meisjes 2016-2018</flux:select.option>
					<flux:select.option value="J2012">Jongens 2012</flux:select.option>
					<flux:select.option value="J1314">Jongens 2013-2014</flux:select.option>
					<flux:select.option value="J1518">Jongens 2015-2018</flux:select.option>
				</flux:select>

				<flux:select variant="listbox" wire:model="club" placeholder="Vereniging" label="Vereniging">
					<flux:select.option value="Kluners">Kluners Drachten</flux:select.option>
					<flux:select.option value="Ijsster">Ijsster Sneek</flux:select.option>
					<flux:select.option value="STD">STD Sint Nicolaasga</flux:select.option>
				</flux:select>

				<flux:checkbox.group wire:model.live="elements" label="Onderdelen">
					<flux:checkbox
							value="CYCLING"
							label="Fietsen"
							description="Neemt deel aan het fietsen."
					/>

					<flux:checkbox
							value="RUNNING_3KM"
							label="Hardlopen"
							description="Neemt deel aan hardlopen."
					/>
					<flux:checkbox
							value="INLINE_SKATING"
							label="Skeeleren"
							description="Neemt deel aan het skeeleren."
					/>
				</flux:checkbox.group>

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
