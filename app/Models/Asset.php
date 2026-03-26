<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Asset extends Model
{
    protected $fillable = [
        'user_id',
        'original_name',
        'filename',
        'mime_type',
        'size',
        'path',
        'status',
    ];

    // Un asset pertenece a un usuario
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Un asset tiene un registro de metadatos
    public function metadata(): HasOne
    {
        return $this->hasOne(AssetMetadata::class);
    }

    // Un asset puede tener muchas categorías
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}