<?php

use App\ManageDatas;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use Toast, WithPagination, ManageDatas, WithFileUploads;

    public string $search = '';

    public bool $drawer = false;
    public bool $myModal = false;

    //image
    public array $config = [
        'guides' => false,
        'aspectRatio' => 1, // Maintain square aspect ratio
    ];
    public ?UploadedFile $image = null;
    public string $oldImage = '';

    //var user
    public $recordId;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public string $phone = '';
    public string $address = '';
    public $roles = [];

    public array $varUser = ['recordId', 'name', 'email', 'password', 'role', 'roles', 'phone', 'address', 'oldImage', 'image'];

    //table
    public array $selected = [];
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];
    public int $perPage = 5;

    // Selected option
    public ?int $role_searchable_id = null;
    public ?array $permissions_multi_searchable_ids = [];

    // Options list
    public Collection $rolesSearchable;
    public Collection $permissionsMultiSearchable;

    //var Role Permission
    public string $roleName = '';
    public string $permissionName = '';

    public function mount()
    {
        // dump($this->role_searchable_id);
        // Fill options when component first renders
        $this->searchSelectRole();
        $this->searchSelectMultiPermission();
        $this->selectPermissions();
    }


    public function updatedRoleSearchableId($value)
    {
        $this->selectPermissions();
    }

    public function selectPermissions(string $value = ''): void
    {

        // Pastikan $this->role_searchable_id memiliki nilai
        if (!$this->role_searchable_id) {
            $this->permissions_multi_searchable_ids = [];
            $this->permissionsMultiSearchable = collect([]);
            return;
        }

        // Cari role berdasarkan ID
        $role = Role::find($this->role_searchable_id);

        if ($role) {
            // Ambil ID permissions dari role
            $this->permissions_multi_searchable_ids = $role->permissions->pluck('id')->toArray();
            $this->permissionsMultiSearchable = Permission::query()
            ->where('name', 'like', "%$value%")
            ->orderBy('name')
            ->get()
            ->merge($role->permissions);
        } else {
            // Jika role tidak ditemukan, set ke array kosong
            $this->permissions_multi_searchable_ids = [];
            $this->permissionsMultiSearchable = collect([]);
        }
    }

     // Also called as you type
     public function searchSelectRole(string $value = '')
     {
         // Besides the search results, you must include on demand selected option
         $selectedOption = Role::where('id', $this->role_searchable_id)->get();

         $this->rolesSearchable = Role::query()
             ->where('name', 'like', "%$value%")
             ->orderBy('name')
             ->get()
             ->merge($selectedOption);     // <-- Adds selected option
     }

     public function searchSelectMultiPermission(string $value = '')
     {
         // Besides the search results, you must include on demand selected option
         $selectedOptions = collect($this->permissions_multi_searchable_ids)
         ->map(fn(int $id) => Permission::where('id', $id)->first())
         ->filter()
         ->values();

         $this->permissionsMultiSearchable = Permission::query()
             ->where('name', 'like', "%$value%")
             ->orderBy('name')
             ->get()
             ->merge($selectedOptions);     // <-- Adds selected option
     }

    //options
    public function saveRole(): void
    {
        $this->setModel(new Role());

        $this->saveOrUpdate(
            validationRules: [
                'roleName' => ['required', 'string', 'max:255'],
            ],

            beforeSave: function ($role, $component) {
                $role->name = $component->roleName;
            },

            afterSave: function ($role, $component) {
                $permission = Permission::where('name', 'dashboard-page')->first();
                $role->permissions()->attach($permission);
            },
        );

        $this->unsetModel();
        $this->reset(['roleName']);
        $this->mount();
    }

    public function savePermission(): void
    {
        $this->setModel(new Permission());

        $this->saveOrUpdate(
            validationRules: [
                'permissionName' => ['required', 'string', 'max:255'],
            ],

            beforeSave: function ($permission, $component) {
                $permission->name = $component->permissionName;
            },
        );

        $this->updatePermissionSuperAdmin($this->permissionName);
        $this->reset(['permissionName']);
        $this->unsetModel();
        $this->mount();
    }

    public function saveRolePermission(): void
    {
        $this->validate([
            'role_searchable_id' => 'required',
            'permissions_multi_searchable_ids' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $role = Role::find($this->role_searchable_id);
            $role->syncPermissions($this->permissions_multi_searchable_ids);
            DB::commit();
            $this->success('Role updated.', position: 'toast-bottom');
            $this->drawer = false;
            $this->reset(['role_searchable_id', 'permissions_multi_searchable_ids']);
        } catch (\Throwable $th) {
            DB::rollBack();
            $this->warning('Will update role', $th->getMessage(), position: 'toast-bottom');
            $this->drawer = false;
        }
    }

    public function updatePermissionSuperAdmin(string $permissionName): void
    {
        $superadmin = Role::where('name', 'Super Admin')->first();
        $permission = Permission::where('name', $permissionName)->first();

        if ($superadmin) {
            $superadmin->permissions()->attach($permission);
            $superadmin->save();
        }
    }

    public function delete(): void
    {
        $this->setModel(new User());

        foreach ($this->selected as $userId) {
            $this->setRecordId($userId);
            $this->deleteData(
                beforeDelete: function ($id, $component) {
                    $user = User::find($id);
                    $user->roles()->detach();
                }
            );
        }
        $this->reset('selected');
        $this->unsetRecordId();
        $this->unsetModel();
    }

    public function edit($id): void
    {
        $this->reset($this->varUser);
        $user = User::with('roles')->find($id);

        $this->recordId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->roles->first()->id;
        $this->roles = Role::all();
        $this->oldImage = $user->image ? 'storage/'.$user->image : 'img/user-avatar.png';

        $this->myModal = true;
    }

    public function create(): void
    {
        $this->reset($this->varUser);
        // dump($this->oldImage);
        $this->roles = Role::all();
        $this->myModal = true;
    }

    public function save(): void
    {
        $this->setModel(new User());

        if ($this->recordId) {
            $this->saveOrUpdate(
                validationRules: [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->recordId],
                    'password' => ['nullable', 'string', 'min:8'],
                    'role' => ['required'],
                    'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
                ],

                beforeSave: function ($user, $component) {
                    $user->name = $component->name;
                    $user->email = $component->email;
                    if ($component->password) {
                        $user->password = Hash::make($component->password);
                    }
                    if ($component->image) {
                        if ($user->image) {
                            $component->deleteImage($user->image);
                        }
                        $path = $component->uploadImage($component->image, 'users');
                        $user->image = $path;
                    }
                },

                afterSave: function ($user, $component) {
                    $role = Role::find($component->role);
                    $user->roles()->detach();
                    $user->assignRole($role->name);
                }
            );
        } else {
            $this->saveOrUpdate(
                validationRules: [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                    'password' => ['required', 'string', 'min:8'],
                    'role' => ['required'],
                ],

                beforeSave: function ($user, $component) {
                    $user->name = $component->name;
                    $user->email = $component->email;
                    $user->password = Hash::make($component->password);
                },

                afterSave: function ($user, $component) {
                    $role = Role::find($component->role);
                    $user->assignRole($role->name);
                },
            );
        }

        $this->reset($this->varUser);
        $this->unsetModel();
        $this->myModal = false;
    }

    public function headers(): array
     {
         return [
             ['key' => 'id', 'label' => '', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Nama', 'class' => 'w-64'],
            ['key' => 'roles.0.name', 'label' => 'Role', 'class' => 'w-40', 'sortable' => false],
            ['key' => 'email', 'label' => 'E-mail']
         ];
     }

     public function datas(): LengthAwarePaginator
    {
        return User::query()
        ->with('roles') // Eager load roles
        ->where(function ($query) {
            $query->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhereHas('roles', function ($query) {
                    $query->where('name', 'like', "%{$this->search}%");
                });
        })
        ->where('id', '!=', Auth::id()) // Exclude current user
        ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
        ->paginate($this->perPage);
    }



    public function with(): array
    {
        return [
            'datas' => $this->datas(),
            'headers' => $this->headers()
        ];
    }

    /**
     * For demo purpose, this is a static collection.
     *
     * On real projects you do it with Eloquent collections.
     * Please, refer to maryUI docs to see the eloquent examples.
     */
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Data User" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            @can('user-create')
                <x-button label="Tambah" @click="$wire.create" responsive icon="o-plus" />
            @endcan
            @can('options')
                <x-button label="Options" @click="$wire.drawer = true" responsive icon="o-cog" />
            @endcan
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card>
        <x-table
        :headers="$headers"
        :rows="$datas"
        :sort-by="$sortBy"
        per-page="perPage"
        :per-page-values="[5, 10, 50]"
        wire:model.live="selected"
        selectable
        with-pagination
        >
            @scope('cell_id', $data)
                <x-avatar :image="$data->image ? asset('storage/'.$data->image) : asset('img/user-avatar.png')" class="!w-14 !rounded-lg" />
            @endscope
            @scope('cell_roles.0.name', $data)
                <x-badge :value="$data['roles'][0]['name']"
                    class="{{ $data['roles'][0]['name'] == 'Super Admin' ? 'badge-warning' : 'badge-primary' }}" />
            @endscope
            @scope('actions', $data)
            <x-button icon="o-eye" wire:click="edit({{ $data['id'] }})" class="btn-ghost btn-sm text-primary-500" />
            @endscope
            <x-slot:empty>
                <x-icon name="o-cube" label="It is empty." />
            </x-slot:empty>
        </x-table>
        @can('user-delete')
            @if ($this->selected)
                <div class="mt-2">
                    <x-button label="Hapus" icon="o-trash" wire:click="delete" spinner class="btn-ghost  text-red-500" wire:confirm="Are you sure?" wire:loading.attr="disabled" />
                </div>
            @endif
        @endcan
    </x-card>

    <!-- FILTER DRAWER -->
    @include('livewire.cms.users.options')

     {{-- modal --}}
     @include('livewire.cms.users.modal')
</div>
