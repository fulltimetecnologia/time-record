<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;
 
new
#[Layout('components.layouts.empty')]
#[Title('Login')]

class extends Component {
 
    #[Rule('required')]
    public string $name = '';
 
    #[Rule('required|email|unique:users')]
    public string $email = '';
 
    #[Rule('required|confirmed')]
    public string $password = '';
 
    #[Rule('required')]
    public string $password_confirmation = '';
 
    public function mount()
    {
        if (auth()->user()) {
            return redirect('/');
        }
    }
 
    public function register()
    {
        $data = $this->validate();
 
        $data['avatar'] = '/empty-user.jpg';
        $data['password'] = Hash::make($data['password']);
 
        $user = User::create($data);
 
        auth()->login($user);
 
        request()->session()->regenerate();
 
        return redirect('/');
    }
}

?>

<div class="md:w-96 mx-auto mt-20">
    <div class="avatar-container">
        <x-avatar image="images/logo.png" class="!w-32" />
    </div>
    <x-form wire:submit="register">
        <x-input label="{{ __('register.name') }}" wire:model="name" icon="o-user" inline />
        <x-input label="{{ __('register.email') }}" wire:model="email" icon="o-envelope" inline />
        <x-input label="{{ __('register.password') }}" wire:model="password" type="password" icon="o-key" inline />
        <x-input label="{{ __('register.confirm_password') }}" wire:model="password_confirmation" type="password" icon="o-key" inline />
        <x-slot:actions>
            <x-button label="{{ __('register.already_registered') }}" class="btn-ghost" link="/login" />
            <x-button label="{{ __('register.register') }}" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="register" />
        </x-slot:actions>
    </x-form>
    <style>
        .avatar-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
    </style>
</div>
