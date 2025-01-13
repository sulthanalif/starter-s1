{{--  modal --}}
<x-modal wire:model="myModal" class="backdrop-blur">
    <x-form wire:submit="save" class="relative" no-separator>
        <div class="">
            <h1>{{ $this->recordId == null ? 'Create User' : 'Detail User' }}</h1>
        </div>
        <div class="flex justify-center items-center">
            @if ($this->recordId != null)
            <div class="w-full lg:w-1/2 flex justify-center">
                <x-file wire:model="image" accept="image/png, image/jpeg, image/jpg, image/webp"
                    crop-after-change change-text="Change" crop-text="Crop" crop-title-text="Crop image"
                    crop-cancel-text="Cancel" crop-save-text="Crop" :crop-config="$config">
                    <img src="{{ asset($oldImage) }}"
                        class="h-40 rounded-lg" />
                </x-file>
            </div>
            @endif
        <div class="mb-5 rounded-lg p-6 w-full">
            <x-input hidden wire:model="recordId" />
            <x-input label="Name" icon="o-user" type="text" wire:model="name" inline autofocus />
            <div class="my-3"></div>
            <x-input label="Email" icon="o-envelope" type="email" wire:model="email" inline />
            <div class="my-3"></div>
            <x-password label="Password"  type="password" wire:model="password" inline hint="{{ $this->recordId == '' ? '' : 'Isi password jika ingin diubah' }}" />
            <div class="my-3"></div>
            <x-select label="Role" icon="o-user" :options="$roles" wire:model="role" placeholder="Select Role" placeholder-value="0" inline />
        </div>
        </div>
        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal = false" />
            <x-button label="{{ $this->recordId == null ? 'Save' : 'Update' }}" class="btn-primary" type="submit" spinner="save" />
        </x-slot:actions>
    </x-form>
</x-modal>
