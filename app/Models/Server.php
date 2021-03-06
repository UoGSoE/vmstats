<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'notes'];

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function hasNotes(): bool
    {
        return (bool) $this->notes;
    }

    public function getWikiLinkAttribute(): string
    {
        return config('vmstats.wiki_base_url') . urlencode($this->name);
    }
}
