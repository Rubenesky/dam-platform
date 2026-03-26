<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMetadata extends Model
{
    protected $fillable = [
        'asset_id',
        'title',
        'description',
        'tags',
        'ai_generated',
    ];

    // Convierte el campo JSON tags en array PHP automáticamente
    protected $casts = [
        'tags' => 'array',
        'ai_generated' => 'boolean',
    ];

    // Los metadatos pertenecen a un asset
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}