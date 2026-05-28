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
                    <flux:table.row wire:key="user-row-{{ $user->id }}" id="user-row-{{ $user->id }}">
                        <flux:table.cell>{{ $user->username }}</flux:table.cell>
                        <flux:table.cell>{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            @if (auth()->id() != $user->id)
                                <flux:modal.trigger :name="'delete-user-'.$user->id">
                                    <flux:button
                                        variant="danger"
                                        size="sm"
                                        icon="trash"
                                        inset="top bottom"
                                    />
                                </flux:modal.trigger>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>

                    @if (auth()->id() != $user->id)
                        <flux:modal :name="'delete-user-'.$user->id" class="md:w-[44rem]" wire:key="delete-user-modal-{{ $user->id }}">
                            <div class="space-y-6">
                                <flux:heading size="lg">Delete user</flux:heading>

                                @if ($user->tokens_count > 0)
                                    <flux:callout variant="danger" icon="exclamation-triangle">
                                        <flux:callout.heading>
                                            <strong>{{ $user->username }}</strong> owns {{ $user->tokens_count }} API {{ Str::plural('token', $user->tokens_count) }}
                                        </flux:callout.heading>
                                        <flux:callout.text>
                                            If you delete the user outright, those tokens will be revoked and any scripts using them will stop working.
                                        </flux:callout.text>
                                        <flux:callout.text>
                                            To keep field scripts working, transfer the tokens to another user first — the token values are preserved so nothing needs reconfiguring.
                                        </flux:callout.text>
                                    </flux:callout>

                                    <flux:select
                                        wire:model.live="transferTargets.{{ $user->id }}"
                                        label="Transfer tokens to"
                                    >
                                        <flux:select.option value="">Choose a user…</flux:select.option>
                                        @foreach ($users as $candidate)
                                            @if ($candidate->id !== $user->id)
                                                <flux:select.option value="{{ $candidate->id }}">{{ $candidate->username }}</flux:select.option>
                                            @endif
                                        @endforeach
                                    </flux:select>

                                    <div class="flex gap-3">
                                        <flux:button x-on:click="$flux.modal('delete-user-{{ $user->id }}').close()">
                                            Cancel
                                        </flux:button>
                                        <flux:spacer />
                                        <flux:button
                                            wire:click="confirmDeleteUser({{ $user->id }})"
                                            variant="danger"
                                        >
                                            Delete user + tokens
                                        </flux:button>
                                        <flux:button
                                            wire:click="transferTokensAndDeleteUser({{ $user->id }}, {{ $transferTargets[$user->id] ?? 'null' }})"
                                            variant="primary"
                                            :disabled="empty($transferTargets[$user->id] ?? null)"
                                        >
                                            Transfer then delete
                                        </flux:button>
                                    </div>
                                @else
                                    <flux:callout variant="warning" icon="exclamation-triangle">
                                        <flux:callout.heading>
                                            Delete <strong>{{ $user->username }}</strong>?
                                        </flux:callout.heading>
                                        <flux:callout.text>
                                            This cannot be undone.
                                        </flux:callout.text>
                                    </flux:callout>

                                    <div class="flex gap-3">
                                        <flux:button x-on:click="$flux.modal('delete-user-{{ $user->id }}').close()">
                                            Cancel
                                        </flux:button>
                                        <flux:spacer />
                                        <flux:button
                                            wire:click="confirmDeleteUser({{ $user->id }})"
                                            variant="danger"
                                        >
                                            Delete user
                                        </flux:button>
                                    </div>
                                @endif
                            </div>
                        </flux:modal>
                    @endif
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
