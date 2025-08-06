<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Username</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            
            <flux:table.rows>
                @foreach ($users as $user)
                    <flux:table.row :key="$user->id" id="user-row-{{ $user->id }}">
                        <flux:table.cell>{{ $user->username }}</flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            @if (auth()->id() != $user->id)
                                <flux:button 
                                    wire:click="deleteUser({{ $user->id }})"
                                    variant="danger"
                                    size="sm"
                                    icon="trash"
                                    inset="top bottom"
                                />
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>
    
    <div>
        <flux:card>
            <flux:heading size="lg" class="mb-6">Add User</flux:heading>
            
            <div class="mb-6">
                <flux:input 
                    wire:model="username"
                    label="Username"
                    placeholder="Username"
                >
                    <x-slot name="iconTrailing">
                        <flux:button 
                            wire:click="lookupUser"
                            variant="primary"
                            size="sm"
                            class="-mr-1"
                        >
                            Lookup
                        </flux:button>
                    </x-slot>
                </flux:input>
                
                @if ($error)
                    <flux:text variant="danger" class="mt-2">{{ $error }}</flux:text>
                @endif
            </div>
            
            <div class="space-y-4 mb-6">
                <flux:input 
                    wire:model="email"
                    label="Email"
                    placeholder="Email"
                    type="email"
                    :disabled="true"
                />
                
                <flux:input 
                    wire:model="forenames"
                    label="Forenames"
                    :disabled="true"
                />
                
                <flux:input 
                    wire:model="surname"
                    label="Surname"
                    :disabled="true"
                />
            </div>
            
            <flux:button 
                wire:click.prevent="createUser"
                variant="primary"
                :disabled="!$email"
                class="w-full"
            >
                Add User
            </flux:button>
        </flux:card>
    </div>
</div>
