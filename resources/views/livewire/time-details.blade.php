<?php

use App\Events\CyclingStartedEvent;
use App\Events\UpdateTimersEvent;
use App\Helpers\FastestTimeCalculator;
use App\Models\Timer;
use Carbon\Carbon;
use Livewire\Attributes\Reactive;
use Livewire\Volt\Component;

new class extends Component {

    public Timer $timer;

    public string $element;
    public string $category;

    public function start(Timer $timer): void
    {
        $timer->update(
                [
                        'start_time' => now(),
                        'end_time'   => null,
                        'start'      => round(microtime(true) * 1000),
                ]
        );

        broadcast(new UpdateTimersEvent);
        broadcast(new CyclingStartedEvent($timer->id))->toOthers();
    }

    public function stop(Timer $timer): void
    {
        $start = Carbon::parse($timer->start_time);
        $stop  = now();

        $end              = round(microtime(true) * 1000);
        $diffMilliseconds = $end - $timer->start;
        $diffSeconds      = $diffMilliseconds / 1000;

        $timer->update(
                [
                        'end_time' => $stop,
                        'duration' => $start->diffInSeconds($stop),
                        'end'      => $end,
                        'total'    => $diffSeconds,
                ]
        );

        (new FastestTimeCalculator())->calculate($timer->athlete_id, $timer->element, $this->category, $timer->run_id);

        broadcast(new UpdateTimersEvent);
    }
};
?>

<div>

    <flux:callout class="mt-2 relative">

        @if($timer->total)
            <div class="absolute top-4 right-4 p-2 text-green-500 text-3xl font-bold">
                {{$timer->duration_text}}
            </div>
        @endif

        <flux:callout.heading class="mt-n2">
            <span class="text-xl text-gray-600 dark:text-white font-extrabold">ID# {{$timer->athlete->start_no}}</span>
            <flux:badge size="sm" color="lime">{{$timer->element_text}}</flux:badge>
        </flux:callout.heading>

        <flux:callout.text>
            <span class="text-lg text-gray-700 dark:text-white font-extrabold">
                {{$timer->athlete->name}} - {{$timer->athlete->club}}
            </span>
        </flux:callout.text>

        <x-slot name="actions">
            @if($timer->start && !$timer->end)
                <flux:button wire:click="stop({{$timer->id}})" variant="danger" class="w-full">STOP</flux:button>
            @endif
            @if(!$timer->start && $element == 'CYCLING')
                <flux:button wire:click="start({{$timer->id}})" class="!bg-green-600 w-full">START</flux:button>
            @endif
        </x-slot>
    </flux:callout>

</div>
