<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>VMStats</title>

        @vite('resources/css/app.css')
    @livewireStyles
    @fluxAppearance
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
    <main class="min-h-screen p-6 max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="xl">
                @isset($title)
                    {{ $title }}
                @else
                    VM Stats
                @endisset
            </flux:heading>
            <div class="flex items-center gap-4">
                @isset($headerActions)
                    {{ $headerActions }}
                @else
                    @if(request()->routeIs('home'))
                        <flux:button href="{{ route('user.index') }}" variant="filled">
                            Manage Users
                        </flux:button>
                    @elseif(request()->routeIs('user.index'))
                        <flux:button href="{{ route('home') }}" variant="filled">
                            Manage Servers
                        </flux:button>
                    @endif
                @endisset
                @auth
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <flux:button type="submit" variant="subtle">
                            Logout
                        </flux:button>
                    </form>
                @endauth
            </div>
        </div>

        <flux:separator class="mb-6" />

        {{ $slot }}

    </main>

    @fluxScripts
    @livewireScripts
        @vite('resources/js/app.js')
</body>
