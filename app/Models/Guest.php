<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'server_id', 'notes'];

    public function server()
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
