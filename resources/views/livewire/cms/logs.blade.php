<?php

use App\ManageDatas;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use Toast, ManageDatas;

    public string $search = '';

    public bool $myModal = false;
    public bool $myModalDelete = false;

    //table
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public int $perPage = 5;

    // properties
    public array $properties = [];
    public array $oldValues = [];
    public array $newValues = [];
    public array $fields = [];

    public function show($id): void
    {
        $this->recordId = $id;
        $activity = Activity::find($id);
        $this->properties = $activity->properties->toArray();
        $this->oldValues = $activity->properties['old'] ?? [];
        $this->newValues = $activity->properties['attributes'] ?? [];
        $this->fields = array_keys(array_merge($this->oldValues, $this->newValues));
        $this->myModal = true;
    }

    public function modalDelete(): void
    {
        $this->myModalDelete = true;
    }

    public function deleteAll(): void
    {

        DB::table('activity_log')->truncate();

        $this->success('All log records deleted.', position: 'toast-bottom');
        $this->myModalDelete = false;
    }

    public function datas(): LengthAwarePaginator
    {
        return Activity::query()
            ->with('causer')
            ->where(function ($query) {
                $query
                    ->where('log_name', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
                    ->orWhereHas('causer', function ($query) {
                        $query->where('name', 'like', "%{$this->search}%");
                    });
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function headers(): array
    {
        return [
            // ['key' => 'id', 'label' => 'ID'],
            ['key' => 'log_name', 'label' => 'Log Name'],
            ['key' => 'description', 'label' => 'Description'],
            ['key' => 'causer.name', 'label' => 'Causer'],
            ['key' => 'created_at', 'label' => 'Created At'],
        ];
    }

    public function with(): array
    {
        return [
            'datas' => $this->datas(),
            'headers' => $this->headers(),
        ];
    }
}; ?>

<div>
    <!-- HEADER -->
    <x-header title="Logs Activity" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            @can('log-delete')
                <x-button label="Clean Logs" @click="$wire.modalDelete" class="btn-ghost text-red-500" responsive icon="o-trash"  />
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
            >
            @scope('cell_causer.name', $data)
                {{ $data['causer']['name'] ?? 'System' }}
            @endscope
            @scope('actions', $data)
                <x-button icon="o-eye" wire:click="show({{ $data['id'] }})" class="btn-ghost btn-sm text-primary-500" />
            @endscope
            <x-slot:empty>
                <x-icon name="o-cube" label="It is empty." />
            </x-slot:empty>
        </x-table>
    </x-card>

    {{--  modal --}}
    <x-modal wire:model="myModal" title="Properties">

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th>Field</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                    </tr>
                </thead>
                <tbody class="mt-2">
                    @foreach ($fields as $field)
                        <tr>
                            <td>{{ $field }}</td>
                            <td>{{ $oldValues[$field] ?? '-' }}</td>
                            <td>{{ $newValues[$field] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </x-modal>
    <x-modal wire:model="myModalDelete" title="Warning!">
        <x-form wire:submit="deleteAll">

            <p>Are you sure you want to delete all log records?</p>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.myModalDelete = false" />
                <x-button label="Confirm" type="submit" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
