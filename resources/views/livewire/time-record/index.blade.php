<?php

use App\Models\User;
use App\Models\TimeRecord;
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
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];

    public function save(): void
    {
        $hasRecordStarted = TimeRecord::where('user_id', auth()->user()->id)->whereDate('start_at', date('Y-m-d'))->whereNull('end_at')->first();
        if (!$hasRecordStarted) {
            $timeRecord = TimeRecord::create([
                'user_id' => auth()->user()->id,
                'start_at' => now(),
            ]);
        } else {
            $hasRecordStarted->update([
                'end_at' => now(),
            ]);
        }
        $this->success(__('time-record.success'), position: 'toast-bottom');
    }

    public function clear(): void
    {
        $this->reset();
        $this->resetPage();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'user.name', 'label' => __('time-record.employee'), 'class' => 'w-64'],
            ['key' => 'created_at', 'label' => __('time-record.date'), 'sortable' => false],
            ['key' => 'start_at', 'label' => __('time-record.start'), 'sortable' => false],
            ['key' => 'end_at', 'label' => __('time-record.end'), 'sortable' => false],
        ];
    }

    public function timeRecords(): LengthAwarePaginator
    {
        // return DB::raw("SELECT tr.*, u.*
        // FROM time_records tr
        // JOIN users u ON tr.user_id = u.id
        // WHERE u.name LIKE '%".$this->search."%'
        // AND (".auth()->user()->is_admin." OR tr.user_id = ".auth()->user()->id.")
        // ORDER BY tr.created_at DESC
        // LIMIT 5 OFFSET 0"
        // );

        return TimeRecord::query()
            ->with('user')
            ->when($this->search, fn(Builder $q) => $q->where('user.name', 'like', "%$this->search%"))
            ->when(!auth()->user()->is_admin, fn(Builder $q) => $q->where('user_id', auth()->user()->id))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(5);
    }

    public function with(): array
    {
        return [
            'timeRecords' => $this->timeRecords(),
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
    <x-header title="{{ __('time-record.title') }}" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="{{__('screens.search')}}" wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="{{__('screens.filters')}}" @click="$wire.drawer = true" responsive icon="o-funnel" :badge="$this->countAppliedFilters()" />
            <x-button label="{{__('time-record.create')}}" wire:click="save" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>
    <x-card>
        <x-table :headers="$headers" :rows="$timeRecords" :sort-by="$sortBy" with-pagination />
    </x-card>
    <x-drawer wire:model="drawer" title="{{__('screens.filters')}}" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="{{__('screens.search')}}" wire:model.live.debounce="search" icon="o-magnifying-glass" @keydown.enter="$wire.drawer = false" />
            <x-input type="date" placeholder="{{__('time-record.start_date')}}" wire:model.live.debounce="start_date" icon="o-clock" @keydown.enter="$wire.drawer = false" />
            <x-input type="date" placeholder="{{__('time-record.end_date')}}" wire:model.live.debounce="end_date" icon="o-clock" @keydown.enter="$wire.drawer = false" />
        </div>
        <x-slot:actions>
            <x-button label="{{__('screens.reset')}}" icon="o-x-mark" wire:click="clear" spinner />
            <x-button label="{{__('screens.done')}}" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
