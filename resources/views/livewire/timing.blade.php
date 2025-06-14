<?php

use App\Events\LoadTimersEvent;
use App\Events\UpdateTimersEvent;
use App\Models\Athlete;
use App\Models\Timer;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {

	public string $element  = '';
	public string $category = '';
	public int    $runId    = 0;

	public function getListeners(): array
	{
		return [
			"echo:update.timers,UpdateTimersEvent"       => 'with',
			"echo:update.element.timers,LoadTimersEvent" => 'loadTimeTable',
			"echo:load-sprint,LoadSprintEvent"           => 'loadSprintTimer',
			"echo:cycle.started,CyclingStartedEvent"     => 'showCyclingStarted',
		];
	}

	public function setTimer(): void
	{
		broadcast(new LoadTimersEvent($this->element, $this->category));
	}

	public function loadSprintTimer($event): void
	{
		$this->category = '';
		$this->element  = $event['element'];
		$this->runId    = $event['id'];
	}

	public function loadTimeTable($event): void
	{
		$this->element  = $event['element'];
		$this->category = $event['category'];
		$this->runId    = 0;

		Storage::put('current.txt', json_encode(['run' => 0, 'element' => $this->element, 'category' => $this->category]));

		$this->modal('choose-element')->close();
	}

	public function showCyclingStarted($event): void
	{
		Flux::toast(
			variant: 'success',
			heading: 'Whooch!!',
			text: $event['athlete'] . ' is gestart met fietsen.',
		);
	}

	public function updatedElement(): void
	{
		if ($this->element === 'CYCLING')
		{
			$this->category = '';
		}
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
		     ->when($this->category, function ($query) {
			     $query->whereRelation('athlete', 'category', $this->category);
		     })
		     ->when($this->runId, function ($query) {
			     $query->where('run_id', $this->runId);
		     })
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

	public function reloadTimer(): void
	{
		$content = Storage::get('current.txt');
		$content = json_decode($content, true);

		$this->category = $content['category'] ?? '';
		$this->element  = $content['element'] ?? '';
		$this->run      = $content['run'] ?? 0;
	}

	public function with(): array
	{
		$timers = Timer::query()
		               ->with('athlete')
		               ->when($this->category, function ($query) {
			               $query->whereRelation('athlete', 'category', $this->category);
		               })
		               ->when($this->runId != 0, function ($query) {
			               $query->where('run_id', $this->runId);
		               })
		               ->where('element', $this->element)
		               ->orderBy('start_time')
		               ->orderByDesc('total')
		               ->get();

		$categories = Athlete::query()
		                     ->distinct()
		                     ->pluck('category')
		                     ->toArray();

		return [
			'timers'     => $timers,
			'categories' => $categories,
		];
	}
}; ?>

<div>
	@if(auth()->user()->role === 'admin')
		<flux:modal.trigger name="choose-element">
			<flux:button class="!bg-blue-700 w-full mb-4 cursor-pointer">Selecteer Onderdeel</flux:button>
		</flux:modal.trigger>

		@if(!empty($element) && $element !== 'CYCLING')
			<flux:modal.trigger name="massa-start-dialog">
				<flux:button class="!bg-green-700 w-full mb-4 cursor-pointer">Start Klaar maken</flux:button>
			</flux:modal.trigger>
		@endif
	@endif

	<flux:button wire:click="reloadTimer" class="!bg-indigo-700 w-full mb-4 cursor-pointer">Herladen</flux:button>

	@if(!$element)
		<flux:callout variant="secondary" icon="information-circle" heading="Wachten op starter.."/>
	@endif

	@foreach($timers as $timer)
		<livewire:time-details
				:timer="$timer"
				:element="$element"
				:category="$category"
				:key="$timer->id.'-'.md5(serialize($timer->toArray()))"
		/>
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

	<flux:modal name="choose-element" variant="flyout">
		<div class="space-y-6">
			<div>
				<flux:heading size="lg">Selecteer een onderdeel</flux:heading>
				<flux:text class="mt-2">Kies een onderdeel om te mee te starten en klik op inladen.</flux:text>
			</div>

			<flux:radio.group wire:model.live="element" variant="cards" class="max-sm:flex-col mb-5">
				<flux:radio value="CYCLING" label="Fietsen 5 km" class="!w-full"/>
				<flux:radio value="RUNNING_3KM" label="Hardlopen 3 km"/>
				<flux:radio value="INLINE_SKATING" label="Skeeleren Inline"/>
			</flux:radio.group>

			@if($element  !== 'CYCLING')
				<flux:select wire:model="category" variant="listbox" placeholder="Kies een Categorie">
					@foreach($categories as $category)
						<flux:select.option value="{{$category}}">{{$category}}</flux:select.option>
					@endforeach
				</flux:select>
			@endif

			<div class="flex">
				<flux:spacer/>
				<flux:button wire:click="setTimer" type="submit" variant="primary" class="!bg-green-600 w-full">
					<span class="text-white">Update Alle Timers</span>
				</flux:button>
			</div>
		</div>
	</flux:modal>
</div>
