<?php

use App\Models\Point;
use Livewire\Volt\Component;

new class extends Component {

	public string $category = 'M1112';

	public function with(): array
	{
		$points = Point::query()->with(['athlete'])
		               ->whereRelation('athlete', 'category', $this->category)
		               ->groupBy('athlete_id')
		               ->selectRaw('athlete_id, SUM(points) as points')
		               ->orderByDesc('points')
		               ->get();

		return [
			'results' => $points,
		];
	}

}; ?>

<div>
	<flux:select variant="listbox" wire:model.live="category" placeholder="Category" class="mb-3" clearable>
		<flux:select.option value="M0910">Meisjes 2009-2010</flux:select.option>
		<flux:select.option value="M1112">Meisjes 2011-2012</flux:select.option>
		<flux:select.option value="M1314">Meisjes 2013-2014</flux:select.option>
		<flux:select.option value="M1618">Meisjes 2016-2018</flux:select.option>
		<flux:select.option value="J2012">Jongens 2012</flux:select.option>
		<flux:select.option value="J1314">Jongens 2013-2014</flux:select.option>
		<flux:select.option value="J1518">Jongens 2015-2018</flux:select.option>
	</flux:select>

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
</div>
