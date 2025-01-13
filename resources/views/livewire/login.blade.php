<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;

new class extends Component {
    use Toast;
    public string $email;
    public string $password;

    public function save(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        auth()->attempt($this->only('email', 'password'));

        if (auth()->check()) {
            $this->redirect(route('dashboard'));
        } else {
            $this->warning('Email atau password salah.', position: 'toast-bottom');
        }
    }
}; ?>

<div class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="rounded-lg shadow p-6 bg-base-100">
            <div class="flex w-full justify-center">
                <x-app-brand class="mb-4" />
            </div>
            {{-- <div class="font-bold text-2xl text-center">
                Login
            </div> --}}
            <div class="mt-4">
                <x-form wire:submit="save" no-separator>
                    <x-input label="Email" icon="o-user" type="email" wire:model="email" inline autofocus />
                    <x-password label="Password" type="password" wire:model="password" inline />

                    <x-slot:actions>
                        <x-button label="Cancel" />
                        <x-button label="Login" class="btn-primary" type="submit" spinner="save" />
                    </x-slot:actions>
                </x-form>
            </div>
        </div>
    </div>
</div>
