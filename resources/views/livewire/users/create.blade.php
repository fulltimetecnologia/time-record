<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Attributes\Rule;
use App\Helpers\CpfHelper;
use App\Helpers\MaskHelper;
use Illuminate\Support\Facades\Http;

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

    public function with(): array 
    {
        return [];
    }
    
    public function save(): void
    {
        $this->fetchAddressFromCep();

        if (!auth()->user()->is_admin) {
            session()->flash('message', __('users.unauthorized'));
            redirect('/users');
            return;
        }

        $data = $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'cpf' => ['required', function ($attribute, $value, $fail) {
                if (!CpfHelper::isValidCpf($value)) {
                    $fail(__('validation.cpf'));
                }
                if (User::where('cpf', $value)->exists()) {
                    $fail(__('validation.cpf_exists'));
                }
            }],
            'date_of_birth' => 'required|date',
            'cep' => 'required',
            'full_address' => 'required',
            'complement' => 'nullable',
            'position' => 'required',
        ]);

        if (auth()->user()->is_admin) {
            $data['user_admin_id'] = auth()->user()->id;
        }

        $data['user_admin_id'] = auth()->user()->id;
        $data['is_admin'] = false;

        $user = User::create($data);

        $this->success(__('users.success'), redirectTo: '/users');
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

    public function togglePasswordVisibility()
    {
        $this->showPassword = !$this->showPassword;
    }
};

?>

<div>
    <x-header title="{{ __('users.creating') }} " separator /> 
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