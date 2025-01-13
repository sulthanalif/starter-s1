<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Dashboard" separator progress-indicator>
        {{-- <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" />
        </x-slot:actions> --}}
    </x-header>

    <div class="py-4 rounded-b-xl grid md:grid-cols-4 gap-5">
        <x-stat title="Messages" value="44" icon="o-envelope" />
        <x-stat title="Messages" value="44" icon="o-envelope" />
        <x-stat title="Messages" value="44" icon="o-envelope" />
        <x-stat title="Messages" value="44" icon="o-envelope" />
    </div>
    <x-card>
        <div class="text-center">
            <div class="flex justify-center">
                <x-app-brand class=" px-5 pt-5" />
            </div>
            <p class="mt-4">Selamat Datang</p>
            <p class="">{{ auth()->user()->name }}</p>
        </div>
    </x-card>
</div>
