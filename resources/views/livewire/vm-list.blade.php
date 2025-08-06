<div wire:poll.1m>
    <div class="mb-6 max-w-md">
        <flux:input 
            wire:model.live="filter" 
            placeholder="Search" 
            icon="magnifying-glass"
            autofocus
        />
    </div>
    
    <div class="w-full md:w-3/4 mx-auto">
        <div class="grid grid-cols-1 {{ $currentNotes ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }} gap-6">
            <div class="{{ $currentNotes ? 'lg:col-span-2' : '' }}">
                @foreach ($servers as $server)
                <div id="server-row-{{ $server->id }}" class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <flux:heading size="lg">
                                <a href="{{ $server->wiki_link }}" class="hover:text-zinc-600 dark:hover:text-zinc-400">
                                    {{ $server->name }}
                                </a>
                            </flux:heading>
                            @if ($server->hasNotes())
                                <flux:button 
                                    wire:click="setCurrentNotesServer({{ $server->id }})"
                                    variant="subtle"
                                    size="sm"
                                >
                                    Notes
                                </flux:button>
                            @endif
                        </div>
                        <flux:button 
                            wire:click="deleteServer({{ $server->id }})"
                            variant="danger"
                            size="sm"
                            icon="trash"
                            inset="top bottom"
                        />
                    </div>
                    
                    @if ($server->guests->count() > 0)
                        <flux:table class="mb-6">
                            <flux:table.rows>
                                @foreach ($server->guests as $guest)
                                    <flux:table.row :key="$guest->id" id="guest-row-{{ $guest->id }}">
                                        <flux:table.cell class="w-1/2">
                                            <a href="{{ $guest->wiki_link }}" class="hover:text-zinc-600 dark:hover:text-zinc-400">
                                                {{ $guest->name }}
                                            </a>
                                        </flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap {{ $guest->updated_at->isBefore(now()->subWeek()) ? 'text-red-600 dark:text-red-400' : '' }}">
                                            {{ $guest->updated_at->format('d/m/Y H:i') }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            @if ($guest->hasNotes())
                                                <flux:button 
                                                    wire:click="setCurrentNotes({{ $guest->id }})"
                                                    variant="{{ $guest->notes_filter_match ? 'primary' : 'subtle' }}"
                                                    size="sm"
                                                >
                                                    Notes
                                                </flux:button>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:button 
                                                wire:click="deleteGuest({{ $guest->id }})"
                                                variant="danger"
                                                size="sm"
                                                icon="trash"
                                                inset="top bottom"
                                            />
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    @else
                        <flux:text variant="muted" class="mb-6">No guests found for this server</flux:text>
                    @endif
                </div>
                @endforeach
            </div>
            
            <div class="lg:col-span-1">
                @if ($currentNotes)
                    <flux:card class="sticky top-4">
                        <flux:heading size="md">Notes for {{ $currentName }}</flux:heading>
                        <flux:text class="mt-4 font-mono text-sm">{!! $currentNotes !!}</flux:text>
                    </flux:card>
                @endif
            </div>
        </div>
    </div>
</div>
