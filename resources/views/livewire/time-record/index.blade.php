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

    const int PER_PAGE = 10;
    public string $search = '';
    public string $start_date = '';
    public string $end_date = '';
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
            ['key' => 'name', 'label' => __('time-record.employee'), 'class' => 'w-64'],
            ['key' => 'position', 'label' => __('time-record.position'), 'class' => 'w-64'],
            ['key' => 'age', 'label' => __('time-record.age'), 'sortable' => false],
            ['key' => 'admin', 'label' => __('time-record.admin'), 'sortable' => false],
            ['key' => 'date', 'label' => __('time-record.date'), 'sortable' => false],
            ['key' => 'start', 'label' => __('time-record.start'), 'sortable' => false],
            ['key' => 'end', 'label' => __('time-record.end'), 'sortable' => false],
        ];
    }

    /**
     * @return LengthAwarePaginator
     *
     * ### SQL Query PURO: ###
     *
     *   SELECT 
     *       u.id,
     *       u.name,
     *       u.email, 
     *       u.position, 
     *       TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) AS age,
     *       u2.name AS admin,
     *       DATE(tr.end_at) AS date,
     *       TIME(tr.start_at) AS start,
     *       TIME(tr.end_at) AS end
     *   FROM 
     *       users u
     *   INNER JOIN 
     *       time_records tr ON tr.user_id = u.id
     *   LEFT JOIN 
     *       users u2 ON u2.id = u.user_admin_id
     *   WHERE 
     *       u.name LIKE '%$this->search%' OR
     *       u.email LIKE '%$this->search%' OR
     *       u.position LIKE '%$this->search%' OR
     *       u2.name LIKE '%$this->search%' OR
     *       DATE(tr.end_at) LIKE '%$this->search%' OR
     *       TIME(tr.start_at) LIKE '%$this->search%' OR
     *       TIME(tr.end_at) LIKE '%$this->search%'
     *       TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) LIKE '%$this->search%';
     */
    public function timeRecords(): LengthAwarePaginator
    {
        $query = DB::table('users as u')
            ->selectRaw('
                u.id,
                u.name,
                u.email,
                u.position,
                TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) AS age,
                u2.name AS admin,
                DATE(tr.start_at) AS date,
                TIME(tr.start_at) AS start,
                TIME(tr.end_at) AS end
            ')
            ->join('time_records as tr', 'tr.user_id', '=', 'u.id')
            ->leftJoin('users as u2', 'u2.id', '=', 'u.user_admin_id')
            ->where(function ($query) {
                $search = $this->search;
                $query->where('u.name', 'like', "%$search%")
                    ->orWhere('u.email', 'like', "%$search%")
                    ->orWhere('u.position', 'like', "%$search%")
                    ->orWhere('u2.name', 'like', "%$search%")
                    ->orWhereRaw('DATE(tr.start_at) LIKE ?', ["%$search%"])
                    ->orWhereRaw('TIME(tr.start_at) LIKE ?', ["%$search%"])
                    ->orWhereRaw('TIME(tr.end_at) LIKE ?', ["%$search%"])
                    ->orWhereRaw('TIMESTAMPDIFF(YEAR, u.date_of_birth, CURDATE()) LIKE ?', ["%$search%"]);
            })
            ->when($this->start_date && $this->end_date, function ($query) {
                $query->whereBetween('tr.start_at', [$this->start_date, $this->end_date]);
            });

        return $query->paginate(self::PER_PAGE);
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
        if ($this->start_date) {
            $count++;
        }
        if ($this->end_date) {
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
