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
        'similar_assets',
    ];

    protected $casts = [
        'tags' => 'array',
        'ai_generated' => 'boolean',
        'similar_assets' => 'array',
    ];

    // Los metadatos pertenecen a un asset
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
