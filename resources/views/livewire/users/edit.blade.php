<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Attributes\Rule;
use App\Helpers\CpfHelper;
use App\Helpers\MaskHelper;

new class extends Component {
    
    use Toast;

    public User $user;
    public string $name = ''; 
    public string $email = '';
    public string $password = '';
    public string $cpf = '';
    public string $date_of_birth = '';
    public string $cep = '';
    public string $full_address = '';
    public string $complement = '';
    public string $position = '';
    public bool $showPassword = false;

    public function mount(): void
    {

        $userArray = $this->user->toArray();
        if ($userArray['user_admin_id'] != auth()->user()->id && $userArray['id'] != auth()->user()->id) {
            session()->flash('message', __('users.unauthorized'));
            redirect('/users');
            return;
        }
        
        $componentProperties = [
            'name',
            'email',
            'password',
            'cpf',
            'date_of_birth',
            'cep',
            'full_address',
            'complement',
            'position',
            'is_admin',
            'user_admin_id'
        ];
        
        $filteredArray = array_filter(
            $userArray,
            fn($key) => in_array($key, $componentProperties),
            ARRAY_FILTER_USE_KEY
        );

        foreach ($filteredArray as $key => $value) {
            if (is_null($value)) {
                $filteredArray[$key] = '';
            }
        }

        $this->fill($filteredArray);
    }

    public function with(): array 
    {
        return [];
    }

    public function fetchAddressFromCep(): void
    {
        $cep = MaskHelper::remove($this->cep);
        $this->validate(['cep' => 'required']);
        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");
        if ($response->successful()) {
            $addressData = $response->json();
            $logradouro = $addressData['logradouro'] . ', ' ?? '';
            $bairro = $addressData['bairro'] . ', ' ?? '';
            $localidade = $addressData['localidade'] . ' - ' ?? '';
            $uf = $addressData['uf'] ?? '';
            $this->full_address = $logradouro . $bairro . $localidade . $uf;
            $this->complement = $addressData['complemento'] ?? '';
        } else {
            $this->error(__('validation.cep_invalid'));
        }
    }
    
    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'cpf' => ['required', function ($attribute, $value, $fail) {
                if (!CpfHelper::isValidCpf($value)) {
                    $fail(__('validation.cpf'));
                }
                if (User::where('cpf', $value)->where('id', '!=', $this->user->id)->exists()) {
                    $fail(__('validation.cpf_exists'));
                }
            }],
            'date_of_birth' => 'required|date',
            'cep' => 'required',
            'full_address' => 'required',
            'position' => 'required',
        ]);
    
        $this->user->update($data);

        $this->success(__('users.success_edit'), redirectTo: '/users');
    }

    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }
};

?>

<div>
    <x-header title="{{ __('users.editing') }} {{ $user->name }}" separator /> 
    <x-form wire:submit="save"> 
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="{{ __('users.basic') }}" subtitle="{{ __('users.basic_info') }}" size="text-2xl" />
            </div>
            <div class="col-span-3 grid gap-3">
                <x-input label="{{ __('users.name') }} *" wire:model="name" />
                <x-input label="{{ __('users.email') }} *" wire:model="email" />
                <div class="relative">
                    <x-input 
                        label="{{ __('users.password') }} *" 
                        type="{{ $showPassword ? 'text' : 'password' }}" 
                        wire:model="password"
                    />
                    <button 
                        type="button" 
                        wire:click="togglePasswordVisibility" 
                        class="absolute right-2 top-[65%] transform -translate-y-1/2"
                    >
                        <x-icon name="{{ $showPassword ? 'o-eye-slash' : 'o-eye' }}" />
                    </button>
                </div>
            </div>
        </div>
        <hr class="my-5" />
        <div class="lg:grid grid-cols-5">
            <div class="col-span-2">
                <x-header title="{{ __('users.detail') }}" subtitle="{{ __('users.details') }}" size="text-2xl" />
            </div>
            <div class="col-span-3 grid gap-3">
                <x-input label="{{ __('users.cpf') }} *" wire:model="cpf" x-mask="999.999.999-99"/>
                <x-input label="{{ __('users.date_of_birth') }} *" type="date" wire:model="date_of_birth" />
                <div class="relative">
                    <x-input label="{{ __('users.cep') }} *" wire:model="cep" x-mask="99.999-999" />
                    <button 
                        type="button" 
                        wire:click="fetchAddressFromCep" 
                        class="absolute right-2 top-[65%] transform -translate-y-1/2"
                    >
                         <x-icon name="o-magnifying-glass" />
                    </button>
                </div>
                <x-input label="{{ __('users.full_address') }} *" wire:model="full_address" />
                <x-input label="{{ __('users.complement') }}" wire:model="complement" />
                <x-input label="{{ __('users.position') }} *" wire:model="position" />
            </div>
        </div>
        <x-slot:actions>
            <x-button label="{{ __('users.cancel') }}" link="/users" />
            <x-button label="{{ __('users.save') }}" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>