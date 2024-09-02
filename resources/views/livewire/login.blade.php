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


<div class="min-h-screen flex items-center justify-center">
    <div class="text-center order-last md:order-first">
        <img src="images/timeline.svg" width="300" class="mx-auto" alt="Timeline" />
        <x-card title="{{ __('menu.home') }}" class="px-8" shadow separator>
            <x-form wire:submit="login">
                <x-input label="{{ __('login.email') }}" wire:model="email" icon="o-envelope" inline />
                <x-input label="{{ __('login.password') }}" wire:model="password" type="password" icon="o-key" inline />
                <x-slot:actions>
                    <x-button label="{{ __('login.create_account') }}" class="btn-ghost" link="/register" />
                    <x-button label="{{ __('login.login') }}" type="submit" icon="o-paper-airplane" class="btn-primary text-white" spinner="login" />
                </x-slot:actions>
            </x-form>
        </x-card>
    </div>
</div>