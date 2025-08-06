<x-layouts.app>
    <x-slot name="title">Login</x-slot>
    
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <flux:heading size="xl" class="mb-2">VMStats Login</flux:heading>
                <flux:text variant="muted">Enter your credentials to access the system</flux:text>
            </div>
            
            @error('authentication')
                <flux:callout variant="danger" icon="exclamation-triangle">
                    <strong>{{ $message }}</strong>
                </flux:callout>
            @enderror
            
            <flux:card class="mt-8">
                <form method="POST" action="{{ route('login.do') }}">
                    @csrf
                    
                    <div class="space-y-6">
                        <flux:input 
                            type="text"
                            name="username"
                            label="Username"
                            required
                            autofocus
                        />
                        @error('username')
                            <flux:text variant="danger" class="mt-1">{{ $message }}</flux:text>
                        @enderror
                        
                        <flux:input 
                            type="password"
                            name="password"
                            label="Password"
                            required
                        />
                        @error('password')
                            <flux:text variant="danger" class="mt-1">{{ $message }}</flux:text>
                        @enderror
                    </div>
                    
                    <flux:separator class="my-6" />
                    
                    <flux:button 
                        type="submit"
                        variant="primary"
                        class="w-full"
                    >
                        Log In
                    </flux:button>
                </form>
            </flux:card>
        </div>
    </div>
</x-layouts.app>
