<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">
        <x-nav sticky class="lg:hidden">
            <x-slot:brand>
                <x-app-brand />
            </x-slot:brand>
            <x-slot:actions>
                <label for="main-drawer" class="lg:hidden me-3">
                    <x-icon name="o-bars-3" class="cursor-pointer" />
                </label>
            </x-slot:actions>
        </x-nav>
        <x-main full-width>
            <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">
                <x-app-brand class="p-5 pt-3" />
                <x-menu activate-by-route>
                    @if($user = auth()->user())
                        <x-menu-separator />
                            <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                                <x-slot:actions>
                                    <x-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff" no-wire-navigate link="/logout" />
                                </x-slot:actions>
                            </x-list-item>
                        <x-menu-separator />
                    @endif
                    <x-menu-item title="{{ __('menu.home') }}" icon="o-sparkles" link="/" /> 
                    <x-menu-item title="{{ __('menu.users') }}" icon="o-users" link="/users" /> 
                </x-menu>
            </x-slot:sidebar>
            <x-slot:content>
                {{ $slot }}
            </x-slot:content>
        </x-main>
        <x-toast />
        <x-spotlight />  
    </body>
</html>
