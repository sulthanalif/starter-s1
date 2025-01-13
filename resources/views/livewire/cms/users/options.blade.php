<x-drawer wire:model="drawer" title="Options" right separator with-close-button class="lg:w-1/3">
        <x-card title="Hak Akses" class="relative" no-separator>
            <x-form wire:submit="saveRolePermission">
                <div>
                    <x-choices
                    label="Role"
                    wire:model.live="role_searchable_id"
                    :options="$rolesSearchable"
                    placeholder="Search ..."
                    search-function="searchSelectRole"
                    no-result-text="Ops! Nothing here ..."
                    single
                    searchable />
                </div>

                <div class="mt-4">
                    <x-choices
                    label="Permissions"
                    wire:model.live="permissions_multi_searchable_ids"
                    :options="$permissionsMultiSearchable"
                    placeholder="Search ..."
                    search-function="searchSelectMultiPermission"
                    no-result-text="Ops! Nothing here ..."
                    multiple
                    searchable  />
                </div>
                <x-slot:actions>
                    <x-button label="Save" icon="o-check" class="btn-primary" type="submit" spinner="save" />
                </x-slot:actions>
            </x-form>
        </x-card>


        <x-card title="Tambah Role">
           <x-form wire:submit="saveRole">
                <div>
                    <x-input label="Masukan Nama Role.." type="text" wire:model="roleName" inline />
                </div>
                <x-slot:actions>
                    <x-button label="Save" icon="o-check" class="btn-primary" type="submit" spinner="save" />
                </x-slot:actions>
           </x-form>
        </x-card>

        <x-card title="Tambah Permission">
           <x-form wire:submit="savePermission">
                <div>
                    <x-input label="Masukan Nama Permission.." type="text" wire:model="permissionName" inline />
                </div>
                <x-slot:actions>
                    <x-button label="Save" icon="o-check" class="btn-primary" type="submit" spinner="save" />
                </x-slot:actions>
           </x-form>
        </x-card>
    </x-drawer>
