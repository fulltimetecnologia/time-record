<?php

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;
use Livewire\WithPagination; 
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    
    use Toast, WithPagination;

    public string $search = '';
    public bool $drawer = false;
    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function updated($property): void
    {
        if (! is_array($property) && $property != "") {
            $this->resetPage();
        }
    }

    public function clear(): void
    {
        $this->reset();
        $this->resetPage(); 
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    public function delete(User $user): void
    {
        $user->delete();
        $this->warning("$user->name deleted", 'Good bye!', position: 'toast-bottom');
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => __('users.filters'), 'class' => 'w-64'],
            ['key' => 'email', 'label' =>  __('users.email'), 'sortable' => false],
            ['key' => 'perfil', 'label' => __('users.perfil'), 'class' => 'w-32'], 
        ];
    }
 
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->when(!auth()->user()->is_admin, fn(Builder $q) => $q->where('id', auth()->user()->id))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(5);
    }

    public function with(): array
    {
        return [
            'users' => $this->users(),
            'headers' => $this->headers(),
        ];
    }

    public function countAppliedFilters(): int
    {
        $count = 0;
        if ($this->search) {
            $count++;
        }
        return $count;
    }

}; ?>

<div>
    <x-header title="{{ __('users.title') }}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="{{__('users.search')}}" wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="{{__('users.filters')}}" @click="$wire.drawer = true" responsive icon="o-funnel" :badge="$this->countAppliedFilters()" />
            @if (auth()->user()->is_admin)
                <x-button label="{{__('users.create')}}" link="/users/create" responsive icon="o-plus" class="btn-primary" /> 
            @endif
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination link="users/{id}/edit">
            <div class="flex gap-3 mt-3">
                @scope('cell_perfil', $user)                                                    
                    <x-badge value="{{ $user['is_admin'] ? 'Administrador' : 'Funcionário' }}" class="{{ $user['is_admin'] ? 'bg-green-500' : 'bg-blue-500' }} text-white" />
                @endscope
                @scope('actions', $user)
                    @if (auth()->user()->is_admin && $user['id'] == auth()->user()->id || $user['user_admin_id'] == auth()->user()->id)
                        <x-button icon="o-trash" wire:click="delete({{ $user['id'] }})" wire:confirm="{{__('users.confirm')}}" spinner class="btn-ghost btn-sm text-red-500" />
                    @endif
                @endscope
            </div>
        </x-table>
    </x-card>
    <x-drawer wire:model="drawer" title="{{__('users.filters')}}" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5"> 
            <x-input placeholder="{{__('users.search')}}" wire:model.live.debounce="search" icon="o-magnifying-glass" @keydown.enter="$wire.drawer = false" />
        </div>
        <x-slot:actions>
            <x-button label="{{__('users.reset')}}" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="{{__('users.done')}}" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
