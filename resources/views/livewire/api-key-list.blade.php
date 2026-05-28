<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Owner</flux:table.column>
                <flux:table.column>Last used</flux:table.column>
                <flux:table.column>Created</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($tokens as $token)
                    <flux:table.row wire:key="token-row-{{ $token->id }}">
                        <flux:table.cell>{{ $token->name }}</flux:table.cell>
                        <flux:table.cell>{{ $token->tokenable?->username ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'never' }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $token->created_at->format('Y-m-d') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:modal.trigger :name="'revoke-'.$token->id">
                                <flux:button
                                    variant="danger"
                                    size="sm"
                                    icon="trash"
                                    inset="top bottom"
                                />
                            </flux:modal.trigger>
                        </flux:table.cell>
                    </flux:table.row>

                    <flux:modal :name="'revoke-'.$token->id" class="md:w-[32rem]" wire:key="revoke-modal-{{ $token->id }}">
                        <div class="space-y-6">
                            <flux:heading size="lg">Revoke API token</flux:heading>

                            <flux:callout variant="danger" icon="exclamation-triangle">
                                <flux:callout.heading>
                                    You are about to revoke <em>{{ $token->name }}</em>
                                </flux:callout.heading>
                                <flux:callout.text>
                                    Currently owned by <strong>{{ $token->tokenable?->username ?? 'unknown' }}</strong>.
                                    Last used {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'never' }}.
                                </flux:callout.text>
                                <flux:callout.text>
                                    To keep field scripts working, you can transfer this token to another user instead of deleting it.
                                    Transferring preserves the token value, so no script needs reconfiguring.
                                </flux:callout.text>
                            </flux:callout>

                            <flux:select
                                wire:model="transferTargets.{{ $token->id }}"
                                label="Transfer to"
                                placeholder="Choose a user…"
                            >
                                @foreach ($users as $candidate)
                                    @if ($candidate->id !== $token->tokenable_id)
                                        <flux:select.option value="{{ $candidate->id }}">{{ $candidate->username }}</flux:select.option>
                                    @endif
                                @endforeach
                            </flux:select>

                            <div class="flex gap-3">
                                <flux:button x-on:click="$flux.modal('revoke-{{ $token->id }}').close()">
                                    Cancel
                                </flux:button>
                                <flux:spacer />
                                <flux:button
                                    wire:click="deleteToken({{ $token->id }})"
                                    variant="danger"
                                >
                                    Delete permanently
                                </flux:button>
                                <flux:button
                                    wire:click="transferToken({{ $token->id }}, {{ $transferTargets[$token->id] ?? 'null' }})"
                                    variant="primary"
                                    :disabled="empty($transferTargets[$token->id] ?? null)"
                                >
                                    Transfer
                                </flux:button>
                            </div>
                        </div>
                    </flux:modal>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

    <div>
        <flux:card>
            @if ($plainTextToken)
                <flux:heading size="lg" class="mb-4">Your new API token</flux:heading>

                <flux:callout variant="warning" icon="exclamation-triangle" class="mb-4">
                    <flux:callout.heading>Save this now — you will not see it again</flux:callout.heading>
                    <flux:callout.text>
                        This token is shown once and then only stored as a hash. Copy it somewhere safe before dismissing.
                    </flux:callout.text>
                </flux:callout>

                <flux:input
                    icon="key"
                    :value="$plainTextToken"
                    readonly
                    copyable
                    class="mb-4"
                />

                <flux:button
                    wire:click="dismissPlainTextToken"
                    variant="primary"
                    class="w-full"
                >
                    I've saved it — dismiss
                </flux:button>
            @else
                <flux:heading size="lg" class="mb-6">Create API Token</flux:heading>

                <div class="space-y-4 mb-6">
                    <flux:select wire:model="selectedUserId" label="Owner">
                        @foreach ($users as $user)
                            <flux:select.option value="{{ $user->id }}">{{ $user->username }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input
                        wire:model.live="tokenName"
                        label="Token name"
                        placeholder="e.g. kvm-host-42"
                    />
                </div>

                <flux:button
                    wire:click.prevent="createToken"
                    variant="primary"
                    :disabled="!$tokenName"
                    class="w-full"
                >
                    Create Token
                </flux:button>
            @endif
        </flux:card>
    </div>
</div>
