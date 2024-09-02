<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\WithFileUploads; 
use Livewire\Attributes\Rule; 

new class extends Component {
    
    use Toast, WithFileUploads;

    public User $user;

    #[Rule('required')] 
    public string $name = '';
 
    #[Rule('required|email')]
    public string $email = '';
    
    #[Rule('required')]
    public string $password = '';

    public bool $showPassword = false;

    public function with(): array 
    {
        return [];
    }
    
    public function save(): void
    {
        $data = $this->validate();

        $user = User::create($data);

        $this->success(__('users.success'), redirectTo: '/users');
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
                <x-input label="{{ __('users.name') }}" wire:model="name" />
                <x-input label="{{ __('users.email') }}" wire:model="email" />
                <div class="relative">
                    <x-input 
                        label="{{ __('users.password') }}" 
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
               
            </div>
        </div>
        <x-slot:actions>
            <x-button label="{{ __('users.cancel') }}" link="/users" />
            <x-button label="{{ __('users.save') }}" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
        </x-slot:actions>
    </x-form>
</div>
