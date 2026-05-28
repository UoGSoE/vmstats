<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('name', 'server_id', 'notes')]
class Guest extends Model
{
    use HasFactory;

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function hasNotes(): bool
    {
        return (bool) $this->notes;
    }

    public function getWikiLinkAttribute(): string
    {
        return config('vmstats.wiki_base_url').urlencode($this->name);
    }
}
