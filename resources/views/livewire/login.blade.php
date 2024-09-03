<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.empty')]
#[Title('Login')]

class extends Component {

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public function mount()
    {
        if (auth()->user()) {
            return redirect('/');
        }
    }

    public function login()
    {
        $credentials = $this->validate();

        if (auth()->attempt($credentials)) {
            request()->session()->regenerate();
            return redirect()->intended('/');
        }

        $this->addError('email', __('login.credentials_error'));
        $this->addError('password', __('login.credentials_error'));
    }
} ?>

<div class="md:w-96 mx-auto mt-20">
    <div class="avatar-container">
        <img src="images/timeline.svg" width="250" class="mx-auto" alt="Timeline" />
    </div>
    <x-card title="Login" class="px-8" shadow separator>
        <div class="mb-8">
            Faça login para acessar o sistema de registro de ponto.
        </div>
        <x-form wire:submit="login">
            <x-input label="{{ __('login.email') }}" wire:model="email" icon="o-envelope" inline />
            <x-input label="{{ __('login.password') }}" wire:model="password" type="password" icon="o-key" inline />
            <x-slot:actions>
                <x-button label="{{ __('login.create_account') }}" class="btn-ghost" link="/register" />
                <x-button label="{{ __('login.login') }}" type="submit" icon="o-paper-airplane" class="btn-primary text-white" spinner="login" />
            </x-slot:actions>
        </x-form>
    </x-card>
    <style>
        .avatar-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
    </style>
</div>
