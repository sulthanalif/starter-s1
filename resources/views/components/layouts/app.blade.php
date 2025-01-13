<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
</head>
<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">

    {{-- NAVBAR mobile only --}}
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

    {{-- MAIN --}}
    <x-main full-width>
        @if (!request()->routeIs('login') && $user = auth()->user())
            {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

        {{-- BRAND --}}
        <div class="flex justify-center">
            <x-app-brand class=" px-5 pt-5" />
        </div>

        {{-- MENU --}}
        <x-menu activate-by-route>

            {{-- User --}}
                {{-- <x-menu-separator /> --}}

                <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="bg-base-100 rounded px-5">
                    <x-slot:avatar>
                        @if (!$user->image)
                        <img src="{{ asset('img/user-avatar.png') }}"
                        class="w-10 rounded-lg" />
                        @else
                        <img src="{{ asset('storage/'.$user->image) }}"
                        class="w-10 rounded-lg" />
                        @endif
                    </x-slot:avatar>
                    <x-slot:actions>
                        <x-dropdown>
                            <x-slot:trigger>
                                <x-icon name="o-ellipsis-vertical" class="cursor-pointer"/>
                            </x-slot:trigger>

                            <x-menu-item title="Profile" icon="o-user-circle"  link="{{ route('profile') }}" wire:navigate />
                            <x-menu-item title="Logout" icon="o-power"  no-wire-navigate link="{{ route('logout') }}" />
                        </x-dropdown>
                    </x-slot:actions>
                </x-list-item>

                <x-menu-separator />

            @include('components.layouts.app-menu')
        </x-menu>
    </x-slot:sidebar>

        @endif
        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
</body>
</html>
