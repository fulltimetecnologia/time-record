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

<div class="grid gap-8 md:grid-cols-3">
    <div class="col-start-2 col-end-2">
        <div class="text-center order-last md:order-first">
            <img src="images/timeline.svg" width="250" class="mx-auto" alt="Timeline" />
        </div>
        <x-card title="{{ __('menu.home')}}" class="px-8" shadow separator>
            <div class="mb-8">
                Faça login para acessar o sistema e registrar seu ponto.
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
        <div class="mt-5">
            <x-card shadow>
                <x-icon name="o-lifebuoy" class="w-10 h-10" />
                <div class="my-5">
                    Contato do suporte: tecnologia.fulltime@gmail.com
                </div>
            </x-card>
        </div>
    </div>
</div>
