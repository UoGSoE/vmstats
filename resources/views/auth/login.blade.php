<x-layouts.app>
    <div class="py-16 flex items-center justify-center">
        <div class="w-full max-w-md">
            <flux:card class="space-y-6">
                @if (config('sso.enabled', true))
                    <flux:button type="submit" variant="primary" class="w-full" href="{{ route('login.sso') }}">Sign in with SSO</flux:button>
                @else
                    <div>
                        <flux:heading size="lg">Sign in</flux:heading>
                        <flux:subheading>Enter your credentials to access your account.</flux:subheading>
                    </div>

                    @if ($errors->any())
                        <flux:callout variant="danger" icon="exclamation-triangle">
                            <flux:callout.heading>Unable to sign in</flux:callout.heading>
                            <flux:callout.text>
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </flux:callout.text>
                        </flux:callout>
                    @endif

                    <form method="POST" action="{{ route('login.local') }}" class="space-y-6">
                        @csrf

                        <flux:input
                            name="username"
                            id="username"
                            label="Username"
                            autocomplete="username"
                            placeholder="Username"
                            icon="user"
                        />

                        <flux:input
                            type="password"
                            name="password"
                            id="password"
                            label="Password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            viewable
                            icon="lock-closed"
                        />

                        <flux:button type="submit" variant="primary" class="w-full">Sign in</flux:button>
                    </form>
                @endif
            </flux:card>
        </div>
    </div>
</x-layouts.app>
