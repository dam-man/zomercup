<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app.frontend')] class extends Component {

	public string $page = 'results';

}; ?>

<div>
	<flux:button href="{{ route('login') }}" class="w-full md:w-96 mt-4 mb-4" variant="primary">
		Inloggen
	</flux:button>

	<flux:radio.group wire:model.live="page" variant="segmented" size="sm" class="mb-3">
		<flux:radio value="results" label="Resultaten"/>
		<flux:radio value="points" label="Klassement"/>
	</flux:radio.group>

	@if($page === 'results')
		<livewire:frontend.sprints/>
	@else
		<livewire:frontend.points/>
	@endif

</div>
